@extends('layouts.admin')

@section('css')
<style>
  .total-proxy {
    cursor: pointer;
  }
</style>
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/admin_proxysummary.css')}}?<?php echo time(); ?>">
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
            <li class="breadcrumb-item actives">Summary</li>
            <li class="breadcrumb-item actives">Amendment</li>
          </ol>
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-md-12">
          <div class="card rounded-0">
            <div class="card-header rounded-0">
              <h1 class="card-title">AMENDMENT PROXY SUMMARY</h1>
              <div class="float-sm-right">
                <button class="btn btn-custom-darkg rounded-0 btn-button d-none"><i class="fa fa-upload"> Export</i></button>
              </div>
            </div>

            <div class="card-body">
              <div class="table-responsive" style="height: 700px; overflow: auto;">
                <table class="table table-bordered" id="table_proxy_summary">
                  <thead class="text-nowrap text-center" style="position: sticky;top: 0; z-index: 1;">
                    <th class="th-padding">#</th>
                    <th class="th-padding">Account No</th>
                    <th class="th-padding">Assignee</th>
                    <th class="th-padding">Role</th>
                    <th class="th-padding">Collected Proxies</th>
                    <th class="th-padding">Active</th>
                    <th class="th-padding">Delinquent</th>
                    <th class="th-padding">Available Proxy</th>

                  </thead>
                  <tbody>
                    <?php

                    $counter = 1;


                    foreach ($proxyholders as $proxyholder) {

                      $totalCollectdProxy = count($proxyholder['proxies']);
                      $countedValues = array_count_values($proxyholder['isDelinquent']);

                      $delinquentCount = $countedValues[1] ?? 0;
                      $activeCount     = $countedValues[0] ?? 0;

                      $availableProxy =  $totalCollectdProxy  - (int) $delinquentCount;


                      $assignee = $proxyholder['stockholder'] . ' ' . ($proxyholder['corpRep'] === null ? '' : '-' . $proxyholder['corpRep']);

                      echo '<tr data-id="' . $proxyholder['userId'] . '">
                                    <td class="td-padding">' . $counter . '</td>
                                    <td class="td-padding">' . $proxyholder['accountNo'] . '</td>
                                    <td class="td-padding">' . $assignee . '</td>
                                    <td class="td-padding text-center">' . $proxyholder['role'] . '</td>
                                    <td class="td-padding text-center"><span class="total-proxy">' . $totalCollectdProxy . '</span></td>
                                    <td class="td-padding text-center">' . $activeCount . '</td>
                                    <td class="td-padding text-center">' . $delinquentCount . '</td>
                                    <td class="td-padding text-center">' . $availableProxy . '</td>
                                  </tr>';
                      $counter++;
                    }


                    if (count($proxyholders) === 0) {
                      echo '<tr><td class="text-center" colspan="8">No record</td></tr>';
                    }

                    ?>

                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</div>



<!-- PROXY LIST MODAL -->
<div class="modal fade" id="proxy_form_modal" tabindex="-1" role="dialog" aria-labelledby="proxyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="proxyModalLabel">Accounts</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="alert alert-success" role="alert">
          <p>Collected: <span id="summaryCollectedProxy"></span></p>
          <p>Delinquent: <span id="summaryDelinquentTotal"></span></p>
          <p>Revoked: <span id="summaryRevokedTotal"></span></p>
          <p>Net available votes: <span id="netAvailableVotes"></span></p>
        </div>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Account No</th>
              <th>Form #</th>
              <th>Assignor</th>
              <th>Status</th>
              <th>Vote</th>
            </tr>
          </thead>
          <tbody>

          </tbody>

        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  function showProxy(data) {

    let stockholders = '';
    let counter = 1;


    let usedAccounts = 0;
    let totalDelinquent = 0;
    let totalCollected = 0;


    for (let proxy of data['proxyList']) {

      totalCollected++;


      let status = proxy.stockholder_account.isDelinquent === 1 ?
        '<span class="badge badge-danger">Delinquent</span>' :
        '<span class="badge badge-success">Active</span>';

      let vote = proxy['used_account'] !== null ? '<span class="badge badge-secondary">Used</span>' : '<span class="badge badge-primary">Available</span>';



      if (proxy.stockholder_account.isDelinquent === 1) {
        totalDelinquent++;

      }


      if (proxy['used_account'] !== null) {
        usedAccounts++;

      }


      let assignorName = proxy.assignor.role === 'stockholder' ?
        proxy.assignor.stockholder.stockholder :
        proxy.assignor.stockholder_account.corpRep;



      stockholders += `<tr>
                      <td> ${counter} </td>
                      <td> ${proxy.stockholder_account.accountKey} </td>
                      <td>A-${proxy.proxyAmendmentFormNo} </td>
                      <td> ${assignorName} </td>
                      <td> ${status} </td>
                      <td> ${vote} </td>
                      </tr>`;
      counter++;

    }

    let netAvailableVote = totalCollected - totalDelinquent - usedAccounts;


    $('#summaryCollectedProxy').text(totalCollected);
    $('#summaryDelinquentTotal').text(totalDelinquent);
    $('#summaryRevokedTotal').text(usedAccounts);
    $('#netAvailableVotes').text(netAvailableVote);


    $('#proxy_form_modal tbody').html(data.length === 0 ? '<tr><td class="text-center text-muted" colspan="5">No data</td></tr>' : stockholders);

    $('#proxy_form_modal').modal('show');

  }

  $(document).on('click', '.total-proxy', function() {
    const id = $(this).closest('tr').attr('data-id');
    $.ajax({
      url: BASE_URL + 'admin/proxy/amendment/summary/' + id,
      method: 'GET',
      dataType: 'json',
      success: function(data) {
        showProxy(data);
      },
      error: function(xhr) {
        handleError(xhr);
      }
    })
  })

  $(document).ready(function() {
    $('.admin-nav-proxy-holders-amendment-summary').addClass('active');
  })
</script>

@endsection