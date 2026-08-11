<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    private const CATEGORIES = ['Identity', 'Education', 'Employment', 'Finance', 'Vehicle', 'Property', 'Other'];

    public function index(): Response
    {
        return Inertia::render('admin/documents/Index', ['documents' => Document::latest()->get()->map(fn (Document $document) => $this->data($document))]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/documents/Form', ['categories' => self::CATEGORIES]);
    }

    public function edit(Document $document): Response
    {
        return Inertia::render('admin/documents/Form', ['document' => $this->data($document), 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->storeFile($request, $data);
        Document::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Document added successfully.']);

        return to_route('admin.documents.index');
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('file')) {
            $oldPath = $document->file_path;
            $this->storeFile($request, $data);
            $document->update($data);
            if ($oldPath) {
                Storage::disk('local')->delete($oldPath);
            }
        } else {
            $document->update($data);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Document updated successfully.']);

        return to_route('admin.documents.index');
    }

    public function destroy(Document $document): RedirectResponse
    {
        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }
        $document->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Document deleted successfully.']);

        return back();
    }

    public function view(Document $document): BinaryFileResponse
    {
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        $response = response()->file(Storage::disk('local')->path($document->file_path), [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
        ]);
        $response->setContentDisposition('inline', $document->original_filename ?: $document->name);

        return $response;
    }

    public function download(Document $document): StreamedResponse
    {
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_filename ?: basename($document->file_path));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'], 'category' => ['required', Rule::in(self::CATEGORIES)],
            'document_number' => ['nullable', 'string', 'max:255'], 'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'notes' => ['nullable', 'string'], 'is_active' => ['required', 'boolean'],
        ]);
    }

    private function storeFile(Request $request, array &$data): void
    {
        $file = $request->file('file');
        unset($data['file']);
        if (! $file) {
            return;
        }
        $data['file_path'] = $file->store('documents', 'local');
        $data['original_filename'] = $file->getClientOriginalName();
        $data['mime_type'] = $file->getMimeType();
    }

    private function data(Document $document): array
    {
        return [...$document->toArray(), 'masked_document_number' => $document->maskedNumber(), 'has_file' => (bool) $document->file_path];
    }
}
