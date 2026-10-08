<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsFilterRequest;
use App\Services\Analytics\AnalyticsService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(AnalyticsFilterRequest $request, AnalyticsService $analytics): \Inertia\Response
    {
        $this->authorize('viewAnalytics');
        $filters = $request->filters();

        return Inertia::render('Analytics/Index', $analytics->report($request->user(), $filters));
    }

    public function export(AnalyticsFilterRequest $request, AnalyticsService $analytics): StreamedResponse
    {
        $this->authorize('viewAnalytics');
        $filters = $request->filters();
        $rows = $analytics->documentQuery($request->user(), $filters)->orderBy('d.created_at')->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Type', 'Control Number', 'Recipient', 'Status', 'Discount', 'Item Subtotal', 'Total', 'Created At']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->id, $row->type, $row->control_number, $row->recipient_name, $row->status, $row->discount, $row->item_subtotal, max(0, $row->item_subtotal - $row->discount), $row->created_at]);
            }
            fclose($handle);
        }, 'analytics.csv', ['Content-Type' => 'text/csv']);
    }
}
