{{-- Extends the main application layout --}}
@extends('layouts.app')

{{-- Main content section for user management and settings --}}
@section('content')
    <section class="custRightBarMn custUserTblMnSec">
        <section class="custTableMn">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-12">
                        <div class="custUseStng">
                            <div class="custFilterTabledng">
                                {{-- Settings section title --}}
                                <h2>Settings</h2>
                            </div>
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    {{-- Display current user's avatar and name --}}
                                    <img src="{{ Auth::user()->avatar ? route('fetch.avatar', ['filename' => basename(Auth::user()->avatar)]) : asset('assets/images/iconProfile.png') }}"
                                        alt="" class="rounded-circle UsrImg" style="width: 30px; height: 30px;" />
                                    {{ Auth::user()->name }}
                                </li>
                            </ul>
                        </div>


                        <div class="custFilterTable">
                            {{-- Search form for filtering users by name --}}
                            <form method="GET" action="{{ route('users.index') }}" class="d-flex w-100 mb-3">
                                <input type="text" name="name"
                                    class="border border-secondary-subtle rounded-2 px-3 w-50 me-2"
                                    placeholder="Search users" value="{{ request()->name }}">
                                <button type="submit" class="btn-custom-new btn">Search</button>
                            </form>
                            <div class="custFilterTabledng">
                                {{-- User management section title and create user button --}}
                                <h3>User Management</h3>
                                <span class="custUpldFlDv">
                                    <a class="btn btn-success mb-2 custCrtUsrBtn custUpldFl1"
                                        href="{{ route('users.create') }}"><i class="fa fa-plus"></i> Create New User</a>
                                </span>
                            </div>
                            <div class="custFilterTableMn">
                                <div class="table-responsive">
                                    {{-- Table displaying all users with their details and actions --}}
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Roles</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <div class="user_table_body">
                                                {{-- Loop through each user and display their information and actions --}}
                                                @foreach ($data as $key => $user)
                                                    <tr>
                                                        <td><img src="{{ $user->avatar ?? asset('assets/images/iconProfile.png') }}"
                                                                class="UsrImg">
                                                            {{ $user->name }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>
                                                            {{-- Display user roles or a placeholder if none --}}
                                                            @if ($user->getRoleNames()->isNotEmpty())
                                                                @foreach ($user->getRoleNames() as $role)
                                                                    {{ $role }}
                                                                @endforeach
                                                            @else
                                                                <span>__</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{-- Dropdown menu for user actions: Share, Edit, Delete --}}
                                                            <div class="ClickToOpen">
                                                                <img src="{{ asset('assets/images/dots.svg') }}"
                                                                    alt="" class="custTablsDots">
                                                                <div class="custShrDv">
                                                                    <div class="custShrDvCld">
                                                                        <ul>
                                                                            {{-- <li><a
                                                                                    href="{{ route('users.show', $user->id) }}"><i
                                                                                        class="fa-solid fa-list"></i>
                                                                                    Show</a></li> --}}
                                                                            <li><a
                                                                                    href="{{ route('users.edit', $user->id) }}#topSearchInput">
                                                                                    <img src="{{ asset('assets/images/iconShare.svg') }}"
                                                                                        alt="" srcset="">
                                                                                    Share</a></li>
                                                                            <li><a
                                                                                    href="{{ route('users.edit', $user->id) }}">
                                                                                    <img src="{{ asset('assets/images/iconedit.svg') }}"
                                                                                        alt="" srcset="">
                                                                                    Edit</a></li>
                                                                            <li>
                                                                                <form method="POST"
                                                                                    action="{{ route('users.destroy', $user->id) }}"
                                                                                    style="display:inline">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit"
                                                                                        class="btn w-100 pl-50">
                                                                                        <img src="{{ asset('assets/images/iconTrash.svg') }}"
                                                                                            alt="" srcset="">
                                                                                        Delete</button>
                                                                                </form>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </div>
                                        </tbody>
                                    </table>
                                    {{-- Pagination for users table --}}
                                    <div class="custTblPgntn">
                                        {!! $data->links('pagination::bootstrap-5') !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
@endsection
