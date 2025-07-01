<?php

use App\Models\User;

/**
 * Helper function to search and retrieve users in alphabetical order with processed avatar URLs.
 */
if (!function_exists('search_users')) {
    /**
     * Retrieve all users in alphabetical order with processed avatar URLs.
     *
     * @param int $limit The maximum number of users to retrieve (default: 10)
     * @return \Illuminate\Support\Collection
     */
    function search_users(int $limit = 10)
    {
        // Retrieve all users in alphabetical order
        $users = User::orderBy('name', 'asc')
            ->limit($limit)
            ->get();

        // Process avatar URLs for each user
        $users->each(function ($user) {
            $user->avatar = $user->avatar
                ? route('fetch.avatar', ['filename' => basename($user->avatar)])
                : asset('assets/images/iconProfile.png');
        });

        return $users;
    }
}
