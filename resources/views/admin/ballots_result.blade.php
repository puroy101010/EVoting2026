@extends('layouts.admin')

@section('css')
  <link rel="stylesheet" type="text/css" href="{{asset('css/admin/admin_ballot_summary.css')}}?<?php echo filemtime('css/admin/admin_ballot_summary.css')?>">
@endsection

@section('content')

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{asset('admin')}}" style="color:#6b8e4e;">Dashboard</a></li>
              <li class="breadcrumb-item actives">Election</li>
            </ol>
          </div>
        </div>

        <div class="row mb-2">
          <div class="col-md-12">
            <div class="card rounded-0">
              <div class="card-header rounded-0">
                <h1 class="card-title text-white">Election Result</h1> <a href="{{asset('admin/ballot/result/export')}}" class="float-right"><button class="btn btn-sm btn-custom-darkg">Export</button></a>
                
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  
                  <table id="candidatesTable" class="table table-bordered table-hover">
                    <thead class="text-nowrap">
                      <tr>
                        <th class="th-padding">#</th>
                        <th class="th-padding">CANDIDATES</th>
                        <th class="th-padding">VOTE BY PROXY</th>
                        <th class="th-padding">VOTE IN PERSON</th>
                        <th class="th-padding">TOTAL</th>
                      
                      </tr>
                    </thead>
                    <tbody>

                    <?php

                      use App\Http\Controllers\AppController;


                        $data = '';

                        $counter      = 1;
                        $totalProxy   = 0;
                        $totalPerson  = 0;
                        $totalVotes   = 0;


                        foreach($candidates as $candidate) {

                          $data .= '<tr>
                                      <td>' . $counter . '</td>
                                      <td>'.$candidate->lastName . ', ' . $candidate->firstName . '</td>';


                          $candidateVotesProxy = 0;


                          if(array_key_exists('proxy', $ballots)) {

                            if(array_key_exists($candidate->candidateId, $ballots['proxy'])) {

                              $candidateVotesProxy = array_sum($ballots['proxy'][$candidate->candidateId]);

                            }

                          }


                          $candidateVotesPerson = 0;

                          if(array_key_exists('person', $ballots)) {

                            if(array_key_exists($candidate->candidateId, $ballots['person'])) {

                              $candidateVotesPerson = array_sum($ballots['person'][$candidate->candidateId]);

                            }

                          }

                          $candidateTotalVote = $candidateVotesProxy + $candidateVotesPerson;
                        
                          
                          $data .= '<td>'.$candidateVotesProxy.'</td>
                                    <td>'.$candidateVotesPerson.'</td>
                                    <td>'.$candidateTotalVote.'</td>
                                    </tr>';




                          $totalVotes   = $totalVotes + $candidateTotalVote;
                          $totalProxy   = $totalProxy + $candidateVotesProxy;
                          $totalPerson  = $totalPerson + $candidateVotesPerson;

                          $counter++;


                        }

                        echo $data;

                        echo '<tr><td></td><td></td><td class="total-number">'.$totalProxy.'</td><td class="total-number">'.$totalPerson.'</td><td class="total-number">'.$totalVotes.'</td></tr>';

                    ?>
                  
                    </tbody>
                  </table>
                 <div class="table-responsive" style="height: 700px; overflow: auto;"> 
                  <table id="amendmetsTable" class="table table-bordered table-hover">
                      <thead class="text-nowrap" style="position: sticky;top: 0; z-index: 1;">
                        <tr>
                        
                          <th class="th-padding">AMENDMENTS</th>
                          <th class="th-padding">IN FAVOR</th>
                          <th class="th-padding">NOT FAVOR</th>
                          <th class="th-padding">ABSTAIN</th>
                        
                        </tr>
                      </thead>
                      <tbody>

                      <?php



                    

                          foreach($amendments as $amendment) {

                            echo '<tr><td>'.$amendment["amendment"].'</td><td>'.$amendment["inFavor"].'</td><td>'.$amendment["notFavor"].'</td><td>'.$amendment["abstain"].'</td></tr>';

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
      </div>
    </section>

    <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></a>

  </div>

  <script>
    $(document).ready(function(){
      $('.admin-nav-ballots-summary').addClass('active');
      })
  </script>

  @endsection