@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time()}}">
<style>
  .selected-filter {
    color: grey;

  }
</style>
@endsection

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <!-- Modern Page Header -->
      <div class="corporate-page-header ultra-compact">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div>
            <h1 class="corporate-page-title ultra-compact">BOD Proxy Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">BOD Proxy</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact" id="btnExportBodProxy" type="button" data-export-url="{{ url('admin/bod-proxy/export') }}">
              <i class="fa fa-upload"></i>
              Export
            </button>
          </div>
        </div>
      </div>
      <!-- Modern Table Container -->
      <div class=" corporate-table-container ultra-compact">
        <div class="table-responsive" style="height: 750px; overflow: auto;">

          <div class="col-md-12 mb-5">
            <ol class="breadcrumb float-sm-right dflex-button">
              <li class="breadcrumb-item"><a class="bod-filter" data-filter="all" href="{{asset('admin/bod-proxy?filter=all')}}">All</a></li>
              <li class="breadcrumb-item"><a class="bod-filter" data-filter="verified" href="{{asset('admin/bod-proxy?filter=verified')}}">Verified Only</a></li>
              <li class="breadcrumb-item"><a class="bod-filter" data-filter="unverified" href="{{asset('admin/bod-proxy?filter=unverified')}}">Unverified Only</a></li>
            </ol>
          </div>

          <table class="table corporate-table ultra-compact" id="proxy_holders_table">
            <thead class="text-nowrap bg-light" style="position: sticky;top: 0; z-index: 1;">
              <tr>
                <th class="th-padding">#</th>
                <th class="th-padding">Assignee</th>
                <th class="th-padding">Proxy Form No</th>
                <th class="th-padding">Assignor</th>
                <th class="th-padding">Account Status</th>
                <th class="th-padding">With Cancelled Proxy</th>
                <th class="th-padding">Vote Status</th>
                <th class="th-padding">Auditor</th>
                <th class="th-padding">Verified</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($proxyholders as $proxyholder)
              @php
              $isSuperAdmin = Auth::user() && Auth::user()->role === 'superadmin';
              $voteStatus = $isSuperAdmin ? ($proxyholder['vote'] ?? '-') : '-';
              $withCancelled = '';

              if(count($proxyholder['cancelled']) > 0) {
              $formNumbers = [];
              foreach($proxyholder['cancelled'] as $cancel) {
              $formNumbers[] = $cancel['proxyBodFormNo'];
              }
              if (!empty($formNumbers)) {
              $withCancelled .= '<small class="text-muted">(' . implode(' | ', $formNumbers) . ')</small>';
              }
              }
              @endphp
              <tr data-id="{{ $proxyholder['id'] }}">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $proxyholder['assigneeAccountNo'] }} - {{ $proxyholder['assignee'] }}</td>
                <td>{{ $proxyholder['proxyFormNo'] }}</td>
                <td>{{ $proxyholder['assignorAccountNo'] }} - {{ $proxyholder['assignor'] }}</td>
                <td>{!! $proxyholder['isDelinquent'] !!}</td>
                <td>{!! $withCancelled !!}</td>
                <td>{!! $voteStatus !!}</td>
                <td>{{ $proxyholder['auditor'] }}</td>
                <td><input class="chk-audit" type="checkbox" {!! $proxyholder['audited'] !!}></td>
              </tr>
              @empty
              <tr class="text-center">
                <td colspan="8">No record found</td>
              </tr>
              @endforelse
            </tbody>


          </table>
        </div>
      </div>
    </div>
  </section>
</div>
<script>
  $(document).ready(function() {
    const filter = "{{ $filter ?? '' }}";
    const target = filter && filter !== 'all' ? `[data-filter="${filter}"]` : '[data-filter="all"]';
    $(target).addClass('selected-filter');
    $('.admin-nav-bod-proxy-holders').addClass('active');
  })




  $(document).on('click', '#btnExportBodProxy', function() {
    let filter = "{{ $filter ?? '' }}";
    let exportUrl = BASE_URL + 'admin/bod-proxy/active/export?filter=' + encodeURIComponent(filter);
    window.location.href = exportUrl;
  })

  $(document).on('click', '.chk-audit', function(e) {
    e.preventDefault(); // don't toggle immediately
    const $chk = $(this);
    const id = $chk.closest('tr').data('id');
    const action = $chk.is(':checked') ? 1 : 0;
    const prompt = action === 0 ?
      "You are about to revoke the proxy's verified status." :
      "You are about to confirm the proxy as verified.";

    Swal.fire({
      title: 'Are you sure?',
      text: prompt,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        $chk.prop('disabled', true);
        audit(id, action).always(function() {
          $chk.prop('disabled', false);
        });
      }
    })
  })

  function audit(id, action) {
    $.ajax({
      url: BASE_URL + `admin/bod-proxy/${id}/audit`,
      method: 'POST',
      dataType: 'json',
      data: {
        id: id,
        action: action,
        _token: '{{ csrf_token() }}'
      },
      success: function(data) {
        if (typeof handleSuccess === 'function') {
          handleSuccess(data);
        } else {
          Swal.fire('Success', data && data.message ? data.message : 'Operation completed.', 'success');
        }
      },
      error: function(xhr) {
        if (typeof handleError === 'function') {
          handleError(xhr);
        } else {
          const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred.';
          Swal.fire('Error', msg, 'error');
        }
        // Revert checkbox state on failure
        const row = $(document).find(`tr[data-id='${id}']`);
        row.find('.chk-audit').prop('checked', function(i, val) {
          return !val;
        });
      }

    })
  }
</script>

@endsection