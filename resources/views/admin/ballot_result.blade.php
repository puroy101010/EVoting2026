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
                  
                                   
                <table id="" class="table table-bordered table-hover mt-5">
                    <thead class="text-nowrap">
                      <tr>
                        <th class="th-padding">#</th>
                        <th class="th-padding">REGULAR</th>
                        <th class="th-padding">STOCKHOLDER</th>
                        <th class="th-padding">PROXY</th>
                        <th class="th-padding">TOTAL</th>
                      
                      </tr>
                    </thead>
                    <tbody>

                        <?php
                        
                        $counter = 1;
                          foreach($candidates as $candidate) {


                            if($candidate->type === 'independent') {
                              continue;
                            }

                            $bodStockholderOnline = array_key_exists($candidate->candidateId, $bodSummary["person"]) ? array_sum($bodSummary["person"][$candidate->candidateId]) : 0;
                            $bodProxy = array_key_exists($candidate->candidateId, $bodSummary["proxy"]) ? array_sum($bodSummary["proxy"][$candidate->candidateId]) : 0;

                            $total = $bodStockholderOnline + $bodProxy;
                            echo '<tr>
                                    <td>'.$counter.'</td>
                                    <td>'.$candidate->firstName.' ' . $candidate->middleName. ' ' . $candidate->lastName.'</td>
                                    <td>'.$bodStockholderOnline.'</td>
                                    <td>'.$bodProxy.'</td>
                                    <td>'.$total.'</td></tr>';





                            $counter++;



                          }
                        ?>
                 
                     
                  
                    </tbody>
                  </table>

                  <table id="" class="table table-bordered table-hover">
                    <thead class="text-nowrap">
                      <tr>
                        <th class="th-padding">#</th>
                        <th class="th-padding">INDEPENDENT</th>
                        <th class="th-padding">STOCKHOLDER</th>
                        <th class="th-padding">PROXY</th>
                        <th class="th-padding">TOTAL</th>
                      
                      </tr>
                    </thead>
                    <tbody>

                        <?php
                        
                        $counter = 1;
                          foreach($candidates as $candidate) {


                            if($candidate->type === 'regular') {
                              continue;
                            }

                            $bodStockholderOnline = array_key_exists($candidate->candidateId, $bodSummary["person"]) ? array_sum($bodSummary["person"][$candidate->candidateId]) : 0;
                            $bodProxy = array_key_exists($candidate->candidateId, $bodSummary["proxy"]) ? array_sum($bodSummary["proxy"][$candidate->candidateId]) : 0;

                            $total = $bodStockholderOnline + $bodProxy;
                            echo '<tr>
                                    <td>'.$counter.'</td>
                                    <td>'.$candidate->firstName.' ' . $candidate->middleName. ' ' . $candidate->lastName.'</td>
                                    <td>'.$bodStockholderOnline.'</td>
                                    <td>'.$bodProxy.'</td>
                                    <td>'.$total.'</td></tr>';





                            $counter++;



                          }
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



                    

                          // foreach($amendments as $amendment) {

                          //   echo '<tr><td>'.$amendment["amendment"].'</td><td>'.$amendment["inFavor"].'</td><td>'.$amendment["notFavor"].'</td><td>'.$amendment["abstain"].'</td></tr>';

                          // }

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