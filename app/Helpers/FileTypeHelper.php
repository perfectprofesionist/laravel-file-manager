<?php

use Illuminate\Support\Facades\Auth;
use App\Models\FileType;

/**
 * Helper function to retrieve all file types for authenticated users.
 *
 * Returns an empty collection if the user is not authenticated.
 */
if (!function_exists('get_file_types')) {
    /**
     * Get all FileType records if the user is authenticated.
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    function get_file_types()
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return collect(); // Return an empty collection if the user is not authenticated
        }

        return FileType::all(); // Return all FileType records
    }
}
