<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\AccessControl;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Models\FileType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:view-user', ['only' => ['index']]);
        $this->middleware('permission:create-user', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-user', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->has('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('name') . '%')
                    ->orWhere('email', 'like', '%' . $request->input('name') . '%');
            });
        }

        $data = $query->orderBy('name', 'asc')->paginate(20);

        foreach ($data as $user) {
            $user->avatar = $user->avatar ? route('fetch.avatar', ["filename" => $user->avatar]) : null;
        }

        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 20);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $roles = Role::pluck('name', 'name')->all();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $user = User::find($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id): View
    {
        $validator = Validator::make($request->all(), [
            'file_name' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'file_type' => 'nullable|string|max:255',
            'owner_id' => 'nullable|exists:users,id',
            'date_modified' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $fileName = $validated['file_name'] ?? '';
        $id = $validated['user_id'] ?? $id;
        $fileType = $validated['file_type'] ?? '';
        $ownerId = $validated['owner_id'] ?? '';
        $dateModified = $validated['date_modified'] ?? '';
        $modifiedFields = [];

        $user = User::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        $sharedWithMeQuery = AccessControl::where('user_id', $user->id)
            ->with('content', 'user')
            ->where(function ($query) {
                $query->whereHas('content', function ($contentQuery) {
                    $contentQuery->whereNull('parent_id');
                })->orWhereHas('content', function ($contentQuery) {
                    $contentQuery->whereNotIn('parent_id', function ($subQuery) {
                        $subQuery->select('content_id')->from('access_controls');
                    });
                });
            });

        if ($fileName) {
            $sharedWithMeQuery->whereHas('content', function ($query) use ($fileName) {
                $query->where('name', 'like', '%' . $fileName . '%');
            });
            $modifiedFields['file_name'] = $fileName;
        }

        if ($fileType === 'Folder') {
            $sharedWithMeQuery->whereHas('content', function ($query) {
                $query->whereNull('parent_id')->where('is_folder', true);
            });
            $modifiedFields['file_type'] = 'folder';
        } elseif ($fileType) {
            $fileTypeModel = FileType::where('guid', $fileType)->first();
            if ($fileTypeModel) {
                $sharedWithMeQuery->whereHas('content', function ($query) use ($fileTypeModel) {
                    $query->where(function ($query) use ($fileTypeModel) {
                        $fileTypeModel->fileExtensions->each(function ($extension) use ($query) {
                            $query->orWhere('extension', 'like', '%' . $extension->name . '%');
                        });
                    });
                });
                $modifiedFields['file_type_guid'] = $fileTypeModel->guid;
                $modifiedFields['file_type'] = $fileTypeModel->name;
            }
        }

        if ($ownerId) {
            $sharedWithMeQuery->whereHas('content', function ($query) use ($ownerId) {
                $query->where('user_id', $ownerId);
            });
            $modifiedFields['owner_id'] = $ownerId;
        }

        if ($dateModified) {
            $dateModifiedFormatted = \Carbon\Carbon::createFromFormat('m/d/Y', $dateModified)->format('Y-m-d');
            $sharedWithMeQuery->whereHas('content', function ($query) use ($dateModifiedFormatted) {
                $query->whereDate('updated_at', $dateModifiedFormatted);
            });
            $modifiedFields['date_modified'] = $dateModified;
        }

        $owners = AccessControl::where('user_id', $user->id)
            ->with('content.user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('content.user')
            ->unique('id');

        $sharedWithMe = $sharedWithMeQuery->orderBy('created_at', 'desc')->paginate(10);

        $sharedWithMe->each(function ($sharedContent) {
            if ($sharedContent->content->is_folder) {
                $sharedContent->content->svgPath = asset('assets/images/folder.svg');
            } else {
                $sharedContent->content->extension = pathinfo($sharedContent->content->name, PATHINFO_EXTENSION);
                $sharedContent->content->svgPath = asset('assets/file-icons/vivid/' . $sharedContent->content->extension . '.svg');
                $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $sharedContent->content->extension . '.svg');
                if (!file_exists($fileAbsolutePath)) {
                    $sharedContent->content->svgPath = asset('assets/file-icons/vivid/txt.svg');
                }
            }

            $sharedContent->content->user->avatar = $sharedContent->content->user->avatar
                ? route('fetch.avatar', ['filename' => basename($sharedContent->content->user->avatar)])
                : null;
        });

        $fileTypes = get_file_types();

        return view('users.edit', compact('user', 'roles', 'userRole', 'sharedWithMe', 'modifiedFields', 'owners', 'fileTypes'));
    }


    public function openFolder(Request $request, $guid)
    {
        $userId = $request->input('user_id');
        $content = Content::select('id')->where('guid', $guid)->first();
        $contentId = $content ? $content->id : null;
        $content = $contentId ? Content::findOrFail($contentId) : null;

        // Log::info('Opening folder', ['guid' => $guid, 'content_id' => $contentId]);

        // Check if the content is present in the access folder
        $accessControl = AccessControl::where('user_id', $userId)
            ->where('content_id', $contentId)
            ->with('content')
            ->first();

        if (!$accessControl) {
            // Log::info('No access control found for the folder', ['guid' => $guid]);
            return response()->json(['message' => 'No access to this folder'], 403);
        }

        // Check if the content is a folder or file and if it exists in the access folder
        $folders = Content::where('parent_id', $contentId)
            ->whereExists(function ($query) use ($userId) {
                $query->select('id')
                    ->from('access_controls')
                    ->whereColumn('access_controls.content_id', 'contents.id')
                    ->where('access_controls.user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->paginate(10);

        // Log::info('Folders retrieved', ['count' => $folders->count()]);

        $folders->each(function ($folder) {
            if ($folder->is_folder) {
                $folder->svgPath = asset('assets/file-icons/vivid/folder.svg');
            } else {
                $folder->extension = pathinfo($folder->name, PATHINFO_EXTENSION);
                $folder->svgPath = asset('assets/file-icons/vivid/' . $folder->extension . '.svg');
                $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $folder->extension . '.svg');
                if (!file_exists($fileAbsolutePath)) {
                    $folder->svgPath = asset('assets/file-icons/vivid/txt.svg');
                }
            }

            $folder->user->avatar = $folder->user->avatar
                ? route('fetch.avatar', ['filename' => basename($folder->user->avatar)])
                : null;
        });

        // Log::info('Folder processing completed');

        return response()->json([
            'folders' => $folders,
            'user_id' => $userId,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'same:confirm-password',
            'roles' => 'required',
            'avatar' => 'image',
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        if ($request->hasFile('avatar')) {
            // Save the avatar in a private storage location
            $avatarPath = $request->file('avatar')->store('avatars', 'local');
            $input['avatar'] = basename($avatarPath);
        } else {
            unset($input['avatar']);
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }


    public function loadContent(Request $request)
    {
        $searchQuery = $request->input('searchQuery', '');
        $page = $request->input('page', 1);
        $perPage = 20;
        $id = $request->input('id');
        $parentId = $request->input('parent_id');
        $type = $request->input('type');

        if ($parentId !== null) {
            $parentContent = Content::find($parentId);
            if ($parentContent) {
                if ($type === 'back') {
                    $parentId = $parentContent->parent_id; // Go one level back
                } else {
                    $parentId = $parentContent->id;
                }
            }
        }

        $query = Content::query();

        if ($id !== null) {
            $content = Content::select('id')->where('guid', $id)->first();
            $parentId = $content ? $content->id : null;
        }

        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        if (!empty($searchQuery)) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        }

        $contents = $query->orderBy('created_at', 'desc')
            ->with('user')
            ->paginate($perPage, ['*'], 'page', $page);

        $contents->each(function ($item) {
            if ($item->is_folder) {
                $item->svgPath = asset('assets/images/folder.svg');
            } else {
                $item->extension = pathinfo($item->name, PATHINFO_EXTENSION);
                $item->svgPath = asset('assets/file-icons/vivid/' . $item->extension . '.svg');
            }

            $item->user->avatar = $item->user->avatar
                ? route('fetch.avatar', ['filename' => basename($item->user->avatar)])
                : asset('assets/images/default-avatar.png');
        });

        return response()->json([
            'contents' => $contents,
            'next_page_url' => $contents->nextPageUrl(),
            'parentId' => $parentId
        ]);
    }


    public function removeAccess(Request $request, $guid)
    {
        $content = Content::where('guid', $guid)->firstOrFail();

        // Remove access control for the content
        AccessControl::where('content_id', $content->id)->withTrashed()->forceDelete();

        // Recursive function to remove access control for all descendants
        $removeAccessForDescendants = function ($content) use (&$removeAccessForDescendants) {
            $children = $content->allChildren()->get();

            foreach ($children as $child) {
                // Remove access control for the child
                AccessControl::where('content_id', $child->id)->withTrashed()->forceDelete();
                // Recursively remove access for the child's children
                $removeAccessForDescendants($child);
            }
        };

        // Initiate recursive access removal
        $removeAccessForDescendants($content);

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Access removed successfully']);
        }

        return redirect()->back()->with('success', 'Access removed successfully');
    }

    public function shareContent(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'accessibility' => 'required|in:Viewer,Editor', // Ensure only 'Viewer' or 'Editor' is allowed
            'user_id' => 'required|exists:users,id', // Validate the user ID exists in the users table
            'contentIds' => 'required|array|min:1', // At least one content ID must be provided
            'contentIds.*' => 'exists:contents,guid', // Validate each content ID exists in the contents table
        ]);

        // Recursive function to share content and all its descendants
        $shareContentAndDescendants = function ($content, $userId, $accessibility) use (&$shareContentAndDescendants) {
            AccessControl::updateOrCreate(
                [
                    'user_id' => $userId,
                    'content_id' => $content->id,
                ],
                [
                    'access_type' => $accessibility,
                ]
            );

            // Share all children if it's a folder
            if ($content->is_folder) {
                $children = $content->allChildren; // Fetch all children, including files
                foreach ($children as $child) {
                    $shareContentAndDescendants($child, $userId, $accessibility);
                }
            }
        };

        foreach ($validated['contentIds'] as $guid) {
            $content = Content::where('guid', $guid)->first();

            if ($content) {
                $shareContentAndDescendants($content, $validated['user_id'], $validated['accessibility']);
            }
        }

        return redirect()->back()->with('success', 'Content shared successfully!');
    }







    // Shared Visualizzatore Search
    public function visualizzatoreFileTypes(Request $request)
    {
        $query = $request->input('name');

        if (!Auth::check()) {
            return collect();
        }

        if ($query) {
            $fileTypes = FileType::where('name', 'like', '%' . $query . '%')->get();
        } else {
            $fileTypes = FileType::all();
        }

        $fileTypes->each(function ($fileType) {
            $fileType->svg_path = asset($fileType->svg_path);
        });

        return response()->json($fileTypes);
    }

    public function ownerSearch(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $searchQuery = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = 10;

        $owners = AccessControl::where('user_id', Auth::id())
            ->whereHas('content.user', function ($query) use ($searchQuery) {
                if ($searchQuery) {
                    $query->where('name', 'like', '%' . $searchQuery . '%');
                }
            })
            ->with('content.user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $uniqueOwners = $owners->pluck('content.user')->unique('id')->values();

        $uniqueOwners->each(function ($owner) {
            $owner->avatar = $owner->avatar ? route('fetch.avatar', ["filename" => basename($owner->avatar)]) : asset('assets/images/iconProfile.png');
        });

        $paginatedOwners = new \Illuminate\Pagination\LengthAwarePaginator(
            $uniqueOwners,
            $owners->total(),
            $perPage,
            $page,
            ['path' => url('/owner')]
        );

        return response()->json($paginatedOwners);
    }


    public function folderSearch(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $query = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = 10;

        $folders = AccessControl::where('user_id', Auth::id())
            ->whereHas('content', function ($q) use ($query) {
                $q->where('is_folder', true);
                if ($query) {
                    $q->where('name', 'like', '%' . $query . '%');
                }
            })
            ->with('content')
            ->paginate($perPage, ['*'], 'page', $page);

        $folders->each(function ($folder) {
            $folder->content->image = $folder->content->image ? route('fetch.image', ["filename" => basename($folder->content->image)]) : asset('assets/images/defaultFolder.png');
        });

        $paginatedFolders = new \Illuminate\Pagination\LengthAwarePaginator(
            $folders->pluck('content'),
            $folders->total(),
            $perPage,
            $page,
            ['path' => url('/folder_list')]
        );

        return response()->json($paginatedFolders);
    }



    public function search(Request $request)
    {
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'updated_at');

        $validated = $request->validate([
            'file_name' => 'nullable|string|max:255',
            'file_type' => 'nullable|exists:file_types,guid',
            'owner' => 'nullable|exists:users,id',
            'folder' => 'nullable|exists:contents,guid',
            'date_modified' => 'nullable|string|max:255',
        ]);

        // Filter content query based on access control
        $accessibleContentIds = AccessControl::where('user_id', Auth::id())->pluck('content_id')->toArray();
        $contentQuery = Content::whereIn('id', $accessibleContentIds)
            ->with(['parent', 'user', 'children'])
            ->orderBy($sort, $order);

        $modifiedFields = [];

        if (!empty($validated['file_name'])) {
            $contentQuery->where('name', 'like', '%' . $validated['file_name'] . '%');
            $modifiedFields['file_name'] = $validated['file_name'];
        }

        if (!empty($validated['owner'])) {
            $contentQuery->where('user_id', $validated['owner']);
            $user = User::find($validated['owner']);
            $modifiedFields['owner_id'] = $validated['owner'];
            $modifiedFields['owner'] = $user ? $user->name : null;
        }

        if (!empty($validated['folder'])) {
            $contentFolder = Content::where('guid', $validated['folder'])->first();
            if ($contentFolder) {
                $contentQuery->where('parent_id', $contentFolder->id);
                $modifiedFields['folder_guid'] = $validated['folder'];
                $modifiedFields['folder'] = $contentFolder->name;
            }
        }

        if (!empty($validated['file_type'])) {
            $fileType = FileType::where('guid', $validated['file_type'])->first();
            if ($fileType && $fileType->name == 'Folder') {
                $contentQuery->where('is_folder', true);
                $modifiedFields['file_type'] = 'Folder';
                $modifiedFields['file_type_guid'] = $validated['file_type'];
            } elseif ($fileType) {
                $contentQuery->where(function ($query) use ($fileType) {
                    $fileType->fileExtensions->each(function ($extension) use ($query) {
                        $query->orWhere('extension', 'like', '%' . $extension->name . '%');
                    });
                });
                $modifiedFields['file_type_guid'] = $validated['file_type'];
                $modifiedFields['file_type'] = $fileType->name;
            }
        }

        if (!empty($validated['date_modified'])) {
            try {
                $dateModified = \Carbon\Carbon::createFromFormat('m/d/Y', $validated['date_modified'])->format('Y-m-d');
                $contentQuery->whereDate('updated_at', $dateModified);
                $modifiedFields['date_modified'] = $validated['date_modified'];
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['date_modified' => 'Invalid date format'])->withInput();
            }
        }

        $contents = $contentQuery->paginate(10);

        $navContents = Content::whereNull('parent_id')
            ->where('is_folder', true)
            ->with(['children', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($contents as $content) {
            if ($content->is_folder) {
                $content->svgPath = asset('assets/file-icons/vivid/folder.svg');
                $content->children->each(function ($childFolder) {
                    $childFolder->svgPath = asset('assets/file-icons/vivid/folder.svg');
                });
            } else {
                $content->extension = pathinfo($content->name, PATHINFO_EXTENSION);
                $content->svgPath = asset('assets/file-icons/vivid/' . $content->extension . '.svg');
                $contentAbsolutePath = public_path('assets/file-icons/vivid/' . $content->extension . '.svg');
                if (!file_exists($contentAbsolutePath)) {
                    $content->svgPath = asset('assets/file-icons/vivid/txt.svg');
                }
            }
            $content->user->avatar = $content->user->avatar ? route('fetch.avatar', ['filename' => basename($content->user->avatar)]) : null;

            // Check if the parent_id is in the accessible content IDs
            if ($content->parent_id && !in_array($content->parent_id, $accessibleContentIds)) {
                $content->parent_id = null;
            }
        }

        return view('content.shared_search_result', [
            'sharedContents' => $contents,
            'navContents' => $navContents,
            'fileType' => get_file_types(),
            'modifiedFields' => $modifiedFields,
        ]);
    }
}
