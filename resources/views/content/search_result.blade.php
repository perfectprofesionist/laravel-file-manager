@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
    <!-- Main section for displaying search results -->
    <section class="custRightBarMn">
        <section class="custTableMn">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-12">
                        <div class="custTableSrch">

                            <!-- Search bar component with modified fields -->
                            @include('components.search', ['modifiedFields' => $modifiedFields])

                            <div class="custFilterTable">
                                <div class="custFilterTabledng">
                                    <h3>Search Results</h3>
                                </div>
                                @if ($contents->isNotEmpty())
                                    <div class="custFilterTableMn">
                                        <div class="table-responsive">
                                            <!-- Table for listing search results -->
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <!-- Sortable columns for name, owner, and date modified -->
                                                        <th class="sort" data-sort="name"
                                                            data-order="{{ request('order') === 'asc' ? 'desc' : 'asc' }}">
                                                            <a
                                                                href="{{ route('files.search', ['order' => request('order') === 'asc' ? 'desc' : 'asc', 'sort' => 'name', 'file_name' => request('file_name'), 'file_type' => request('file_type'), 'owner' => request('owner'), 'folder' => request('folder'), 'date_modified' => request('date_modified')]) }}">
                                                                Name
                                                                <i
                                                                    class="fa-solid {{ request('sort') === 'name' ? (request('order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                            </a>
                                                        </th>
                                                        <th class="sort" data-sort="user_order"
                                                            data-order="{{ request('user_order') === 'asc' ? 'desc' : 'asc' }}">
                                                            <a
                                                                href="{{ route('files.search', ['user_order' => request('user_order') === 'asc' ? 'desc' : 'asc', 'user_sort' => 'name', 'file_name' => request('file_name'), 'file_type' => request('file_type'), 'owner' => request('owner'), 'folder' => request('folder'), 'date_modified' => request('date_modified')]) }}">
                                                                Owner
                                                                <i
                                                                    class="fa-solid {{ request('user_sort') === 'name' ? (request('user_order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                            </a>
                                                        </th>
                                                        <th class="sort" data-sort="updated_at"
                                                            data-order="{{ request('order') === 'asc' ? 'desc' : 'asc' }}">
                                                            <a
                                                                href="{{ route('files.search', ['order' => request('order') === 'asc' ? 'desc' : 'asc', 'sort' => 'updated_at', 'file_name' => request('file_name'), 'file_type' => request('file_type'), 'owner' => request('owner'), 'folder' => request('folder'), 'date_modified' => request('date_modified')]) }}">
                                                                Date Modified
                                                                <i
                                                                    class="fa-solid {{ request('sort') === 'updated_at' ? (request('order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                            </a>
                                                        </th>
                                                        <th>Location</th>
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="content-list">
                                                    <!-- Loop through each search result item (file or folder) -->
                                                    @if ($contents->isNotEmpty())
                                                        @foreach ($contents as $content)
                                                            <tr>
                                                                <td>
                                                                    @if ($content->is_folder)
                                                                        <!-- Folder link -->
                                                                        <a class="custmclckbtnshared"
                                                                            href="{{ route('list.contents.folder', $content->guid) }}">
                                                                            <b><img src="{{ $content->svgPath }}"
                                                                                    alt="{{ $content->extension }}"
                                                                                    style="width: 20px; height: 20px;">
                                                                                {{ $content->name }}
                                                                            </b>
                                                                        </a>
                                                                    @else
                                                                        <!-- File display -->
                                                                        <b><img src="{{ $content->svgPath }}"
                                                                                alt="{{ $content->extension }}"
                                                                                style="width: 20px; height: 20px;">
                                                                            {{ $content->name }}
                                                                        </b>
                                                                    @endif
                                                                </td>
                                                                <td><img src="{{ $content->user->avatar ? $content->user->avatar : asset('assets/images/iconProfile.png') }}"
                                                                        class="UsrImg">
                                                                    {{ $content->user->name == Auth::user()->name ? 'Me' : $content->user->name }}
                                                                </td>
                                                                <td>{{ $content->updated_at->format('d/m/Y') }}</td>
                                                                <td>
                                                                    @if ($content->parent)
                                                                        @if ($content->parent->is_folder == 1)
                                                                            <!-- Link to parent folder if available -->
                                                                            <a class="custmclckbtnshared"
                                                                                href="{{ route('list.contents.folder', $content->parent->guid) }}">
                                                                                <img src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                    alt="">
                                                                                <span>{{ $content->parent->name }}</span>
                                                                            </a>
                                                                        @else
                                                                            <!-- Show parent file info if not a folder -->
                                                                            <img src="{{ $content->parent->svgPath ? asset($content->parent->svgPath) : asset('assets/images/folder.svg') }}"
                                                                                alt="{{ $content->parent->extension }}"
                                                                                style="width: 20px; height: 20px;">
                                                                            {{ $content->parent->name }}
                                                                        @endif
                                                                    @else
                                                                        <span>__</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <!-- Action menu for each file/folder -->
                                                                    <div class="ClickToOpen">
                                                                        <img src="{{ asset('assets/images/dots.svg') }}"
                                                                            alt="" class="custTablsDots">
                                                                        <div class="custShrDv">
                                                                            <div class="custShrDvCld">
                                                                                @can('content-move')
                                                                                    <ul>
                                                                                        <!-- Move to folder action -->
                                                                                        <li class="custHasCldMenu">
                                                                                            <img src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                                alt=""> Move
                                                                                            to
                                                                                            <ul>
                                                                                                <li>
                                                                                                    <a href="javascript:void(0);"
                                                                                                        id="moveToFolderLink"
                                                                                                        class="folder-info-link"
                                                                                                        data-bs-toggle="modal"
                                                                                                        data-bs-target="#moveToFolderInOtherModal"
                                                                                                        data-folder-guid="{{ $content->guid }}"
                                                                                                        data-folder-name = "{{ $content->name }}">
                                                                                                        <img src="{{ asset('assets/images/iconMove.svg') }}"
                                                                                                            alt="">
                                                                                                        Select
                                                                                                        Folder
                                                                                                    </a>
                                                                                                </li>
                                                                                            </ul>
                                                                                        </li>
                                                                                    @endcan
                                                                                    @if ($content->is_folder == 0)
                                                                                        <!-- Download file action -->
                                                                                        <li>
                                                                                            <a href="{{ route('content.download', ['guid' => $content->guid]) }}"
                                                                                                target="_blank"
                                                                                                data-file-guid="{{ $content->guid }}">
                                                                                                <img src="{{ asset('assets/images/iconDownload.svg') }}"
                                                                                                    alt="">
                                                                                                Download
                                                                                            </a>
                                                                                        </li>
                                                                                    @endif

                                                                                    @can('content-copy')
                                                                                        <!-- Make a copy action -->
                                                                                        <li>
                                                                                            <form
                                                                                                action="{{ route('content.makeCopy', $content->guid) }}"
                                                                                                method="POST"
                                                                                                class="make-copy-form-folder"
                                                                                                data-folder-id="{{ $content->guid }}">
                                                                                                @csrf
                                                                                                @method('POST')
                                                                                                <button type="submit"
                                                                                                    class="btn w-100 pl-50">
                                                                                                    <img src="{{ asset('assets/images/iconCopy.svg') }}"
                                                                                                        alt=""> Make a
                                                                                                    copy
                                                                                                </button>
                                                                                            </form>
                                                                                        </li>
                                                                                    @endcan

                                                                                    <!-- Rename action -->
                                                                                    <li>
                                                                                        <a href="javascript:void(0);"
                                                                                            class="folder-rename-link"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#renamepopupmodalfolder"
                                                                                            data-folder-id="{{ $content->guid }}"
                                                                                            data-folder-name="{{ $content->name }}">
                                                                                            <img src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                                alt="">
                                                                                            Rename
                                                                                        </a>
                                                                                    </li>

                                                                                    <!-- File/folder info action -->
                                                                                    <li>
                                                                                        <a href="javascript:void(0);"
                                                                                            class="folder-detail-link"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#folderinfopopupmodal"
                                                                                            data-folder-id="{{ $content->guid }}">
                                                                                            <img src="{{ asset('assets/images/iconInfo.svg') }}"
                                                                                                alt=""> File
                                                                                            Info
                                                                                        </a>
                                                                                    </li>


                                                                                    @can('content-delete')
                                                                                        <!-- Move to trash action -->
                                                                                        <li>
                                                                                            <form
                                                                                                action="{{ route('content.moveToTrash', $content->guid) }}"
                                                                                                method="POST"
                                                                                                class="move-to-trash-folder-form"
                                                                                                style="display: inline;">
                                                                                                @csrf
                                                                                                @method('POST')
                                                                                                <button type="submit"
                                                                                                    class="btn w-100 pl-50">
                                                                                                    <img src="{{ asset('assets/images/iconTrash.svg') }}"
                                                                                                        alt=""> Move to
                                                                                                    trash

                                                                                                </button>
                                                                                            </form>
                                                                                        </li>
                                                                                    @endcan
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif



                                                </tbody>
                                            </table>
                                            <!-- Pagination links -->
                                            <div class="d-flex justify-content-center">
                                                {{ $contents->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex justify-content-center">
                                        No files found
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>


                <!-- Start Model Popup Code -->
                {{-- Model for Folders --}}
                <!-- Modal for renaming a folder -->
                <div class="modal fade custModelNew" id="renamepopupmodalfolder" tabindex="-1"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Rename Folder</h5>
                            </div>
                            <div class="modal-body">
                                <!-- Form to rename the folder -->
                                <form id="rename-form-folder" method="POST" action="">
                                    @csrf
                                    @method('POST') <!-- We use POST to send the rename request -->
                                    <input type="text" name="name" id="old-name" class="form-control"
                                        placeholder="Enter Folder Name" required>
                                    <input type="hidden" id="folder-id" name="id">
                                    <!-- Hidden input to store folder ID -->
                                    <button type="submit" class="btn btn-primary mt-3 mb-3 w-100">Rename Folder</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Modal for file/folder info -->
                <div class="modal fade" id="folderinfopopupmodal" tabindex="-1"
                    aria-labelledby="folderinfopopupmodalLabel" aria-hidden="true">
                    <div class="modal-dialog">

                        <div class="modal-content">

                            <div class="modal-header" style="display: none">
                                <input type="hidden" id="content-id" name="content-id">

                                <h5 class="modal-title" id="folderinfopopupmodalLabel">
                                    <div class="custmmodalheading">
                                        <img id="content-icon" src="" alt="Content Icon">
                                        <p id="folder-info-name"></p>
                                    </div>
                                </h5>
                            </div>
                            <div class="modal-body">
                                <div class="loader"></div>
                                <!-- Common Content Information -->
                                <div class="content-info" style="display: none;">
                                    <p id="folder-info-parent"></p>
                                    <p id="folder-info-size"></p>
                                    <p id="folder-info-type"></p>
                                    <hr>
                                    <p id="folder-info-user-name"></p>
                                    <p id="folder-info-user-email"></p>
                                    <button type="button" class="flex flex-end btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal for moving a file/folder to another folder -->
                <div class="modal fade" id="moveToFolderInOtherModal" tabindex="-1"
                    aria-labelledby="moveToFolderInOtherModal" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content ModalContent">
                            <div class="modal-header">
                                <h5 class="modal-title" id="moveToFolderInOtherModal">Select a Target Folder to move</h5>
                                <button type="button" class="CloseBtn btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Search input for filtering folders -->
                                <div class="mb-3">
                                    <input type="text" id="folderSearchInput" class="form-control"
                                        placeholder="Search folders..." aria-label="Search folders">
                                </div>

                                <div class="mb-3 selectFolderPopup">
                                    <button type="button" id="folderHomeButton">
                                        <img src="{{ asset('assets/images/iconHome.png') }}" alt="Home">
                                    </button>
                                    <button type="button" id="folderBackButton">
                                        Up One Level
                                        <img src="{{ asset('assets/images/iconBack.png') }}" alt="Back"
                                            style="transform: rotate(90deg);">
                                    </button>
                                </div>

                                <!-- Simplified grid view for files and folders -->
                                <div class="loader" style="display: none;"></div>
                                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-1"
                                    id="folderContentGrid">
                                    {{-- Content items will be loaded here --}}
                                </div>

                            </div>
                            <input type="hidden" id="selected-id" name="folderId" value="">
                            <input type="hidden" id="id-to-move" name="targetId" value="">
                            <input type="hidden" id="parent_id" value="">

                            <div class="modal-footer">
                                <div
                                    class="ms-3 text-muted small text-center w-60 me-auto d-flex justify-content-start align-items-center">
                                    <em>Note: Click to select, double-click to open.</em>
                                </div>
                                <button type="button" id="moveFolderBtn" class="btn btn-primary">Move</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- End Model Popup Code -->
            </div>
        </section>
    </section>
@endsection

@push('scripts')
    <!-- JavaScript for handling file/folder info, renaming, moving, copying, and trash actions -->
    <script>
        // Show file/folder info modal and load details via AJAX
        $('.folder-detail-link').on('click', function() {
            var loader = $('.loader');
            var contentguid = $(this).attr(
                'data-folder-id'); // Get the content ID (GUID) from the clicked content's data attribute

            $('#content-id').val(contentguid);

            loader.show();
            $('.modal-header').hide();
            $('.content-info').hide();

            var contentId = $('#content-id').val();
            $('.content-info').val(contentguid);
            // Make an AJAX request to fetch content details
            $.ajax({
                url: '/content-info/' + contentId, // API endpoint
                method: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        loader.hide();
                        // Populate the modal with common content information
                        $('#folder-info-name').text(response.content.name ||
                            '__');
                        $('#folder-info-parent').text('Parent: ' + (response.content.parent_name ||
                            'Root'));
                        $('#folder-info-user-name').text('Owner: ' + (response.content.user.name ||
                            '__'));
                        $('#folder-info-user-email').text('Email: ' + (response.content.user.email ||
                            '__'));

                        // Check if content is a folder (is_folder === 1) or a file (is_folder === 0)
                        if (response.content.is_folder === 1) {
                            // If it's a folder, show only basic details
                            $('#folder-info-size').text('');
                            $('#folder-info-type').text('Type: Folder');
                        } else {
                            // If it's a file, show all the details (including size, mime_type, etc.)
                            $('#folder-info-size').text('Size: ' + (response.content.size || '__'));
                            $('#folder-info-type').text('Type: ' + (response.content.extension ||
                                '__'));
                        }

                        // Set the SVG icon dynamically based on the response
                        $('#content-icon').attr('src', response.content.svgPath);

                        $('.content-info').show();
                        $('.modal-header').show();

                        // Show the modal
                        // $('#folderinfopopupmodal').modal('show');
                    }
                },
                error: function(xhr, status, error) {
                    loader.hide();
                    console.error("Error fetching content details:", error);
                }
            });
        });

        // When the 'rename' link for a folder is clicked
        $('.folder-rename-link').on('click', function() {
            var folderId = $(this).attr(
                'data-folder-id'); // Get the folder ID from the clicked link's data attribute
            var folderName = $(this).attr('data-folder-name'); // Get the folder's current name

            $('#folder-id').val(folderId); // Store the folder ID in the hidden input field
            $('#old-name').val(folderName); // Set the current folder name in the input field
            $('#rename-form-folder').attr('action', '/folder/' + folderId + '/rename');
        });

        // Handle the form submission for renaming a folder
        $(document).on('submit', '#rename-form-folder', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Create the payload object from form data
            var payload = {
                name: $('#old-name').val(), // Get the new folder name value
                id: $('#folder-id').val(), // Get the folder ID value
                _token: $('meta[name="csrf-token"]').attr('content') // CSRF token for security
            };

            // Perform the AJAX request to rename the folder
            $.ajax({
                url: '/content/' + payload.id + '/rename', // Build the URL for the request dynamically
                type: 'POST', // POST request because we are sending the data
                data: JSON.stringify(payload), // Send the data as JSON
                contentType: 'application/json', // Set the content type as JSON
                headers: {
                    'X-CSRF-TOKEN': payload._token // Include the CSRF token in the headers
                },
                success: function(response) {
                    // Show success message using SweetAlert2
                    Swal.fire({
                        title: 'Success',
                        text: response.message || 'Folder renamed successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        // Optionally, reset the form fields (if needed)
                        // Reset the form inputs
                        //$('#renamepopupmodalfolder').modal('hide'); // Close the modal after successful rename
                        window.location.reload();

                    });
                },
                error: function(response) {
                    // Handle errors and display an error message
                    Swal.fire({
                        title: 'Error',
                        text: response.responseJSON.message ||
                            'An error occurred while renaming the folder.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        $(document).on('submit', '.move-to-trash-folder-form', function(e) {
            e.preventDefault(); // Prevent the form from being submitted in the traditional way

            var form = $(this); // Store the form reference
            var formData = new FormData(this); // Create FormData object

            $.ajax({
                url: form.attr('action'), // Get the action URL from the form
                type: 'POST',
                data: formData, // Send the FormData object
                processData: false, // Don't process the data as query string
                contentType: false, // Let jQuery handle content-type
                success: function(response) {
                    // Handle success
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Remove the specific row corresponding to the form
                            form.closest('tr').remove();

                            // Optionally, check if there are any remaining files in the list
                            if ($('.content-list tr').length === 0) {
                                $('.content-list').html(
                                    '<tr><td colspan="5" class="text-center">No  file available.</td></tr>'
                                );
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error if the AJAX request fails
                    Swal.fire({
                        title: 'Error',
                        text: 'There was an error processing your request.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        $(document).on('submit', '.make-copy-form-folder', function(e) {
            e.preventDefault(); // Prevent default form submission

            var form = $(this); // The form itself
            var formData = new FormData(form[0]); // Collect the form data

            $.ajax({
                url: form.attr('action'), // Use the action attribute of the form for the URL
                type: 'POST',
                data: formData, // Send the form data
                processData: false, // Disable processing of data as a query string
                contentType: false, // Don't set content type to avoid the browser trying to guess it
                success: function(response) {
                    if (response.status === 'success') {
                        // Show success notification using Swal
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.reload();
                        });

                        // Check if response contains the copied folder and files
                        if (response.folder) {
                            // Prepend the new copied folder to the folder list
                            $('.folder-list').prepend(response.folder);
                        }

                        if (response.files && response.files.length > 0) {
                            // Prepend each new copied file to the content list
                            response.files.forEach(function(file) {
                                $('.content-list').prepend(file);
                            });
                        }

                        // Optionally, you can reload or dynamically update content
                        // location.reload(); // Uncomment if you need a full reload
                    } else {
                        // If response status is not success, show an error message
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(response) {
                    // Show error message if the request fails
                    Swal.fire({
                        title: 'Error',
                        text: 'An error occurred while copying the folder or files.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>
@endpush
