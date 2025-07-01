<nav class="navbar">
    <form class="form-inline" method="GET" action="{{ route('files.search') }}" id="searchForm">
        <div class="custTableSrchChld">
            <div class="custTableSrchChldTp">
                <input id="topSearchInput" class="form-control mr-sm-2" type="search" name="file_name"
                    placeholder="Search in library" aria-label="Search"
                    value="{{ isset($modifiedFields['file_name']) ? $modifiedFields['file_name'] : '' }}">
                <input id="replacement" class="form-control mr-sm-2" type="search" name="file_name"
                    placeholder="Search in library" aria-label="Search" value="" style="display: none" disabled>
                <button class="custSrchBtn" type="submit">Search <img
                        src="{{ asset('assets/images/iconRightSearch.svg') }}" alt=""></button>
                <img class="custFltrSechDropdown" src="{{ asset('assets/images/iconFilter.svg') }}" alt="">
            </div>

            <div class="custTableSrchChldBtm">
                <div class="custTableSrchChldBtmFrm">
                    <div class="custTableSrchChldBtmFrmRpt ">
                        <div class="custTableSrchChldBtmFrmRptLft">
                            <label>Type</label>
                        </div>
                        <div class="custTableSrchChldBtmFrmRptRt">

                            <div class="select">
                                <select name="file_type">
                                    <option value=""></option>
                                    @if (!empty($modifiedFields['file_type_guid']))
                                        <option value="{{ $modifiedFields['file_type_guid'] }}" selected>
                                            {{ $modifiedFields['file_type_guid'] }}</option>
                                    @endif
                                </select>
                                <div>
                                    <div class="selected">
                                        <span><img src="{{ asset('assets/images/iconPolygon.svg') }}"
                                                alt=""></span>
                                        @if (!empty($modifiedFields['file_type']))
                                            <div>{{ $modifiedFields['file_type'] }}</div>
                                        @else
                                            <div>Select</div>
                                        @endif
                                    </div>
                                    <div class="select-dropdown">
                                        <div class="item-search">
                                            <input autocomplete="off" placeholder="search..." id="file-type-search">
                                        </div>
                                        <div class="file_type_item_list item-list">
                                            @foreach ($fileType as $file)
                                                <div class="items" data-value="{{ $file->guid }}">
                                                    <div>
                                                        <div class="img">
                                                            <img src="{{ asset($file->svg_path) }}" alt="">
                                                        </div>
                                                        <div class="content">
                                                            <div><b>{{ ucfirst($file->name) }}</b></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custTableSrchChldBtmFrmRpt ">
                        <div class="custTableSrchChldBtmFrmRptLft">
                            <label>Owner</label>
                        </div>
                        <div class="custTableSrchChldBtmFrmRptRt">
                            <div class="select">
                                <select name="owner">
                                    <option value=""></option>
                                    @if (!empty($modifiedFields['owner_id']))
                                        <option value="{{ $modifiedFields['owner_id'] }}" selected>
                                            {{ $modifiedFields['owner_id'] }}</option>
                                    @endif
                                </select>
                                <div>
                                    <div class="selected" id="owner">
                                        <span><img src="{{ asset('assets/images/iconPolygon.svg') }}"
                                                alt=""></span>
                                        @if (!empty($modifiedFields['owner']))
                                            <div>{{ $modifiedFields['owner'] }}</div>
                                        @else
                                            <div>Select</div>
                                        @endif
                                    </div>
                                    <div class="select-dropdown">
                                        <div class="item-search">
                                            <input autocomplete="off" placeholder="search..." id="owner-search">
                                        </div>
                                        <div class="owner_item_list item-list"
                                            style="max-height: 200px; overflow-y: auto;">
                                            {{-- users will load here --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custTableSrchChldBtmFrmRpt ">
                        <div class="custTableSrchChldBtmFrmRptLft">
                            <label>File name</label>
                        </div>
                        <div class="custTableSrchChldBtmFrmRptRt">
                            <input type="text" id="nested_search" class="form-control" name="file_name"
                                placeholder="Enter a term that matches the file name"
                                value="{{ isset($modifiedFields['file_name']) ? $modifiedFields['file_name'] : '' }}">
                        </div>
                    </div>
                    <div class="custTableSrchChldBtmFrmRpt ">
                        <div class="custTableSrchChldBtmFrmRptLft">
                            <label>Location</label>
                        </div>
                        <div class="custTableSrchChldBtmFrmRptRt">
                            <div class="select" id="folder">
                                <select name="folder">
                                    <option value="">Select Folder</option>
                                    @if (!empty($modifiedFields['folder_guid']))
                                        <option value="{{ $modifiedFields['folder_guid'] }}" selected>
                                            {{ $modifiedFields['folder_guid'] }}</option>
                                    @endif
                                    <!-- Add other options here dynamically if needed -->
                                </select>

                                <div>
                                    <div class="selected">
                                        <span><img src="{{ asset('assets/images/iconPolygon.svg') }}"
                                                alt=""></span>
                                        @if (!empty($modifiedFields['folder']))
                                            <div>{{ $modifiedFields['folder'] }}</div>
                                        @else
                                            <div>Select Folder</div>
                                        @endif
                                    </div>
                                    <div class="select-dropdown">
                                        <div class="item-search">
                                            <input autocomplete="off" placeholder="search..." id="folder-search">
                                        </div>
                                        <div class="item-list folder_item_list" id="folder-list"
                                            style="max-height: 200px; overflow-y: auto;">
                                            {{-- folders will load here --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custTableSrchChldBtmFrmRpt ">
                        <div class="custTableSrchChldBtmFrmRptLft">
                            <label>Date modified</label>
                        </div>
                        <div class="custTableSrchChldBtmFrmRptRt">
                            <div>
                                <input type="text" id="date_modified" name="date_modified"
                                    value="{{ isset($modifiedFields['date_modified']) ? $modifiedFields['date_modified'] : '' }}"
                                    placeholder="Select Date">
                            </div>
                        </div>
                    </div>
                    <div class="custTableSrchChldBtmFrmRpt ">
                        <div class="SrchBarFlterBtns">
                            <a href="javascript:void(0)" class="custRstBtn" id="resetButton">Reset</a>
                            <button class="custSrchBtn" type="submit">Search <img
                                    src="{{ asset('assets/images/iconRightSearch.svg') }}" alt=""></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</nav>

<div class="custFilterFrSrch">
    <form class="form-inline" method="GET" action="{{ route('files.search') }}" id="custom-search-form">
        <ul>
            <li>
                <div class="select">
                    <select name="file_type">
                        <option value=""></option>
                        @if (!empty($modifiedFields['file_type_guid']))
                            <option value="{{ $modifiedFields['file_type_guid'] }}" selected>
                                {{ $modifiedFields['file_type_guid'] }}</option>
                        @endif
                    </select>
                    <div>
                        <div class="selected">
                            <span><img src="{{ asset('assets/images/iconDownWt.svg') }}" alt=""></span>
                            @if (!empty($modifiedFields['file_type']))
                                <div>{{ $modifiedFields['file_type'] }}</div>
                            @else
                                <div>Type</div>
                            @endif
                        </div>
                        <div class="select-dropdown">
                            <div class="item-search">
                                <input autocomplete="off" placeholder="search..." id="custom_file_type_search">
                            </div>
                            <div class="file_type_item_list item-list">
                                @foreach ($fileType as $file)
                                    <div class="items" data-value="{{ $file->guid }}">
                                        <div>
                                            <div class="img">
                                                <img src="{{ asset($file->svg_path) }}" alt="">
                                            </div>
                                            <div class="content">
                                                <div><b>{{ ucfirst($file->name) }}</b></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <input type="hidden" name="file_name" value="{{ $modifiedFields['file_name'] ?? '' }}">

            <li>
                <div class="select">
                    <select name="owner">
                        <option value=""></option>
                        @if (!empty($modifiedFields['owner_id']))
                            <option value="{{ $modifiedFields['owner_id'] }}" selected>
                                {{ $modifiedFields['owner_id'] }}</option>
                        @endif
                    </select>
                    <div>
                        <div class="selected" id="custom_owner">
                            <span><img src="{{ asset('assets/images/iconDownWt.svg') }}" alt=""></span>
                            @if (!empty($modifiedFields['owner']))
                                <div>{{ $modifiedFields['owner'] }}</div>
                            @else
                                <div>Owner</div>
                            @endif
                        </div>
                        <div class="select-dropdown">
                            <div class="item-search">
                                <input autocomplete="off" placeholder="search..." id="custom_owner_search">
                            </div>
                            <div class="owner_item_list item-list" style="max-height: 200px; overflow-y: auto;">
                                {{-- users will load here --}}
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div class="select">
                    <select name="folder">
                        <option value=""></option>
                        @if (!empty($modifiedFields['folder_guid']))
                            <option value="{{ $modifiedFields['folder_guid'] }}" selected>
                                {{ $modifiedFields['folder_guid'] }}</option>
                        @endif
                    </select>
                    <div>
                        <div class="selected" id="custom_folder">
                            <span><img src="{{ asset('assets/images/iconDownWt.svg') }}" alt=""></span>
                            @if (!empty($modifiedFields['folder']))
                                <div>{{ $modifiedFields['folder'] }}</div>
                            @else
                                <div>Folder</div>
                            @endif
                        </div>
                        <div class="select-dropdown">
                            <div class="item-search">
                                <input autocomplete="off" placeholder="search..." id="custom_folder_search">
                            </div>
                            <div class="item-list folder_item_list" id="cust_folder_list"
                                style="max-height: 200px; overflow-y: auto;">
                                {{-- folders will load here --}}
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div class="select">
                    <div class=" selected">
                        <input type="text" id="custom_date_modified" name="date_modified"
                            value="{{ isset($modifiedFields['date_modified']) ? $modifiedFields['date_modified'] : '' }}"
                            placeholder="Date Modified">
                    </div>
                </div>
            </li>
            <li>
                <div class="w-100">
                    <div class="d-flex justify-content-start">
                        <a href="javascript:void(0)" class="custRstBtn" id="customResetButton">Reset</a>
                    </div>
                </div>

            </li>
        </ul>
    </form>
</div>
