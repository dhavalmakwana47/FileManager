@extends('app.layouts.layout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('select2.min.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('app/css/folders.css') }}">
@endpush

@section('content')
    <x-app-breadcrumb title="Folders" :breadcrumbs="[['name' => 'Home', 'url' => route('home')], ['name' => 'Folders', 'url' => route('folder.index')]]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="cm-address-bar-search" clear>
                <div class="address-search">
                    <div class="pos"><input type="text" class="address-search-input"><div class="cm-button address-button"><i class="fas fa-arrow-right"></i></div><div class="address-short-btn"></div></div>
                </div>
                <div class="search-file-and-folder">
                    <div class="pos"><input placeholder="Search.." type="text" class="files-search-input"><div class="cm-button file-search-button"><i class="fas fa-search"></i></div></div>
                </div>
            </div>
            <div class="theme-structure big-file-manager">
                <ul>
                    <li class="file-sub-active show-up"><b>Project 01</b></li>
                    <li data-file-icon="folder"><b>Assets</b>
                        <ul>
                            <li data-file-icon="folder"><b>image</b>
                                <ul>
                                    <li data-file-id="sdfsdfsdfsdf45456sd" data-file-icon="video"><b>movie.mp4</b></li>
                                    <li data-file-id="sdfsdfsdf454" data-file-icon="image"><b>cat.png</b></li>
                                    <li data-file-id="sdf4334545" data-file-icon="image"><b>banner.jpg</b></li>
                                    <li data-file-id="sdfs4355" data-file-icon="image"><b>user.gif</b></li>
                                </ul>
                            </li>
                            <li data-file-icon="folder"><b>fonts</b>
                                <ul>
                                    <li data-file-icon="folder"><b>A-1</b></li>
                                    <li data-file-icon="folder"><b>B-1</b></li>
                                    <li data-file-icon="folder"><b>C-1</b>
                                        <ul>
                                            <li data-file-icon="folder"><b>A-2</b></li>
                                            <li data-file-icon="folder"><b>B-2</b></li>
                                            <li data-file-icon="folder"><b>C-2</b>
                                                <ul>
                                                    <li data-file-icon="folder"><b>A-3</b></li>
                                                    <li data-file-icon="folder"><b>B-3</b></li>
                                                    <li data-file-icon="folder"><b>C-3</b></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li data-file-icon="folder"><b>files</b></li>
                            <li data-file-icon="folder"><b>random</b></li>
                        </ul>
                    </li>
                    <li data-file-icon="folder"><b>Pages</b>
                        <ul>
                            <li data-file-id="5" data-file-icon="html"><b>main.html</b></li>
                            <li data-file-id="6" data-file-icon="php"><b>error.php</b></li>
                            <li data-file-id="7" data-file-icon="css"><b>serach-result.css</b></li>
                            <li data-file-id="8" data-file-icon="js"><b>extra.js</b></li>
                        </ul>
                    </li>
                    <li data-file-icon="folder"><b>Layout</b>
                        <ul>
                            <li data-file-icon="layout"><b>Two Column Left Image</b></li>
                            <li data-file-icon="layout"><b>Three Column Equal</b></li>
                            <li data-file-icon="layout"><b>Two Column</b></li>
                            <li data-file-icon="layout"><b>Full Width</b></li>
                        </ul>
                    </li>
                    <li data-file-icon="folder"><b>File 04</b></li>
                </ul>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('select2.full.min.js') }}"></script>
    <script src="{{ asset('app/js/folders.js') }}"></script>

    <script>
        $('#role-select').select2({
            dropdownParent: $('#createFolderModal')
        });
        $('#role-select-edit').select2({
            dropdownParent: $('#folderModal')
        });

        // $('.select2').select2({
        //     placeholder: "Select roles",
        //     allowClear: true
        // });

        function create_folder_form(id) {
            $('#createFolderForm')[0].reset();
            $('#role-select').change();
            $('#parentFolder').val(id)
            $('#createFolderModal').modal('show');
        }

        $(document).ready(function() {
            // Handle modal open for folder edit
            $(document).on('click', '.edit-folder', function() {
                const folderName = $(this).closest('summary').contents().filter(function() {
                    return this.nodeType === 3; // Get the text node only
                }).text().trim();
                const updateUrl = $(this).data('url');

                // Set folder data in modal
                $('#folderForm').attr('action', updateUrl)
                $('#folderName').val(folderName);
                $('#folderModalLabel').text('Edit Folder');
                $.ajax({
                    url: updateUrl,
                    type: 'GET',

                    success: function(response) {
                        $('#role-select-edit').val(response).change()
                        $('#folderModal').modal('show');
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON.message ||
                            'An unexpected error occurred.'));
                    }
                });
            });

            // Submit folder form for create/edit
            $('#folderForm').on('submit', function(e) {
                e.preventDefault();

                const editUrl = $(this).attr('action');

                $.ajax({
                    url: editUrl,
                    type: 'PUT',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // Include CSRF token
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload(); // Reload page for simplicity
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON.message ||
                            'An unexpected error occurred.'));
                    }
                });
            });


            $(document).on('click', '.delete-folder', function() {
                if (confirm('Are you sure you want to delete this folder?')) {
                    const deleteUrl = $(this).data('url');

                    $.ajax({
                        url: deleteUrl, // Use the resource route format
                        type: 'DELETE', // Use DELETE method for deletion
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                location.reload(); // Reload page to reflect the changes
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON.message ||
                                'An unexpected error occurred.');
                        }
                    });
                }
            });


            // Handle folder creation
            $('#createFolderForm').on('submit', function(e) {
                e.preventDefault();

                // Get the CSRF token value
                let csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: "{{ route('folder.store') }}", // Adjust the URL as per your route
                    type: "POST",
                    data: $(this).serialize(), // Serialize the form data
                    headers: {
                        'X-CSRF-TOKEN': csrfToken, // Include CSRF token for security
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close the modal after successful folder creation
                            $('#createFolderModal').modal('hide');
                            // Optionally, reload the page or update the UI with new data
                            alert(response.message); // Display success message
                            location
                                .reload(); // Reload the page to show the new folder
                        } else {
                            alert(response.message); // Display any error messages
                        }
                    },
                    error: function(xhr) {
                        // Handle any AJAX errors
                        alert('Error: ' + xhr.responseJSON.message ||
                            'An unexpected error occurred.');
                    }
                });
            });

        });
    </script>
@endpush
