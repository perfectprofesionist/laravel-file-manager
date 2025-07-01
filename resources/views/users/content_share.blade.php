{{-- Main section for the content sharing page --}}
<section class="custTableMn">
    <div class="container">
        <div class="row">
            <div class="col-xxl-12">
                <div class="custTableSrch">

                    {{-- Search and filter component for content, includes user, fields, owners, and file types --}}
                    @include('components.content_search', [
                        'user' => $user,
                        'modifiedFields' => $modifiedFields,
                        'owners' => $owners,
                        'fileTypes' => $fileTypes,
                    ])

                    <div class="custFilterTable">
                        <div class="custFilterTabledng">
                            <div class="d-flex justify-content-between">
                                {{-- Section title and share button to open the share modal --}}
                                <h3>Shared with user</h3>
                                <a href="javascript:void(0);" id="shareContentBtn" class="btn btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#shareContentPopup">Share</a>
                            </div>
                        </div>

                        <div class="custFilterTableMn">
                            <div class="table-responsive">

                                {{-- Table displaying content shared with the user --}}
                                <table class="table table-bordered">

                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Owner</th>
                                            <th>Date Modified</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody class="content-list">

                                        {{-- If there is shared content, display each item --}}
                                        @if ($sharedWithMe->isNotEmpty())
                                            @foreach ($sharedWithMe as $sharedContent)
                                                <tr>
                                                    <td>
                                                        {{-- If the content is a folder, make it clickable to open --}}
                                                        @if ($sharedContent->content->is_folder == 1)
                                                            <a href="javascript:void(0);"
                                                                class="clickToOpenFolder custmclckbtnshared"
                                                                data-user-id="{{ $user->id }}"
                                                                data-guid="{{ $sharedContent->content->guid }}"
                                                                data-is-folder="{{ $sharedContent->content->is_folder }}">
                                                                <b><img src="{{ $sharedContent->content->svgPath }}"
                                                                        alt="{{ $sharedContent->content->extension }}"
                                                                        style="width: 20px; height: 20px;">
                                                                    {{ $sharedContent->content->name }}
                                                                </b>
                                                            </a>
                                                        @else
                                                            {{-- If the content is a file, display as non-clickable --}}
                                                            <div class="clickToOpen">
                                                                <b><img src="{{ $sharedContent->content->svgPath }}"
                                                                        alt="{{ $sharedContent->content->extension }}"
                                                                        style="width: 20px; height: 20px;">
                                                                    {{ $sharedContent->content->name }}
                                                                </b>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td><img src="{{ $sharedContent->content->user->avatar ? $sharedContent->content->user->avatar : asset('assets/images/iconProfile.png') }}"
                                                            class="UsrImg">
                                                        {{-- Show 'Me' if the owner is the current user --}}
                                                        {{ $sharedContent->content->user->name == Auth::user()->name ? 'Me' : $sharedContent->content->user->name }}
                                                    </td>
                                                    <td>{{ $sharedContent->content->updated_at->format('d/m/Y') }}
                                                    </td>
                                                    <td style="opacity: 0;"></td>
                                                    <td>
                                                        {{-- Dropdown menu for removing access to shared content --}}
                                                        <div class="ClickToOpen">
                                                            <img src="{{ asset('assets/images/dots.svg') }}"
                                                                alt="" class="custTablsDots">
                                                            <div class="custShrDv">
                                                                <div class="custShrDvCld">
                                                                    <ul>
                                                                        <li>
                                                                            <form
                                                                                action="{{ route('content.removeAccess', $sharedContent->content->guid) }}"
                                                                                method="POST"
                                                                                class="remove-access-form"
                                                                                style="display: inline;">
                                                                                @csrf
                                                                                @method('POST')
                                                                                <button type="submit"
                                                                                    class="btn w-100">
                                                                                    <img src="{{ asset('assets/images/iconFolder.svg') }}"
                                                                                        alt="">
                                                                                    Remove Access
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- If there is no shared content, show a message --}}
                                            <tr>
                                                <td colspan="5" class="text-center">No file available.</td>
                                            </tr>
                                        @endif


                                    </tbody>

                                </table>
                                {{-- Pagination for shared content table --}}
                                <div class="custTblPgntn">
                                    {!! $sharedWithMe->links('pagination::bootstrap-5') !!}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- Share Folder Popup Modal --}}
        <div class="modal fade" id="shareContentPopup" tabindex="-1" aria-labelledby="shareFolderModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content ModalContent">
                    <div class="modal-header">
                        {{-- Modal title for selecting a folder to share --}}
                        <h5 class="modal-title" id="shareFolderModalTarget">Select a Folder</h5>
                        <button type="button" class="CloseBtn btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Search input for filtering folders -->
                        <div class="mb-3">
                            <input type="text" id="contentSearchInput" class="form-control"
                                placeholder="Search folders..." aria-label="Search folders">
                        </div>

                        <div class="mb-3 selectFolderPopup">
                            {{-- Home and back navigation buttons for folder selection --}}
                            <button type="button" id="homeButton">
                                <img src="{{ asset('assets/images/iconHome.png') }}" alt="Home">
                            </button>
                            <button type="button" id="backButton">
                                Up One Level
                                <img src="{{ asset('assets/images/iconBack.png') }}" alt="Back"
                                    style="transform: rotate(90deg);">
                            </button>
                        </div>

                        <!-- Simplified grid view for files and folders -->
                        <div class="loader" style="display: none;"></div>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-1" id="contentGrid">
                            {{-- Content items will be loaded here --}}
                        </div>


                    </div>

                    <input type="hidden" id="user_id" value="{{ $user->id }}">
                    <input type="hidden" id="parent_id" value="">

                    <div class="modal-footer">
                        <div class="ms-3 text-muted small text-center w-60 me-auto d-flex justify-content-start align-items-center">
                            <em>Note: Click to select, double-click to open.</em>
                        </div>
                        {{-- Button to share selected items with the user --}}
                        <button type="button" id="shareItemsSelectedBtn" class="btn btn-primary">Share</button>
                    </div>
                </div>
            </div>
        </div>



    </div>
</section>
