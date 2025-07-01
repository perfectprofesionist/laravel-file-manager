{{-- Extends the main application layout --}}
@extends('layouts.app')

{{-- Main content section for editing a user --}}
@section('content')

<section class="custRightBarMn">
<div class="row">
    <div class="col-lg-12 margin-tb d-flex justify-content-between align-items-center">
        <div class="pull-left">
            {{-- Page title for editing a user --}}
            <h2>Edit User</h2>
        </div>
        <div class="pull-right">
            {{-- Back button to return to the users index page --}}
            <a class="btn btn-primary btn-md mb-2" href="{{ route('users.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

{{-- Display validation errors, if any --}}
@if (count($errors) > 0)
    <div class="alert alert-danger">
      <strong>Whoops!</strong> There were some problems with your input.<br><br>
      <ul>
         @foreach ($errors->all() as $error)
           <li>{{ $error }}</li>
         @endforeach
      </ul>
    </div>
@endif

{{-- User edit form --}}
<form class="mb-5" method="POST" action="{{ route('users.update', $user->id) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3   ">
            <div class="form-group">
                {{-- Input for user's name --}}
                <strong>Name:</strong>
                <input type="text" name="name" placeholder="Name" class="form-control" value="{{ $user->name }}" autocomplete="off">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3   ">
            <div class="form-group">
                {{-- Input for user's email --}}
                <strong>Email:</strong>
                <input type="email" name="email" placeholder="Email" class="form-control" value="{{ $user->email }}" autocomplete="off">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3   ">
            <div class="form-group">
                {{-- Input for new password (optional) --}}
                <strong>New Password:</strong>
                <input type="password" name="password" placeholder="Password" class="form-control" autocomplete="new-password">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3   ">
            <div class="form-group">
                {{-- Input for confirming new password (optional) --}}
                <strong>Confirm New Password:</strong>
                <input type="password" name="confirm-password" placeholder="Confirm Password" class="form-control" autocomplete="new-password">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
            <div class="form-group">
                {{-- Dropdown to select user role(s) --}}
                <strong>Role:</strong>
                <select name="roles[]" class="form-control">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" {{ isset($userRole[$value]) ? 'selected' : ''}}>
                            {{ $label }}
                        </option>
                     @endforeach
                </select>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            {{-- Submit button to update the user --}}
            <button type="submit" class="btn btn-primary btn-md mt-2 mb-3"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
        </div>
    </div>
</form>

{{-- Include the content sharing section for this user --}}
@include('users.content_share', ['sharedWithMe' => $sharedWithMe, 'user' => $user, 'modifiedFields' => $modifiedFields, 'owners' => $owners, 'fileTypes' => $fileTypes])
</section>


@endsection
