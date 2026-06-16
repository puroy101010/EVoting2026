@extends('layouts.web_admin')

@section('css')
    
@endsection

@section('content')
  <div class="card rounded-0">
    <div class="card-header">
        <h3 class="font-weight-bold">Audit</h3>
     </div>
    <div class="card-body">
      <table class="table table-bordered table-hover"> 
        <thead class="table-success">
          <tr>
            <th>Ballot No</th>
            <th>Voter's Name</th>
            <th>Candidate Name</th>
            <th>Total Vote Casted</th>
            <th>Revoked</th>
            <th>Date & Time</th>
          </tr>
        </thead>
          <tbody>
             <?php

                $data = "";

                if(count($ballots) > 0) {
                  
                  foreach($ballots as $key => $ballot) {

                    $candidateName  = $ballot->cFirstName . " " . $ballot->cMiddleName . "  " . $ballot->cLastName;
                    
                    $data .= '<tr>';
                    $data .= '<td>'.$ballot->ballotNo.'</td>';
                    $data .= '<td>'.$ballot->voterName.'</td>';
                    $data .= '<td>'.$candidateName.'</td>';
                    $data .= '<td>'.$ballot->totalVote.'</td>';
                    $data .= '<td></td>';
                    $data .= '<td>'.$ballot->createdAt.'</td>';
                    $data .= '</tr>';


                  }

                  

                }

                else {

                  $data .= '<tr>
                              <td colspan="4" class="text-center">No Data</td>
                            </tr>';
                }


                echo $data;



              

             ?>
          </tbody>
      </table>
    </div>
  </div>

  @endsection