<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class AnalyticsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'preset' => ['nullable', 'in:today,7d,30d,this_month,last_month,this_year,custom'],
            'from' => ['nullable', 'date', 'required_if:preset,custom'],
            'to' => ['nullable', 'date', 'after_or_equal:from', 'required_if:preset,custom'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string', 'in:'.implode(',', array_keys(Document::getTypes()))],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'compare' => ['nullable', 'boolean'],
        ];
    }

    public function filters(): array
    {
        $preset = $this->string('preset')->toString() ?: '30d';
        $now = now();
        $to = $this->date('to')?->endOfDay() ?? $now->copy()->endOfDay();

        $from = match ($preset) {
            'today' => $now->copy()->startOfDay(),
            '7d' => $now->copy()->subDays(6)->startOfDay(),
            'this_month' => $now->copy()->startOfMonth(),
            'last_month' => $now->copy()->subMonthNoOverflow()->startOfMonth(),
            'this_year' => $now->copy()->startOfYear(),
            'custom' => $this->date('from')?->startOfDay() ?? $now->copy()->subDays(29)->startOfDay(),
            default => $now->copy()->subDays(29)->startOfDay(),
        };

        if ($preset === 'last_month') {
            $to = $now->copy()->subMonthNoOverflow()->endOfMonth();
        }

        return [
            'preset' => $preset,
            'from' => $from,
            'to' => $to,
            'types' => array_values(array_filter((array) $this->input('types', []))),
            'user_id' => $this->user()?->hasRole('admin') ? $this->integer('user_id') : null,
            'compare' => $this->boolean('compare'),
        ];
    }
}
