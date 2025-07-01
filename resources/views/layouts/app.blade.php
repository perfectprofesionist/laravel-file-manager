<!doctype html>
<!-- Main application layout for all pages -->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token for security -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Main application styles -->
    @vite(['resources/sass/app.scss'])

    <style>
        .custRptDropdown ul.custAllChildUlPrnt li {
            position: relative;
            padding-top: 10px;
            padding-bottom: 10px;
        }
    </style>

</head>

<body class="{{ str_replace('.', '-', Route::currentRouteName()) }}">
    <div id="app">
        <!-- Header with logo and navigation -->
        <header class="custHeader" style="background-image:url('{{ asset('assets/images/headerBg.png') }}');">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12 align-self-center">
                        <div class="custHdrLft">
                            @can('can-see-all')
                                    <a href="{{ url('/') }}" class="fw-bold fs-4 text-primary text-decoration-none text-white">
                                        File Manager
                                    </a>
                            @endcan

                            @cannot('can-see-all')
                                    <a href="{{ url('/shared') }}" class="fw-bold fs-4 text-primary text-decoration-none text-white">
                                        File Manager
                                    </a>
                            @endcannot

                        </div>
                    </div>
                    @guest
                    @else
                        <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-8 col-sm-12 col-12">
                            <nav class="navbar navbar-expand-md navbar-light">
                                <div class="container">
                                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                        <!-- Right Side Of Navbar -->
                                        <div class="custNavBarHdr ms-auto">
                                            <div class="custNavBarHdrIcons">
                                                <ul>
                                                    <li><a href="{{ route('user.profile') }}"><img
                                                                src="{{ asset('assets/images/iconSetting.svg') }}" /></a>
                                                    </li>
                                                    <li><a href="#"><img
                                                                src="{{ asset('assets/images/iconFAQ.svg') }}" /></a></li>
                                                </ul>
                                            </div>
                                            <ul class="navbar-nav">
                                                <li class="nav-item dropdown">
                                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#"
                                                        role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false" v-pre>
                                                        <img src="{{ Auth::user()->avatar ? route('fetch.avatar', ['filename' => basename(Auth::user()->avatar)]) : asset('assets/images/iconProfile.png') }}"
                                                            alt="" class="rounded-circle" />
                                                        {{ Auth::user()->name }}
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end"
                                                        aria-labelledby="navbarDropdown">
                                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                                            onclick="event.preventDefault();
                                                                    document.getElementById('logout-form').submit();">
                                                            {{ __('Logout') }}
                                                        </a>
                                                        <form id="logout-form" action="{{ route('logout') }}"
                                                            method="POST" class="d-none">
                                                            @csrf
                                                        </form>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    @endguest
                </div>
            </div>
        </header>
        <main class="custMainTag">
            @guest
            @else
                <!-- Sidebar navigation for folders, recents, trash, and users -->
                <aside class="custSidebar">
                    @if (
                        !request()->routeIs('users.index') &&
                            !request()->routeIs('users.profile') &&
                            !request()->routeIs('user.profile') &&
                            !request()->routeIs('users.show') &&
                            !request()->routeIs('users.edit') &&
                            !request()->routeIs('users.create'))
                        @can('can-see-all')
                            <div class="custSidebarTp">
                                <div class="custUpldFlDv">
                                    @can('upload-file')
                                        <!-- Upload new files form -->
                                        <form action="{{ route('upload.file') }}" method="POST" enctype="multipart/form-data"
                                            id="upload-file-form">
                                            @csrf
                                            <input type="file" name="file[]" class="form-control upload-file-input" required
                                                id="file-upload" style="display: none;" multiple>
                                            <input type="hidden" name="folder_id" value="{{ $current_content->guid ?? '' }}">
                                            <a href="javascript:void(0)" class="custUpldFl1 upload-file-btn">Upload new files</a>
                                        </form>
                                    @endcan
                                </div>
                                <div class="custUpldFlDv">
                                    @can('create-folder')
                                        <!-- Button to open create folder modal -->
                                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#folderpopupmodal"
                                            class="custUpldFl2">Create new folder</a>
                                    @endcan
                                </div>
                            </div>
                        @endcan
                        <div class="custSidebarBtm">
                            <div class="custRptDropdown custShowDsktp">
                                @can('can-see-all')
                                    <!-- Desktop folder navigation tree -->
                                    <ul class="custAllChildUlPrnt">
                                   
                                        <li class="custHasChild">
                                            <a href="{{ route('list.contents') }}">
                                                <img class="custIconNormal" src="{{ asset('assets/images/iconFolder.svg') }}"
                                                    alt="">
                                                <img class="custIconActive"
                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}" alt="">
                                                My folders
                                            </a>
                                            <ul>
                                                @if (isset($navContents))
                                                    @foreach ($navContents as $content)
                                                        <li class="custHasChild">
                                                            <a href="{{ route('list.contents.folder', $content->guid) }}">
                                                                <img class="custIconNormal"
                                                                    src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                    alt="">
                                                                <img class="custIconActive"
                                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}"
                                                                    alt="">
                                                                {{ $content->name }}
                                                            </a>
                                                            @if ($content->children->isNotEmpty())
                                                                <ul>
                                                                    @foreach ($content->children as $childContent)
                                                                        <li class="custHasChild">
                                                                            <a
                                                                                href="{{ route('list.contents.folder', $childContent->guid) }}">
                                                                                <img class="custIconNormal"
                                                                                    src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                    alt="">
                                                                                <img class="custIconActive"
                                                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}"
                                                                                    alt="">
                                                                                {{ $childContent->name }}
                                                                            </a>
                                                                            @if ($childContent->children->isNotEmpty())
                                                                                <ul>
                                                                                    @foreach ($childContent->children as $subChildContent)
                                                                                        <li>
                                                                                            <a
                                                                                                href="{{ route('list.contents.folder', $subChildContent->guid) }}">
                                                                                                <img class="custIconNormal"
                                                                                                    src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                                    alt="">
                                                                                                <img class="custIconActive"
                                                                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}"
                                                                                                    alt="">
                                                                                                {{ $subChildContent->name }}
                                                                                            </a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </li>
                                    </ul>
                                @endcan
                            </div>
                            @can('can-see-all')
                                <!-- Mobile folder navigation -->
                                <div class="custRptDropdown custShowMbl">
                                    <h4>
                                        <a href="{{ route('list.contents') }}">
                                            <img class="custIconNormal" src="{{ asset('assets/images/iconFolder.svg') }}"
                                                alt="">
                                            <img class="custIconActive"
                                                src="{{ asset('assets/images/iconFolderActive.svg') }}" alt="">
                                            My folders
                                        </a>
                                    </h4>
                                </div>
                            @endcan
                            @cannot('can-see-all')
                                <!-- Shared folders navigation for users without 'can-see-all' -->
                                <div class="custRptDropdown">
                                    <ul class="custAllChildUlPrnt">
                                        <li class="@if (isset($navContents)) custHasChild @endif">
                                            <a href="{{ route('shared') }}">
                                                <img class="custIconNormal" src="{{ asset('assets/images/iconFolder.svg') }}"
                                                    alt="">
                                                <img class="custIconActive"
                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}" alt="">
                                                folders
                                            </a>
                                            <ul>
                                                @if (isset($navContents))
                                                    @foreach ($navContents as $content)
                                                        <li class="@if ($content->children->isNotEmpty()) custHasChild @endif">
                                                            <a href="{{ route('list.contents.shared', $content->guid) }}" title="{{$content->name}}">
                                                                <img class="custIconNormal"
                                                                    src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                    alt="">
                                                                <img class="custIconActive"
                                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}"
                                                                    alt="">
                                                                    {{ \Illuminate\Support\Str::limit($content->name, 14, '...') }}
                                                            </a>
                                                            @if ($content->children->isNotEmpty())
                                                                <ul>
                                                                    @foreach ($content->children as $childContent)
                                                                        <li class="@if ($childContent->children->isNotEmpty()) custHasChild @endif">
                                                                            <a
                                                                                href="{{ route('list.contents.shared', $childContent->guid) }}" title="{{$childContent->name}}">
                                                                                <img class="custIconNormal"
                                                                                    src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                    alt="">
                                                                                <img class="custIconActive"
                                                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}"
                                                                                    alt="">
                                                                                    {{ \Illuminate\Support\Str::limit($childContent->name, 13, '...') }}
                                                                            </a>
                                                                            @if ($childContent->children->isNotEmpty())
                                                                                <ul>
                                                                                    @foreach ($childContent->children as $subChildContent)
                                                                                        <li>
                                                                                            <a
                                                                                                href="{{ route('list.contents.shared', $subChildContent->guid) }}" title="{{$subChildContent->name}}">
                                                                                                <img class="custIconNormal"
                                                                                                    src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                                    alt="">
                                                                                                <img class="custIconActive"
                                                                                                    src="{{ asset('assets/images/iconFolderActive.svg') }}"
                                                                                                    alt="">
                                                                                                    {{ \Illuminate\Support\Str::limit($subChildContent->name, 12, '...') }}
                                                                                                
                                                                                            </a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </li>
                                    </ul>
                                </div>


                                
                            @endcannot
                            @can('can-see-all')
                                <!-- Recents navigation link -->
                                <div class="custRptDropdown">
                                    <h4>
                                        <a href="{{ route('recent') }}">
                                            <img class="custIconNormal" src="{{ asset('assets/images/iconClock.svg') }}"
                                                alt="">
                                            <img class="custIconActive"
                                                src="{{ asset('assets/images/iconClockActive.svg') }}" alt="">
                                            Recents
                                        </a>
                                    </h4>
                                </div>
                            @endcan
                            @can('can-see-all')
                                <!-- Trash navigation link -->
                                <div class="custRptDropdown">
                                    <h4>
                                        <a href="{{ route('trash') }}">
                                            <img class="custIconNormal" src="{{ asset('assets/images/iconTrash.svg') }}"
                                                alt="">
                                            <img class="custIconActive"
                                                src="{{ asset('assets/images/iconTrashActive.svg') }}" alt="">
                                            Trash
                                        </a>
                                    </h4>
                                </div>
                            @endcan
                            @can('view-user')
                                <!-- Manage users navigation link -->
                                <div class="custRptDropdown">
                                    <h4>
                                        <a href="{{ route('users.index') }}">
                                            <img class="custIconNormal" src="{{ asset('assets/images/iconUsers.svg') }}"
                                                alt="">
                                            <img class="custIconActive"
                                                src="{{ asset('assets/images/iconUsersActive.svg') }}" alt="">
                                            Manage Users
                                        </a>
                                    </h4>
                                </div>
                            @endcan
                        </div>
                    @else
                        @can('can-see-all')
                            <div class="custSidebarTp">
                                <div class="custUpldFlDv">
                                    <div>
                                        <a href="{{ route('list.contents') }}" class="custUpldFl1">
                                            Back to Home
                                        </a>
                                    </div>
                                </div>
                                @can('view-user')
                                    <div class="custUpldFlDv">
                                        <a href="{{ route('users.index') }}" class="custUpldFl2">Manage Users</a>
                                    </div>
                                @endcan
                            </div>
                        @endcan
                        @cannot('can-see-all')
                            <div class="custSidebarTp">
                                <div class="custUpldFlDv">
                                    <div>
                                        <a href="{{ route('shared') }}" class="custUpldFl1">
                                            Back to Home
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endcannot
                    @endif
                </aside>

            @endguest

            <!-- Main content for each page -->
            @yield('content')
        </main>

        <!-- Modal for creating a new folder -->
        <div class="modal fade custModelNew" id="folderpopupmodal" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Create Folder</h5>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('create.folder') }}" method="POST" class="mb-4" id="folder-form">
                            @csrf
                            <input type="text" name="name" id="folder-name" class="form-control"
                                placeholder="Enter Folder Name" required>
                            <input type="hidden" name="parent_id" value="{{ $current_content->guid ?? '' }}">
                            <button type="submit" class="btn btn-primary mt-3 mb-3 w-100">Create Folder</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Main application scripts and libraries -->
    @vite(['resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

    <script>
        window.addEventListener('load', function() {

            // Show a SweetAlert popup if a "success" session message exists
            @if (session('success'))

                Swal.fire({
                    title: 'Success',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            // Show a SweetAlert popup if an "error" session message exists
            @if (session('error'))

                Swal.fire({
                    title: 'Error',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif

            // Show a SweetAlert popup if a "warning" session message exists
            @if (session('warning'))

                Swal.fire({
                    title: 'Warning',
                    text: "{{ session('warning') }}",
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            @endif

            // Show a SweetAlert popup if an "info" session message exists
            @if (session('info'))

                Swal.fire({
                    title: 'Info',
                    text: "{{ session('info') }}",
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            @endif
        });

        jQuery(document).ready(function($) {

            // Trigger file input click when a custom upload button is clicked
            $(document).on('click', '.upload-file-btn', function() {
                $(this).siblings('.upload-file-input').click();
            });

            $(document).on('change', '.upload-file-input', function() {
                $(this).closest('form').submit();
            });

            // Handle AJAX file upload on form submit
            $(document).on('submit', '#upload-file-form', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                        }).then(function() {
                            // $('.content-list').prepend(response.files);
                            window.location.reload();
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                            title: 'Error',
                            text: response.responseJSON.message ||
                                'An error occurred while uploading the file.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

        });
    </script>
    @stack('scripts')

</body>

</html>
