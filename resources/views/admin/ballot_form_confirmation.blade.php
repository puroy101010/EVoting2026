<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>BALLOT #<?php echo $ballot->ballotNo; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

  <style>
      @font-face {
        src: url(Chapaza.ttf);
        font-family: chapaza-bold;
      }
        
      @font-face {
        src: url(AvenirLTStd-Black.otf);
        font-family: Avenir LT Std;
      }
        
      @font-face {
        src: url(AvenirLTStd-Roman.otf);
        font-family: AvenirLTStd-Roman;
      }

      .jumbotron {
        background-color: #304c40;
      }

      .jumbotron .title-summary {
        color: #ffffff;
        font-family: chapaza-bold;
      }

      .jumbotron .ballot_no {
        color: #ffffff;
        font-family: Avenir LT Std;
      }

      .jumbotron .p-datetime {
        color: #ffffff;
        font-family: AvenirLTStd-Roman;
      }

      .alert-mid-green {
        background-color: #6b8e4e;
        border-color: #b0d182;
        font-family: AvenirLTStd-Roman;
        color: #ffffff;
        font-size: 1.25em;
        font-style: bold;
      }
      
      #summary_candidate_tb th {
        background-color:#304c40;
        color: #fff;
        font-size: 1.25em;
        font-family: Avenir LT Std;
      }

      #summary_candidate_tb td {
        color:  #6b8e4e;;
        font-family: AvenirLTStd-Roman;
        font-size: 1em;
     }

     #summary_amendments_tb th {
        background-color:#304c40;
        color: #fff;
        font-size: 1.25em;
        font-family: Avenir LT Std;
     }

     #summary_amendments_tb td {
        color:  #6b8e4e;;
        font-family: AvenirLTStd-Roman;
        font-size: 1em;
    }

     #summary_candidate_tb .td-total-vote-cast {
      font-size: 1.25em;
      color: #304c40;
     }

     #summary_candidate_tb .td-total-cast {
      font-size: 1.25em;
      font-style: bold;
      color: #304c40;
     }

     .bg-light-green {
        background-color: #6b8e4e;
        color: #ffffff!important;
        border: 1px solid #ffffff;
        font-family: chapaza-bold;
     }

     h4 {
      color: #304c40;
      font-family: chapaza-bold;
     }

     input[type='radio']:after {
        height: 1.2rem;
        width: 1.2rem;
        border-radius: 15px;
        top: -2px;
        left: -1px;
        position: relative;
        background-color: #b0d182;
        content: '';
        display: inline-block;
        visibility: visible;
        border: 2px solid white;
    }

    input[type='radio']:checked:after {
        height: 1.2rem;
        width: 1.2rem;
        border-radius: 15px;
        top: -2px;
        left: -1px;
        position: relative;
        background-color: #304c40;
        content: '';
        display: inline-block;
        visibility: visible;
        border: 2px solid #b0d182;
    }

    /*------Media Query--------*/
    /* iPhone X Portrait and Landscape */
    @media only screen 
      and (min-device-width: 375px) 
      and (max-device-width: 812px) 
      and (-webkit-min-device-pixel-ratio: 3) {

      .jumbotron {
        padding: 5px;
      }
   
      .jumbotron .title-summary {
        font-size: 1.563em;
      }

      .jumbotron .ballot_no {
        font-size: 1.25em;
      }

      .jumbotron .p-datetime {
        font-size: 0.938em;
      }

      h4 {
        font-size: 1.125em;
        font-weight: bold;
      }

      #summary_candidate_tb th {
        font-size: 0.938em;
      }

      #summary_candidate_tb td {
        font-size: 0.875em;
     }

     #summary_candidate_tb .td-total-vote-cast {
      font-size: 0.938em;
      font-weight: bold;
     }

     #summary_candidate_tb .td-total-cast {
      font-size: 1.125em;
      font-weight: bold;
     }

     #summary_amendments_tb th {
       font-size: 0.938em;
     }

     #summary_amendments_tb td {
      font-size: 0.875em;
    }

  }

  /* iPhone 6, 6S, 7 and 8 Portrait and Landscape */
    @media only screen 
      and (min-device-width: 375px) 
      and (max-device-width: 667px) 
      and (-webkit-min-device-pixel-ratio: 2) {
        
        .jumbotron {
        padding: 5px;
      }
   
      .jumbotron .title-summary {
        font-size: 1.563em;
      }

      .jumbotron .ballot_no {
        font-size: 1.25em;
      }

      .jumbotron .p-datetime {
        font-size: 0.938em;
      }

      h4 {
        font-size: 1.125em;
        font-weight: bold;
      }

      #summary_candidate_tb th {
        font-size: 0.938em;
      }

      #summary_candidate_tb td {
        font-size: 0.875em;
     }

     #summary_candidate_tb .td-total-vote-cast {
      font-size: 0.938em;
      font-weight: bold;
     }

     #summary_candidate_tb .td-total-cast {
      font-size: 1.125em;
      font-weight: bold;
     }

     #summary_amendments_tb th {
       font-size: 0.938em;
     }

     #summary_amendments_tb td {
      font-size: 0.875em;
    } 

    }

    /* iPhone 5, 5S, 5C and 5SE Portrait and Landscape */
    @media only screen 
      and (min-device-width: 320px) 
      and (max-device-width: 568px)
      and (-webkit-min-device-pixel-ratio: 2) {

      .jumbotron {
        padding: 5px;
      }
   
      .jumbotron .title-summary {
        font-size: 1.563em;
      }

      .jumbotron .ballot_no {
        font-size: 1.25em;
      }

      .jumbotron .p-datetime {
        font-size: 0.938em;
      }

      h4 {
        font-size: 1.125em;
        font-weight: bold;
      }

      #summary_candidate_tb th {
        font-size: 0.938em;
      }

      #summary_candidate_tb td {
        font-size: 0.875em;
     }

     #summary_candidate_tb .td-total-vote-cast {
      font-size: 0.938em;
      font-weight: bold;
     }

     #summary_candidate_tb .td-total-cast {
      font-size: 1.125em;
      font-weight: bold;
     }

     #summary_amendments_tb th {
       font-size: 0.938em;
     }

     #summary_amendments_tb td {
      font-size: 0.875em;
    } 

  }

  </style>
