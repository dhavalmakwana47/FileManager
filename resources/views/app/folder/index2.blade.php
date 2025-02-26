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
        let createFolderPermission = "{{ current_user()->hasPermission('Folder', 'create') }}"
        let deleteFolderPermission = "{{ current_user()->hasPermission('Folder', 'delete') }}"
        $('#role-select').select2({
            dropdownParent: $('#createFolderModal')
        });
        $('#role-select-edit').select2({
            dropdownParent: $('#folderModal')
        });
    </script>
    <script src="{{ asset('app/js/folders.js') }}"></script>
@endpush
