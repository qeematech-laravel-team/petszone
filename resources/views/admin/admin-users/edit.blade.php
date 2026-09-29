@extends('layouts.app')

@section('title', __('Edit Admin Employee'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('Edit Admin Employee') }}</h1>
        <p class="text-muted mb-0">{{ __('Update employee information, password, status, and dashboard access.') }}</p>
    </div>

    <form action="{{ route('admin.admin-users.update', $adminUser) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.admin-users._form', ['submitLabel' => __('Save Changes')])
    </form>
@endsection