</head>
<body>

<div class="jumbotron rounded-0 text-center">
  <h1 class="title-summary">Summary Confirmation</h1>
  <h3 class="ballot_no">BALLOT #: <?php echo $ballot->ballotNo;  ?></h3>

  <div class="row">
    <div class="col-md-12"><p class="p-datetime text-uppercase"><?php echo date('F d, Y g:i:s A', strtotime($ballot->submittedAt)); ?></p></div>
  </div>
  
</div>
  
  <div class="container">
    <div style="display: flex; align-items: center; justify-content: center;">
      <div class="alert alert-mid-green text-center col-md-4" role="alert"><?php echo $remarks; ?></div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
        <h4>CANDIDATES</h4>
          <table class="table table-bordered" id="summary_candidate_tb">
            <thead>
              <tr>
                <th class="text-center">Candidate</th>
                <th class="text-center">Vote</th>
              </tr>
            </thead>
            <tbody>

              <?php

                try {
                  

                  $voteCounter = 0;

                  foreach($candidates as $candidate) {

                    $voteCounter = $voteCounter + $candidate["vote"];

                    echo '<tr><td>'.$candidate["name"].'</td><td>'.$candidate["vote"].'</td></tr>';
          
                  }


                  if(count($candidates) == 0) {

    
                    echo '<tr><td class="text-center td-total-vote-cast" colspan="2">You didn\'t cast a vote to any candidates</td></tr>';
                  }

                  $unusedVote = ($ballot->votesAvailable * 3) - $voteCounter;
                  $unusedVote = ($unusedVote == 0) ? 'NO UNUSED VOTES' : 'UNUSED VOTES: ' . $unusedVote;

                }

                catch(Exception $e) {


                }

              ?>
            
            </tbody>
            <tfoot>
              <tr>
                <td class="text-right td-total-vote-cast">TOTAL VOTES CAST </td><td class="td-total-cast"><?php echo $voteCounter; ?></td>
              </tr>

              <tr>
                <td colspan="2" class="text-center bg-light-green"><?php echo $unusedVote; ?></td>
              <tr>
              
              
            </tfoot>
          </table>
        </div> 

        <h4>AMENDMENTS</h4>
          <div class="table-responsive" style="height: 700px; overflow: auto;">
            <table class="table table-bordered" id="summary_amendments_tb">
              <thead class="text-nowrap" style="position: sticky;top: 0; z-index: 1;">
                <tr>
                  <th>Amendment</th>
                  <th>Favor</th>
                  <th>Not Favor</th>
                  <th>Abstain</th>
                </tr>
              </thead>
              <tbody>
                <?php

                  foreach($amendments as $amendment) {
                    echo  '<tr><td class="td-ammend-desc">'.$amendment["amendment"].'</td>
                            <td class="td-radio text-center"><input class="custom-radio" type="radio" ' .($amendment["i"]  == 1 ? "checked" : ""). ' disabled></td>
                            <td class="td-radio text-center"><input class="custom-radio" type="radio" ' .($amendment["n"]  == 1 ? "checked" : ""). ' disabled></td>
                            <td class="td-radio text-center"><input class="custom-radio" type="radio" ' .($amendment["a"]   == 1 ? "checked" : ""). ' disabled></td>
                            </tr>';
                            
                  }

                ?>

              </tbody>
            </table>
          </div>
      </div>
    </div>
  </div>
</body>
</html>
