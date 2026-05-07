@extends('layouts.admin')

@section('css')
    
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
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
              <li class="breadcrumb-item active">Activity Logs</li>
            </ol>
          </div>
        </div>

        <div class="row mb-2">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h1 class="card-title">Activity Logs</h1>
              </div>
              <!-- /.card-header -->
               <div class="card-body">
                  <table id="activityTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                  <tbody>

                    <?php

  
                        if(isset($activities)) {

                          $data = "";

                          if(count($activities) > 0) {
                            
                           

                            foreach($activities as $key => $activity) {


                              $data .= '<tr>';
                              $data .= '<td>'.$activity->createdAt.'</td>';
                              $data .= '<td>'.$activity->stockholder.'</td>';
                              $data .= '<td>'.$activity->description.'</td>';
                              $data .= '<td>'.$activity->action.'</td>';
                              $data .= '</tr>';


                            }

                            

                          }

                          else {

                            $data .= '<tr>
                                        <td colspan="4" class="text-center">No Data</td>
                                      </tr>';
                          }


                          echo $data;



                        }

                    ?>
                   
                    
                  </tbody>

                </table>
              </div>
              <!-- /.card-body -->
            </div>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    
    <!-- /.content -->

    <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
      <i class="fas fa-chevron-up"></i>
    </a>
  </div>
<!-- /.content-wrapper -->
  <script>
    $(document).ready(function(){
      $('.admin-nav-activity').addClass('active');
    })
  </script>

  @endsection