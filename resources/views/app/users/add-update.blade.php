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
    <x-app-breadcrumb title="Users" :breadcrumbs="[
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Companies', 'url' => route('company.index')],
        ['name' =>  isset($user) ? 'Update' : 'Create'],
    ]" />
    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h4>{{ isset($user) ? 'Edit' : 'Add' }} User</h4>
                </div>
                <div class="card-body">
                    <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}"
                        method="POST">
                        @csrf
                        @if (isset($user))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="user-name">Name</label>
                            <input type="text" class="form-control" id="user-name" name="user_name"
                                placeholder="Enter user name"
                                value="{{ old('user_name', isset($user) ? $user->name : '') }}">

                            @error('user_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user-email">Email</label>
                            <input type="email" class="form-control" id="user-email" name="user_email"
                                placeholder="Enter user email"
                                value="{{ old('user_email', isset($user->email) ? $user->email : '') }}">
                            @error('user_email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user-email">Role</label>
                            <select class="form-control" name="role" id="role">
                                <option value="">Select Role</option>
                                @foreach ($roleArr as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role') == $role->id || (isset($user) && $user->companyRoles()->where('company_role_id', $role->id)->exists())? 'selected': '' }}>
                                        {{ $role->role_name }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        @if (!isset($user))
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter password">
                                @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm-password"
                                    name="password_confirmation" placeholder="Confirm password">
                                @error('password_confirmation')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update' : 'Create' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
