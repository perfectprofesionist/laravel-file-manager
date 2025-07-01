{{-- Navigation bar with search form for filtering content by name --}}
<nav class="navbar">
    <form class="form-inline" method="GET" action="{{ route('users.edit', ['user' => $user->id]) }}"
        id="userEditShareSearchForm">
        @csrf
        @method('GET')
        <div class="custTableSrchChld">
            <div class="custTableSrchChldTp">
                {{-- Input for searching by file name --}}
                <input id="topSearchInput" class="form-control mr-sm-2" type="search" name="file_name"
                    placeholder="Search by name" aria-label="Search"
                    value="{{ old('file_name', $modifiedFields['file_name'] ?? '') }}">
                {{-- Hidden input to keep track of the user being edited --}}
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                {{-- Search button with icon --}}
                <button class="custSrchBtn" type="submit">Search <img
                        src="{{ asset('assets/images/iconRightSearch.svg') }}" alt="Search Icon"></button>
            </div>
        </div>
    </form>
</nav>

{{-- Filter form for advanced content filtering by type, owner, and date modified --}}
<form method="GET" action="{{ route('users.edit', ['user' => $user->id]) }}">
    @csrf
    @method('GET')
    <div class="container mt-3">
        <div class="row g-3 d-flex justify-content-center align-items-center" id="userEditShareFilterForm">
            <div class="col-lg-3 col-md-4 col-sm-6">
                <label for="typeSelect" class="form-label">Type</label>
                {{-- Dropdown to filter by file type --}}
                <select id="typeSelect" name="file_type" class="form-select">
                    <option value="">Select Type</option>
                    @foreach ($fileTypes as $file)
                        <option value="{{ $file->guid }}"
                            {{ old('file_type', $modifiedFields['file_type_guid'] ?? '') == $file->guid ? 'selected' : '' }}>
                            {{ ucfirst($file->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <label for="ownerSelect" class="form-label">Owner</label>
                {{-- Dropdown to filter by owner --}}
                <select id="ownerSelect" name="owner_id" class="form-select">
                    <option value="">Select Owner</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}"
                            {{ old('owner_id', $modifiedFields['owner_id'] ?? '') == $owner->id ? 'selected' : '' }}>
                            {{ $owner->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <label for="dateModifiedInput" class="form-label">Date Modified</label>
                {{-- Input for filtering by date modified --}}
                <input type="text" id="dateModifiedInput" name="date_modified" class="form-control"
                    value="{{ isset($modifiedFields['date_modified']) ? $modifiedFields['date_modified'] : '' }}"
                    placeholder="Date Modified">
            </div>
            <div class="col-lg-1 col-md-4 col-sm-6 text-end">
                {{-- Button to reset all filters and return to the default view --}}
                <a href="{{ route('users.edit', ['user' => $user->id]) }}" id="resetFilterBtn"
                    class="btn btn-primary btn-md">Reset</a>
            </div>
        </div>
    </div>
</form>
