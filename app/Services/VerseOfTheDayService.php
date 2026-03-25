<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VerseOfTheDayService
{
    /**
     * Get a random verse of the day.
     * Caches the result for 12 hours to avoid hitting API rate limits.
     *
     * @return array
     */
    public function getVerse(): array
    {
        // Check cache first - if it exists, return immediately
        if (Cache::has('daily_verse')) {
            return Cache::get('daily_verse');
        }

        // Try to fetch from API with a short timeout
        try {
            $response = Http::timeout(2)->connectTimeout(1)->get('https://beta.ourmanna.com/api/v1/get', [
                'format' => 'json',
                'order' => 'random',
            ]);

            if ($response->successful()) {
                $data = $response->json('verse.details');
                $verse = [
                    'text' => $data['text'] ?? '',
                    'reference' => $data['reference'] ?? '',
                    'version' => $data['version'] ?? '',
                ];

                // Cache for 12 hours
                Cache::put('daily_verse', $verse, now()->addHours(12));
                return $verse;
            }
        } catch (\Exception $e) {
            // Fallback gracefully if API fails or times out
        }

        // Fallback verse if API fails
        $fallback = [
            'text' => 'For I know the plans I have for you," declares the LORD, "plans to prosper you and not to harm you, plans to give you hope and a future.',
            'reference' => 'Jeremiah 29:11',
            'version' => 'NIV',
        ];

        // Cache the fallback to prevent repeated failed API calls
        Cache::put('daily_verse', $fallback, now()->addMinutes(5));

        return $fallback;
    }
}
