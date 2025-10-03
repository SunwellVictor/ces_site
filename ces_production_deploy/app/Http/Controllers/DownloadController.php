<?php

namespace App\Http\Controllers;

use App\Models\DownloadGrant;
use App\Models\DownloadToken;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    /**
     * Display the user's available downloads.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all valid download grants for the user with pagination
        $downloadGrants = DownloadGrant::where('user_id', $user->id)
            ->with(['product', 'file', 'order'])
            ->whereRaw('downloads_used < max_downloads')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Group by product for better organization (for current page only)
        $downloadsByProduct = $downloadGrants->getCollection()->groupBy('product.title');

        return view('downloads.index', compact('downloadGrants', 'downloadsByProduct'));
    }

    /**
     * Issue a temporary download token for a specific grant.
     */
    public function issueToken(Request $request, DownloadGrant $grant)
    {
        // Rate limiting: 1 request per minute per grant
        $key = 'download:grant:' . $grant->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => 'Too many requests for this grant. Please try again later.',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 60); // 60 seconds = 1 minute

        // Verify the grant belongs to the authenticated user
        if ($grant->user_id !== Auth::id()) {
            return response()->json([
                'error' => 'Unauthorized access to this grant'
            ], 403);
        }

        // Check if the grant is still valid
        if (!$grant->isValid()) {
            return response()->json([
                'error' => 'Grant is no longer valid'
            ], 403);
        }

        // Create a temporary download token (valid for 10 minutes)
        $token = DownloadToken::create([
            'grant_id' => $grant->id,
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'success' => true,
            'token' => $token->token,
            'download_url' => route('downloads.consume', ['token' => $token->token]),
            'expires_at' => $token->expires_at->toISOString(),
            'message' => 'Download token generated successfully. Link expires in 10 minutes.'
        ]);
    }

    /**
     * Consume a download token and stream the file.
     */
    public function consumeToken(string $token)
    {
        // Find the download token
        $downloadToken = DownloadToken::where('token', $token)->first();

        if (!$downloadToken) {
            abort(404, 'Download token not found.');
        }

        // Check if token is still valid
        if (!$downloadToken->isValid()) {
            return response()->json([
                'error' => 'Token is invalid or expired'
            ], 403);
        }

        // Get the associated grant and file
        $grant = $downloadToken->grant;
        
        if (!$grant || !$grant->isValid()) {
            abort(410, 'Download grant is no longer valid.');
        }

        $file = $grant->file;
        
        if (!$file) {
            abort(404, 'File not found.');
        }

        // Check if file exists on storage
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, 'File not found on storage.');
        }

        try {
            // Mark token as used
            $downloadToken->markAsUsed();

            // Increment download count on grant
            $grant->increment('downloads_used');

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

        } catch (\Exception $e) {
            abort(500, 'Error processing download.');
        }
    }

    /**
     * Get download statistics for a user.
     */
    public function getDownloadStats()
    {
        $user = Auth::user();

        $stats = [
            'total_grants' => DownloadGrant::where('user_id', $user->id)->count(),
            'active_grants' => DownloadGrant::where('user_id', $user->id)
                ->whereRaw('downloads_used < max_downloads')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->count(),
            'total_downloads' => DownloadGrant::where('user_id', $user->id)
                ->sum('downloads_used'),
            'expired_grants' => DownloadGrant::where('user_id', $user->id)
                ->where('expires_at', '<', now())
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Check if a specific grant is still valid.
     */
    public function checkGrantStatus(DownloadGrant $grant)
    {
        $user = Auth::user();

        if ($grant->user_id !== $user->id) {
            abort(403);
        }

        return response()->json([
            'is_valid' => $grant->isValid(),
            'downloads_used' => $grant->downloads_used,
            'max_downloads' => $grant->max_downloads,
            'downloads_remaining' => $grant->max_downloads - $grant->downloads_used,
            'expires_at' => $grant->expires_at?->toISOString(),
            'days_until_expiry' => $grant->expires_at ? (int) round(now()->diffInDays($grant->expires_at, false)) : null,
        ]);
    }
}
