{{-- Extends the main application layout --}}
@extends('layouts.app')

{{-- Main content section for displaying a single role's details --}}
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            {{-- Page title for showing a role --}}
            <h2> Show Role</h2>
        </div>
        <div class="pull-right">
            {{-- Back button to return to the roles index page --}}
            <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            {{-- Display the name of the role --}}
            <strong>Name:</strong>
            {{ $role->name }}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            {{-- Display the permissions associated with the role --}}
            <strong>Permissions:</strong>
            @if(!empty($rolePermissions))
                {{-- Loop through each permission and display its name --}}
                @foreach($rolePermissions as $v)
                    <label class="label label-success">{{ $v->name }},</label>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
