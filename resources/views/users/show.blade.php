{{-- Extends the main application layout --}}
@extends('layouts.app')

{{-- Main content section for displaying a user's details --}}
@section('content')
<section class="custRightBarMn">
<div class="row mb-4">
    <div class="col-lg-12 margin-tb d-flex justify-content-between align-items-center">
        <div class="pull-left">
            {{-- Page title for showing a user --}}
            <h2> Show User</h2>
        </div>
        <div class="pull-right">
            {{-- Back button to return to the users index page --}}
            <a class="btn btn-primary btn-md mb-2" href="{{ route('users.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
        <div class="form-group" >
            {{-- Display the user's name --}}
            <strong>Name:</strong>
            {{ $user->name }}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
        <div class="form-group" >
            {{-- Display the user's email --}}
            <strong>Email:</strong>
            {{ $user->email }}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
        <div class="form-group" >
            {{-- Display the user's roles as badges --}}
            <strong>Roles:</strong>
            @if(!empty($user->getRoleNames()))
                @foreach($user->getRoleNames() as $v)
                    <label class="badge bg-success">{{ $v }}</label>
                @endforeach
            @endif
        </div>
    </div>
</div>
</section>
@endsection
