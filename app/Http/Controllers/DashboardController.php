<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\VerseOfTheDayService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, VerseOfTheDayService $verseService)
    {
        $user = $request->user();

        // Scope query based on role
        // Admins see all documents, users see only their own
        $query = $user->hasRole('admin') ? Document::query() : $user->documents();

        // Calculate statistics
        // We clone the query to avoid modifying the base query for subsequent counts
        $totalDocuments = $query->count();
        $totalAmount = $query->sum('total_amount');

        $draftsQuery = (clone $query)->where('status', 'draft');
        $draftsCount = $draftsQuery->count();
        $draftsAmount = $draftsQuery->sum('total_amount');

        $finalizedQuery = (clone $query)->where('status', 'finalized');
        $finalizedCount = $finalizedQuery->count();
        $finalizedAmount = $finalizedQuery->sum('total_amount');

        $paidQuery = (clone $query)->where('status', 'paid');
        $paidCount = $paidQuery->count();
        $paidAmount = $paidQuery->sum('total_amount');

        $isAdmin = $user->hasRole('admin');

        $stats = [
            'total_documents' => $totalDocuments,
            'total_amount' => $isAdmin ? $totalAmount : 0,
            'drafts' => $draftsCount,
            'drafts_amount' => $isAdmin ? $draftsAmount : 0,
            'finalized' => $finalizedCount,
            'finalized_amount' => $isAdmin ? $finalizedAmount : 0,
            'paid' => $paidCount,
            'paid_amount' => $isAdmin ? $paidAmount : 0,
            'is_admin' => $isAdmin, // Pass this to frontend
        ];

        return Inertia::render('Dashboard', [
            'verse' => $verseService->getVerse(),
            'stats' => $stats,
        ]);
    }
}
