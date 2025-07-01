{{-- Extends the main application layout --}}
@extends('layouts.app')

{{-- Main content section for Role Management --}}
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                {{-- Page title for Role Management --}}
                <h2>Role Management</h2>
            </div>
            <div class="pull-right">
                {{-- Button to create a new role, visible only to users with 'role-create' permission --}}
                @can('role-create')
                    <a class="btn btn-success btn-sm mb-2" href="{{ route('roles.create') }}"><i class="fa fa-plus"></i> Create New Role</a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Display success message if available in session --}}
    @session('success')
        <div class="alert alert-success" role="alert">
            {{ $value }}
        </div>
    @endsession

    {{-- Table listing all roles with actions --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th width="100px">No</th>
                    <th>Name</th>
                    <th width="280px">Action</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop through each role and display its details and available actions --}}
                @foreach ($roles as $key => $role)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $role->name }}</td>
                        <td>
                            {{-- Show button for viewing role details --}}
                            <a class="btn btn-info btn-sm" href="{{ route('roles.show', $role->id) }}"><i
                                    class="fa-solid fa-list"></i> Show</a>
                            {{-- Edit button, visible only to users with 'role-edit' permission --}}
                            @can('role-edit')
                                <a class="btn btn-primary btn-sm" href="{{ route('roles.edit', $role->id) }}"><i
                                        class="fa-solid fa-pen-to-square"></i> Edit</a>
                            @endcan

                            {{-- Delete button, visible only to users with 'role-delete' permission --}}
                            @can('role-delete')
                                <form method="POST" action="{{ route('roles.destroy', $role->id) }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i>
                                        Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination links for roles table --}}
    {!! $roles->links('pagination::bootstrap-5') !!}

@endsection
