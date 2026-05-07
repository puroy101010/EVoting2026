@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/admin_proxyholder.css')}}?v=<?php echo time(); ?>">
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
      <div class="row mb-2">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right dflex-button">
            <li class="breadcrumb-item"><a href="{{asset('admin')}}" style="color:#6b8e4e;">Dashboard</a></li>
            <li class="breadcrumb-item actives">Proxy</li>
            <li class="breadcrumb-item actives">Amendment</li>
          </ol>
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-md-12">
          <div class="card rounded-0">
            <div class="card-header rounded-0">
              <h1 class="card-title">AMENDMENT PROXIES</h1>
              <div class="float-sm-right">
                <button class="btn btn-custom-darkg rounded-0 btn-button" id="btnExportAmendmentProxy"><i class="fa fa-upload"> Export</i></button>
              </div>
            </div>

            <div class="card-body">
              <div class="table-responsive" style="height: 750px; overflow: auto;">

                <div class="col-md-12 mb-5">
                  <ol class="breadcrumb float-sm-right dflex-button">
                    <li class="breadcrumb-item"><a class="bod-filter" data-filter="all" href="{{asset('admin/proxy/amendment?filter=all')}}">All</a></li>
                    <li class="breadcrumb-item"><a class="bod-filter" data-filter="verified" href="{{asset('admin/proxy/amendment?filter=verified')}}">Verified Only</a< /li>
                    <li class="breadcrumb-item"><a class="bod-filter" data-filter="unverified" href="{{asset('admin/proxy/amendment?filter=unverified')}}">Unverified Only</a></li>
                  </ol>
                </div>



                <table class="table table-bordered" id="proxy_holders_table">
                  <thead class="text-nowrap bg-light" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding">#</th>
                      <th class="th-padding">Assignee</th>
                      <th class="th-padding">Ref No</th>
                      <th class="th-padding">Assignor</th>
                      <th class="th-padding">Account Status</th>
                      <th class="th-padding">Vote Status</th>
                      <th class="th-padding">Auditor</th>
                      <th class="th-padding">Verified</th>
                    </tr>
                  </thead>
                  <tbody>


                    <?php

                    $counter = 0;

                    foreach ($proxyholders as $proxyholder) {

                      $counter++;

                      $voteStatus = '';

                      if (Auth::user()->role === 'superadmin') {

                        $voteStatus = $proxyholder['vote'];
                      }

                      echo '<tr data-id="' . $proxyholder['id'] . '">
                          <td>' . $counter . '</td>
                          <td>' . $proxyholder['assigneeAccountNo'] . ' - ' . $proxyholder['assignee'] . '</td>
                          <td>A-  ' . $proxyholder['proxyFormNo'] . '</td>
                          <td>' . $proxyholder['assignorAccountNo'] . '-' .  $proxyholder['assignor'] . '</td>
                          <td>' . $proxyholder['isDelinquent'] . '</td>
                          <td>' . $voteStatus . '</td>
                          <td>' . $proxyholder['auditor'] . '</td>
                          <td><input class="chk-audit" type="checkbox" ' . $proxyholder['audited'] . '></td>
                        </tr>';
                    }

                    if (count($proxyholders) === 0) {
                      echo '<tr class="text-center"><td colspan="8">No record found</td></tr>';
                    }
                    ?>


                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</div>






<script>
  $(document).ready(function() {


    let filter = "{{$filter}}";

    if (filter === '') {
      $('[data-filter="all"]').attr('disabled', true).addClass('selected-filter');
    }

    $('[data-filter="' + filter + '"]').attr('disabled', true).addClass('selected-filter');

    // $('#assignProxyBodModal').modal('show');
    $('.admin-nav-proxy-holders-amendment').addClass('active');

  })


  $(document).on('click', '#btnExportAmendmentProxy', function() {
    location.href = BASE_URL + 'admin/proxy/amendment/export'
  })

  $(document).on('click', '.chk-audit', function(e) {

    e.preventDefault();

    const id = $(this).closest('tr').attr('data-id');
    const action = $(this).is(':checked') ? 1 : 0

    const prompt = action == 0 ?
      "You are about to revoke the proxy's verified status" :
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
        audit(id, action);

      }
    })
  })

  function audit(id, action) {
    $.ajax({
      url: BASE_URL + `admin/proxy/amendment/${id}/audit`,
      method: 'POST',
      dataType: 'json',
      data: {
        id,
        action
      },
      success: function(data) {
        handleSuccess(data);
      },
      error: function(xhr) {
        handleError(xhr);
      }

    })
  }
</script>

@endsection