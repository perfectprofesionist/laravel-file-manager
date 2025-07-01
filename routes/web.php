<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileTypeController;
use App\Http\Controllers\FileExtentionController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ContentController;

// Disable registration route
Auth::routes(['register' => false]);

// All routes within this group require authentication
Route::group(['middleware' => ['auth']], function () {

    // ----------------------
    // User Management Routes
    // ----------------------
    Route::get('users', [UserController::class, 'index'])->name('users.index'); // List users
    Route::get('users/create', [UserController::class, 'create'])->name('users.create'); // Show create user form
    Route::post('users', [UserController::class, 'store'])->name('users.store'); // Store new user
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show'); // Show user details
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); // Show edit user form
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update'); // Update user
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy'); // Delete user
    Route::get('/load-content', [UserController::class, 'loadContent'])->name('load.content'); // Load content for user
    Route::post('/content/{content}/remove-access', [UserController::class, 'removeAccess'])->name('content.removeAccess'); // Remove access to shared content
    Route::post('/share-content', [UserController::class, 'shareContent'])->name('content.share'); // Share content with user
    Route::get('/open-folder/{guid}', [UserController::class, 'openFolder'])->name('open.folder'); // Open a folder by GUID

    // ----------------------
    // Role Management Routes
    // ----------------------
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index'); // List roles
    Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create'); // Show create role form
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store'); // Store new role
    Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show'); // Show role details
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit'); // Show edit role form
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update'); // Update role
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy'); // Delete role

    // ------------------------
    // Profile Management Routes
    // ------------------------
    Route::get('/profile', [ProfileController::class, 'index'])->name('user.profile'); // Show profile
    Route::post('/profile', [ProfileController::class, 'store'])->name('user.profile.store'); // Update profile
    Route::post('/profile/password', [ProfileController::class, 'storeOnlyPassword'])->name('user.profile.store.password'); // Change password
    Route::post('/profile/avatar', [ProfileController::class, 'storeAvatar'])->name('user.profile.store.avatar'); // Change avatar

    // Serve user avatar images securely
    Route::get('/avatar/{filename}', function ($filename) {
        $filename = basename($filename); // Sanitize filename
        $path = storage_path('app/private/avatars/' . $filename);

        if (!File::exists($path)) {
            abort(404, 'Avatar not found.');
        }

        return Response::file($path);
    })->middleware('auth')->name('fetch.avatar');

    // ----------------------
    // Content Management Routes
    // ----------------------
    // Home route: redirect based on permission
    Route::get('/', function () {
        if (!Auth::user()->hasPermissionTo('can-see-all')) {
            return redirect()->route('shared');
        }
        return app(ContentController::class)->listContents(request());
    })->name('list.contents');
    
    Route::get('/folder/{contentId?}', [ContentController::class, 'listContents'])->name('list.contents.folder')->middleware('permission:can-see-all'); // List folder contents
    Route::post('/upload-file', [ContentController::class, 'uploadFile'])->name('upload.file')->middleware('permission:upload-file'); // Upload file
    Route::post('/create-folder', [ContentController::class, 'createFolder'])->name('create.folder')->middleware('permission:create-folder'); // Create folder
    Route::match(['get', 'post'], '/share/{type}/{id}', [ContentController::class, 'share'])->name('share.resource'); // Share resource
    Route::get('/recent', [ContentController::class, 'recentFiles'])->name('recent')->middleware('permission:can-see-all'); // Recent files
    Route::get('/files/search', [ContentController::class, 'search'])->name('files.search'); // Search files
    Route::get('/file_types', [ContentController::class, 'fileTypes'])->name('files_types.search'); // Search file types
    Route::get('/owner', [ContentController::class, 'ownerSearch'])->name('owner.search'); // Search owners
    Route::get('/share-owner', [ContentController::class, 'shareOwnerSearch'])->name('share.owner.search'); // Search share owners
    Route::get('/folder_list', [ContentController::class, 'folderSearch'])->name('folder.search'); // Search folders
    Route::get('/move-folder-search', [ContentController::class, 'moveFolderSearch'])->name('move.folder.search'); // Move folder search

    // File Type and Extension Management (API Resource routes)
    Route::apiResource('file-types', FileTypeController::class); // File types API
    Route::apiResource('file-extentions', FileExtentionController::class); // File extensions API

    // ----------------------
    // Trash and Content Management
    // ----------------------
    Route::post('/content/{content}/trash', [ContentController::class, 'moveToTrash'])->name('content.moveToTrash')->middleware('permission:content-delete'); // Move content to trash
    Route::get('/trash', [ContentController::class, 'showTrashed'])->name('trash')->middleware('permission:can-see-all'); // Show trashed content
    Route::post('/move-content/{selectedFolderId}', [ContentController::class, 'moveContent'])->name('content.move')->middleware('permission:content-move'); // Move content
    Route::get('/shared', [ContentController::class, 'sharedContents'])->name('shared'); // Show shared contents
    Route::get('/shared/{contentId?}', [ContentController::class, 'sharedFolder'])->name('list.contents.shared'); // Show shared folder contents

    // Additional Content Operations
    Route::get('/content-info/{guid}', [ContentController::class, 'getContentInfo'])->name('content.getInfo'); // Get content info by GUID
    Route::post('/content/{id}/rename', [ContentController::class, 'rename'])->name('content.rename'); // Rename content
    Route::get('/content-info/{folderId}', [ContentController::class, 'getInfo'])->name('content.getInfo'); // Get folder info
    Route::post('/move-content/{guid}', [ContentController::class, 'moveContent'])->name('content.move'); // Move content by GUID
    Route::post('/content/{guid}/makeCopy', [ContentController::class, 'makeCopy'])->name('content.makeCopy'); // Make a copy of content
    Route::post('/content/{id}/restore', [ContentController::class, 'restore'])->name('content.restore'); // Restore content
    Route::post('/content/{guid}/deleteForever', [ContentController::class, 'permanentDelete'])->name('content.deleteForever'); // Permanently delete content
    Route::get('/download/{guid}', [ContentController::class, 'fileDownload'])->name('content.download'); // Download content

    // ----------------------
    // Visualizzatore (Viewer) Routes
    // ----------------------
    Route::get('/visualizzatore_file_types', [UserController::class, 'visualizzatoreFileTypes'])->name('visualizzatore.file_types'); // Viewer file types
    Route::get('/visualizzatore_owner', [UserController::class, 'ownerSearch'])->name('visualizzatore.owner.search'); // Viewer owner search
    Route::get('/visualizzatore_folder', [UserController::class, 'folderSearch'])->name('visualizzatore.folder.search'); // Viewer folder search
    Route::get('/search', [UserController::class, 'search'])->name('visualizzatore.search'); // Viewer search
});
