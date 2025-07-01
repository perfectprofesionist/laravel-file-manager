{{-- Table row displaying file or folder details and actions --}}
<tr>
    <td><b>
        {{-- Display file or folder icon and name --}}
        @if(isset($file))
            <img src="{{ $file->svgPath }}" alt="{{ $file->extension }}" style="width: 20px; height: 20px;">
            {{ $file->name }}
        @elseif(isset($folder))
            <img src="{{ $folder->svgPath }}" alt="Folder" style="width: 20px; height: 20px;">
            {{ $folder->name }}
        @endif
    </b></td>
    <td>
        {{-- Display owner avatar and name, show 'Me' if current user --}}
        @if(isset($file))
            <img src="{{ $file->user->avatar }}">
            {{ $file->user->name == Auth::user()->name ? 'Me' : $file->user->name }}
        @elseif(isset($folder))
            <img src="{{ $folder->user->avatar }}">
            {{ $folder->user->name == Auth::user()->name ? 'Me' : $folder->user->name }}
        @endif
    </td>
    <td>
        {{-- Display creation date of file or folder --}}
        @if(isset($file))
            {{ $file->created_at->format('d/m/Y') }}
        @elseif(isset($folder))
            {{ $folder->created_at->format('d/m/Y') }}
        @endif
    </td>
    <td>
        {{-- Display parent folder name or 'Root' if none --}}
        @if(isset($file) && $file->folder)
            <img src="{{ asset('assets/images/iconFolder.svg') }}" alt="">
            {{ $file->folder->name ?? 'Root' }}
        @elseif(isset($folder) && $folder->parent)
            <img src="{{ asset('assets/images/iconFolder.svg') }}" alt="">
            {{ $folder->parent->name ?? 'Root' }}
        @endif
    </td>
    <td>
        {{-- Dropdown menu for file/folder actions: move, share, download, copy, rename, info, trash --}}
        <div class="ClickToOpen">
            <img src="{{ asset('assets/images/dots.svg') }}" alt="" class="custTablsDots">
            <div class="custShrDv">
                <div class="custShrDvCld">
                    <ul>
                        <li class="custHasCldMenu">
                            <img src="{{ asset('assets/images/iconFolder.svg') }}" alt="">
                            Move to
                            <ul>
                                <li><a href="#"><img src="{{ asset('assets/images/iconShare.svg') }}" alt=""> Share</a></li>
                                <li><a href="#"><img src="{{ asset('assets/images/iconCopyLink.svg') }}" alt=""> Copy link</a></li>
                            </ul>
                        </li>
                        <li class="custHasCldMenu">
                            <img src="{{ asset('assets/images/iconShare.svg') }}" alt="">
                            Share
                            <ul>
                                <li>
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                        <img src="{{ asset('assets/images/iconShare.svg') }}" alt=""> Share
                                    </a>
                                </li>
                                <li><a href="#"><img src="{{ asset('assets/images/iconCopyLink.svg') }}"
                                            alt=""> Copy link</a></li>
                            </ul>
                        </li>
                        <li><a href="#"><img src="{{ asset('assets/images/iconDownload.svg') }}" alt="">
                                Download</a></li>
                        <li><a href="#"><img src="{{ asset('assets/images/iconCopy.svg') }}" alt="">
                                Make a copy</a></li>
                        <li><a href="#"><img src="{{ asset('assets/images/iconFolder.svg') }}" alt="">
                                Rename</a></li>
                        <li><a href="#"><img src="{{ asset('assets/images/iconInfo.svg') }}" alt="">
                                File Info</a></li>
                        <li><a href="#"><img src="{{ asset('assets/images/iconTrash.svg') }}" alt="">
                                Move to trash</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </td>
</tr>
