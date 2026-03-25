<?php

namespace App\Http\Controllers;

use App\Models\PublicLink;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicDocumentController extends Controller
{
    /**
     * Display the public document view.
     */
    public function show(string $token): Response
    {
        $link = PublicLink::where('token', $token)
            ->with(['document.items', 'document.user'])
            ->firstOrFail();

        // Check if link is valid
        if ($link->isExpired()) {
            return Inertia::render('Public/DocumentExpired');
        }

        // Record access
        $link->recordAccess();

        // Calculate days remaining
        $daysRemaining = $link->expires_at
            ? max(0, (int) now()->diffInDays($link->expires_at, false))
            : null;

        return Inertia::render('Public/DocumentView', [
            'document' => $link->document,
            'allowDownload' => $link->allow_download,
            'expiresAt' => $link->expires_at?->toISOString(),
            'daysRemaining' => $daysRemaining,
            'documentTypes' => \App\Models\Document::getTypes(),
        ]);
    }

    /**
     * Download the public document as PDF.
     */
    public function download(string $token, PdfGeneratorService $pdfService)
    {
        $link = PublicLink::where('token', $token)
            ->with(['document.items', 'document.user'])
            ->firstOrFail();

        // Check if link is valid and allows download
        if ($link->isExpired()) {
            abort(404, 'This link has expired.');
        }

        if (!$link->allow_download) {
            abort(403, 'Download is not allowed for this document.');
        }

        return $pdfService->download($link->document);
    }
}
