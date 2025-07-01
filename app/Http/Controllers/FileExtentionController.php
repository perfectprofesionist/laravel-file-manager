<?php

namespace App\Http\Controllers;

use App\Models\FileExtention;
use Illuminate\Http\Request;

/**
 * Controller for managing file extensions.
 * Handles CRUD operations for file extensions and their association with file types.
 */
class FileExtentionController extends Controller
{
    /**
     * Display a listing of all file extensions with their associated file types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $fileExtentions = FileExtention::with('fileType')->get();
        return response()->json($fileExtentions);
    }

    /**
     * Store a newly created file extension in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file_type_id' => 'required|exists:file_types,id',
            'name' => 'required|string|max:255',
            'svg_path' => 'required|string|max:255',
        ]);

        $fileExtention = FileExtention::create($validated);
        return response()->json($fileExtention, 201);
    }

    /**
     * Display the specified file extension with its associated file type.
     *
     * @param FileExtention $fileExtention
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(FileExtention $fileExtention)
    {
        return response()->json($fileExtention->load('fileType'));
    }

    /**
     * Update the specified file extension in storage.
     *
     * @param Request $request
     * @param FileExtention $fileExtention
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, FileExtention $fileExtention)
    {
        $validated = $request->validate([
            'file_type_id' => 'required|exists:file_types,id',
            'name' => 'required|string|max:255',
            'svg_path' => 'required|string|max:255',
        ]);

        $fileExtention->update($validated);
        return response()->json($fileExtention);
    }

    /**
     * Remove the specified file extension from storage.
     *
     * @param FileExtention $fileExtention
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(FileExtention $fileExtention)
    {
        $fileExtention->delete();
        return response()->json(['message' => 'FileExtention deleted successfully.']);
    }
}
