<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Shared Files and Folders')

@section('content')

    <!-- Main section for the right bar and table -->
    <section class="custRightBarMn">
        <section class="custTableMn">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-12">
                        <div class="custTableSrch">
                            <!-- Show different search components based on user permission -->
                            @can('can-see-all')
                                @include('components.search')
                            @endcan
                            @cannot('can-see-all')
                                @include('components.shared_search')
                            @endcannot

                            <div class="custFilterTable">
                                <div class="custFilterTabledng">
                                    <h3>Search Result</h3>
                                </div>

                                <div class="custFilterTableMn">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            @if (isset($sharedContents))
                                                <thead>
                                                    <tr>
                                                        <!-- Sortable column: Name -->
                                                        <th class="sort" data-sort="name"
                                                            data-order="{{ request('order') === 'asc' ? 'desc' : 'asc' }}">
                                                            <a href="{{ route('visualizzatore.search', ['order' => request('order') === 'asc' ? 'desc' : 'asc', 'sort' => 'name', 'file_name' => request('file_name'), 'file_type' => request('file_type'), 'owner' => request('owner'), 'folder' => request('folder'), 'date_modified' => request('date_modified')]) }}">
                                                                Name
                                                                <i class="fa-solid {{ request('sort') === 'name' ? (request('order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                            </a>
                                                        </th>
                                                        <!-- Sortable column: Date Modified -->
                                                        <th class="sort" data-sort="updated_at"
                                                            data-order="{{ request('order') === 'asc' ? 'desc' : 'asc' }}">
                                                            <a href="{{ route('visualizzatore.search', ['order' => request('order') === 'asc' ? 'desc' : 'asc', 'sort' => 'updated_at', 'file_name' => request('file_name'), 'file_type' => request('file_type'), 'owner' => request('owner'), 'folder' => request('folder'), 'date_modified' => request('date_modified')]) }}">
                                                                Date Modified
                                                                <i class="fa-solid {{ request('sort') === 'updated_at' ? (request('order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                            </a>
                                                        </th>
                                                        <th>Location</th>
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="content-list">

                                                    @if ($sharedContents->count() > 0)
                                                        <!-- Loop through each shared content item -->
                                                        @foreach ($sharedContents as $accessControl)
                                                            <tr>
                                                                <td>
                                                                    <!-- Show folder or file with icon and name -->
                                                                    @if ($accessControl->is_folder)
                                                                        <a href="{{ route('list.contents.shared', $accessControl->guid) }}"
                                                                            class="custmclckbtnshared">
                                                                            <b><img src="{{ $accessControl->svgPath ? $accessControl->svgPath : asset('assets/images/folder.svg') }}"
                                                                                    alt="{{ $accessControl->extension }}"
                                                                                    style="width: 20px; height: 20px;">
                                                                                {{ $accessControl->name }}
                                                                            </b>
                                                                        </a>
                                                                    @else
                                                                        <b><img src="{{ $accessControl->svgPath ? $accessControl->svgPath : asset('assets/images/folder.svg') }}"
                                                                                alt="{{ $accessControl->extension }}"
                                                                                style="width: 20px; height: 20px;">
                                                                            {{ $accessControl->name }}
                                                                        </b>
                                                                    @endif
                                                                </td>
                                                                <!-- Date modified column -->
                                                                <td>{{ $accessControl->updated_at->format('d/m/Y') }}</td>
                                                                <!-- Location column: shows parent folder if available -->
                                                                <td>
                                                                    @if ($accessControl->parent && $accessControl->parent_id != null)
                                                                        <img src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                            alt="">
                                                                        <span>{{ $accessControl->parent ? $accessControl->parent->name : '__' }}</span>
                                                                    @else
                                                                        <span>__</span>
                                                                    @endif
                                                                </td>
                                                                <td style="opacity: 0;"></td>
                                                                <!-- Action menu (dots) -->
                                                                <td>
                                                                    <div class="ClickToOpen">
                                                                        <img src="{{ asset('assets/images/dots.svg') }}"
                                                                            alt="" class="custTablsDots">
                                                                        <div class="custShrDv">
                                                                            <div class="custShrDvCld">

                                                                                <ul>
                                                                                    <!-- Rename option (only for editors) -->
                                                                                    @if ($accessControl->access_type == 'editor')
                                                                                        <li>
                                                                                            <a href="javascript:void(0);"
                                                                                                class="folder-rename-link"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#renamepopupmodalfolder"
                                                                                                data-folder-id="{{ $accessControl->guid }}"
                                                                                                data-folder-name="{{ $accessControl->name }}">
                                                                                                <img src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                                    alt="">
                                                                                                Rename
                                                                                            </a>
                                                                                        </li>
                                                                                    @endif

                                                                                    <!-- File Info option -->
                                                                                    <li>
                                                                                        <a href="javascript:void(0);"
                                                                                            class="folder-detail-link"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#folderinfopopupmodal"
                                                                                            data-folder-id="{{ $accessControl->guid }}">
                                                                                            <img src="{{ asset('assets/images/iconInfo.svg') }}"
                                                                                                alt=""> File
                                                                                            Info
                                                                                        </a>
                                                                                    </li>
                                                                                    <!-- Download option (only for files) -->
                                                                                    @if ($accessControl->is_folder == 0)
                                                                                        <li>
                                                                                            <a href="{{ route('content.download', ['guid' => $accessControl->guid]) }}"
                                                                                                target="_blank"
                                                                                                data-file-guid="{{ $accessControl->guid }}">
                                                                                                <img src="{{ asset('assets/images/iconDownload.svg') }}"
                                                                                                    alt="">
                                                                                                Download
                                                                                            </a>
                                                                                        </li>
                                                                                    @endif

                                                                                </ul>

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <!-- No files found message -->
                                                        <tr>
                                                            <td colspan="5" class="text-center">No files found</td>
                                                        </tr>
                                                    @endif


                                                </tbody>
                                            @else
                                                <!-- No sharedContents variable available -->
                                                <tr>
                                                    <td colspan="5" class="text-center">No file or folder available.
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                        <!-- Pagination links -->
                                        <div class="d-flex justify-content-center">
                                            {{ $sharedContents->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Start Model Popup Code -->

                        <!-- Rename Folder Modal -->
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
                                            <button type="submit" class="btn btn-primary mt-3 mb-3 w-100">Rename
                                                Folder</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Folder/File Info Modal -->
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

                        <!-- End Model Popup Code -->
                    </div>
                </div>
            </div>
        </section>
    </section>

@endsection

@push('scripts')
    <script>
        // Show folder/file info in modal when info link is clicked
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
                        location.reload();

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
    </script>
@endpush
