@extends('layouts.admin')

@section('title', 'App Versions')
@section('page-title', 'App Versions')
@section('page-subtitle', 'Manage mobile app versions and updates')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">App Versions</h3>
        <a href="{{ route('admin.app-versions.create') }}" class="btn btn-primary">
            <i data-lucide="plus"></i> Upload New Version
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Force Update</th>
                        <th>Downloads</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versions as $version)
                    <tr>
                        <td>{{ $version->version_code }}</td>
                        <td>{{ $version->version_name }}</td>
                        <td>
                            <span class="status-badge {{ $version->is_active ? 'success' : 'inactive' }}">
                                {{ $version->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge {{ $version->force_update ? 'warning' : 'inactive' }}">
                                {{ $version->force_update ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>0</td> <!-- Placeholder for download count if we track it -->
                        <td>{{ $version->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="action-btns">
                                <form action="{{ route('admin.app-versions.toggle-status', $version) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn" title="Toggle Status">
                                        <i data-lucide="{{ $version->is_active ? 'eye-off' : 'eye' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.app-versions.edit', $version) }}" class="action-btn" title="Edit">
                                    <i data-lucide="edit-2"></i>
                                </a>
                                <form action="{{ route('admin.app-versions.destroy', $version) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn danger" title="Delete">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                                <a href="{{ Storage::url($version->apk_path) }}" class="action-btn" title="Download" download>
                                    <i data-lucide="download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No app versions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $versions->links() }}
        </div>
    </div>
</div>
@endsection
