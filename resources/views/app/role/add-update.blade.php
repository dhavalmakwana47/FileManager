@extends('app.layouts.layout')

@push('styles')
    <style>
        /* Custom styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            width: 100%;
        }

        .text-danger {
            font-size: 0.875rem;
        }
    </style>
@endpush

@section('content')
    <x-app-breadcrumb title="Role" :breadcrumbs="[
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Role', 'url' => route('companyrole.index')],
        ['name' => 'Create'],
    ]" />
    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h4>{{ isset($role) ? 'Edit' : 'Add' }} Role</h4>
                </div>
                <div class="card-body">

                    <form action="{{ isset($role) ? route('companyrole.update', $role->id) : route('companyrole.store') }}"
                        method="POST">
                        @csrf
                        @if (isset($role))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="role-name">Name</label>
                            <input type="text" class="form-control" id="role-name" name="role_name"
                                placeholder="Enter role name"
                                value="{{ old('role_name', isset($role) ? $role->role_name : '') }}">

                            @error('role_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>



                        <button type="submit" class="btn btn-primary">{{ isset($role) ? 'Update' : 'Create' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
