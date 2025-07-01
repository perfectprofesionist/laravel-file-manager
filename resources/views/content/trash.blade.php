<!-- Main view for displaying trashed files and folders -->
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <!-- Main section for trashed content -->
    <section class="custRightBarMn">
        <section class="custTableMn">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-12">
                        <div class="custTableSrch">
                            <!-- Search bar component -->
                            @include('components.search')

                            <div class="custFilterTable">
                                <!-- Heading for trashed files -->
                                <div class="custFilterTabledng">
                                    <h3>Trashed Files</h3>
                                </div>

                                <div class="custFilterTableMn">

                                    <div class="table-responsive">
                                        <div class="table-responsive">
                                            <!-- Table for listing trashed files and folders -->
                                            <table class="table table-bordered">
                                                @if ($trashedContent->isNotEmpty())

                                                    <!-- Table headers for trashed content -->
                                                    <thead>
                                                        <tr>
                                                            <th class="sort" data-sort="name"
                                                                data-order="{{ request('order') === 'asc' ? 'desc' : 'asc' }}">
                                                                <a href="{{ route('trash', ['order' => request('order') === 'asc' ? 'desc' : 'asc', 'sort' => 'name']) }}">Name
                                                                    <i class="fa-solid {{ request('sort') === 'name' ? (request('order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                                </a>
                                                            </th>
                                                            <th class="sort" data-sort="user_order"
                                                                data-order="{{ request('user_order') === 'asc' ? 'desc' : 'asc' }}">
                                                                <a href="{{ route('trash', ['user_order' => request('user_order') === 'asc' ? 'desc' : 'asc', 'user_sort' => 'name']) }}">Owner
                                                                    <i class="fa-solid {{ request('user_sort') === 'name' ? (request('user_order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                                </a>
                                                            </th>
                                                            <th class="sort" data-sort="trashed_at"
                                                                data-order="{{ request('order') === 'asc' ? 'desc' : 'asc' }}">
                                                                <a href="{{ route('trash', ['order' => request('order') === 'asc' ? 'desc' : 'asc', 'sort' => 'trashed_at']) }}">Date
                                                                    <i class="fa-solid {{ request('sort') === 'trashed_at' ? (request('order') === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down' }}"></i>
                                                                </a>
                                                            </th>
                                                            <th>Location</th>
                                                            <th>&nbsp;</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="content-list">
                                                        <!-- Loop through each trashed content item -->
                                                        @foreach ($trashedContent as $log)
                                                            <tr>
                                                                <td>
                                                                    <b>
                                                                        <img src="{{ $log->svgPath ?? '' }}"
                                                                            alt="Content Icon"
                                                                            style="width: 20px; height: 20px;">
                                                                        {{ $log->content->name ?? 'No Name' }}
                                                                    </b>
                                                                </td>
                                                                <td>
                                                                    <img src="{{ $log->user->avatar ?? asset('assets/images/iconProfile.png') }}"
                                                                        alt="User Avatar" class="UsrImg">
                                                                    {{ $log->user->name ?? 'Unknown' }}
                                                                </td>
                                                                <td>{{ \Carbon\Carbon::parse($log->trashed_at)->format('d/m/Y') }}
                                                                </td>
                                                                <td>
                                                                    @if ($log->content->parent)
                                                                        <img src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                            alt="Parent Folder Icon">
                                                                        <span>{{ $log->content->parent->name }}</span>
                                                                    @else
                                                                        <span>__</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <!-- Action menu for each trashed file/folder -->
                                                                    <div class="ClickToOpen">
                                                                        <img src="{{ asset('assets/images/dots.svg') }}"
                                                                            alt="Options" class="custTablsDots">
                                                                        <div class="custShrDv">
                                                                            <div class="custShrDvCld">
                                                                                <ul>
                                                                                    @can('restore-content')
                                                                                        <!-- Restore action -->
                                                                                        <li>
                                                                                            <form
                                                                                                action="{{ route('content.restore', $log->guid) }}"
                                                                                                method="POST"
                                                                                                class="restore-file-form"
                                                                                                style="display: inline;">
                                                                                                @csrf
                                                                                                @method('POST')
                                                                                                <button type="submit"
                                                                                                    class="btn w-100 pl-50">
                                                                                                    <img src="{{ asset('assets/images/iconRestore.svg') }}"
                                                                                                        alt="">
                                                                                                    Restore</button>
                                                                                            </form>
                                                                                        </li>
                                                                                    @endcan
                                                                                    @can('permanent-delete-content')
                                                                                        <!-- Permanent delete action -->
                                                                                        <li>
                                                                                            <form
                                                                                                action="{{ route('content.deleteForever', $log->guid) }}"
                                                                                                method="POST"
                                                                                                class="delete-file-form"
                                                                                                style="display: inline;">
                                                                                                @csrf
                                                                                                @method('POST')
                                                                                                <button type="submit"
                                                                                                    class="btn w-100 pl-50">
                                                                                                    <img src="{{ asset('assets/images/iconTrash.svg') }}"
                                                                                                        alt=""> Delete
                                                                                                    Forever</button>
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
                                                    </tbody>
                                                @else
                                                    <!-- No trashed content available -->
                                                    <tr>
                                                        <td colspan="5">No trashed content available.</td>
                                                    </tr>
                                                @endif
                                            </table>
                                            <!-- Pagination links -->
                                            <div class="custTblPgntn">
                                                {!! $trashedContent->appends(request()->except('page'))->links('pagination::bootstrap-5') !!}
                                            </div>
                                        </div>

                                    </div>


                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Start Model Popup Code -->
                <!-- Modal for sharing (not currently used, placeholder for future) -->
                <div class="modal fade custModelNew" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Share
                                    <!-- This span for Dynamic Title --><span class="custDynamicTtl">“ Document 1 “</span>
                                </h5>
                            </div>
                            <div class="modal-body">
                                <div class="custMdlData">
                                    <div class="custMdlDataTp">
                                        <form action="">
                                            <ul>
                                                <li>Accessibility</li>
                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value=""
                                                            id="flexCheckDefault">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Only viewer
                                                        </label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value=""
                                                            id="flexCheckDefault2">
                                                        <label class="form-check-label" for="flexCheckDefault2">
                                                            Editor
                                                        </label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </form>
                                    </div>
                                    <div class="custMdlDataBtm">
                                        <h3>File link</h3>
                                        <div class="custMdlDataBtmLnk">
                                            <a href="#" class="custRstBtn"><span
                                                    class="custHdnOvflw">https://www.google.com/search?q=traduttore</span></a>
                                            <button class="custSrchBtn" type="submit">Copy link</button>
                                        </div>
                                    </div>
                                </div>
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
    <!-- JavaScript for handling restore and permanent delete actions for trashed files/folders -->
    <script>
        $(document).ready(function() {

            // Handle the restore action via AJAX
            $(document).on('submit', '.restore-file-form', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                form.closest('tr')
                                    .remove(); // Remove the file row from the list after restoring
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
                        Swal.fire({
                            title: 'Error',
                            text: 'There was an error processing your request.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Handle the delete forever action via AJAX
            $(document).on('submit', '.delete-file-form', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                form.closest('tr')
                                    .remove(); // Remove the file row from the list after permanent deletion

                                if ($('.content-list tr').length === 0) {
                                    $('.content-list').html(
                                        '<tr><td colspan="5" class="text-center">No trashed files available.</td></tr>'
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
                        Swal.fire({
                            title: 'Error',
                            text: 'There was an error processing your request.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });


        });
    </script>
@endpush
