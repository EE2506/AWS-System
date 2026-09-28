<?php

namespace App\Services\Analytics;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function __construct(private readonly DateBucket $dateBucket) {}

    public function report(User $viewer, array $filters): array
    {
        $cacheKey = 'analytics:'.($viewer->hasRole('admin') ? 'admin' : $viewer->id).':'.md5(serialize([
            $filters['from']->toDateTimeString(), $filters['to']->toDateTimeString(), $filters['types'], $filters['user_id'], $filters['compare'],
        ]));

        return Cache::remember($cacheKey, now()->addMinutes(5), fn () => $this->buildReport($viewer, $filters));
    }

    public function documentQuery(User $viewer, array $filters): Builder
    {
        $items = DB::table('document_items')
            ->select('document_id', DB::raw('COALESCE(SUM(total_cost), 0) as item_subtotal'))
            ->groupBy('document_id');

        $query = DB::table('documents as d')
            ->leftJoinSub($items, 'item_totals', 'item_totals.document_id', '=', 'd.id')
            ->whereBetween('d.created_at', [$filters['from'], $filters['to']])
            ->whereNull('d.deleted_at')
            ->select('d.*', DB::raw('COALESCE(item_totals.item_subtotal, 0) as item_subtotal'));

        if (! $viewer->hasRole('admin')) {
            $query->where('d.user_id', $viewer->id);
        } elseif (! empty($filters['user_id'])) {
            $query->where('d.user_id', $filters['user_id']);
        }

        if ($filters['types']) {
            $query->whereIn('d.type', $filters['types']);
        }

        return $query;
    }

    private function buildReport(User $viewer, array $filters): array
    {
        $base = $this->documentQuery($viewer, $filters);
        $totalExpression = 'CASE WHEN COALESCE(item_subtotal, 0) - discount < 0 THEN 0 ELSE COALESCE(item_subtotal, 0) - discount END';
        $types = Document::getTypes();

        $kpis = [
            'total_documents' => (clone $base)->count(),
            'total_billed' => (clone $base)->where('type', Document::TYPE_SOA)->sum(DB::raw($totalExpression)),
            'total_quoted' => (clone $base)->where('type', Document::TYPE_QUOTATION)->sum(DB::raw($totalExpression)),
            'total_discounts' => (clone $base)->whereIn('type', [Document::TYPE_SOA, Document::TYPE_QUOTATION])->sum('discount'),
            'by_type' => (clone $base)->select('type', DB::raw('COUNT(*) as count'))->groupBy('type')->pluck('count', 'type'),
            'average_by_type' => (clone $base)->select('type', DB::raw("AVG({$totalExpression}) as average"))->groupBy('type')->pluck('average', 'type'),
        ];

        $bucket = $this->granularity($filters['from'], $filters['to']);
        $bucketExpression = $this->dateBucket->expression('d.created_at', $bucket);
        $trendRows = (clone $base)->select(DB::raw("{$bucketExpression} as bucket"), 'type', DB::raw('COUNT(*) as count'), DB::raw("SUM(CASE WHEN type = 'soa' THEN {$totalExpression} ELSE 0 END) as billed"), DB::raw("SUM(CASE WHEN type = 'quotation' THEN {$totalExpression} ELSE 0 END) as quoted"))->groupBy(DB::raw($bucketExpression), 'type')->orderBy('bucket')->get();

        $clients = (clone $base)->select(DB::raw('LOWER(TRIM(recipient_name)) as name'), DB::raw('COUNT(*) as document_count'), DB::raw("SUM(CASE WHEN type = 'soa' THEN {$totalExpression} ELSE 0 END) as billed_value"))->whereNotNull('recipient_name')->where('recipient_name', '!=', '')->groupBy(DB::raw('LOWER(TRIM(recipient_name))'))->orderByDesc('billed_value')->limit(10)->get();
        $items = DB::table('document_items as i')->joinSub($base, 'scoped_documents', 'scoped_documents.id', '=', 'i.document_id')->select(DB::raw('LOWER(TRIM(COALESCE(i.name, i.description))) as name'), DB::raw('SUM(i.quantity) as quantity'), DB::raw('SUM(i.total_cost) as revenue'))->groupBy(DB::raw('LOWER(TRIM(COALESCE(i.name, i.description)))'))->orderByDesc('revenue')->limit(10)->get();
        $status = (clone $base)->select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->pluck('count', 'status');
        $weekday = (clone $base)->select(DB::raw($this->dateBucket->weekday('d.created_at').' as weekday'), DB::raw('COUNT(*) as count'))->groupBy(DB::raw($this->dateBucket->weekday('d.created_at')))->orderByDesc('count')->get();
        $hours = (clone $base)->select(DB::raw($this->dateBucket->hour('d.created_at').' as hour'), DB::raw('COUNT(*) as count'))->groupBy(DB::raw($this->dateBucket->hour('d.created_at')))->orderByDesc('count')->get();

        $sharing = $this->sharing($viewer, $filters);
        $people = $viewer->hasRole('admin') ? $this->people($viewer, $filters, $totalExpression) : null;
        $users = $viewer->hasRole('admin') ? User::query()->select('id', 'name')->orderBy('name')->get() : null;
        $quality = $this->quality($base);

        return compact('filters', 'types', 'users', 'kpis', 'bucket', 'trendRows', 'clients', 'items', 'status', 'weekday', 'hours', 'sharing', 'people', 'quality');
    }

    private function sharing(User $viewer, array $filters): array
    {
        $links = DB::table('public_links as l')->join('documents as d', 'd.id', '=', 'l.document_id')->whereBetween('l.created_at', [$filters['from'], $filters['to']])->whereNull('d.deleted_at');
        if (! $viewer->hasRole('admin')) {
            $links->where('d.user_id', $viewer->id);
        } elseif (! empty($filters['user_id'])) {
            $links->where('d.user_id', $filters['user_id']);
        }
        $now = now();

        return ['created' => (clone $links)->count(), 'active' => (clone $links)->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now))->count(), 'expired' => (clone $links)->whereNotNull('expires_at')->where('expires_at', '<=', $now)->count(), 'top_viewed' => (clone $links)->orderByDesc('access_count')->limit(10)->get(['d.id', 'd.control_number', 'l.access_count'])];
    }

    private function people(User $viewer, array $filters, string $totalExpression): array
    {
        $base = $this->documentQuery($viewer, $filters);

        return ['leaderboard' => (clone $base)->join('users as u', 'u.id', '=', 'd.user_id')->select('u.id', 'u.name', DB::raw('COUNT(*) as document_count'), DB::raw("SUM(CASE WHEN d.type = 'soa' THEN {$totalExpression} ELSE 0 END) as billed_value"))->groupBy('u.id', 'u.name')->orderByDesc('billed_value')->get(), 'registrations' => User::whereBetween('created_at', [$filters['from'], $filters['to']])->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))->groupBy(DB::raw('DATE(created_at)'))->orderBy('date')->get(), 'without_documents' => User::whereDoesntHave('documents')->count()];
    }

    private function quality(Builder $base): array
    {
        return ['missing_control_number' => (clone $base)->where(fn ($q) => $q->whereNull('control_number')->orWhere('control_number', ''))->count(), 'zero_items' => (clone $base)->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('document_items as qi')->whereColumn('qi.document_id', 'd.id'))->count(), 'discount_over_subtotal' => (clone $base)->whereRaw('discount > COALESCE(item_subtotal, 0)')->count(), 'duplicate_control_numbers' => (clone $base)->whereNotNull('control_number')->select('control_number')->groupBy('control_number')->havingRaw('COUNT(*) > 1')->count()];
    }

    private function granularity(Carbon $from, Carbon $to): string
    {
        return $from->diffInDays($to) > 180 ? 'month' : ($from->diffInDays($to) > 60 ? 'week' : 'day');
    }
}
