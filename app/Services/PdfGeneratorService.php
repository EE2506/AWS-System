<?php

namespace App\Services;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfGeneratorService
{
    /**
     * Generate a PDF for the given document.
     */
    public function generate(Document $document): \Barryvdh\DomPDF\PDF
    {
        // Dompdf can be slow on first run (font caching, autoloading).
        // Raise the limit only for this request so we don't hit the 30s wall.
        set_time_limit(120);

        $template = match ($document->type) {
            Document::TYPE_SOA => 'pdf.soa',
            Document::TYPE_PURCHASE_ORDER => 'pdf.purchase-order',
            Document::TYPE_QUOTATION => 'pdf.quotation',
            Document::TYPE_DELIVERY_RECEIPT => 'pdf.delivery-receipt',
            default => 'pdf.document',
        };

        return Pdf::loadView($template, [
            'document' => $document->load('items', 'user'),
        ])->setPaper('letter', 'portrait');
    }

    /**
     * Stream the PDF to the browser.
     */
    public function stream(Document $document): Response
    {
        $filename = $this->generateFilename($document);
        return $this->generate($document)->stream($filename);
    }

    /**
     * Download the PDF.
     */
    public function download(Document $document): Response
    {
        $filename = $this->generateFilename($document);
        return $this->generate($document)->download($filename);
    }

    /**
     * Generate a filename for the PDF.
     */
    protected function generateFilename(Document $document): string
    {
        // Human-readable document type label
        $typeLabel = match ($document->type) {
            Document::TYPE_SOA              => 'SOA',
            Document::TYPE_PURCHASE_ORDER   => 'Purchase-Order',
            Document::TYPE_QUOTATION        => 'Quotation',
            Document::TYPE_DELIVERY_RECEIPT => 'Delivery-Receipt',
            default                         => 'Document',
        };

        // Sanitize recipient name: strip invalid chars, spaces → underscores, trim
        $recipientName = $document->recipient_name ?? 'Unknown';
        $recipientName = preg_replace('/[^a-zA-Z0-9 \-_]/', '', $recipientName);
        $recipientName = str_replace(' ', '_', $recipientName);
        $recipientName = trim($recipientName, '_-');

        $controlNumber = $document->control_number ?? 'NO-REF';

        return sprintf('%s-%s-%s.pdf', $recipientName, $typeLabel, $controlNumber);
    }
}
