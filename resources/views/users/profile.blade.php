{{-- Extends the main application layout --}}
@extends('layouts.app')

{{-- Main content section for editing user profile --}}
@section('content')
<section class="custRightBarMn">
    <section class="custTableMn custEdtPflSec">
        <div class="container">
            {{-- Profile edit form: name and email --}}
            <form method="POST" action="{{ route('user.profile.store') }}" enctype="multipart/form-data">
                <div class="d-flex  align-items-center mb-4 flex-wrap">
                    {{-- Page title and action buttons --}}
                    <h2 class="mb-3 mb-md-0 fw-bold">
                        Edit Profile
                    </h2>
                    <div class="d-flex">
                        {{-- Save changes button for profile form --}}
                        <button type="submit" class="btn btn-primary me-2 mb-2 mb-md-0 mx-2">
                            Save changes
                        </button>
                        {{-- Back button: destination depends on user permission --}}
                        @can('can-see-all')
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary mx-2">
                                Back
                            </a>
                        @endcan
                        @cannot('can-see-all')
                            <a href="{{ url('/shared') }}" class="btn btn-outline-secondary mx-2">
                                Back
                            </a>
                        @endcannot
                    </div>
                </div>

                @csrf

                <div class="form-section">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="name">
                                Name
                                <span class="text-danger">
                                    *
                                </span>
                            </label>

                            {{-- Input for user's name --}}
                            <input class="form-control  @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ $user->name }}" autofocus="" >
                            @error('name')
                                <span role="alert" class="text-danger">
                                    <p>{{ $message }}</p>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="email">
                                E-mail
                                <span class="text-danger">
                                    *
                                </span>
                            </label>
                            {{-- Input for user's email --}}
                            <input class="form-control  @error('email') is-invalid @enderror" type="text" id="email" name="email" value="{{ $user->email }}" autofocus="" >
                            @error('email')
                                <span role="alert" class="text-danger">
                                    <p>{{ $message }}</p>
                                </span>
                            @enderror
                        </div>

                    </div>
                </div>

            </form>

            {{-- Password change form --}}
            <form method="POST" action="{{ route('user.profile.store.password') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-section">
                    <h5>
                        Change password
                    </h5>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="password">
                                New Password
                                <span class="text-danger">
                                    *
                                </span>
                            </label>
                            {{-- Input for new password --}}
                            <input class="form-control  @error('password') is-invalid @enderror" type="password" id="password" name="password" autofocus="" autocomplete="new-password" >
                            @error('password')
                                <span role="alert" class="text-danger">
                                    <p>{{ $message }}</p>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="retype-password">
                                Retype Password
                                <span class="text-danger">
                                    *
                                </span>
                            </label>
                            {{-- Input for confirming new password --}}
                            <input class="form-control  @error('confirm_password') is-invalid @enderror" type="password" id="confirm_password" name="confirm_password" autofocus="" autocomplete=new-password >
                            @error('confirm_password')
                                <span role="alert" class="text-danger">
                                    <p>{{ $message }}</p>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4 form-group d-flex align-items-end">
                            {{-- Button to confirm password change --}}
                            <button class="btn btn-outline-secondary">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            {{-- Avatar change form --}}
            <form method="POST" action="{{ route('user.profile.store.avatar') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-section">
                    <h5>
                        Change Avatar
                    </h5>
                    <label for="avatar" class="avatar">

                        {{-- Display current avatar or placeholder if none --}}
                        @if($user->avatar != null)
                            <img src="/avatar/{{ $user->avatar }}" height="150" width="150" id="avatar-preview">
                        @else
                            <img alt="Avatar placeholder" height="150" src="https://placehold.co/150x150" width="150" id="avatar-preview"/>
                        @endif

                    </div>
                    {{-- Button to upload new avatar image --}}
                    <button class="btn btn-outline-secondary">
                        Upload image
                    </button>

                    {{-- Hidden file input for avatar upload, triggers preview on change --}}
                    <input id="avatar" type="file" class="form-control @error('avatar') is-invalid @enderror d-none" name="avatar" value="{{ old('avatar') }}"  autocomplete="avatar" onchange="previewImage(event)" preview-target="avatar-preview">

                        @error('avatar')
                            <span role="alert" class="text-danger">
                                <p>{{ $message }}</p>
                            </span>
                        @enderror
                </div>
            </form>
        </div>
    </section>
</section>
@endsection
