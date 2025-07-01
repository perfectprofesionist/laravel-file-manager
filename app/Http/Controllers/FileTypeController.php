<?php

namespace App\Http\Controllers;

use App\Models\FileType;
use Illuminate\Http\Request;

/**
 * FileTypeController
 *
 * This controller manages file types and their related operations.
 * It provides endpoints for listing, creating, viewing, updating, and deleting file types.
 * Each file type can have multiple file extensions associated with it.
 *
 * Thank you for reading and maintaining this code! :)
 */
class FileTypeController extends Controller
{
    /**
     * Display a listing of all file types with their associated file extensions.
     *
     * Shows all file types and their related extensions in a JSON response.
     */
    public function index()
    {
        $fileTypes = FileType::with('fileExtensions')->get();
        return response()->json($fileTypes);
    }

    /**
     * Store a newly created file type in storage.
     *
     * Validates and creates a new file type record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $fileType = FileType::create($validated);
        return response()->json($fileType, 201);
    }

    /**
     * Display the specified file type with its associated file extensions.
     *
     * Loads a file type and its extensions for viewing.
     */
    public function show(FileType $fileType)
    {
        return response()->json($fileType->load('fileExtensions'));
    }

    /**
     * Update the specified file type in storage.
     *
     * Validates and updates an existing file type record.
     */
    public function update(Request $request, FileType $fileType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $fileType->update($validated);
        return response()->json($fileType);
    }

    /**
     * Remove the specified file type from storage.
     *
     * Deletes a file type and returns a success message.
     */
    public function destroy(FileType $fileType)
    {
        $fileType->delete();
        return response()->json(['message' => 'FileType deleted successfully.']);
    }
}
