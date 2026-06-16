@extends('layouts.admin')

@section('css')

<style>
  .card-header {
    background-color: green;
  }
</style>
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/admin_ballots.css')}}?<?php

use Illuminate\Support\Facades\Auth;

 echo filemtime('css/admin/admin_ballots.css') ?>">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <!-- <h1>Candidates</h1> -->
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right dflex-button">
            <li class="breadcrumb-item"><a href="{{asset('admin')}}" style="color:#6b8e4e;">Dashboard</a></li>
            <li class="breadcrumb-item actives">Ballots</li>
          </ol>
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-md-12">
          <div class="card rounded-0">
            <div class="card-header rounded-0">
              <h1 class="card-title">Board of Directors</h1>
              
            </div>
            <div class="card-body">
              <div class="table-responsive" style="height: 700px; overflow: auto;">

                <h3 class="text-center font-weight-bold">Stockholder Online</h3>
                <table class="table table-bordered" id="table_ballots">
                  <thead class="text-nowrap table-light" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding">Ballot Type</th>
                      <th class="th-padding">Ballot No</th>
                      <th class="th-padding">Role</th>
                      <th class="th-padding">Revoked</th>
                      <th class="th-padding">Votes Available</th>
                      <th class="th-padding">Unused Votes</th>
                      <th class="th-padding">Status</th>
                      <?php
                      foreach ($candidates as $candidate) {
                        echo '<th class="th-padding">' . $candidate->firstName . ' ' . $candidate->lastName . '</th>';
                      }
                      ?>
                      <th class="th-padding table-secondary">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php


                    $rowCounter = 0;
                    foreach ($ballots as $ballot) {


                      if ($ballot['ballotType'] === 'proxy') {
                        continue;
                      }


                      $rowCounter++;

                      $voteCounter = 0;

                      $status = $ballot['isSubmitted'] === 1 ? '<span class="badge badge-mid-green">Submitted</span>' : '<span class="badge badge-dark-green">Unsubmitted</span>';

                      $revoked = Auth::user()->role === 'superadmin' ? $ballot['revoked'] : '-';

                      echo '<tr>
                                <td>' . $ballot['ballotType'] . '</td>
                                <td><a target="_blank" href="'.asset("admin/ballots/preview/").'/'.$ballot['ballotId'].'">' . $ballot['ballotNo'] . '</a></td>
                                <td>' . $ballot['created_by']['role'] . '</td>
                                <td>' . $revoked . '</td>
                                <td>' . $ballot['availableVotesBod'] . '</td>
                                <td>' . $ballot['unusedVotesBod'] . '</td>
                                <td>' . $status . '</td>';

                      foreach ($candidates as $candidate) {
                        $key = array_search($candidate->candidateId, array_column($ballot['bod_details'], 'candidateId'));

                        if ($key !== false) {

                          echo '<td>' . $ballot['bod_details'][$key]['vote'] . '</td>';

                          $voteCounter = $voteCounter + (int) $ballot['bod_details'][$key]['vote'];
                        } else {

                          echo '<td>-</td>';
                        }
                      }

                      echo '<td>' . $voteCounter . '</td>';


                      echo '</tr>';
                    }

                    if ($rowCounter === 0) {

                      $colspan = count($candidates) + 8;

                      echo '<tr><td class="text-center" colspan="' . $colspan . '">No record found</td></tr>';
                    }
                    ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>


                <h3 class="mt-5 text-center font-weight-bold">Proxy Voting</h3>

                <table class="table table-bordered" id="table_ballots">
                  <thead class="text-nowrap table-light" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding">Ballot Type</th>
                      <th class="th-padding">Ballot No</th>
                      <th class="th-padding">Role</th>
                      <th class="th-padding">Revoked</th>
                      <th class="th-padding">Votes Available</th>
                      <th class="th-padding">Unused Votes</th>
                      <th class="th-padding">Status</th>
                      <?php
                      foreach ($candidates as $candidate) {
                        echo '<th class="th-padding">' . $candidate->firstName . ' ' . $candidate->lastName . '</th>';
                      }
                      ?>
                      <th class="th-padding table-secondary">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php

                    $rowCounter = 0;
                    foreach ($ballots as $ballot) {


                      if ($ballot['ballotType'] === 'person') {
                        continue;
                      }


                      $rowCounter++;


                      $voteCounter = 0;

                      $status = $ballot['isSubmitted'] === 1 ? '<span class="badge badge-mid-green">Submitted</span>' : '<span class="badge badge-dark-green">Unsubmitted</span>';

                      echo '<tr>
                                <td>' . $ballot['ballotType'] . '</td>
                                <td><a target="_blank" href="'.asset("admin/ballots/preview/").'/'.$ballot['ballotId'].'">' . $ballot['ballotNo'] . '</a></td>
                                <td>' . $ballot['created_by']['role'] . '</td>
                                <td>' . $ballot['revoked'] . '</td>
                                <td>' . $ballot['availableVotesBod'] . '</td>
                                <td>' . $ballot['unusedVotesBod'] . '</td>
                                <td>' . $status . '</td>';

                      foreach ($candidates as $candidate) {
                        $key = array_search($candidate->candidateId, array_column($ballot['bod_details'], 'candidateId'));

                        if ($key !== false) {

                          echo '<td>' . $ballot['bod_details'][$key]['vote'] . '</td>';

                          $voteCounter = $voteCounter + (int) $ballot['bod_details'][$key]['vote'];
                        } else {

                          echo '<td>-</td>';
                        }
                      }

                      echo '<td>' . $voteCounter . '</td>';


                      echo '</tr>';
                    }

                    if ($rowCounter === 0) {

                      $colspan = count($candidates) + 8;

                      echo '<tr><td class="text-center" colspan="' . $colspan . '">No record found</td></tr>';
                    }
                    ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>


              </div>
            </div>

          </div>
        </div>
      </div>


      <div class="row mb-2">
        <div class="col-md-12">
          <div class="card rounded-0">
            <div class="card-header rounded-0">
              <h1 class="card-title">Agendas</h1>
            </div>


            <div class="card-body">
              <div class="table-responsive" style="height: 700px; overflow: auto;">

          
              <h3 class="text-center font-weight-bold">Stockholder Online</h3>
                <table class="table table-bordered" id="table_ballots">
                  <thead class="text-nowrap table-light" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding text-center" rowspan="2">Ballot Type</th>
                      <th class="th-padding text-center" rowspan="2">Ballot No</th>
                      <th class="th-padding text-center" rowspan="2">Role</th>
                      <th class="th-padding text-center" rowspan="2">Revoked</th>
                      <th class="th-padding text-center" rowspan="2">Status</th>
                      <?php
                      foreach ($agendas as $agenda) {
                        echo '<th class="th-padding text-center" colspan="3">' . $agenda->agendaCode . '</th>';
                      }
                      ?>
                      <th class="th-padding text-center" rowspan="2">Total Each</th>
                    
                    </tr>

                    <?php
                      for($i = 0; $i < count($agendas); $i++) {
                        echo '<td>Favor</td><td>Not Favor</td><td>Abstain</td>';
                      }
                    ?>

                  </thead>
                  <tbody>
                    <?php

                
                    $rowCounter = 0;
                    foreach ($ballots as $ballot) {


                      if ($ballot['ballotType'] === 'proxy') {
                        continue;
                      }


                      $rowCounter++;

                      $voteCounter = 0;

                      $status = $ballot['isSubmitted'] === 1 ? '<span class="badge badge-mid-green">Submitted</span>' : '<span class="badge badge-dark-green">Unsubmitted</span>';

                      echo '<tr>
                                <td class="text-center">' . $ballot['ballotType'] . '</td>
                                <td><a target="_blank" href="'.asset("admin/ballots/preview/").'/'.$ballot['ballotId'].'">' . $ballot['ballotNo'] . '</a></td>
                                <td class="text-center">' . $ballot['created_by']['role'] . '</td>
                                <td class="text-center">' . $ballot['revoked'] . '</td>
                                <td class="text-center">' . $status . '</td>';

                      foreach ($agendas as $agenda) {
                        $key = array_search($agenda->agendaId, array_column($ballot['agenda_details'], 'agendaId'));

                        if ($key !== false) {

                          $favor =  $ballot['agenda_details'][$key]['favor'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';
                          $notFavor =  $ballot['agenda_details'][$key]['notFavor'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';
                          $abstain =  $ballot['agenda_details'][$key]['abstain'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';


                          echo '<td class="text-center bg-light">' . $favor . '</td>';
                          echo '<td class="text-center">' . $notFavor . '</td>';
                          echo '<td>' . $abstain . '</td>';

                          // $voteCounter = $voteCounter + (int) $ballot['amendment_details'][$key]['vote'];
                        } else {

                            echo '<td>-</td>';
                            echo '<td>-</td>';
                            echo '<td>-</td>';
                        }
                      }

                      echo '<td class="text-center">' . ($ballot['availableVotesBod'] / 9) . '</td>';


                      echo '</tr>';
                    }

                    if ($rowCounter === 0) {

                      $colspan = (count($agendas)*3) + 8;

                      echo '<tr><td class="text-center" colspan="' . $colspan . '">No record found</td></tr>';
                    }
                    // ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>


                <h3 class="mt-5 text-center font-weight-bold">Proxy Voting</h3>

                <table class="table table-bordered" id="table_ballots">
                  <thead class="text-nowrap table-light" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding text-center" rowspan="2">Ballot Type</th>
                      <th class="th-padding text-center" rowspan="2">Ballot No</th>
                      <th class="th-padding text-center" rowspan="2">Role</th>
                      <th class="th-padding text-center" rowspan="2">Revoked</th>
                      <th class="th-padding text-center" rowspan="2">Status</th>
                      <?php
                      foreach ($agendas as $agenda) {
                        echo '<th class="th-padding text-center" colspan="3">' . $agenda->agendaCode . '</th>';
                      }
                      ?>
                      <th class="th-padding text-center" rowspan="2">Total Each</th>
                    
                    </tr>

                    <?php
                      for($i = 0; $i < count($agendas); $i++) {
                        echo '<td>Favor</td><td>Not Favor</td><td>Abstain</td>';
                      }
                    ?>

                  </thead>
                  <tbody>
                    <?php

                
                    $rowCounter = 0;
                    foreach ($ballots as $ballot) {


                      if ($ballot['ballotType'] === 'person') {
                        continue;
                      }


                      $rowCounter++;

                      $voteCounter = 0;

                      $status = $ballot['isSubmitted'] === 1 ? '<span class="badge badge-mid-green">Submitted</span>' : '<span class="badge badge-dark-green">Unsubmitted</span>';

                      echo '<tr>
                                <td class="text-center">' . $ballot['ballotType'] . '</td>
                                <td><a target="_blank" href="'.asset("admin/ballots/preview/").'/'.$ballot['ballotId'].'">' . $ballot['ballotNo'] . '</a></td>
                                <td class="text-center">' . $ballot['created_by']['role'] . '</td>
                                <td class="text-center">' . $ballot['revoked'] . '</td>
                                <td class="text-center">' . $status . '</td>';

                      foreach ($agendas as $agenda) {
                        $key = array_search($agenda->agendaId, array_column($ballot['agenda_details'], 'agendaId'));

                        if ($key !== false) {

                          $favor =  $ballot['agenda_details'][$key]['favor'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';
                          $notFavor =  $ballot['agenda_details'][$key]['notFavor'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';
                          $abstain =  $ballot['agenda_details'][$key]['abstain'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';


                          echo '<td class="text-center">' . $favor . '</td>';
                          echo '<td class="text-center">' . $notFavor . '</td>';
                          echo '<td class="text-center">' . $abstain . '</td>';

                          // $voteCounter = $voteCounter + (int) $ballot['amendment_details'][$key]['vote'];
                        } else {

                            echo '<td>-</td>';
                            echo '<td>-</td>';
                            echo '<td>-</td>';
                        }
                      }

                      echo '<td class="text-center">' . $ballot['availableVotesBod'] . '</td>';


                      echo '</tr>';
                    }

                    if ($rowCounter === 0) {

                      $colspan = (count($agendas)*3) + 8;

                      echo '<tr><td class="text-center" colspan="' . $colspan . '">No record found</td></tr>';
                    }
                    // ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>



              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-md-12">
          <div class="card rounded-0">
            <div class="card-header rounded-0">
              <h1 class="card-title">Amendments</h1>
            </div>


            <div class="card-body">
              <div class="table-responsive" style="height: 700px; overflow: auto;">

                <h3 class="text-center font-weight-bold">Stockholder Online</h3>
                <table class="table table-bordered" id="table_ballots">
                  <thead class="text-nowrap table-light" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding text-center" rowspan="2">Ballot Type</th>
                      <th class="th-padding text-center" rowspan="2">Ballot No</th>
                      <th class="th-padding text-center" rowspan="2">Role</th>
                      <th class="th-padding text-center" rowspan="2">Revoked</th>
                      <th class="th-padding text-center" rowspan="2">Status</th>
                      <?php
                      foreach ($amendments as $amendment) {
                        echo '<th class="th-padding text-center" colspan="2">' . $amendment->amendmentCode . '</th>';
                      }
                      ?>
                      <th class="th-padding text-center" rowspan="2">Total Each</th>
                    
                    </tr>

                    <?php
                      for($i = 0; $i < count($amendments); $i++) {
                        echo '<td>Yes</td><td>No</td>';
                      }
                    ?>

                  </thead>
                  <tbody>
                    <?php

                
                    $rowCounter = 0;
                    foreach ($ballots as $ballot) {


                      if ($ballot['ballotType'] === 'proxy') {
                        continue;
                      }


                      $rowCounter++;

                      $voteCounter = 0;

                      $status = $ballot['isSubmitted'] === 1 ? '<span class="badge badge-mid-green">Submitted</span>' : '<span class="badge badge-dark-green">Unsubmitted</span>';

                      echo '<tr>
                                <td class="text-center">' . $ballot['ballotType'] . '</td>
                                <td><a target="_blank" href="'.asset("admin/ballots/preview/").'/'.$ballot['ballotId'].'">' . $ballot['ballotNo'] . '</a></td>
                                <td class="text-center">' . $ballot['created_by']['role'] . '</td>
                                <td class="text-center">' . $ballot['revoked'] . '</td>
                                <td class="text-center">' . $status . '</td>';

                      foreach ($amendments as $amendment) {
                        $key = array_search($amendment->amendmentId, array_column($ballot['amendment_details'], 'amendmentId'));

                        if ($key !== false) {

                          $yes =  $ballot['amendment_details'][$key]['yes'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';
                          $no =  $ballot['amendment_details'][$key]['no'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';


                          echo '<td class="text-center">' . $yes . '</td>';
                          echo '<td class="text-center">' . $no . '</td>';

                          // $voteCounter = $voteCounter + (int) $ballot['amendment_details'][$key]['vote'];
                        } else {

                            echo '<td>-</td>';
                            echo '<td>-</td>';
                        }
                      }

                      echo '<td class="text-center">' . $ballot['availableVotesAmendment'] . '</td>';


                      echo '</tr>';
                    }

                    if ($rowCounter === 0) {

                      $colspan = (count($amendments)*2) + 8;

                      echo '<tr><td class="text-center" colspan="' . $colspan . '">No record found</td></tr>';
                    }
                    // ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>


                <h3 class="mt-5 text-center font-weight-bold">Proxy Voting</h3>

                <table class="table table-bordered" id="table_ballots">
                  <thead class="text-nowrap table-light" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding text-center" rowspan="2">Ballot Type</th>
                      <th class="th-padding text-center" rowspan="2">Ballot No</th>
                      <th class="th-padding text-center" rowspan="2">Role</th>
                      <th class="th-padding text-center" rowspan="2">Revoked</th>
                      <th class="th-padding text-center" rowspan="2">Status</th>
                      <?php
                      foreach ($amendments as $amendment) {
                        echo '<th class="th-padding text-center" colspan="2">' . $amendment->amendmentCode . '</th>';
                      }
                      ?>
                      <th class="th-padding text-center" rowspan="2">Total Each</th>
                    
                    </tr>

                    <?php
                      for($i = 0; $i < count($amendments); $i++) {
                        echo '<td>Yes</td><td>No</td>';
                      }
                    ?>

                  </thead>
                  <tbody>
                    <?php

                
                    $rowCounter = 0;
                    foreach ($ballots as $ballot) {


                      if ($ballot['ballotType'] === 'person') {
                        continue;
                      }


                      $rowCounter++;

                      $voteCounter = 0;

                      $status = $ballot['isSubmitted'] === 1 ? '<span class="badge badge-mid-green">Submitted</span>' : '<span class="badge badge-dark-green">Unsubmitted</span>';

                      echo '<tr>
                                <td class="text-center">' . $ballot['ballotType'] . '</td>
                                <td><a target="_blank" href="'.asset("admin/ballots/preview/").'/'.$ballot['ballotId'].'">' . $ballot['ballotNo'] . '</a></td>
                                <td class="text-center">' . $ballot['created_by']['role'] . '</td>
                                <td class="text-center">' . $ballot['revoked'] . '</td>
                                <td class="text-center">' . $status . '</td>';

                      foreach ($amendments as $amendment) {
                        $key = array_search($amendment->amendmentId, array_column($ballot['amendment_details'], 'amendmentId'));

                        if ($key !== false) {

                          $yes =  $ballot['amendment_details'][$key]['yes'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';
                          $no =  $ballot['amendment_details'][$key]['no'] === 0 ? '<input type="radio">' : '<input type="radio" checked>';


                          echo '<td>' . $yes . '</td>';
                          echo '<td>' . $no . '</td>';

                          // $voteCounter = $voteCounter + (int) $ballot['amendment_details'][$key]['vote'];
                        } else {

                            echo '<td>-</td>';
                            echo '<td>-</td>';
                        }
                      }

                      echo '<td class="text-center">' . $ballot['availableVotesAmendment'] . '</td>';


                      echo '</tr>';
                    }

                    if ($rowCounter === 0) {

                      $colspan = (count($amendments)*2) + 8;

                      echo '<tr><td class="text-center" colspan="' . $colspan . '">No record found</td></tr>';
                    }
                    // ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>


              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
</section>

<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
  <i class="fas fa-chevron-up"></i>
</a>
</div>



<script>
  $(document).on('click', '.ballot-no', function() {

    let id = $(this).closest('tr').attr('data-id');


    location.href = BASE_URL + 'admin/ballot/form/' + id;
    // window.open("{{asset('admin/ballot/form')}}/" + id, '_blank');

  })


  $(document).on('click', '.ballot-id', function() {

    let id = $(this).attr('data-id');

    $('#ballotModal').modal('show');

  })
  $(document).ready(function() {

    $('.admin-nav-ballots').addClass('active');

  })
</script>

@endsection