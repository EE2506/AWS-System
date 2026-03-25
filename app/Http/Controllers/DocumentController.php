<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\PublicLink;
use App\Services\PdfGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->authorizeResource(Document::class, 'document');
    }

    /**
     * Display a listing of documents.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // Admin sees all documents, users see only their own
        $query = $user->hasRole('admin')
            ? Document::query()
            : Document::where('user_id', $user->id);

        // Apply filters
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('control_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $documents = $query->with(['user:id,name', 'items'])
            ->withCount('items')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'filters' => $request->only(['type', 'search', 'status']),
            'documentTypes' => Document::getTypes(),
        ]);
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Documents/Create', [
            'documentTypes' => Document::getTypes(),
            'selectedType' => $request->get('type', Document::TYPE_SOA),
            'recentClients' => $this->getRecentClients(),
        ]);
    }

    /**
     * Store a newly created document.
     */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['discount'] = $data['discount'] ?? 0;

        // Auto-generate control number if not provided
        if (empty($data['control_number'])) {
            $data['control_number'] = Document::generateControlNumber($data['type']);
        }

        $document = Document::create($data);

        // Create items
        if ($request->has('items')) {
            foreach ($request->items as $index => $item) {
                $document->items()->create([
                    'item_number' => $index + 1,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'remarks' => $item['remarks'] ?? '',
                ]);
            }
        }

        // Recalculate total
        $document->updateTotal();

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document created successfully.');
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): Response
    {
        $this->authorize('view', $document);

        $document->load(['items', 'user', 'publicLink']);

        return Inertia::render('Documents/Show', [
            'document' => $document,
            'documentTypes' => Document::getTypes(),
            'can' => [
                'change_status' => auth()->user()?->can('changeStatus', $document) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for editing the document.
     */
    public function edit(Document $document): Response
    {
        $this->authorize('update', $document);

        $document->load('items');

        return Inertia::render('Documents/Edit', [
            'document' => $document,
            'documentTypes' => Document::getTypes(),
            'recentClients' => $this->getRecentClients(),
        ]);
    }

    /**
     * Update the specified document.
     */
    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $data = $request->validated();
        $data['discount'] = $data['discount'] ?? 0;
        $document->update($data);

        // Update items
        if ($request->has('items')) {
            // Delete existing items and recreate
            $document->items()->delete();

            foreach ($request->items as $index => $item) {
                $document->items()->create([
                    'item_number' => $index + 1,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'remarks' => $item['remarks'] ?? '',
                ]);
            }
        }

        // Recalculate total
        $document->updateTotal();

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Mark the document as complete (finalized).
     */
    public function markAsComplete(Document $document): RedirectResponse
    {
        $this->authorize('changeStatus', $document);

        $document->update(['status' => Document::STATUS_FINALIZED]);

        return back()->with('success', 'Document marked as complete.');
    }

    /**
     * Mark the document as paid.
     */
    public function markAsPaid(Document $document): RedirectResponse
    {
        $this->authorize('changeStatus', $document);

        $document->update(['status' => Document::STATUS_PAID]);

        return back()->with('success', 'Document marked as paid.');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Download the document as PDF.
     */
    public function download(Document $document, PdfGeneratorService $pdfService)
    {
        $this->authorize('view', $document);

        return $pdfService->download($document);
    }

    /**
     * Preview the document as PDF in browser.
     */
    public function preview(Document $document, PdfGeneratorService $pdfService)
    {
        $this->authorize('view', $document);

        return $pdfService->stream($document);
    }

    /**
     * Generate a public sharing link.
     */
    public function share(Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $days = (int) request('expires_in_days', 5);

        if (!$document->publicLink) {
            $document->publicLink()->create([
                'token' => PublicLink::generateToken(),
                'allow_download' => true,
                'expires_at' => now()->addDays($days),
            ]);
        }

        return back()->with('success', 'Public link generated successfully.');
    }

    /**
     * Revoke public sharing link.
     */
    public function revokeShare(Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $document->publicLink?->delete();

        return back()->with('success', 'Public link revoked.');
    }

    /**
     * Get recent distinct clients for auto-suggest
     */
    protected function getRecentClients()
    {
        $query = auth()->user()->hasRole('admin') ? Document::query() : Document::where('user_id', auth()->id());
        
        return $query->select('recipient_name', 'recipient_email', 'recipient_phone', 'recipient_address', 'created_at')
            ->whereNotNull('recipient_name')
            ->where('recipient_name', '!=', '')
            ->latest()
            ->limit(100)
            ->get()
            ->unique('recipient_name')
            ->values()
            ->toArray();
    }
}
