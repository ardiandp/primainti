<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Services\AuditLogger;
use App\Services\Media\MediaUploader;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaLibraryController extends Controller
{
    public function __construct(
        private readonly MediaUploader $uploader,
        private readonly MediaUsageService $usage,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', MediaAsset::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:image,document'],
            'view' => ['nullable', 'in:grid,list'],
            'trash' => ['nullable', 'in:1'],
        ]);

        $query = MediaAsset::query()
            ->with(['uploader', 'variants'])
            ->when($validated['trash'] ?? null, fn ($q) => $q->onlyTrashed())
            ->when(($validated['type'] ?? null) === 'image', fn ($q) => $q->where('mime_type', 'like', 'image/%'))
            ->when(($validated['type'] ?? null) === 'document', fn ($q) => $q->where('mime_type', 'application/pdf'))
            ->when($validated['q'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhere('alt', 'like', "%{$search}%");
                });
            })
            ->latest();

        return view('admin.media.index', [
            'assets' => $query->paginate(24)->withQueryString(),
            'filters' => $validated,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', MediaAsset::class);

        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'max:10240'],
        ]);

        $created = 0;
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $this->uploader->upload($file, $request->user());
                $created++;
            } catch (ValidationException $e) {
                $errors[] = $file->getClientOriginalName().': '.$e->getMessage();
            }
        }

        if ($created > 0) {
            AuditLogger::log('media.upload', null, null, ['count' => $created]);
        }

        return back()
            ->with('status', "{$created} file berhasil diunggah.")
            ->with('upload_errors', $errors);
    }

    public function edit(MediaAsset $media)
    {
        $this->authorize('update', $media);

        return view('admin.media.edit', [
            'media' => $media->load(['uploader', 'variants']),
            'usages' => $this->usage->usages($media),
        ]);
    }

    public function update(Request $request, MediaAsset $media)
    {
        $this->authorize('update', $media);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = [
            'title' => $media->title,
            'alt' => $media->alt,
            'caption' => $media->caption,
            'description' => $media->description,
        ];

        $media->update($validated);

        AuditLogger::log('media.update', $media, $old, $validated);

        return redirect()->route('admin.media.edit', $media)->with('status', 'Metadata diperbarui.');
    }

    public function destroy(MediaAsset $media)
    {
        $this->authorize('delete', $media);

        if ($this->usage->isReferenced($media)) {
            return back()->withErrors([
                'media' => 'Media masih dipakai konten lain dan tidak bisa dihapus. Lepas referensinya terlebih dahulu.',
            ]);
        }

        $media->delete();

        AuditLogger::log('media.delete', $media, ['deleted_at' => now()->toDateTimeString()], null);

        return redirect()->route('admin.media.index')->with('status', 'Media dipindahkan ke trash.');
    }

    public function restore(int $media)
    {
        // Resolve eksplisit dengan trashed: implicit binding default
        // mengecualikan soft-deleted sehingga restore selalu 404 tanpanya.
        $model = MediaAsset::withTrashed()->findOrFail($media);

        $this->authorize('update', $model);

        $model->restore();

        AuditLogger::log('media.restore', $model, null, ['restored' => true]);

        return back()->with('status', 'Media dipulihkan.');
    }

    public function forceDestroy(int $media)
    {
        $model = MediaAsset::withTrashed()->findOrFail($media);

        $this->authorize('delete', $model);

        if ($this->usage->isReferenced($model)) {
            return back()->withErrors([
                'media' => 'Media masih dipakai konten lain dan tidak bisa dihapus permanen.',
            ]);
        }

        // Hapus file fisik (asli + varian) sebelum baris database dihapus permanen.
        foreach ($model->variants as $variant) {
            Storage::disk($model->disk)->delete($variant->path);
        }

        Storage::disk($model->disk)->delete($model->path);

        $model->variants()->delete();
        $model->forceDelete();

        AuditLogger::log('media.force_delete', $model, ['path' => $model->path], null);

        return redirect()->route('admin.media.index', ['trash' => 1])->with('status', 'Media dihapus permanen.');
    }
}