{{-- Extends the main application layout --}}
@extends('layouts.app')

{{-- Main content section for creating a new user --}}
@section('content')
<section    class="custRightBarMn">
    <div class="row mb-50">
        <div class="col-lg-12 margin-tb d-flex justify-content-between align-items-center">
            <div class="pull-left">
                {{-- Page title for creating a new user --}}
                <h2>Create New User</h2>
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

{{-- User creation form --}}
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    <div class="row gap-20">
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
            <div class="form-group">
                {{-- Input for user's name --}}
                <strong>Name:</strong>
                <input type="text" name="name" placeholder="Name" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
            <div class="form-group">
                {{-- Input for user's email --}}
                <strong>Email:</strong>
                <input type="email" name="email" placeholder="Email" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
            <div class="form-group">
                {{-- Input for user's password --}}
                <strong>Password:</strong>
                <input type="password" name="password" placeholder="Password" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
            <div class="form-group">
                {{-- Input for confirming user's password --}}
                <strong>Confirm Password:</strong>
                <input type="password" name="confirm-password" placeholder="Confirm Password" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
            <div class="form-group">
                {{-- Dropdown to select user role(s) --}}
                <strong>Role:</strong>
                <select name="roles[]" class="form-control">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                     @endforeach
                </select>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            {{-- Submit button to create the user --}}
            <button type="submit" class="btn btn-primary btn-md mt-2 mb-3"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
        </div>
    </div>
</form>

</section>

@endsection
