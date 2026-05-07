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
                <li class="breadcrumb-item active" aria-current="page">Masterlist</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact" id="btnExportBodProxy" type="button">
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
              <li class="breadcrumb-item"><a class="bod-filter" data-filter="all" href="{{asset('admin/bod-proxy/masterlist?filter=all')}}">All</a></li>
              <li class="breadcrumb-item"><a class="bod-filter" data-filter="multiple issuance" href="{{asset('admin/bod-proxy/masterlist?filter=multiple issuance')}}">Multiple Issuance</a></li>
              <li class="breadcrumb-item"><a class="bod-filter" data-filter="cancelled" href="{{asset('admin/bod-proxy/masterlist?filter=cancelled')}}">Cancelled Only</a></li>
              <!-- <li class="breadcrumb-item"><a class="bod-filter" data-filter="unverified" href="{{asset('admin/bod-proxy/masterlist?filter=unverified')}}">Unverified Only</a></li> -->
            </ol>
          </div>

          <table class="table corporate-table ultra-compact" id="proxy_holders_table">
            <thead class="text-nowrap bg-light" style="position: sticky;top: 0; z-index: 1;">
              <tr>
                <th class="th-padding">#</th>
                <th class="th-padding">Account</th>
                <th class="th-padding">Proxy Form No</th>
                <th class="th-padding">Assignee</th>
                <th class="th-padding">Assignor</th>
                <th class="th-padding">Status</th>
                <th class="th-padding">Reason</th>

              </tr>
            </thead>
            <tbody>
              @foreach($proxies as $proxy)
              <tr data-id="{{ $proxy['id'] }}">
                <td>{{ $loop->iteration }}</td>
                <th>{{$proxy['account'] }}</th>
                <td>{{ $proxy['proxyBodFormNo'] }}</td>
                <td>{{ $proxy['assigneeAccount'] }} - {{ $proxy['assignee'] }}</td>

                <td>{{ $proxy['assignorAccount'] }} - {{ $proxy['assignor'] }}</td>
                <td>{{ $proxy['status'] }}</td>
                <td>{{ $proxy['remarks'] }}</td>


              </tr>
              @endforeach
              @if(count($proxies) == 0)
              <tr>
                <td colspan="7" class="text-center">No proxies found.</td>
              </tr>
              @endif
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
    $('.admin-nav-bod-proxy-holders-masterlist').addClass('active');
  })




  $(document).on('click', '#btnExportBodProxy', function() {

    let filter = "{{ $filter ?? '' }}";
    let exportUrl = BASE_URL + 'admin/bod-proxy/masterlist/export?filter=' + encodeURIComponent(filter);
    window.location.href = exportUrl;

  })
</script>

@endsection