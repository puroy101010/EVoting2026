@extends('layouts.admin')

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
@endsection

@section('content')

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Modern Page Header -->
            <div class="corporate-page-header ultra-compact">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div const $firstEnabled=$menu.find('.corporate-context-menu-item:not(.disabled):first');
                        <h1 class="corporate-page-title ultra-compact">Roles and Permissions Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Roles and Permissions</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-3 mt-md-0">

                    </div>
                </div>
            </div>

            <!-- Modern Table Container -->
            <div class="corporate-table-container ultra-compact">
                <div class="table-responsive">
                    <table id="candidatesTable" class="table corporate-table ultra-compact">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                <th>Type</th>

                                <th class="text-center" style="width: 60px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $index => $role)
                            <tr data-id="{{ $role->id }}">
                                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td class="first-name fw-semibold">{{ $role->name }}</td>
                                <td class="middle-name">{{ $role->guard_name }}</td>
                                <td class="last-name fw-semibold">{{ $role->created_at }}</td>
                                <td class="type">
                                    <span class="badge bg-light text-dark rounded-pill" style="font-size: 0.65rem; padding: 0.2rem 0.5rem;">{{ ucfirst($role->type) }}</span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="corporate-modern-context-menu-wrapper"> <button class="btn corporate-btn-modern-action-trigger show-role-menu"
                                            type="button"
                                            data-context-menu="role-actions"
                                            data-item-id="{{ $role->id }}"
                                            data-item-name="{{ $role->name }}"
                                            data-item-type="role"
                                            data-status="{{ $role->status ?? 'active' }}"
                                            data-user-count="{{ $role->users_count ?? 0 }}"
                                            data-role-system="{{ $role->name === 'Super Admin' ? 'true' : 'false' }}"
                                            data-role-users="{{ $role->users_count ?? 0 }}"
                                            title="Actions">
                                            <i class="fas fa-cog"></i> Action
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="corporate-empty-state">
                                    <i class="fas fa-users"></i>
                                    <h4>No Candidates Found</h4>
                                    <p>Start by adding your first candidate using the button above.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modern Back to Top Button -->
    <button id="back-to-top" class="btn corporate-back-to-top" role="button" aria-label="Scroll to top" style="display: none;">
        <i class="fas fa-chevron-up"></i>
    </button>
</div>


<script src="{{ asset('js/admin/corporate-ui.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/admin/corporate-ui.modal.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/admin/roles_and_permissions.js') }}?v={{ time() }}"></script>

<script>
    // Basic error message utility
    function getErrorMessage(xhr, defaultMessage = 'An error occurred.') {
        if (xhr.status === 404) return 'Resource not found.';
        if (xhr.status === 403) return 'No permission.';
        if (xhr.status === 422) return 'Invalid data.';
        if (xhr.status === 500) return 'Server error.';
        if (xhr.statusText === 'timeout') return 'Request timed out.';
        return defaultMessage;
    }
</script>


@endsection