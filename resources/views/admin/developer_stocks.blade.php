@extends('layouts.admin')

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
<!-- DataTables Bootstrap 4 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap4.min.css">
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">


<style>
    body {
        font-size: 0.8em;
    }

    /* Custom Search Box Styling */
    .custom-search-container {
        margin-bottom: 18px;
        text-align: right;
    }

    .custom-search-input {
        border: 1px solid #34495e;
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 13px;
        width: 260px;
        outline: none;
        transition: box-shadow 0.2s;
        color: #2c3e50;
        background: #f8f9fa;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.04);
    }

    .custom-search-input:focus {
        box-shadow: 0 0 0 2px #667eea33;
        background: #fff;
    }

    .custom-search-input::placeholder {
        color: #b0b6be;
        font-style: italic;
    }

    /* Hide default DataTable search box if it appears */
    .dataTables_filter {
        display: none !important;
    }

    .form-control {
        padding: .2rem 1.25rem !important;
    }
</style>
@endsection

@section('content')

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Modern Page Header -->
            <div class="corporate-page-header ultra-compact">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <h1 class="corporate-page-title ultra-compact">Stock Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Stock Management</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex align-items-center">
                        <button id="exportExcel" class="btn btn-success btn-sm me-2" title="Export to Excel">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                        <div class="custom-search-container mb-0" style="margin-bottom:0; float: left;">
                            <input type="text" id="customSearchInput" class="custom-search-input" placeholder="Search records...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="corporate-filter-container mb-3">
                <div class="row">
                    <div class="col-md-2">
                        <select id="statusFilter" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="delinquent">Delinquent</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="accountTypeFilter" class="form-control form-control-sm">
                            <option value="">All Account Types</option>
                            <option value="Individual">Individual</option>
                            <option value="Corporate">Corporate</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="stockholderFilter" class="form-control form-control-sm">
                            <option value="">All Stockholders</option>
                            @foreach ($stocks->unique('stockholder.stockholder') as $stock)
                            <option value="{{ $stock->stockholder->stockholder }}">{{ $stock->stockholder->stockholder }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select id="bodVoteFilter" class="form-control form-control-sm">
                            <option value="">BOD Vote</option>
                            <option value="Available">Available</option>
                            <option value="Used">Used</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select id="amendmentVoteFilter" class="form-control form-control-sm">
                            <option value="">Amendment</option>
                            <option value="Available">Available</option>
                            <option value="Used">Used</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="bodProxyFilter" class="form-control form-control-sm">
                            <option value="">BOD Proxy Status</option>
                            <option value="with">With Proxy</option>
                            <option value="without">Without Proxy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="clearFilters" class="btn btn-sm btn-outline-secondary">Clear Filters</button>
                    </div>
                </div>
            </div>

            <!-- Modern Table Container -->
            <div class="corporate-table-container ultra-compact">
                <div class="table-responsive">
                    <table id="stocksTable" class="table table-striped table-bordered table-hover">
                        <thead class="">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Account</th>
                                <th>Stockholder</th>
                                <th>Account Type</th>
                                <th>Status</th>
                                <th>Vote Status (bod)</th>
                                <th>Vote Status (amendment)</th>
                                <th>Ballot No (bod)</th>
                                <th>Ballot No (amendment)</th>
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

                            $ballotNoBod = null;
                            if(count($stock->usedBodAccount) > 0) {
                            $ballotNoBod = $stock->usedBodAccount->first()->ballot->ballotNo;
                            }
                            $ballotNoAmendment = null;
                            if(count($stock->usedAmendmentAccount) > 0) {
                            $ballotNoAmendment = $stock->usedAmendmentAccount->first()->ballot->ballotNo;
                            }
                            @endphp
                            <tr data-id="{{ $stock->id }}">
                                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td class=" fw-semibold">{{ $stock->accountKey }}</td>
                                <td class="">{{ $stock->stockholder->stockholder }}</td>
                                <td class="">{{ $stock->stockholder->accountType }}</td>
                                <th>{{ $stock->isDelinquent === 1 ? 'delinquent' : 'active' }}</th>
                                <th>{{ $ballotNoBod ? 'Used' : 'Available' }}</th>
                                <th>{{ $ballotNoAmendment ? 'Used' : 'Available' }}</th>

                                <th>{{ $ballotNoBod ?? '' }}</th>
                                <th>{{ $ballotNoAmendment ?? '' }}</th>

                                <td class="">{{ $stock->proxyBoard ? $stock->proxyBoard->assignee->full_name : '' }}</td>
                                <td class="">{{ $stock->proxyAmendment ? $stock->proxyAmendment->assignee->full_name : '' }}</td>

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
<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
    $(document).ready(function() {
        let table = new DataTable('#stocksTable', {
            paging: false,
            lengthChange: false,
            info: true,
            searching: true,
            dom: 'Brt',
            buttons: [{
                extend: 'excel',
                text: 'Export Excel',
                className: 'btn btn-success btn-sm d-none', // Hidden default button
                filename: 'Stock_Management_' + new Date().toISOString().slice(0, 10),
                title: 'Stock Management Report',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] // Export all columns except Actions
                }
            }]
        });

        // Custom export button
        $('#exportExcel').on('click', function() {
            table.button(0).trigger();
        });

        // Custom search box logic
        $('#customSearchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Filter functions
        $('#statusFilter').on('change', function() {
            table.column(4).search(this.value).draw();
        });

        $('#accountTypeFilter').on('change', function() {
            table.column(3).search(this.value).draw();
        });

        $('#stockholderFilter').on('change', function() {
            table.column(2).search(this.value).draw();
        });

        $('#bodVoteFilter').on('change', function() {
            table.column(5).search(this.value).draw();
        });

        $('#amendmentVoteFilter').on('change', function() {
            table.column(6).search(this.value).draw();
        });

        $('#bodProxyFilter').on('change', function() {
            var searchValue = '';
            if (this.value === 'with') {
                searchValue = '^(?!\\s*$).+'; // regex for non-empty
            } else if (this.value === 'without') {
                searchValue = '^\\s*$'; // regex for empty
            }
            table.column(9).search(searchValue, true, false).draw();
        });

        // Clear all filters
        $('#clearFilters').on('click', function() {
            $('#statusFilter, #accountTypeFilter, #stockholderFilter, #bodVoteFilter, #amendmentVoteFilter, #bodProxyFilter').val('');
            $('#customSearchInput').val('');
            table.search('').columns().search('').draw();
        });
    });
</script>

@endsection