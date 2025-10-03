<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of files with search and filtering.
     */
    public function index(Request $request)
    {
        $query = File::with('products');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('path', 'like', "%{$search}%");
            });
        }

        // Size filtering (in MB)
        if ($request->filled('min_size')) {
            $minSizeBytes = $request->min_size * 1024 * 1024; // Convert MB to bytes
            $query->where('size_bytes', '>=', $minSizeBytes);
        }

        if ($request->filled('max_size')) {
            $maxSizeBytes = $request->max_size * 1024 * 1024; // Convert MB to bytes
            $query->where('size_bytes', '<=', $maxSizeBytes);
        }

        $files = $query->latest()->paginate(20);

        // Calculate storage stats from all files (not just paginated results)
        $allFiles = File::all();
        $totalFiles = $allFiles->count();
        $totalSize = $allFiles->sum('size_bytes');
        $totalAttachments = $allFiles->sum(function($file) { 
            return $file->products()->count(); 
        });

        return view('admin.files.index', compact('files', 'totalFiles', 'totalSize', 'totalAttachments'));
    }

    /**
     * Show the form for creating a new file.
     */
    public function create()
    {
        return view('admin.files.create');
    }

    /**
     * Store a newly uploaded file in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:102400'], // 100MB max
            'disk' => ['sometimes', 'string', 'in:local,public,s3'],
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $size = $uploadedFile->getSize();
        $disk = $request->input('disk', 'public'); // Default to public

        // Store file in files directory using hash name
        $path = $uploadedFile->store('files', $disk);

        // Generate checksum
        $checksum = hash_file('sha256', $uploadedFile->getPathname());

        // Create file record
        $file = File::create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $originalName,
            'size_bytes' => $size,
            'checksum' => $checksum,
        ]);

        return redirect()->route('admin.files.index')
                        ->with('success', 'File uploaded successfully.');
    }

    /**
     * Display the specified file.
     */
    public function show(File $file)
    {
        $file->load('products');
        return view('admin.files.show', compact('file'));
    }

    /**
     * Show the form for editing the specified file.
     */
    public function edit(File $file)
    {
        return view('admin.files.edit', compact('file'));
    }

    /**
     * Update the specified file metadata.
     */
    public function update(Request $request, File $file)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:download,image,document,video,audio,other'],
            'status' => ['required', 'in:active,inactive'],
            'is_public' => ['boolean'],
        ]);

        $validated['is_public'] = $request->boolean('is_public');

        $file->update($validated);

        return redirect()->route('admin.files.index')
                        ->with('success', 'File updated successfully.');
    }

    /**
     * Remove the specified file from storage.
     */
    public function destroy(File $file)
    {
        // Check if file is attached to products
        if ($file->products()->exists()) {
            return redirect()->route('admin.files.index')
                            ->with('error', 'Cannot delete file that is attached to products.');
        }

        // Delete physical file
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Delete database record
        $file->delete();

        return redirect()->route('admin.files.index')
                        ->with('success', 'File deleted successfully.');
    }

    /**
     * Download the specified file.
     */
    public function download(File $file)
    {
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, 'File not found.');
        }

        // Get file content and metadata
        $fileContent = Storage::disk($file->disk)->get($file->path);
        $mimeType = mime_content_type(Storage::disk($file->disk)->path($file->path)) ?: 'application/octet-stream';
        $fileSize = Storage::disk($file->disk)->size($file->path);

        // Create response with proper headers for file download
        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $file->original_name . '"',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Replace an existing file with a new upload.
     */
    public function replace(Request $request, File $file)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:102400'], // 100MB max
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        // Delete old file
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Generate new filename
        $filename = Str::uuid() . '.' . $extension;
        
        // Store new file
        $path = $uploadedFile->storeAs('files', $filename, 'public');

        // Update file record
        $file->update([
            'original_name' => $originalName,
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
        ]);

        return redirect()->route('admin.files.show', $file)
                        ->with('success', 'File replaced successfully.');
    }
}
