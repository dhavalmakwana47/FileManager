@extends('app.layouts.layout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('select2.min.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('app/css/folders.css') }}">
    <link rel="stylesheet" href="https://cdn3.devexpress.com/jslib/22.2.6/css/dx.light.css">
@endpush

@section('content')
    <x-app-breadcrumb title="Folders" :breadcrumbs="[['name' => 'Home', 'url' => route('home')], ['name' => 'Folders', 'url' => route('folder.index')]]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="modal fade " id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form id="createFolderForm">
                            @include('app.folder.update')
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="folderModal" tabindex="-1" aria-labelledby="folderModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form id="folderForm" action="" method="POST">

                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form id="fileForm" action="" method="POST">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="fileModalLabel"><i class="fas fa-file-edit"></i>Add File</h5>
                                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="fileUpload" class="form-label fw-bold">
                                        <i class="fas fa-folder"></i> Upload File
                                    </label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="fileUpload" name="file" required>
                                        <label class="input-group-text" for="fileUpload">
                                            <i class="fas fa-upload"></i> Choose File
                                        </label>
                                    </div>
                                </div>
                                @include('app.folder.filepermissions')
                            </div>

                            <div class="modal-footer bg-light">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>Add</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>



            <div class="dx-viewport demo-container">
                <div id="file-manager"></div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('select2.full.min.js') }}"></script>
    <script src="https://cdn3.devexpress.com/jslib/22.2.6/js/dx.all.js"></script>

    <script>
        let getFileMangerRoute = "{{ route('filemanger.data') }}";
        let createFolderRoute = "{{ route('folder.store') }}";
        let deleteFolderRoute = "{{ route('folders.delete') }}";
        let createFileRoute = "{{ route('file.store') }}";


        let createFolderPermission = "{{ current_user()->hasPermission('Folder', 'create') }}"
        let deleteFolderPermission = "{{ current_user()->hasPermission('Folder', 'delete') }}"
        let updateFolderPermission = "{{ current_user()->hasPermission('Folder', 'update') }}"

        $('#role-select').select2({
            dropdownParent: $('#createFolderModal')
        });
        $('#role-select-edit').select2({
            dropdownParent: $('#folderModal')
        });
    </script>
    <script src="{{ asset('app/js/folders.js') }}"></script>
@endpush
