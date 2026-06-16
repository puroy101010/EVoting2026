@extends('layouts.admin')

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
<style>

</style>
@endsection

@section('content')

<div class="content-wrapper">
  <section class="content">
    <div class="container-fluid">
      <!-- Modern Page Header -->
      <div class="corporate-page-header ultra-compact">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div const $firstEnabled=$menu.find('.corporate-context-menu-item:not(.disabled):first');
            <h1 class="corporate-page-title ultra-compact">Attendance Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Attendance Management</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact" id="btnExportAttendance" type="button" data-export-url="{{ url('admin/attendance/export') }}">
              <i class="fa fa-upload"></i>
              Export
            </button>

            <button class="btn corporate-action-btn ultra-compact" id="btnPrintAttendanceSummary" type="button" data-print-url="{{ url('admin/attendance/print') }}">
              <i class="fa fa-print"></i>
              Print Summary
            </button>
          </div>
        </div>
      </div>

      <!-- Modern Table Container -->
      <div class="corporate-table-container ultra-compact">
        <div class="table-responsive">
          <table id="attendanceTable" class="table corporate-table ultra-compact">
            <thead>
              <tr>
                <th style="width: 40px;">#</th>
                <th>Account No</th>
                <th>Shareholder</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($attendance as $record)
              <tr>
                <td class="fw-semibold text-muted">{{ $loop->iteration }}</td>
                <td class="first-name fw-semibold">{{ $record->stockholderAccount->accountKey }}</td>
                <td class="middle-name">{{ $record->stockholderAccount->stockholder->stockholder }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="corporate-empty-state">
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

<script>
  $(document).on('click', '#btnExportAttendance', function() {
    location.href = BASE_URL + 'admin/attendance/export'
  })

  $(document).on('click', '#btnPrintAttendanceSummary', function() {
    location.href = BASE_URL + 'admin/attendance/print'
  })
</script>


@endsection