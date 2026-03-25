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
        $typeLabel = match ($document->type) {
            Document::TYPE_SOA              => 'S',
            Document::TYPE_PURCHASE_ORDER   => 'PO',
            Document::TYPE_QUOTATION        => 'Q',
            Document::TYPE_DELIVERY_RECEIPT => 'DR',
            default                         => 'Document',
        };

        $controlNumber = $document->control_number ?? $document->id;
        $date = now()->format('Ymd');

        return sprintf('%s-%s-%s.pdf', $typeLabel, $controlNumber, $date);
    }
}
