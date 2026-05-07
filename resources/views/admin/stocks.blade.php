@extends('layouts.admin')

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
<!-- DataTables Bootstrap 4 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Modern Page Header -->
            <div class="corporate-page-header ultra-compact">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div const $firstEnabled=$menu.find('.corporate-context-menu-item:not(.disabled):first');
                        <h1 class="corporate-page-title ultra-compact">Stock Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Stock Management</li>
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
                    <table id="candidatesTable" class="table table-striped table-bordered table-hover">
                        <thead class="">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Account</th>
                                <th>Stockholder</th>
                                <th>Account Type</th>
                                <th>BOD Proxy</th>
                                <th>Amendment Proxy</th>
                                <th class="text-center" style="width: 60px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stocks as $index => $stock)

                            @php
                            $revoked = null;
                            if($stock->proxyBoard) {
                            $revoked = $stock->usedBodAccount ? ' (Revoked)' : '';
                            }
                            @endphp
                            <tr data-id="{{ $stock->id }}">
                                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td class=" fw-semibold">{{ $stock->accountKey }}</td>
                                <td class="">{{ $stock->stockholder->stockholder }}</td>
                                <td class="">{{ $stock->stockholder->accountType }}</td>
                                <td class="">{{ $stock->proxyBoard ? $stock->proxyBoard->assignee->full_name : '-' }}</td>
                                <td class="">{{ $stock->proxyAmendment ? $stock->proxyAmendment->assignee->full_name : '-' }}{{ $revoked }}</td>

                                <td class="text-center align-middle">
                                    <div class="corporate-modern-context-menu-wrapper"> <button class="btn corporate-btn-modern-action-trigger show-role-menu"
                                            type="button"
                                            data-context-menu="role-actions"
                                            data-item-id="{{ $stock->accountId }}"
                                            data-item-name="{{ $stock->stockholder->stockholder }}"
                                            data-item-type="role"
                                            data-status="{{ $stock->status ?? 'active' }}"
                                            data-user-count="{{ $stock->users_count ?? 0 }}"
                                            data-role-system="{{ $stock->name === 'Super Admin' ? 'true' : 'false' }}"
                                            data-role-users="{{ $stock->users_count ?? 0 }}"
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
<script src="{{ asset('js/admin/stocks.js') }}?v={{ time() }}"></script>

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
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        let table = new DataTable('#candidatesTable');
    });
</script>

@endsection