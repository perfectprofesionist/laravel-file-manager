<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use App\Models\AccessControl;
use App\Models\TrashLog;
use App\Models\FileType;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

/**
 * Controller for managing content (files and folders) in the library system.
 * Handles upload, creation, listing, sharing, searching, trash, restore, and download operations.
 */
class ContentController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Constructor applies middleware for permissions on various actions.
     */
    function __construct()
    {
        $this->middleware('permission:can-see-all', ['only' => ['listContents', 'recentFiles']]);
        $this->middleware('permission:can-see', ['only' => ['sharedContents', 'sharedFolder']]);
        $this->middleware('permission:upload-file', ['only' => ['uploadFile']]);
        $this->middleware('permission:create-folder', ['only' => ['createFolder']]);
        $this->middleware('permission:content-delete', ['only' => ['deleteFolder', 'moveToTrash']]);
        $this->middleware('permission:restore-content', ['only' => ['restore']]);
        $this->middleware('permission:permanent-delete-content', ['only' => ['permanentDelete']]);
        $this->middleware('permission:content-move', ['only' => ['moveContent']]);
        $this->middleware('permission:content-copy', ['only' => ['makeCopy']]);
    }

    /**
     * Handles file upload to a folder. Validates file type and size, prevents executable uploads, and ensures unique file names.
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|array',
            'file.*' => 'file|max:102400', // max size 100MB
            'folder_id' => 'nullable|exists:contents,guid',
        ]);

        $folder_id = null;
        if ($request->folder_id) {
            $folder = Content::where('guid', $request->folder_id)->first();
            if ($folder) {
                $folder_id = $folder->id;
            }
        }

        $uploadedFilesHtml = [];
        $executableExtensions = ['exe', 'sh', 'bat', 'cmd', 'com', 'msi', 'scr', 'pif', 'application', 'gadget'];

        foreach ($request->file('file') as $file) {
            $extension = $file->getClientOriginalExtension();
            if (in_array(strtolower($extension), $executableExtensions)) {
                return response()->json(['message' => 'File type not allowed.'], 400);
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $name = $originalName;
            $counter = 1;

            // Check for existing files with the same name
            while (Content::where('name', $name . '.' . $extension)->where('parent_id', $folder_id)->exists()) {
                $name = $originalName . '(' . $counter . ')';
                $counter++;
            }

            $path = $file->store('uploads');

            $uploadedFile = Content::create([
                'name' => $name . '.' . $extension,
                'path' => $path,
                'user_id' => Auth::id(),
                'parent_id' => $folder_id,
                'size' => $file->getSize(),
                'extension' => $extension,
                'is_folder' => false,
            ]);

            $uploadedFile->extension = pathinfo($uploadedFile->name, PATHINFO_EXTENSION);
            $uploadedFile->svgPath = asset('assets/file-icons/vivid/' . $uploadedFile->extension . '.svg');
            $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $uploadedFile->extension . '.svg');
            if (!file_exists($fileAbsolutePath)) {
                $uploadedFile->svgPath = asset('assets/file-icons/vivid/txt.svg');
            }

            if ($uploadedFile->user->avatar) {
                $uploadedFile->user->avatar = route('fetch.avatar', ["filename" => $uploadedFile->user->avatar]);
            } else {
                $uploadedFile->user->avatar = null;
            }

            $html = view('components.tableRow', [
                'file' => $uploadedFile,
            ])->render();

            $uploadedFilesHtml[] = $html;
        }

        return response()->json(['message' => 'Files uploaded successfully', 'files' => $uploadedFilesHtml], 201);
    }

    /**
     * Creates a new folder under a parent folder (if provided).
     */
    public function createFolder(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:contents,guid',
        ]);

        $folderId = null;
        if ($validatedData['parent_id']) {
            $parentFolder = Content::where('guid', $validatedData['parent_id'])->first();
            if ($parentFolder) {
                $folderId = $parentFolder->id;
            }
        }

        // Check if a folder with the same name already exists in the parent folder
        $existingFolder = Content::where('name', $validatedData['name'])
            ->where('parent_id', $folderId)
            ->where('is_folder', true)
            ->first();

        if ($existingFolder) {
            return redirect()->back()->with('error', 'A folder with the same name already exists in this location');
        }

        $folder = Content::create([
            'name' => $validatedData['name'],
            'user_id' => Auth::id(),
            'parent_id' => $folderId,
            'is_folder' => true,
        ]);

        $folder->svgPath = asset('assets/images/folder.svg');
        $folder->user->avatar = $folder->user->avatar
            ? route('fetch.avatar', ['filename' => basename($folder->user->avatar)])
            : null;

        return redirect()->back()->with('success', 'Folder created successfully');
    }

    /**
     * Lists the contents (files and folders) of a folder, with sorting and navigation support.
     */
    public function listContents(Request $request, $contentId = null)
    {
        $currentFolderGuid = $contentId;
        $content = Content::select('id')->where('guid', $contentId)->first();
        $contentId = $content ? $content->id : null;
        $content = $contentId ? Content::findOrFail($contentId) : null;
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'updated_at');
        $user_order = $request->input('user_order', '');

        $navContents = Content::whereNull('parent_id')
            ->where('is_folder', true)
            ->with(['children', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        if (!empty($user_order)) {
            $contents = Content::where('parent_id', $contentId)
                ->join('users', 'contents.user_id', '=', 'users.id')
                ->orderBy('users.name', $user_order)
                ->select('contents.*')
                ->with('user') // Load the user relationship
                ->paginate(10);
        } else {
            $contents = Content::where('parent_id', $contentId)
                ->orderBy($sort, $order)
                ->with(['user'])
                ->paginate(10);
        }

        $contents->each(function ($item) {
            if ($item->is_folder) {
                $item->svgPath = asset('assets/images/folder.svg');
            } else {
                $item->extension = pathinfo($item->name, PATHINFO_EXTENSION);
                $item->svgPath = asset('assets/file-icons/vivid/' . $item->extension . '.svg');
                $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $item->extension . '.svg');
                if (!file_exists($fileAbsolutePath)) {
                    $item->svgPath = asset('assets/file-icons/vivid/txt.svg');
                }
            }

            $item->user->avatar = $item->user->avatar
                ? route('fetch.avatar', ['filename' => basename($item->user->avatar)])
                : null;
        });

        // Get the parent folders
        $parents = [];
        if ($content) {
            $folder = $content;

            // Loop through parent folders until parent_id is null
            while ($folder) {
                $parents[] = [
                    'guid' => $folder->guid,
                    'name' => $folder->name,
                    'isActive' => false, // Initially set it as false, will mark current folder as true later
                ];

                // Move to the parent folder
                if ($folder->parent_id) {
                    $folder = Content::find($folder->parent_id);
                } else {
                    break; // Reached root folder
                }
            }

            // Reverse the array so the root folder comes first
            $parents = array_reverse($parents);

            // Mark the current folder as active
            if (count($parents) > 0) {
                $parents[count($parents) - 1]['isActive'] = true;
            }
        }

        return view('content.index', [
            'current_content' => $content,
            'navContents' => $navContents,
            'contents' => $contents,
            'fileType' => get_file_types(),
            'parents' => $parents,
            'currentFolderGuid' => $currentFolderGuid,
        ]);
    }

    /**
     * Lists all contents shared with the authenticated user.
     */
    public function sharedContents(Request $request)
    {
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'updated_at');

        // Ensure the sort column is valid
        $validSortColumns = ['updated_at', 'created_at', 'name']; // Add any valid columns including related model columns
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'updated_at'; // Default to 'updated_at' if invalid
        }

        $sharedContents = AccessControl::where('access_controls.user_id', Auth::id()) // Explicit table reference
            ->join('contents', 'access_controls.content_id', '=', 'contents.id')
            ->orderBy('contents.' . $sort, $order) // Sort by content name
            ->join('users', 'contents.user_id', '=', 'users.id')
            ->where(function ($query) {
                $query->whereNull('contents.parent_id') // Case: parent_id is null
                    ->orWhereNotIn('contents.parent_id', function ($subQuery) {
                        $subQuery->select('content_id')->from('access_controls'); // Case: parent_id is not in access_controls
                    });
            })
            ->select('access_controls.*', 'contents.name as content_name', 'contents.updated_at as content_updated_at') // Ensure correct selection
            ->paginate(10);

        $sharedContents->load('content', 'user'); // Eager load the relationships for additional processing

        $sharedContents->each(function ($content) {
            if ($content->content->user !== null) {
                $contentOwner = $content->content->user;
                $contentOwner->avatar = $contentOwner->avatar
                    ? route('fetch.avatar', ['filename' => basename($contentOwner->avatar)])
                    : null;

                if ($content->content->is_folder ?? $content->is_folder) {
                    $content->svgPath = asset('assets/file-icons/vivid/folder.svg');
                } else {
                    if ($content->content) {
                        $content->content->extension = pathinfo($content->content->name, PATHINFO_EXTENSION);
                        $content->content->svgPath = asset('assets/file-icons/vivid/' . $content->content->extension . '.svg');
                        $contentAbsolutePath = public_path('assets/file-icons/vivid/' . $content->content->extension . '.svg');
                        if (!file_exists($contentAbsolutePath)) {
                            $content->content->svgPath = asset('assets/file-icons/vivid/txt.svg');
                        }
                    } else {
                        $content->extension = pathinfo($content->name, PATHINFO_EXTENSION);
                        $content->svgPath = asset('assets/file-icons/vivid/' . $content->extension . '.svg');
                        $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $content->extension . '.svg');
                        if (!file_exists($fileAbsolutePath)) {
                            $content->svgPath = asset('assets/file-icons/vivid/txt.svg');
                        }
                    }
                }
                $content->user = $contentOwner;
            }
        });
        if(Gate::allows('can-see-all')) {
            $navContents = Content::whereNull('parent_id')
            ->where('is_folder', true)
            ->with(['children', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        } else {
            
            $navContents = Content::where(function ($query) {
                // Top-level folders accessible to the user
                $query->whereNull('parent_id')
                    ->where('is_folder', true)
                    ->where(function ($query) {
                        $query->whereHas('accessControls', function ($accessQuery) {
                            $accessQuery->where('user_id', Auth::id());
                        })
                        ->orWhereHas('children', function ($childQuery) {
                            $childQuery->whereHas('accessControls', function ($accessQuery) {
                                $accessQuery->where('user_id', Auth::id());
                            });
                        });
                    });

                // Orphaned children (parents are not accessible at all)
                $query->orWhere(function ($query) {
                    $query->whereNotNull('parent_id')
                        ->where('is_folder', true)
                        ->whereHas('accessControls', function ($accessQuery) {
                            $accessQuery->where('user_id', Auth::id());
                        })

                        
                        
                        
                        /*->whereDoesntHave('parent', function ($parentQuery) {
                            $parentQuery->where('is_folder', true)
                                ->whereHas('accessControls', function ($accessQuery) {
                                    $accessQuery->where('user_id', Auth::id());
                                });
                        })*/
                        ;
                });
            })
            ->with([
                'children' => function ($childQuery) {
                    $childQuery->where(function ($query) {
                        $query->whereHas('accessControls', function ($accessQuery) {
                            $accessQuery->where('user_id', Auth::id());
                        })
                        ->orWhereHas('children', function ($childQuery) {
                            $childQuery->whereHas('accessControls', function ($accessQuery) {
                                $accessQuery->where('user_id', Auth::id());
                            });
                        });
                    });
                },
                'parent', // Include parent for debugging or filtering
                'user'
            ])
            ->where('is_folder', true)
            ->orderBy('name', 'asc')
            ->distinct()
            ->get();

            
            foreach($navContents as $key => $navContent) {
                
                foreach($navContents as $navContent_inn) {
                    if(!empty($navContent_inn->children) && count($navContent_inn->children) >= 1){
                        foreach($navContent_inn->children as $navContent_inn_child) {
                            if($navContent_inn_child->id == $navContent->id) {
                                unset($navContents[$key]);
                            }
                        }
                    }
                }
                
                //return ($navContent);
                
                //die();
                
            }

            
            
        }
        

        return view('content.shared', [
            'sharedContents' => $sharedContents,
            'navContents' => $navContents,
            'fileType' => get_file_types(),
        ]);
    }

    /**
     * Lists the contents of a shared folder for the authenticated user.
     */
    public function sharedFolder(Request $request, $contentId = null)
    {
        $sharedFolderGuid = $contentId;
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'updated_at');

        $content = Content::select('id')->where('guid', $contentId)->first();
        $contentId = $content ? $content->id : null;
        $content = $contentId ? Content::findOrFail($contentId) : null;

        $accessControl = AccessControl::where('content_id', $contentId)
            ->where('user_id', Auth::id())
            ->with('content')
            ->first();

        // If no access control exists for top parent, return empty results
        if (!$accessControl) {
            return view('content.share', [
                'current_content' => null,
                'mainContentAccessType' => 'viewer',
                'sharedContents' => collect([]),
                'fileType' => get_file_types(),
                'getFolder' => get_folders(),
            ]);
        }

        $mainContentAccessType = $accessControl->access_type;

        $sharedContents = Content::where('parent_id', $contentId)
            ->whereExists(function ($query) {
                $query->select('id')
                    ->from('access_controls')
                    ->whereColumn('access_controls.content_id', 'contents.id')
                    ->where('access_controls.user_id', Auth::id());
            })
            ->orderBy($sort, $order)
            ->with('user')
            ->paginate(10);

        $accessControlContents = AccessControl::whereIn('content_id', $sharedContents->pluck('id'))
            ->where('user_id', Auth::id())
            ->get();

        $sharedContents->each(function ($content) use ($accessControlContents) {
            $accessControl = $accessControlContents->firstWhere('content_id', $content->id);
            $content->access_type = $accessControl ? $accessControl->access_type : 'viewer';
        });

        if ($sharedContents->isNotEmpty()) {
            $sharedContents->each(function ($content) {
                if ($content->user !== null) {
                    $contentOwner = $content->user;
                    $contentOwner->avatar = $contentOwner->avatar ? route('fetch.avatar', ['filename' => basename($contentOwner->avatar)]) : null;
                    if ($content->is_folder) {
                        $content->svgPath = asset('assets/file-icons/vivid/folder.svg');
                    } else {
                        $content->extension = pathinfo($content->name, PATHINFO_EXTENSION);
                        $content->svgPath = asset('assets/file-icons/vivid/' . $content->extension . '.svg');
                        $contentAbsolutePath = public_path('assets/file-icons/vivid/' . $content->extension . '.svg');
                        if (!file_exists($contentAbsolutePath)) {
                            $content->svgPath = asset('assets/file-icons/vivid/txt.svg');
                        }
                    }
                    $content->user = $contentOwner;
                }
            });
        }

        // Get the parent folders
        $parents = [];
        if ($content) {
            $folder = $content;

            // Loop through parent folders until parent_id is null
            while ($folder) {
                // Check if the folder is present in access control
                $accessControl = AccessControl::where('content_id', $folder->id)
                    ->where('user_id', Auth::id())
                    ->first();

                if ($accessControl) {
                    $parents[] = [
                        'guid' => $folder->guid,
                        'name' => $folder->name,
                        'isActive' => false, // Initially set it as false, will mark current folder as true later
                    ];
                }

                // Move to the parent folder
                if ($folder->parent_id) {
                    $folder = Content::find($folder->parent_id);
                } else {
                    break; // Reached root folder
                }
            }

            // Reverse the array so the root folder comes first
            $parents = array_reverse($parents);

            // Mark the current folder as active
            if (count($parents) > 0) {
                $parents[count($parents) - 1]['isActive'] = true;
            }
        }
        
        if(Gate::allows('can-see-all')) {
            $navContents = Content::whereNull('parent_id')
            ->where('is_folder', true)
            ->with(['children', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        } else {
            
            $navContents = Content::where(function ($query) {
                // Top-level folders accessible to the user
                $query->whereNull('parent_id')
                    ->where('is_folder', true)
                    ->where(function ($query) {
                        $query->whereHas('accessControls', function ($accessQuery) {
                            $accessQuery->where('user_id', Auth::id());
                        })
                        ->orWhereHas('children', function ($childQuery) {
                            $childQuery->whereHas('accessControls', function ($accessQuery) {
                                $accessQuery->where('user_id', Auth::id());
                            });
                        });
                    });

                // Orphaned children (parents are not accessible at all)
                $query->orWhere(function ($query) {
                    $query->whereNotNull('parent_id')
                        ->where('is_folder', true)
                        ->whereHas('accessControls', function ($accessQuery) {
                            $accessQuery->where('user_id', Auth::id());
                        })

                        
                        
                        
                        /*->whereDoesntHave('parent', function ($parentQuery) {
                            $parentQuery->where('is_folder', true)
                                ->whereHas('accessControls', function ($accessQuery) {
                                    $accessQuery->where('user_id', Auth::id());
                                });
                        })*/
                        ;
                });
            })
            ->with([
                'children' => function ($childQuery) {
                    $childQuery->where(function ($query) {
                        $query->whereHas('accessControls', function ($accessQuery) {
                            $accessQuery->where('user_id', Auth::id());
                        })
                        ->orWhereHas('children', function ($childQuery) {
                            $childQuery->whereHas('accessControls', function ($accessQuery) {
                                $accessQuery->where('user_id', Auth::id());
                            });
                        });
                    });
                },
                'parent', // Include parent for debugging or filtering
                'user'
            ])
            ->where('is_folder', true)
            ->orderBy('name', 'asc')
            ->distinct()
            ->get();

            
            foreach($navContents as $key => $navContent) {
                
                foreach($navContents as $navContent_inn) {
                    if(!empty($navContent_inn->children) && count($navContent_inn->children) >= 1){
                        foreach($navContent_inn->children as $navContent_inn_child) {
                            if($navContent_inn_child->id == $navContent->id) {
                                unset($navContents[$key]);
                            }
                        }
                    }
                }
                
                //return ($navContent);
                
                //die();
                
            }

            
            
        }
        
        

        return view('content.share', [
            'current_content' => $content,
            'mainContentAccessType' => $mainContentAccessType,
            'sharedContents' => $sharedContents,
            'fileType' => get_file_types(),
            'getFolder' => get_folders(),
            'parents' => $parents,
            'sharedFolderGuid' => $sharedFolderGuid,
            "navContents" => $navContents
        ]);
    }

    /**
     * Lists the most recently updated files for the authenticated user.
     */
    public function recentFiles(Request $request)
    {
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'updated_at');
        $user_order = $request->input('user_order', '');

        // Get top-level folders (no parent_id)
        $navContents = Content::whereNull('parent_id')
            ->where('is_folder', true)
            ->with(['children', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        if (!empty($user_order)) {
            $recentfiles = Content::where('is_folder', false)
                ->join('users', 'contents.user_id', '=', 'users.id')
                ->orderBy('users.name', $user_order)
                ->select('contents.*')
                ->with('user')
                ->paginate(10);
        } else {
            $recentfiles = Content::where('is_folder', false)
                ->orderBy($sort, $order)
                ->with('user', 'parent')
                ->paginate(10);
        }


        $recentfiles->each(function ($item) {
            if ($item->is_folder) {
                $item->svgPath = asset('assets/images/folder.svg');
            } else {
                $item->extension = pathinfo($item->name, PATHINFO_EXTENSION); // Get file extension
                $item->svgPath = asset('assets/file-icons/vivid/' . $item->extension . '.svg');

                // Check if the icon exists, else set a default icon
                $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $item->extension . '.svg');
                if (!file_exists($fileAbsolutePath)) {
                    $item->svgPath = asset('assets/file-icons/vivid/txt.svg'); // Default icon
                }
            }

            // Handle user avatar (if exists)
            $item->user->avatar = $item->user->avatar
                ? route('fetch.avatar', ['filename' => basename($item->user->avatar)])
                : null;
        });

        // Return the view with recent files and navigation folders
        return view('content.recent', [
            'files' => $recentfiles,
            'navContents' => $navContents,
            'fileType' => get_file_types(), // Assuming this function is defined elsewhere
        ]);
    }

    /**
     * Searches for folders by name (for folder selection dialogs, etc.).
     */
    public function folderSearch(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $query = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = 10;

        $folders = Content::query()
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%');
            })
            ->where('is_folder', true)
            ->paginate($perPage, ['*'], 'page', $page);

        foreach ($folders as $folder) {
            $folder->image = $folder->image ? route('fetch.image', ["filename" => basename($folder->image)]) : asset('assets/images/defaultFolder.png');
        }

        return response()->json($folders);
    }

    /**
     * Searches for folders for moving content, supporting navigation and search.
     */
    public function moveFolderSearch(Request $request)
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
                    $parentId = $parentContent->parent_id; // Update if the parent exists
                } else {
                    $parentId = $parentContent->id;
                }
            } else {
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
            ->where('is_folder', true)
            ->with('user')
            ->paginate($perPage, ['*'], 'page', $page);

        $contents->each(function ($item) {
            if ($item->is_folder) {
                $item->svgPath = asset('assets/images/folder.svg');
            } else {
                $item->extension = pathinfo($item->name, PATHINFO_EXTENSION);
                $item->svgPath = asset('assets/file-icons/vivid/' . $item->extension . '.svg');
                $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $item->extension . '.svg');
                if (!file_exists($fileAbsolutePath)) {
                    $item->svgPath = asset('assets/file-icons/vivid/txt.svg');
                }
            }

            $item->user->avatar = $item->user->avatar
                ? route('fetch.avatar', ['filename' => basename($item->user->avatar)])
                : asset('assets/images/default-avatar.png');
        });

        return response()->json(['contents' => $contents, 'parentId' => $parentId]);
    }

    /**
     * Returns a list of file types, optionally filtered by name.
     */
    public function fileTypes(Request $request)
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

    /**
     * Searches for users by name or email (for owner selection).
     */
    public function ownerSearch(Request $request)
    {
        $query = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = 10;

        $users = User::where('name', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->paginate($perPage, ['*'], 'page', $page);

        $users->each(function ($user) {
            $user->avatar = $user->avatar ? route('fetch.avatar', ["filename" => basename($user->avatar)]) : asset('assets/images/iconProfile.png');
        });

        return response()->json($users);
    }

    /**
     * Searches for users to share content with, excluding the current user.
     */
    public function shareOwnerSearch(Request $request)
    {
        $query = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = 10;

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%');
        })
            ->where('id', '!=', Auth::id())
            ->paginate($perPage, ['*'], 'page', $page);

        $users->each(function ($user) {
            $user->avatar = $user->avatar ? route('fetch.avatar', ["filename" => basename($user->avatar)]) : asset('assets/images/iconProfile.png');
        });

        return response()->json($users);
    }

    /**
     * Searches for content based on file name, type, owner, folder, and date modified.
     */
    public function search(Request $request)
    {
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'updated_at');
        $user_order = $request->input('user_order', '');

        $validated = $request->validate([
            'file_name' => 'nullable|string|max:255',
            'file_type' => 'nullable|exists:file_types,guid',
            'owner' => 'nullable|exists:users,id',
            'folder' => 'nullable|exists:contents,guid',
            'date_modified' => 'nullable|string|max:255',
        ]);

        $contentQuery = Content::query()
            ->with(['parent', 'user', 'children'])
            ->leftJoin('users', 'contents.user_id', '=', 'users.id')
            ->select('contents.*'); // Avoid selecting ambiguous columns

        // Apply sorting
        if (!empty($user_order)) {
            $contentQuery->orderBy('users.name', $user_order);
        } else if (!empty($sort) && in_array($sort, ['updated_at', 'created_at', 'name'])) {
            $contentQuery->orderBy($sort, $order);
        } else {
            $contentQuery->orderBy('updated_at', 'desc');
        }


        $modifiedFields = [];

        // Search by file name
        if (!empty($validated['file_name'])) {
            $contentQuery->where('contents.name', 'like', '%' . $validated['file_name'] . '%');
            $modifiedFields['file_name'] = $validated['file_name'];
        }

        // Filter by owner
        if (!empty($validated['owner'])) {
            $contentQuery->where('contents.user_id', $validated['owner']);
            $user = User::find($validated['owner']);
            $modifiedFields['owner_id'] = $validated['owner'];
            $modifiedFields['owner'] = $user ? $user->name : null;
        }

        // Filter by folder
        if (!empty($validated['folder'])) {
            $contentFolder = Content::where('guid', $validated['folder'])->first();
            if ($contentFolder) {
                $contentQuery->where('contents.parent_id', $contentFolder->id);
                $modifiedFields['folder_guid'] = $validated['folder'];
                $modifiedFields['folder'] = $contentFolder->name;
            }
        }

        // Filter by file type
        if (!empty($validated['file_type'])) {
            $fileType = FileType::where('guid', $validated['file_type'])->first();
            if ($fileType && $fileType->name === 'Folder') {
                $contentQuery->where('contents.is_folder', true);
                $modifiedFields['file_type'] = 'Folder';
                $modifiedFields['file_type_guid'] = $validated['file_type'];
            } elseif ($fileType) {
                $contentQuery->where(function ($query) use ($fileType) {
                    $fileType->fileExtensions->each(function ($extension) use ($query) {
                        $query->orWhere('contents.extension', 'like', '%' . $extension->name . '%');
                    });
                });
                $modifiedFields['file_type_guid'] = $validated['file_type'];
                $modifiedFields['file_type'] = $fileType->name;
            }
        }

        // Filter by date modified
        if (!empty($validated['date_modified'])) {
            try {
                $dateModified = \Carbon\Carbon::createFromFormat('m/d/Y', $validated['date_modified'])->format('Y-m-d');
                $contentQuery->whereDate('contents.updated_at', $dateModified);
                $modifiedFields['date_modified'] = $validated['date_modified'];
            } catch (\Exception $e) {
                // Handle invalid date format
                $modifiedFields['date_modified_error'] = 'Invalid date format';
            }
        }

        $contents = $contentQuery->paginate(10);

        // Fetch navigation contents
        $navContents = Content::whereNull('parent_id')
            ->where('is_folder', true)
            ->with(['children', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Add SVG paths and user avatars
        foreach ($contents as $content) {
            $content->svgPath = $content->is_folder
                ? asset('assets/file-icons/vivid/folder.svg')
                : asset('assets/file-icons/vivid/' . ($content->extension ?? 'txt') . '.svg');

            $filePath = public_path('assets/file-icons/vivid/' . ($content->extension ?? 'txt') . '.svg');
            if (!file_exists($filePath)) {
                $content->svgPath = asset('assets/file-icons/vivid/txt.svg');
            }

            if ($content->user) {
                $content->user->avatar = $content->user->avatar
                    ? route('fetch.avatar', ['filename' => basename($content->user->avatar)])
                    : null;
            }
        }

        return view('content.search_result', [
            'contents' => $contents,
            'navContents' => $navContents,
            'fileType' => get_file_types(),
            'modifiedFields' => $modifiedFields,
        ]);
    }

    /**
     * Shows trashed (soft-deleted) content for the authenticated user.
     */
    public function showTrashed(Request $request)
    {
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'trashed_at'); // Change default sort to 'trashed_at'
        $user_order = $request->input('user_order', '');

        // Get top-level folders (no parent_id)
        $navContents = Content::whereNull('parent_id')
            ->where('is_folder', true)  // Only folders
            ->with(['children', 'user']) // Eager load children and user relationships
            ->orderBy('created_at', 'desc')
            ->get();

        $trashedContent = TrashLog::with(['content', 'user']) // Join with content and user
            ->orderBy('trashed_at', 'desc');

        if (!empty($user_order)) {
            // Remove ordering by users.name since it may not exist in the context of TrashLog
            // $trashedContent->orderBy('users.name', $user_order);
        } else if (!empty($sort) && in_array($sort, ['trashed_at', 'created_at'])) { // Update sortable fields
            $trashedContent->orderBy($sort, $order);
        } else {
            $trashedContent->orderBy('trashed_at', 'desc');
        }

        $trashedContent = $trashedContent->paginate(10);

        $trashedContent->each(function ($item) {
            if ($item->content && $item->content->is_folder) {
                $item->svgPath = asset('assets/images/folder.svg');
            } elseif ($item->content) {
                $item->extension = pathinfo($item->content->name, PATHINFO_EXTENSION);
                $item->svgPath = asset('assets/file-icons/vivid/' . $item->extension . '.svg');
                $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $item->extension . '.svg');
                if (!file_exists($fileAbsolutePath)) {
                    $item->svgPath = asset('assets/file-icons/vivid/txt.svg');
                }
            }

            if ($item->user) {
                $item->user->avatar = $item->user->avatar
                    ? route('fetch.avatar', ['filename' => basename($item->user->avatar)])
                    : null;
            }
        });

        // Return the view with trashed content, nav contents, and pagination
        return view('content.trash', [
            'trashedContent' => $trashedContent,
            'navContents' => $navContents,
            'fileType' => get_file_types(),
            'getFolder' => get_folders(),
        ]);
    }

    /**
     * Moves content (file or folder) to the trash, recursively handling children.
     */
    public function moveToTrash($guid)
    {
        // Find the content by its GUID (could be a file or a folder)
        $content = Content::where('guid', $guid)->first();

        // Check if the content exists
        if (!$content) {
            return response()->json([
                'status' => 'error',
                'message' => 'Content not found or you do not have permission to delete it.',
            ], 404);
        }

        // Recursively move all child folders and files to trash, and delete them
        $this->moveToTrashRecursive($content);

        // Now, soft delete the content itself (folder or file)
        $content->delete();

        // Log the content deletion in TrashLog
        TrashLog::create([
            'guid' => $content->guid,
            'content_id' => $content->id,
            'user_id' => $content->user_id,
            'trashed_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Content and its related items moved to trash successfully.',
        ]);
    }

    /**
     * Recursively moves all child folders and files to trash.
     */
    private function moveToTrashRecursive($content)
    {
        // If it's a folder, handle its children first
        if ($content->is_folder == 1) {
            // Get all child folders (recursive deletion)
            $childFolders = Content::where('parent_id', $content->id)->where('is_folder', 1)->get();

            foreach ($childFolders as $childFolder) {
                // Recursively move child folders and their files to trash
                $this->moveToTrashRecursive($childFolder);
            }

            // Get all files within the current folder and delete them
            $filesInFolder = Content::where('parent_id', $content->id)->where('is_folder', 0)->get();
            foreach ($filesInFolder as $file) {
                $file->delete();  // Soft delete the file

            }
        }

        // Now delete the current content (folder or file itself)
        $content->delete(); // Soft delete the content
    }

    /**
     * Renames a file or folder by GUID.
     */
    public function rename(Request $request, $guid)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Find the content by its GUID
        $content = Content::where('guid', $guid)->first();

        if (!$content) {
            return response()->json(['error' => 'Content not found'], 404);
        }


        // Change the content's name to the new name from the request
        $content->name = $request->input('name');
        $content->save();


        return response()->json([
            'guid' => $content->guid,
            'newName' => $content->name,
        ]);
    }

    /**
     * Makes a copy of a file or folder (recursively for folders).
     */
    public function makeCopy($guid)
    {
        // Step 1: Find the content by GUID
        $content = Content::where('guid', $guid)->first();

        if (!$content) {
            return response()->json([
                'status' => 'error',
                'message' => 'Content not found or invalid GUID.',
            ], 404);
        }

        // Step 2: Check if the content is a file or a folder and call appropriate copy logic
        return $content->is_folder ? $this->copyFolder($content) : $this->copyFile($content);
    }

    /**
     * Creates a copy of a file, including its storage and database record.
     */
    private function copyFile($file)
    {
        // Step 3: Create a copy of the file with a new GUID and change its name
        $copyFile = $file->replicate();
        $copyFile->guid = Str::uuid(); // Assign a new GUID to the copied file
        $copyFile->name = 'Copy of ' . $file->name; // Prepend "Copy of" to the file name

        // Ensure the path is unique and create a new path
        $newFilePath = $this->getUniqueFilePath($file->path);
        $copyFile->path = $newFilePath; // Update the path in the copied file's record

        // Create the copy of the file on the disk
        $filePath = storage_path('app/private/' . $file->path); // Original file path
        $copyFilePath = storage_path('app/private/' . $newFilePath); // New copied file path

        // Ensure the directory exists
        $copyFileDir = dirname($copyFilePath);
        if (!is_dir($copyFileDir)) {
            mkdir($copyFileDir, 0755, true);
        }

        // Copy the file to the new location
        if (file_exists($filePath)) {
            copy($filePath, $copyFilePath);
        }

        $copyFile->save(); // Save the copied file

        return response()->json([
            'status' => 'success',
            'message' => 'File has been copied successfully.',
            'file' => $copyFile,
        ]);
    }

    /**
     * Creates a copy of a folder and all its contents recursively.
     */
    private function copyFolder($folder)
    {
        // Step 4: Create a copy of the main folder with a new GUID and change its name
        $copyFolder = $folder->replicate();
        $copyFolder->guid = Str::uuid(); // Assign a new GUID to the copied folder
        $copyFolder->name = 'Copy of ' . $folder->name; // Prepend "Copy of" to the folder name
        $copyFolder->save(); // Save the copied folder

        // Step 5: Copy files within the original folder
        $this->copyFilesInFolder($folder, $copyFolder);

        // Step 6: Recursively copy child folders and their contents
        $this->copyChildFolders($folder, $copyFolder);

        return response()->json([
            'status' => 'success',
            'message' => 'Folder and its related files have been copied successfully.',
        ]);
    }

    /**
     * Copies all files within a folder to a new folder.
     */
    private function copyFilesInFolder($folder, $copyFolder)
    {
        // Step 7: Copy all files within the folder to the new copied folder
        $files = Content::where('parent_id', $folder->id)->where('is_folder', 0)->get();

        foreach ($files as $file) {
            // Copy file to the new folder
            $copyFile = $file->replicate();
            $copyFile->guid = Str::uuid(); // Assign a new GUID to the copied file
            $copyFile->name = 'Copy of ' . $file->name; // Prepend "Copy of" to the file name
            $copyFile->parent_id = $copyFolder->id; // Assign the copied file to the copied folder

            // Ensure the path is unique and create a new path
            $newFilePath = $this->getUniqueFilePath($file->path);
            $copyFile->path = $newFilePath; // Update the path in the copied file's record

            // Create the copy of the file on the disk
            $filePath = storage_path('app/private/' . $file->path); // Original file path
            $copyFilePath = storage_path('app/private/' . $newFilePath); // New copied file path

            // Ensure the directory exists
            $copyFileDir = dirname($copyFilePath);
            if (!is_dir($copyFileDir)) {
                mkdir($copyFileDir, 0755, true);
            }

            // Copy the file to the new location
            if (file_exists($filePath)) {
                copy($filePath, $copyFilePath);
            }

            $copyFile->save(); // Save the copied file
        }
    }

    /**
     * Recursively copies all child folders and their contents.
     */
    private function copyChildFolders($parentFolder, $copyParentFolder)
    {
        // Step 8: Copy all child folders (and their files) recursively
        $childFolders = Content::where('parent_id', $parentFolder->id)->where('is_folder', 1)->get();

        foreach ($childFolders as $childFolder) {
            // Copy the child folder
            $copyChildFolder = $childFolder->replicate();
            $copyChildFolder->guid = Str::uuid(); // Assign a new GUID to the copied child folder
            $copyChildFolder->parent_id = $copyParentFolder->id; // Set the new parent to the copied folder
            $copyChildFolder->name = 'Copy of ' . $childFolder->name; // Prepend "Copy of" to the child folder's name
            $copyChildFolder->save(); // Save the copied child folder

            // Recursively copy files and child folders
            $this->copyFilesInFolder($childFolder, $copyChildFolder);
            $this->copyChildFolders($childFolder, $copyChildFolder);
        }
    }

    /**
     * Generates a unique file path for a copied file to avoid overwriting existing files.
     */
    private function getUniqueFilePath($filePath)
    {
        // Check if file already exists
        $fullPath = storage_path('app/private/' . $filePath);

        // If file exists, generate a unique file name by appending a random character
        if (file_exists($fullPath)) {
            $pathInfo = pathinfo($filePath);
            $newName = $pathInfo['filename']; // Keep the original filename

            // Add a random character from 'a' to 'z' at the end of the filename
            $randomChar = chr(rand(97, 122)); // Random character between 'a' and 'z'
            $newName .= '_' . $randomChar; // Append the random character

            $newFilePath = $pathInfo['dirname'] . '/' . $newName . '.' . $pathInfo['extension'];

            // Recursively check until a unique path is found
            return $this->getUniqueFilePath($newFilePath);
        }

        return $filePath; // If the file doesn't exist, return the path
    }

    /**
     * Moves content (file or folder) to a new parent folder, with validation.
     */
    public function moveContent(Request $request, $guid)
    {
        $contentGuid = $guid;
        $targetGuid = $request->targetId;

        // Find the content by GUID
        $content = Content::where('guid', $contentGuid)->first();

        if (!$content) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid content.',
            ], 400);
        }

        // If the target GUID is the same as the content GUID, return an error
        if ($contentGuid === $targetGuid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot move content to the same folder.',
            ], 400);
        }

        // Check if the target folder is a child or descendant of the content (not its parent)
        if ($this->isChildOrDescendant($content, $targetGuid)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot move content into a child or descendant folder.',
            ], 400);
        }

        // If the target GUID is empty, move the content to root (parent_id = null)
        if ($targetGuid === 'Myfolders') {
            $content->parent_id = null;
        } else {
            // Find the target folder by GUID
            $targetFolder = Content::where('guid', $targetGuid)->first();

            if (!$targetFolder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid target folder.',
                ], 400);
            }

            // Set the parent_id of the content to the target folder's id
            $content->parent_id = $targetFolder->id;
        }

        // Save the updated content
        $content->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Content moved successfully.',
        ]);
    }

    /**
     * Checks if a target folder is a child or descendant of the given content (to prevent circular moves).
     */
    private function isChildOrDescendant($content, $targetGuid)
    {
        // Start with the target folder and trace its parent chain
        $targetFolder = Content::where('guid', $targetGuid)->first();

        if (!$targetFolder) {
            return false; // Target folder not found
        }

        // Traverse the parent chain to see if any parent folder matches the content's GUID
        while ($targetFolder) {
            if ($targetFolder->guid === $content->guid) {
                return true; // Found a match, meaning the content is a parent of the target
            }

            // Move to the next parent folder
            $targetFolder = Content::find($targetFolder->parent_id);

            if (!$targetFolder) {
                break; // Reached the top of the hierarchy
            }
        }

        return false; // No child or descendant folder found
    }

    /**
     * Restores a trashed content item (and its children) by GUID.
     */
    public function restore($guid)
    {
        // Get the content by its GUID (with trashed items included)
        $content = Content::withTrashed()->where('guid', $guid)->first();

        // If content is not found, return an error
        if (!$content) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Content not found or you do not have permission to restore it.',
                ],
                404,
            );
        }

        if ($content->parent_id) {
            // Check if the parent content exists in the Content table
            $parentExists = Content::where('id', $content->parent_id)->exists();

            if (!$parentExists) {
                // If the parent doesn't exist, set the parent_id to null
                $content->parent_id = null;
                $content->save();
            }
        }

        // Start the restore process for the given content and its children
        $this->restoreContentAndChildren($content);

        // Delete the trash log entry for the GUID
        TrashLog::where('guid', $guid)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Content restored successfully.',
        ]);
    }

    /**
     * Recursively restores a content item and its children.
     */
    private function restoreContentAndChildren(Content $content)
    {
        // Restore the content itself
        $content->restore();

        // Get and restore all child content that is trashed (files and folders)
        $children = Content::onlyTrashed()
            ->where('parent_id', $content->id)
            ->get();

        // Loop through each child content and restore them
        foreach ($children as $child) {
            // Check if the child content's ID exists in the TrashLog (based on content_id)
            $isInTrashLog = TrashLog::where('content_id', $child->id)->exists();

            // If the child is not in the TrashLog, restore it
            if (!$isInTrashLog) {
                $this->restoreContentAndChildren($child);
            }
        }
    }

    /**
     * Permanently deletes a content item (and all its children) by GUID.
     */
    public function permanentDelete($guid)
    {
        $content = Content::withTrashed()->where('guid', $guid)->first();

        if (!$content) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Content not found or you do not have permission to delete it.',
                ],
                404,
            );
        }

        try {
            $childIds = $this->getAllChildIds($content);
            $childIds[] = $content->id;

            $this->deleteContentAndFiles(array_reverse($childIds));

            return response()->json([
                'status' => 'success',
                'message' => 'Content permanently deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while deleting the content.',
                ],
                500,
            );
        }
    }

    /**
     * Recursively gets all child content IDs for permanent deletion.
     */
    private function getAllChildIds(Content $content)
    {
        $childIds = [];

        $children = $content->allChildren()->get();

        foreach ($children as $child) {
            $childIds[] = $child->id;

            if ($child->is_folder) {
                $childIds = array_merge($childIds, $this->getAllChildIds($child));
            }
        }

        return $childIds;
    }

    /**
     * Deletes content and associated files from the file system and database.
     */
    private function deleteContentAndFiles(array $contentIds)
    {
        foreach ($contentIds as $contentId) {
            $content = Content::withTrashed()->find($contentId);

            if (!$content) {
                continue;
            }

            if ($content->path) {
                $this->deleteFileFromFileSystem($content);
            }
        }

        foreach ($contentIds as $contentId) {
            $content = Content::withTrashed()->find($contentId);

            if (!$content) {
                continue;
            }

            $this->deleteFileFromDatabase($content);
        }
    }

    /**
     * Deletes a file from the file system if it exists.
     */
    private function deleteFileFromFileSystem(Content $content)
    {
        if (!$content->path) {
            return;
        }

        $filePath = storage_path('app/private/' . $content->path);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Force deletes a content record from the database.
     */
    private function deleteFileFromDatabase(Content $content)
    {
        $content->forceDelete();
    }

    /**
     * Returns detailed information about a content item by GUID.
     */
    public function getContentInfo($guid)
    {

        // Fetch the content by its GUID (assuming 'guid' is stored in the 'id' field)
        $content = Content::where('guid', $guid)->with('user', 'parent')->first();

        if (!$content) {
            return response()->json([
                'status' => 'error',
                'message' => 'Content not found or permission denied.',
            ], 404);
        }

        // Determine the SVG icon path
        if ($content->is_folder) {
            $content->svgPath = asset('assets/images/folder.svg'); // Folder icon
        } else {
            $content->extension = pathinfo($content->name, PATHINFO_EXTENSION); // Get file extension
            $content->svgPath = asset('assets/file-icons/vivid/' . $content->extension . '.svg');

            // Check if the icon exists, else set a default icon
            $fileAbsolutePath = public_path('assets/file-icons/vivid/' . $content->extension . '.svg');
            if (!file_exists($fileAbsolutePath)) {
                $content->svgPath = asset('assets/file-icons/vivid/txt.svg'); // Default icon
            }
        }

        // Formatting the size if needed
        $formattedSize = $this->formatFileSize($content->size);

        return response()->json([
            'status' => 'success',
            'content' => [
                'name' => $content->name,
                'is_folder' => $content->is_folder,
                'user_id' => $content->user_id,
                'parent_id' => $content->parent_id,
                'parent_name' => $content->parent ? $content->parent->name : 'Root', // Include parent name
                'path' => $content->path,
                'size' => $formattedSize,
                'extension' => $content->extension,
                'deleted_at' => $content->deleted_at ? $content->deleted_at->format('Y-m-d H:i:s') : null,
                'created_at' => $content->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $content->updated_at->format('Y-m-d H:i:s'),
                'svgPath' => $content->svgPath, // Include SVG path
                'user' => [
                    'id' => $content->user->id,
                    'name' => $content->user->name,
                    'email' => $content->user->email,
                ],
            ],
        ]);
    }

    /**
     * Formats a file size in bytes to a human-readable string.
     */
    public function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            // GB
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            // MB
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            // KB
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            // Bytes
            return $bytes . ' bytes';
        }
    }

    /**
     * Handles file download for a content item, checking user permissions.
     */
    public function fileDownload($guid)
    {
        // Step 1: Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to download files.');
        }

        // Get the authenticated user
        $user = Auth::user();

        // Step 2: Check if the user is Super Admin or Admin
        if ($user->can('can-see-all')) {
            // If the user is Super Admin or Admin, they have access to all files
            return $this->downloadFile($guid);
        }

        // Get the authenticated user's ID
        $userId = $user->id;

        // Step 3: Retrieve the content by its GUID
        $file = Content::where('guid', $guid)->firstOrFail();

        // Step 4: Check if the user has access to the content
        if (!$this->hasAccessToContent($userId, $file->id)) {
            abort(403, 'File not found.');
        }

        // If access is granted, prepare the file for download
        return $this->downloadFile($guid);
    }

    /**
     * Prepares and returns a file for download by GUID.
     */
    private function downloadFile($guid)
    {
        // Retrieve the content by its GUID
        $file = Content::where('guid', $guid)->firstOrFail();

        // Log the file path to debug
        $filePath = storage_path('app/private/' . $file->path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Prepare the file for download
        $fileName = $file->name;
        $mimeType = $file->mime_type ?: mime_content_type($filePath); // Use fallback mime type if necessary

        return response()->download($filePath, $fileName, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Checks if a user has access to a content item, directly or via parent folders.
     */
    private function hasAccessToContent($userId, $contentId)
    {
        // Check if the user has direct access to the content
        $accessControl = AccessControl::where('user_id', $userId)
            ->where('content_id', $contentId)
            ->exists();

        if ($accessControl) {
            return true;
        }

        // If no direct access, check the content's parents recursively
        $content = Content::find($contentId);

        while ($content && $content->parent_id) {
            $content = Content::find($content->parent_id);

            // Check if the user has access to this parent content
            $accessControl = AccessControl::where('user_id', $userId)
                ->where('content_id', $content->id)
                ->exists();

            if ($accessControl) {
                return true;
            }
        }

        // If no access found for the content or its parents, return false
        return false;
    }
}
