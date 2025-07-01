import './bootstrap';
import $ from 'jquery';
import swal from 'sweetalert2';
import 'select2';


window.$ = window.jQuery = $;
window.Swal = swal;


$(document).ready(function () {

    $('#nested_search').prop('disabled', true);

    $(".custFltrSechDropdown").click(function () {
        $(".custTableSrchChldBtm").toggle();
        $(this).parent('.custTableSrchChldTp').toggleClass('active');
        $('#nested_search').prop('disabled', false);
        if ($('.custTableSrchChldBtm').is(':visible')) {
            $('#topSearchInput').hide();
            $('#replacement').show();
        } else {
            $('#replacement').hide();
            $('#topSearchInput').show();
        }
    });

    $(".custFilterFrSrch ul li .select").click(function () {
        $(this).toggleClass('active');
    });
    $(".custFilterFrSrch ul li .item-search").click(function () {
        $('.custFilterFrSrch ul li .select').removeClass('active');
    });
    $(".custFilterFrSrch ul li .custTableSrchChldBtmFrmRptRt select.form-control").click(function () {
        $(this).toggleClass('active');
    });


    $(document).on('click', '.custTablsDots', function (e) {
        e.stopPropagation();
        var $custShrDv = $(this).siblings(".custShrDv");
        if ($custShrDv.hasClass('active')) {
            $custShrDv.removeClass('active');
        } else {
            $(".custShrDv").removeClass('active');
            $custShrDv.addClass('active');
        }
    });

    $(document).click(function () {
        $(".custShrDv").removeClass('active');
    });

    $(".custShrDv").click(function (e) {
        e.stopPropagation();
    });


    $(".custShrDvCld > ul > li.custHasCldMenu").click(function () {
        $(this).siblings().removeClass('active');
        $(this).removeClass('active');
        $(this).addClass('active');
    });



    var containerWidth = $('.custRptDropdown').width();
    $('.custRptDropdown li').each(function () {
        var textWidth = $(this).width();
        if (textWidth > containerWidth) {
            var text = $(this).text();
            var truncatedText = text.substring(0, Math.floor(containerWidth / 8)) + '...';
            $(this).text(truncatedText);
        }
        $(this).css({
            'white-space': 'nowrap',
            'overflow': 'hidden',
            'text-overflow': 'ellipsis'
        });
    });


    $('.custRptDropdown li').on('click', function (e) {
        if ($(this).children('ul').length > 0) {
            $(this).toggleClass('active');
        }
        $(this).children('ul').stop(true, true).slideToggle();
        e.stopPropagation();
        var textWidth = $(this).width();
        if (textWidth > containerWidth) {
            var text = $(this).text();
            var truncatedText = text.substring(0, Math.floor(containerWidth / 8)) + '...';
            $(this).text(truncatedText);
        }
    });

    $('.selected').on('click', function () {
        var $this = $(this);
        if ($this.next().hasClass('opened')) {
            $('.select-dropdown').removeClass('opened');
        } else {
            $('.select-dropdown').removeClass('opened');
            $this.next().addClass('opened');
        }
    });

    $('.item-list').on('click', '.items > div', function () {
        var $currentDiv = $(this);
        var $select = $currentDiv.closest('.select');
        var txt = $currentDiv.find('.content > div:first-child b').text();
        // var imgHtml = $currentDiv.find('.img').html();
        var selectValue = $currentDiv.parent().data('value');

        $select.find('.selected div').html(txt);
        $select.find('.opened').removeClass('opened');
        $select.find('select > option').val(selectValue).change();

        var $form = $currentDiv.closest('form#custom-search-form');
        if ($form.length) {
            $form.submit();
        }
    });

    $('#custom_date_modified').on('change', function () {
        var $currentDiv = $(this);
        var $form = $currentDiv.closest('form#custom-search-form');
        if ($form.length) {
            $form.submit();
        }
    });

    $('#resetButton, #customResetButton').click(function () {
        if ($('#custom-search-form').length) {
            $('#custom-search-form')[0].reset();
            window.location.href = '/';
        } else {
            $('#searchForm')[0].reset();
            window.location.href = '/';
        }

    });


    $('#file-type-search').on('keyup', debounce(function () {
        var txt = $(this).val().charAt(0).toUpperCase() + $(this).val().slice(1).toLowerCase();
        var user = $(this).data('user');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let url = '/file_types';
        if (user == 'Visualizzatore') {
            url = '/visualizzatore_file_types';
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: { name: txt },
            success: function (response) {
                var $itemsContainer = $('.file_type_item_list');
                $itemsContainer.html('');

                if (response.length > 0) {
                    var html = response.map(function (item) {
                        return `
                            <div class="items" data-value="${item.guid}">
                                <div>
                                    <div class="img">
                                        <img src="${item.svg_path}" alt="${item.type}">
                                    </div>
                                    <div class="content">
                                        <div><b>${item.name}</b></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    $itemsContainer.html(html);
                } else {
                    $itemsContainer.html('<div>No results found</div>');
                }
            },
            error: function () {
                console.error('Error fetching search results');
            }
        });
    }, 300));

    $('#custom_file_type_search').on('keyup', debounce(function () {
        var txt = $(this).val().charAt(0).toUpperCase() + $(this).val().slice(1).toLowerCase();
        var user = $(this).data('user');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let url = '/file_types';
        if (user == 'Visualizzatore') {
            url = '/visualizzatore_file_types';
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: { name: txt },
            success: function (response) {
                var $itemsContainer = $('.file_type_item_list');
                $itemsContainer.html('');

                if (response.length > 0) {
                    var html = response.map(function (item) {
                        return `
                            <div class="items" data-value="${item.guid}">
                                <div>
                                    <div class="img">
                                        <img src="${item.svg_path}" alt="${item.type}">
                                    </div>
                                    <div class="content">
                                        <div><b>${item.name}</b></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    $itemsContainer.html(html);
                } else {
                    $itemsContainer.html('<div>No results found</div>');
                }
            },
            error: function () {
                console.error('Error fetching search results');
            }
        });
    }, 300));



    function debounce(func, wait) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                func.apply(context, args);
            }, wait);
        };
    }



    $('#owner, #custom_owner').on('click', function (event) {
        var user = $(this).data('user');
        currentPage = 1; // Reset to first page
        let query = $(event.target).val().toLowerCase();

        let $itemsContainer;
        if ($(event.target).closest('form#custom-search-form').length) {
            $itemsContainer = $(event.target).closest('form#custom-search-form').find('.owner_item_list');
        } else {
            $itemsContainer = $('.owner_item_list');
        }

        fetchOwners(query, $itemsContainer, false, user); // Fetch and replace content
    });

    let currentPage = 1;
    let isLoading = false;

    function fetchOwners(query, $itemsContainer, append = false, user = null) {
        if (isLoading) return;
        isLoading = true;

        var url = '/owner';
        if (user == 'Visualizzatore') {
            url = '/visualizzatore_owner';
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: url,
            method: 'GET',
            data: { name: query, page: currentPage, user: user },
            success: function (response) {
                if (!append) {
                    $itemsContainer.html(''); // Clear list if not appending
                }

                if (response.data && response.data.length > 0) {
                    let html = response.data.map(function (item) {
                        return `
                            <div class="items" data-value="${item.id}">
                                <div>
                                    <div class="img">
                                        <img src="${item.avatar}" class="UsrImg" alt="User Image">
                                    </div>
                                    <div class="content">
                                        <div><b>${item.name}</b></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    $itemsContainer.append(html);
                } else if (!append) {
                    $itemsContainer.html('<div>No results found</div>');
                }
            },
            error: function () {
                console.error('Error fetching search results');
            },
            complete: function () {
                isLoading = false;
            }
        });
    }

    // Unified handler for search inputs
    function handleSearchInput(event) {
        var user = $(event.target).data('user');
        currentPage = 1; // Reset to first page
        let query = $(event.target).val().toLowerCase();

        let $itemsContainer;
        if ($(event.target).closest('form#custom-search-form').length) {
            $itemsContainer = $(event.target).closest('form#custom-search-form').find('.owner_item_list');
        } else {
            $itemsContainer = $('.owner_item_list');
        }

        fetchOwners(query, $itemsContainer, false); // Fetch and replace content
    }

    // Event listener for both inputs
    $('#owner-search, #custom_owner_search').on('keyup', handleSearchInput);

    // Scroll event listener for infinite scroll
    $('.owner_item_list').on('scroll', function () {
        let $this = $(this);
        if ($this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 10) { // Near bottom
            if (!isLoading) {
                currentPage++; // Increment page number
                let query = $('#owner-search').val().toLowerCase();
                fetchOwners(query, $this, true); // Fetch and append content
            }
        }
    });







    $('#folder, #custom_folder').on('click', function (event) {
        var user = $(this).data('user');
        currentPage = 1; // Reset to first page
        let query = $(event.target).val().toLowerCase();

        let $itemsContainer;
        if ($(event.target).closest('form#custom-search-form').length) {
            $itemsContainer = $(event.target).closest('form#custom-search-form').find('#cust_folder_list');
        } else {
            $itemsContainer = $('#folder-list');
        }

        fetchFolders(query, $itemsContainer, false, user); // Fetch and replace content
    });

    // Function to fetch folders based on query and append them to the given container
    function fetchFolders(query, $itemsContainer, append = false, user = null) {
        if (isLoading) return;
        isLoading = true;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let url = '/folder_list';
        if (user == 'Visualizzatore') {
            url = '/visualizzatore_folder';
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: { name: query, page: currentPage, user: user },
            success: function (response) {
                if (!append) {
                    $itemsContainer.html(''); // Clear list if not appending
                }

                if (response.data && response.data.length > 0) {
                    let html = response.data.map(function (folder) {
                        return `
                            <div class="items" data-value="${folder.guid}">
                                <div class="folder-item">
                                    <div class="img">
                                        <img src="${assetPath('assets/images/iconFolder.svg')}" alt="">
                                    </div>
                                    <div class="content">
                                        <div><b>${folder.name}</b></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    $itemsContainer.append(html);
                } else if (!append) {
                    $itemsContainer.html('<div>No folders found</div>');
                }
            },
            error: function () {
                console.error('Error fetching folder search results');
            },
            complete: function () {
                isLoading = false;
            }
        });
    }

    // Event listener for custom folder search input (for the dropdown)
    $('#custom_folder_search').on('keyup', function () {
        var user = $(this).data('user');
        currentPage = 1; // Reset to first page
        let query = $(this).val().toLowerCase();
        let $itemsContainer = $('#cust_folder_list');
        fetchFolders(query, $itemsContainer, false); // Fetch and replace content
    });

    // Event listener for folder search input in the main select dropdown
    $('#folder-search').on('keyup', function () {
        var user = $(this).data('user');
        currentPage = 1; // Reset to first page
        let query = $(this).val().toLowerCase();
        let $itemsContainer = $('#folder-list');
        fetchFolders(query, $itemsContainer, false); // Fetch and replace content
    });

    $('.folder_item_list').on('scroll', function () {
        let $this = $(this);
        if ($this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 10) { // Near bottom
            if (!isLoading) {
                currentPage++; // Increment page number
                let query = $('#folder-search').val().toLowerCase(); // Get current search query
                fetchFolders(query, $this, true); // Fetch and append folder content
            }
        }
    });









    function assetPath(path) {
        // This function should return the correct asset path
        // You may need to adjust this based on your Laravel setup
        return '/' + path;
    }



    $(document).on('click', function (e) {
        if (!$(e.target).closest('.select').length) {
            $('.select-dropdown').removeClass('opened');
        }
    });

    // $(document).on('submit', 'form[id = "search-form"]', function (e) {
    //     e.preventDefault();
    //     var formData = $this
    // });
});



