<?php

  if(count($arr_ballots) == 0) {

    return view('errors.500');

  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>BALLOT #<?php echo $arr_ballots["ballot_no"]; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<div class="jumbotron text-center">
  <h1>BALLOT #: <?php echo $arr_ballots["ballot_no"];  ?></h1>

  
  <div class="row">
    <div class="col-md-12"><h3 class="aliegn-left d-block text-uppercase"><?php echo date('F d, Y g:i:s A', strtotime($arr_ballots["date_time"])); ?></h3></div>
  
  </div>
  
</div>
  
  <div class="container-fluid">
    <div class="card card-success">
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Vote</th>
            </tr>
          </thead>
          <tbody>

            <?php
              try {

                $candidates = '';
                $totalVoteCasted = 0;
              
                
                foreach($arr_candidates as $key => $candidateName) {

                  $vote = '';

                  if(isset($arr_ballots["candidates"][$key])) {

                    $vote = $arr_ballots["candidates"][$key];

                    $totalVoteCasted += $vote;

                  }
                 
                  $candidates .= '<tr>';
                  $candidates .= '<td>' . $candidateName . '</td>';
                  $candidates .= '<td>' . $vote . '</td>';
                  $candidates .= '</tr>';

                }



                $unusedVote = ($arr_ballots["total_share"] * 3) - $totalVoteCasted;

                $unusedVote = ($unusedVote == 0) ? 'NO VOTES UNUSED' : 'VOTES UNUSED: ' . $unusedVote;

                echo $candidates;

              }

              catch(Exception $e) {
                echo $e->getMessage();
              }

            ?>
           
          </tbody>
          <tfoot>
            <tr>
              <td class="text-right">TOTAL VOTES CASTED </td><td><?php echo $totalVoteCasted; ?></td>
            </tr>

            <tr>
              <td colspan="2" class="text-center bg-success"><?php echo $unusedVote; ?></td>
            <tr>
             
            
          </tfoot>
        </table>

        <h1>Amendments</h1>
        <table class="table table-success table-bordered">
          <thead>
            <tr>
              <th>Amendment</th>
              <th>Favor</th>
              <th>Not Favor</th>
              <th>Abstain</th>
            </tr>
          </thead>
          <tbody>
            <?php

              $amendmentData = '';

              foreach($arr_amendments_id as $key => $amendment) {

                $amendmentData .= '<tr>';
                
                $amendmentData .= '<td>'.$key . ". " . $amendment.'</td>';
                
                $amendmentData .= '<td>'.$arr_amendments[$key]["favor"].'</td>';
                $amendmentData .= '<td>'.$arr_amendments[$key]["not_favor"].'</td>';
                $amendmentData .= '<td>'.$arr_amendments[$key]["abstain"].'</td>';
                
                $amendmentData .= '</tr>';


              }



              echo $amendmentData;
            ?>


          </tbody>
        </table>

        <?php
        

    
          
         
        ?>
      </div>
    </div>
  </div>


</body>
</html>
