<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Content;

/**
 * Helper function to retrieve a list of folders for authenticated users.
 *
 * Returns an empty collection if the user is not authenticated.
 */
if (!function_exists('get_folders')) {
    /**
     * Get a limited list of folder-type Content records if the user is authenticated.
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    function get_folders() {

        // Check if the user is authenticated
        if (!Auth::check()) {
            return collect(); // Return an empty collection if the user is not authenticated
        }

        // Retrieve up to 10 folders (Content records where is_folder is true)
        $folders = Content::query()
            ->where('is_folder', true)
            ->limit(10)
            ->get();

        return $folders;
    }
}
