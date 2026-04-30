<?php

namespace App\Http\Controllers\api;

use App\Models\Upload;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'file' => 'required|file|max:102400', // max 100MB
                'receiver_id' => 'required',
            ]);

            $user = Auth::user();
            $file = $request->file('file');

            $fileType = $file->getMimeType() ?: $file->getClientMimeType() ?: $file->getClientOriginalExtension();
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());

            $uploadDir = public_path('uploads');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the file
            $file->move($uploadDir, $fileName);

            $filePath = 'uploads/' . $fileName; // Relative path for URL

            // Save to database
            $upload = Upload::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id, // make sure this column exists
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_path' => $filePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $upload,
                'url' => url($filePath),
                'file_url' => url($filePath),
                'file_path' => $filePath,
                'file_type' => $fileType,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // Get all uploaded files for authenticated user
    public function list()
    {
        $user = Auth::user();

        $uploads = Upload::where('sender_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($upload) {
                return [
                    'id' => $upload->id,
                    'name' => $upload->file_name,
                    'type' => $upload->file_type,
                    'url' => url($upload->file_path),
                    'file_url' => url($upload->file_path),
                    'file_path' => $upload->file_path,
                    'uploaded_at' => $upload->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Files list fetched successfully',
            'data' => $uploads
        ]);
    }
}
