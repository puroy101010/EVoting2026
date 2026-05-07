    <?php


      header("Content-Type: application/vnd.ms-excel");
      header("Expires: 0");
      header("content-disposition: attachment;filename=Stockholder_List_" . date('ymd', strtotime($datetime)) . '.xls');

      use App\Http\Controllers\AppController;

      ?>
    <table id="memberTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <td>ID</td>
          <th>Stockholder</th>
          <th>Email</th>
          <th>Account No</th>
          <th>Suffix</th>
          <th>Proxy Form No.</th>
          <th>Status</th>

        </tr>
      </thead>
      <tbody>

        <?php

          try {

            $roman = array(1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI");

            $tableRow = "";

            if(isset($members)) {
        
              if(count($members) > 0) {

                foreach($members as $key => $member) {

              



                  $status = 'active';


                  if($member["status"] == 0 AND $member["suffix"] == 1) {

                    $status = 'delinquent';

                  }

                  



                  $tableRow .= '
                    <tr>
                      <td>'.$member["account_no"] . '-'.$roman[$member["suffix"]].'</td>
                      <td>'.$member["stockholder"].'</td>
                      <td>'.$member["email"].'</td>
                      <td>'.$member["account_no"].'</td>
                      <td>'.$roman[$member["suffix"]].'</td>
                      <td>'.$member["proxy_form_number"] .'</td>
                      <td>'.$status.'</td>
                    
                    </tr>';
                }
              }

              else {

                $tableRow .= '<tr><td colspan="7">No Datsa</td></tr>';

              }

              echo $tableRow;

            }
          }
          catch (Exception $e) {

            AppController::systemm_log(500, "EXPORT_STOCKHOLDERS", $e->getMessage());
            
          }

        ?>
          
      </tbody>

    </table>
              