$(document).ready(function () {
    var currentPage = 1; // Initialize the page for pagination

    // When the "Move to Folder" link is clicked
    $('.file-info-link').on('click', function () {
        var fileId = $(this).attr('data-file-id'); // Get the file ID from the clicked file's data attribute
        $('#fileid').val(fileId); // Store the file ID in a hidden input field
    });

    // Handle when a folder is selected from the search results
    $(document).on('click', '.folder-item', function () {
        var folderId = $(this).data('id'); // Get the folder ID
        var folderName = $(this).data('name'); // Get the folder name

        // Set the folder ID in the hidden input
        $('#folder-id').val(folderId);

        // Display the selected folder name in the "Selected Folder" section
        $('#selectedFolder').show(); // Make the selected folder box visible
        $('#selectedFolderNamefile').text(folderName); // Set the selected folder name text
        $('#selectedFolderNameDisplayfile').text(folderName); // Also display in the blue area below the input

        // Optionally, close the search results
        $('#searchResultfiles').hide();
    });

    // Handle search input to dynamically show folder search results
    $('#folderSearchforfile').on('input', function () {
        var searchQuery = $(this).val();
        if (searchQuery.length > 0) {
            // Make an AJAX request to get folder results based on the search input
            $.ajax({
                url: '/folder_list', // Your folder search API endpoint
                type: 'GET',
                data: { name: searchQuery, page: currentPage, limit: 10 }, // Fetch first 10 results, include the page number
                success: function (response) {
                    var resultsHtml = '';
                    // Add "My Folders" with folder_id = null at the top of the results
                    resultsHtml += `<div class="folder-item" data-id="null" data-name="My Folders">
                                    <span>My Folders</span>
                                </div>`;

                    // Ensure there are results in the response
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (folder) {
                            resultsHtml += `<div class="folder-item" data-id="${folder.id}" data-name="${folder.name}">
                                                <span>${folder.name}</span>
                                            </div>`;
                        });
                        $('#searchResultfiles').html(resultsHtml).show();
                    } else {
                        $('#searchResultfiles').html('<div>No folders found</div>').show();
                    }
                },
                error: function () {
                    $('#searchResultfiles').html('<div>Error fetching folders</div>').show();
                }
            });
        } else {
            $('#searchResultfiles').hide(); // Hide results if input is empty
            $('#selectedFolder').hide(); // Hide selected folder if input is cleared
            $('#folder-id').val(''); // Clear the folder ID in hidden input
        }
    });

    // Handle the "Move File" button click to move the selected file
    $('#moveFileBtn').on('click', function () {
        var folderId = $('#folder-id').val(); // Get the selected folder ID
        var fileId = $('#fileid').val(); // Get the file ID from the hidden input

        // Debugging: Log the selected folder and file ID
        // Ensure that both the folder ID and file ID are selected
        if (!fileId) {
            swal.fire({
                title: 'Error',
                text: 'Please select a folder and a file to move!',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Send the move request to the backend using AJAX
        $.ajax({
            url: '/files/' + fileId + '/move', // Your file move API endpoint
            type: 'POST',
            data: {
                folder_id: folderId,  // Send the selected folder ID
                file_id: fileId,      // Send the file ID
                _token: $('meta[name="csrf-token"]').attr('content') // CSRF token for security
            },
            success: function (response) {
                swal.fire({
                    title: 'Success',
                    text: 'File moved successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function () {
                    window.location.reload();
                });
            },
            error: function () {
                swal.fire({
                    title: 'Error',
                    text: 'Error moving file. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Handle the modal show event to reset the modal content when it opens
    $('#moveToFolderModal').on('show.bs.modal', function () {
        // Clear the folder search input field and hide the selected folder information
        $('#folderSearchforfile').val(''); // Clear the input field
        $('#selectedFolder').hide(); // Hide the selected folder section
        $('#folder-id').val(''); // Clear the selected folder ID hidden input
        $('#selectedFolderNamefile').text('None'); // Reset selected folder name to 'None'
        $('#selectedFolderNameDisplayfile').text(''); // Clear the displayed folder name in the blue section
        $('#searchResultfiles').hide(); // Hide the search results
    });
});

// Datepicker
import('jquery-ui/ui/widgets/datepicker.js').then(() => {
    // Your code that depends on jQuery UI can go here
    // For example, initializing a datepicker:
    $(function () {

        $("#date_modified").datepicker();
        $("#custom_date_modified").datepicker();
        $("#dateModifiedInput").datepicker();
    });
});

// Share Content
$(document).ready(function () {

    let selectedContent = [];
    let currentPage = 1;
    let hasMoreContent = true; // Keep track of whether there is more content to load

    $(document).on('click', '.content-item', function () {
        const contentId = $(this).data('content-id');
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            selectedContent = selectedContent.filter(id => id !== contentId);
        } else {
            $(this).addClass('active');
            selectedContent.push(contentId);
        }
    });

    $(document).on('mouseover', '.content-item', function () {
        var poptext = $(this).children('#itemName');
        poptext.show();
    }).on('mouseout', '.content-item', function () {
        var poptext = $(this).children('#itemName');
        poptext.hide();
    });

    $('#shareContentBtn').on('click', function () {
        selectedContent = [];
        loadMoreContent();  // Load more content when button is clicked
    });

    $('#contentGrid').on('scroll', function () {
        let $this = $(this);
        var parentId = $('#parent_id').val();

        if (hasMoreContent && $this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 10) {
            loadMoreContent(parentId); // Ensure this only triggers once per scroll
        }
    });

    $('#contentSearchInput').on('input', function () {
        currentPage = 1; // Reset to first page when search input changes
        hasMoreContent = true; // Reset to allow loading new content
        $('#contentGrid').empty(); // Clear existing content
        var searchQuery = $(this).val(); // Get the search query
        var parentId = $('#parent_id').val(); // Get the current parent ID
        backButtonContent(parentId, searchQuery, 'search'); // Load new content based on the search input and current folder
    });

    $('#contentGrid').on('dblclick', '.content-item', function () {
        if ($(this).data('type') === 'folder') {
            $('#contentSearchInput').val('');
            var contentId = $(this).data('content-id');
            var searchQuery = $('#contentSearchInput').val();
            currentPage = 1;
            hasMoreContent = true;
            selectedContent = [];
            $('#contentGrid').empty();
            $('#parent_id').val(contentId);
            loadContent(contentId, searchQuery);
        }
    });

    $('#homeButton').on('click', function () {
        currentPage = 1;
        hasMoreContent = true;
        selectedContent = [];
        $('#contentGrid').empty();
        $('#parent_id').val('');
        $('#contentSearchInput').val('');
        loadMoreContent();
    });

    $('#backButton').on('click', function () {
        // Reset variables
        currentPage = 1;
        hasMoreContent = false;
        selectedContent = [];
        $('#contentSearchInput').val(''); // Clear search query if any

        var parentId = $('#parent_id').val(); // Get current parent ID

        backButtonContent(parentId, '', 'back');
    });

    function backButtonContent(parentId, searchQuery, type) {
        var loader = $('.loader');
        loader.show();

        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            page: currentPage,
            searchQuery: searchQuery,
            parent_id: parentId,
            type: type,
        };

        $.ajax({
            url: '/load-content',
            type: 'GET',
            data: payload,
            success: function (response) {
                loader.hide();
                $('#contentGrid').empty(); // Ensure the content grid is emptied
                if (response.next_page_url) {
                    currentPage++; // Update to next page
                    hasMoreContent = true; // Allow loading more content
                }

                var parentId = response.parentId;
                $('#parent_id').val(parentId);

                response.contents.data.forEach(function (content) {
                    appendContent(content);
                });
            },
            error: function (response) {
                loader.hide();
                console.error('Error loading content:', response);
            }
        });
    }

    function loadContent(id, searchQuery) {
        var loader = $('.loader');
        loader.show();

        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            page: currentPage,
            searchQuery: searchQuery,
            id: id,
        };

        $.ajax({
            url: '/load-content',
            type: 'GET',
            data: payload,
            success: function (response) {
                loader.hide();

                if (response.next_page_url) {
                    currentPage++; // Update to next page
                    hasMoreContent = true; // Allow loading more content
                }

                var parentId = response.parentId;
                $('#parent_id').val(parentId);

                response.contents.data.forEach(function (content) {
                    appendContent(content);
                });
            },
            error: function (response) {
                loader.hide();
                console.error('Error loading content:', response);
            }
        });
    }

    function loadMoreContent(id) {
        var loader = $('.loader');
        loader.show();

        if (!hasMoreContent) {
            loader.hide();
            return;
        }

        var searchQuery = $('#contentSearchInput').val();
        hasMoreContent = false; // Prevent multiple scroll triggers

        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            page: currentPage,
            searchQuery: searchQuery,
            parent_id: id,
        };

        $.ajax({
            url: '/load-content',
            type: 'GET',
            data: payload,
            success: function (response) {
                loader.hide();
                if (response.next_page_url) {
                    currentPage++; // Update to next page
                    hasMoreContent = true; // Allow loading more content
                }

                var parentId = response.parentId;
                $('#parent_id').val(parentId);

                response.contents.data.forEach(function (content) {
                    appendContent(content);
                });
            },
            error: function (response) {
                loader.hide();
                console.error('Error loading more content:', response);
                hasMoreContent = true; // Allow retrying if there was an error
            }
        });
    }

    function appendContent(content) {
        var contentHtml = `
            <div class="col content-item-container" data-name="${content.name.toLowerCase()}">
                <div class="card text-center content-item" data-type="${content.is_folder ? 'folder' : 'file'}" data-content-id="${content.guid}">
                    <img class="checked-icon" src="/assets/images/checked.png" alt="checked icon"/>
                    <img src="${content.svgPath}" alt="${content.extension}" class="card-img-top img-fluid mx-auto d-block">
                    <div class="card-body p-1">
                        <p class="card-text small text-truncate">${content.name}</p>
                    </div>
                    <div id="itemName" style="display: none;">
                        <p class="card-text small">${content.name}</p>
                    </div>
                </div>
            </div>
        `;
        $('#contentGrid').append(contentHtml);
    }

    $('#shareItemsSelectedBtn').on('click', function () {
        shareContent();
    });

    function shareContent() {
        var loader = $('.loader');
        loader.show(); // Ensure the loader is shown while processing
        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            contentIds: selectedContent,
            accessibility: 'Viewer',
            user_id: $('#user_id').val(),
        };

        $.ajax({
            url: '/share-content',
            type: 'POST',
            data: payload,
            success: function (response) {
                loader.hide(); // Hide the loader on success
                swal.fire({
                    title: 'Success',
                    text: 'Content shared successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function () {
                    window.location.reload();
                });
            },
            error: function (response) {
                loader.hide(); // Hide the loader on error
                swal.fire({
                    title: 'Error',
                    text: 'No content selected.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

});

// Click to open folder
$(document).ready(function () {
    var parentGuid = '';

    $(document).on('click', '.clickToOpenFolder', function () {
        parentGuid = $(this).data('guid');
        const guid = $(this).data('guid');
        const isFolder = $(this).data('is-folder');
        const userId = $(this).data('user-id');
        if (isFolder) {
            let currentPage = 1;
            loadFolderContent(guid, userId, currentPage);

            $(document).on('click', '.pagination-link', function () {
                currentPage = $(this).data('page');
                loadFolderContent(guid, userId, currentPage);
            });
        }
    });

    function loadFolderContent(guid, userId, page) {
        $.ajax({
            url: `/open-folder/${guid}`,
            type: 'GET',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                user_id: userId,
                page: page
            },
            success: function (response) {
                let folderContentHtml = '';
                if (response.folders.data.length > 0) {
                    response.folders.data.forEach(function (item) {
                        folderContentHtml += `
                            <tr>
                                <td>
                                    ${item.is_folder ? `
                                        <a href="javascript:void(0);" class="clickToOpenFolder custmclckbtnshared"
                                            data-user-id="${userId}"
                                            data-guid="${item.guid}"
                                            data-is-folder="${item.is_folder}">
                                            <b><img src="${item.svgPath}"
                                                    alt="${item.extension}"
                                                    style="width: 20px; height: 20px;">
                                                ${item.name}
                                            </b>
                                        </a>
                                    ` : `
                                        <div class="clickToOpen">
                                            <b><img src="${item.svgPath}"
                                                    alt="${item.extension}"
                                                    style="width: 20px; height: 20px;">
                                                ${item.name}
                                            </b>
                                        </div>
                                    `}
                                </td>
                                <td><img src="${item.user.avatar ? item.user.avatar : '/assets/images/iconProfile.png'}"
                                        class="UsrImg">
                                    ${item.user.name === $('meta[name="auth-user-name"]').attr('content') ? 'Me' : item.user.name}
                                </td>
                                <td>${new Date(item.updated_at).toLocaleDateString()}
                                </td>
                                <td style="opacity: 0;"></td>
                                <td>
                                    <div class="ClickToOpen">
                                        <img src="/assets/images/dots.svg"
                                            alt="" class="custTablsDots">
                                        <div class="custShrDv">
                                            <div class="custShrDvCld">
                                                <ul>
                                                    <li>
                                                        <form
                                                            action="/content/${item.guid}/remove-access"
                                                            method="POST"
                                                            class="remove-access-form ajax-remove-access-form"
                                                            data-guid="${item.guid}"
                                                            data-parent-guid="${item.parent_id}"
                                                            data-user-id="${userId}"
                                                            data-current-page="${page}"
                                                            style="display: inline;">
                                                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                                            <button type="submit"
                                                                class="btn w-100">
                                                                <img src="/assets/images/iconFolder.svg"
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
                        `;
                    });
                } else {
                    folderContentHtml += `
                        <tr>
                            <td colspan="5" class="text-center">No file available.</td>
                        </tr>
                    `;
                }
                $('.content-list').html(folderContentHtml);

                // Only add pagination if there are items in the folder
                if (response.folders.data.length > 0) {
                    let paginationHtml = '';
                    const currentPage = response.folders.current_page;
                    const lastPage = response.folders.last_page;

                    paginationHtml += '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

                    // Previous button
                    if (currentPage > 1) {
                        paginationHtml += `<li class="page-item"><a class="page-link pagination-link" href="javascript:void(0);" data-page="${currentPage - 1}">&laquo;</a></li>`;
                    } else {
                        paginationHtml += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
                    }

                    // Page numbers
                    for (let i = 1; i <= lastPage; i++) {
                        if (i === currentPage) {
                            paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                        } else {
                            paginationHtml += `<li class="page-item"><a class="page-link pagination-link" href="javascript:void(0);" data-page="${i}">${i}</a></li>`;
                        }
                    }

                    // Next button
                    if (currentPage < lastPage) {
                        paginationHtml += `<li class="page-item"><a class="page-link pagination-link" href="javascript:void(0);" data-page="${currentPage + 1}">&raquo;</a></li>`;
                    } else {
                        paginationHtml += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
                    }

                    paginationHtml += '</ul></nav>';

                    $('.custTblPgntn').html(paginationHtml);
                } else {
                    $('.custTblPgntn').empty(); // Clear pagination if no items
                }

            },
            error: function (error) {
                console.error('Error opening folder:', error);
            }
        });
    }
    // Add event listener for pagination links
    $('.pagination-link').on('click', function () {
        const page = $(this).data('page');
        // Call the function to fetch and display the content for the selected page
        fetchContentForPage(page);
    });
    var guid = '';
    var userId = '';
    var currentPage = '';

    $(document).on('click', '.ajax-remove-access-form', function (e) {
        e.preventDefault();
        var form = $(this);
        guid = form.data('guid');
        userId = form.data('user-id');
        currentPage = form.data('current-page');
        var formData = form.serialize();

        $.ajax({
            url: '/content/' + guid + '/remove-access',
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Success',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    })
                    loadFolderContent(parentGuid, userId, currentPage);

                } else {
                    console.error('Error removing access:', response);
                }
            },
            error: function (error) {
                console.error('Error removing access:', error);
            }
        });
    });
});


// Move Content
$(document).ready(function () {

    let selectedContent = [];
    let currentPage = 1;
    let hasMoreContent = true;

    // Handle clicking on content items
    $(document).on('click', '.folder-item', function () {
        const contentId = $(this).data('content-id'); // Get the content ID

        $('.folder-item').find('.checked-icon').hide();
        $('.folder-item').removeClass('active');

        $(this).find('.checked-icon').show();
        $(this).addClass('active');
        $('#selected-id').val(contentId);
    });

    // Handle mouseover for folder name display
    $(document).on('mouseover', '.folder-item', function () {
        var poptext = $(this).children('#itemName');
        poptext.show();
    }).on('mouseout', '.folder-item', function () {
        var poptext = $(this).children('#itemName');
        poptext.hide();
    });

    $('.folder-info-link').on('click', function () {
        var targetId = $(this).attr(
            'data-folder-guid'); // Get the folder ID from the clicked link's data attribute

        $('#id-to-move').val(targetId);


    });

    $('.folder-info-link').on('click', function () {

        resetState();
        folderLoadContent();
    });

    function showMyFolders() {
        var contentHtml = `
            <div class="col folder-item-container" data-name="my folders" data-parent-id="">
                <div class="card text-center folder-item" data-type="folder" data-content-id="Myfolders">
                    <img class="checked-icon" src="/assets/images/checked.png" alt="checked icon"/>
                    <img src="/assets/images/folder.svg" alt="folder" class="card-img-top img-fluid mx-auto d-block">

                 <div class="card-body p-1">
                        <p class="card-text small text-truncate">My Folders</p>
                    </div>
                    <div id="itemName" style="display: none;">
                        <p class="card-text small">My Folders</p>
                    </div>
                </div>
            </div>
        `;
        $('#folderContentGrid').append(contentHtml);
    }

    // Reset content and state
    function resetState() {
        $('#folderSearchInput').val('');
        $('#selected-id').val('');
        $('#folderContentGrid').empty();
        $('.folder-item').removeClass('active').find('.checked-icon').hide();
    }

    // Handle scroll event for loading more content
    $('#folderContentGrid').on('scroll', function () {
        let $this = $(this);
        let folderId = $this.data('folder-id'); // Assuming there's a data attribute for the folder ID
        if (hasMoreContent && $this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 10) {
            folderLoadMoreContent(folderId); // Pass the folder ID to the loadMoreContent function
        }
    });

    // Search input changes
    $('#folderSearchInput').on('input', function () {
        currentPage = 1; // Reset to the first page when search input changes
        hasMoreContent = true; // Reset hasMoreContent to true initially
        $('#folderContentGrid').empty(); // Clear existing content
        var searchQuery = $(this).val(); // Get the search query
        var parentId = $('#parent_id').val(); // Get the current parent ID
        folderBackButtonContent(parentId, searchQuery, 'search'); // Load new content based on the search input and current folder
    });

    // Double click on folder
    $('#folderContentGrid').on('dblclick', '.folder-item', function () {
        $('#folderSearchInput').val('');
        $('#selected-id').val('');
        if ($(this).data('type') === 'folder') {
            var contentId = $(this).data('content-id');
            var searchQuery = $('#folderSearchInput').val();
            currentPage = 1;
            hasMoreContent = true;
            selectedContent = [];
            $('#folderContentGrid').empty();
            $('#parent_id').val(contentId);
            folderLoadContent(contentId, searchQuery);
        }
    });

    // Handle Home button click
    $('#folderHomeButton').on('click', function () {
        currentPage = 1;
        hasMoreContent = true;
        selectedContent = [];
        $('#folderContentGrid').empty();
        $('#parent_id').val('');
        $('#folderSearchInput').val('');
        $('#selected-id').val('');
        showMyFolders(); // Show "My Folders" when Home is clicked
    });

    // Back button click: show previous content
    $('#folderBackButton').on('click', function () {
        currentPage = 1;
        hasMoreContent = false;
        $('#folderSearchInput').val('');
        $('#selected-id').val('');
        var parentId = $('#parent_id').val();
        var searchQuery = $('#folderSearchInput').val();
        selectedContent = [];
        $('#folderContentGrid').empty();
        folderBackButtonContent(parentId, searchQuery, 'back');
    });



    function folderBackButtonContent(parentId, searchQuery, type) {
        var loader = $('.loader');
        loader.show();

        var movedguid = $('#id-to-move').val();

        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            page: currentPage,
            searchQuery: searchQuery,
            parent_id: parentId,
            type: type,
            movedcontentguid: movedguid,
        };

        $.ajax({
            url: '/move-folder-search',
            type: 'GET',
            data: payload,
            success: function (response) {
                loader.hide();
                $('#folderContentGrid').empty();

                if (response.next_page_url) {
                    currentPage++;
                    hasMoreContent = true; // Allow loading more pages if there are more pages
                }
                var parentId = response.parentId;
                $('#parent_id').val(parentId);

                response.contents.data.forEach(function (content) {
                    var contentHtml = `
                        <div class="col folder-item-container" data-name="${content.name.toLowerCase()}" data-parent-id="${content.parent_id}">
                            <div class="card text-center folder-item" data-type="${content.is_folder ? 'folder' : 'file'}" data-content-id="${content.guid}">
                                <img class="checked-icon" src="/assets/images/checked.png" alt="checked icon"/>
                                <img src="${content.svgPath}" alt="${content.extension}" class="card-img-top img-fluid mx-auto d-block">
                                <div class="card-body p-1">
                                    <p class="card-text small text-truncate">${content.name}</p>
                                </div>
                                <div id="itemName" style="display: none;">
                                    <p class="card-text small">${content.name}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#folderContentGrid').append(contentHtml);
                });
            },
            error: function (response) {
                loader.hide();
                console.error('Error loading content:', response);
            }
        });
    }

    function folderLoadContent(id, searchQuery) {
        var loader = $('.loader');
        loader.show();
        var movedguid = $('#id-to-move').val();

        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            page: currentPage,
            searchQuery: searchQuery,
            id: id,
            movedcontentguid: movedguid,

        };

        $.ajax({
            url: '/move-folder-search',
            type: 'GET',
            data: payload,
            success: function (response) {
                loader.hide();

                if (response.next_page_url) {
                    currentPage++;
                    hasMoreContent = true;
                }
                var parentId = response.parentId;
                $('#parent_id').val(parentId);

                response.contents.data.forEach(function (content) {
                    var contentHtml = `
                        <div class="col folder-item-container" data-name="${content.name.toLowerCase()}">
                            <div class="card text-center folder-item" data-type="${content.is_folder ? 'folder' : 'file'}" data-content-id="${content.guid}">
                                <img class="checked-icon" src="/assets/images/checked.png" alt="checked icon"/>
                                <img src="${content.svgPath}" alt="${content.extension}" class="card-img-top img-fluid mx-auto d-block">
                                <div class="card-body p-1">
                                    <p class="card-text small text-truncate">${content.name}</p>
                                </div>
                                <div id="itemName" style="display: none;">
                                    <p class="card-text small">${content.name}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#folderContentGrid').append(contentHtml);
                });
            },
            error: function (response) {
                loader.hide();
                console.error('Error loading content:', response);
            }
        });
    }

    function folderLoadMoreContent(id) {
        var loader = $('.loader');
        loader.show();
        if (!hasMoreContent) {
            loader.hide();
            return;
        }
        var searchQuery = $('#folderSearchInput').val();

        var movedguid = $('#id-to-move').val();
        hasMoreContent = false; // Prevent further loading until current request completes

        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            page: currentPage,
            searchQuery: searchQuery,
            movedcontentguid: movedguid,
        };

        $.ajax({
            url: '/move-folder-search',
            type: 'GET',
            data: payload,
            success: function (response) {
                loader.hide();
                if (response.next_page_url) {
                    currentPage++;
                    hasMoreContent = true;
                }
                var parentId = response.parentId;
                $('#parent_id').val(parentId);

                response.contents.data.forEach(function (content) {
                    var contentHtml = `
                        <div class="col folder-item-container" data-name="${content.name.toLowerCase()}">
                            <div class="card text-center folder-item" data-type="${content.is_folder ? 'folder' : 'file'}" data-content-id="${content.guid}">
                                <img class="checked-icon" src="/assets/images/checked.png" alt="checked icon"/>
                                <img src="${content.svgPath}" alt="${content.extension}" class="card-img-top img-fluid mx-auto d-block">
                                <div class="card-body p-1">
                                    <p class="card-text small text-truncate">${content.name}</p>
                                </div>
                                <div id="itemName" style="display: none;">
                                    <p class="card-text small">${content.name}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#folderContentGrid').append(contentHtml);
                });
            },
            error: function (response) {
                loader.hide();
                console.error('Error loading more content:', response);
                hasMoreContent = true;
            }
        });
    }





    $('#moveFolderBtn').on('click', function () {
        var targetFolderId = $('#selected-id').val();
        var selectedFolderId = $('#id-to-move').val();

        $.ajax({
            url: '/move-content/' + selectedFolderId,  // Send the selected folder ID as part of the URL
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token for security
                guid: selectedFolderId,
                targetId: targetFolderId,
            },
            success: function (response) {
                if (response.status === 'success') {
                    swal.fire({
                        title: 'Success',
                        text: 'Content moved successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    // Optional: Reload the page to reflect changes (or you can dynamically update the UI)
                    window.location.reload();
                } else {
                    swal.fire({
                        title: 'Unexpected Success Response',
                        text: 'An unexpected issue occurred. Please try again later.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr, status, error) {
                // Check the response status code and error message for specific handling
                if (xhr.status === 400) {
                    // Handle different types of 400 errors based on the message in the response

                    let errorMessage = xhr.responseJSON.message;

                    // Handle specific errors
                    if (errorMessage === 'Invalid content.') {
                        swal.fire({
                            title: 'Error',
                            text: 'The content you are trying to move does not exist or is invalid.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else if (errorMessage === 'Cannot move content to the same folder.') {
                        swal.fire({
                            title: 'Error',
                            text: 'You cannot move the content to the same folder.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else if (errorMessage === 'Cannot move content into a child or descendant folder.') {
                        swal.fire({
                            title: 'Error',
                            text: `Cannot move content to the same folder's children.`,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else if (errorMessage === 'Invalid target folder.') {
                        swal.fire({
                            title: 'Error',
                            text: 'please select a target folder.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Generic error handling for any other 400 errors
                        swal.fire({
                            title: 'Error',
                            text: 'An error occurred while moving content. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                } else if (xhr.status >= 500) {
                    // Server-side error (Internal server error)
                    swal.fire({
                        title: 'Server Error',
                        text: 'There was a problem on the server. Please try again later.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    // General error handling for other unexpected errors
                    swal.fire({
                        title: 'Error',
                        text: 'Error moving content. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }

        });
    });
});

$(document).on('change', '#userEditShareFilterForm select, #userEditShareFilterForm input', function (e) {
    $(this).closest('form').submit();
});










