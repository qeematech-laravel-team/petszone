@extends('layouts.app')

@section('title', __('Add Admin Employee'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('Add Admin Employee') }}</h1>
        <p class="text-muted mb-0">{{ __('Create login credentials and choose the dashboard sections this employee can access.') }}</p>
    </div>

    <form action="{{ route('admin.admin-users.store') }}" method="POST">
        @csrf
        @include('admin.admin-users._form', ['submitLabel' => __('Add Employee')])
    </form>
@endsection
