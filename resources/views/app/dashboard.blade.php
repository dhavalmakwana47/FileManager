@extends('app.layouts.layout')
@section('content')
    <x-app-breadcrumb title="Dashboard" :breadcrumbs="[['name' => 'Home', 'url' => route('dashboard')], ['name' => 'Dashboard', 'url' => null]]" />
    <div class="app-content">
        <div class="container-fluid">
        </div>
    </div>
@endsection
