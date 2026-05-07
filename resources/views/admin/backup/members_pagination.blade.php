@extends('layouts.admin')

@section('css')
    
@endsection
  
@section('content')

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{asset('admin/dashboard')}}">Home</a></li>
              <li class="breadcrumb-item active">Members</li>
            </ol>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h1 class="card-title">Members</h1>
                <div class="float-sm-right">
                  <button class="btn btn-md btn-success rounded-0" data-toggle="modal" data-target="#importModal"><i class="fas fa-file-import"> Import</i></button>
                  <button class="btn btn-md btn-success rounded-0" id="btn_add_member"><i class="fas fa-plus"> Add Member</i></button>
                </div>
              </div>
              
              <div class="card-body">
                <div class="row">
                  <div class="col-md-2">
                 
                    <select name="" id="no_per_page" class="form-control form-control-sm">
                    
                        <?php

                          for($i = 10; $i < count($members); $i = $i + 5) {

                            echo '<option value="'.$i.'">'.$i.'</option>';

                          } 
                        ?>
                      
                    </select>
                  </div>
                  <div class="col-md-2 offset-md-8">
                    <input type="search" class="form-control form-control-sm" id="search_member">
                  </div>
                  
                </div>
                <br>
                <table id="memberTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Stock Holder</th>
                      <th>Email</th>
                      <th>Account Number</th>
                      <th>Proxy Form No.</th>
                      <th>No of Share</th>
                      <th>In-Person (vote)</th>
                      <th>SPA (vote)</th>
                      <th>Proxy (vote)</th>
                      <th>Type</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="member_body">
                      
                  </tbody>
          
                </table><br>

                <nav aria-label="Page navigation example">
                  <ul class="pagination justify-content-center" id="btn_pages">
                    <!-- <li class="page-item disabled">
                      <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                      <a class="page-link" href="#">Next</a>
                    </li> -->
                  </ul>
                </nav>   
                
                <p>TOTAL RECORD: <span id="total_record"></span></p>
              </div>
                
            </div>
          </div>
        </div>
      </div>
    </section>
    <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></a>  
  </div>
  

  <!-- ADD MEMBER MODAL -->
  <div class="modal fade" id="addMemberModal">
    <div class="modal-dialog">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Add Member</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <form id="form_add_member">
          <!-- Modal body -->
          <div class="modal-body">
        
            @csrf

            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Stockholder <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <input type="text" id="" class="form-control" name="stockholder" placeholder="Stockholder" autocomplete="off" required>
              </div>
            </div>
            
            <div class="form-group row">
              <label for="email" class="col-md-4 col-form-label">Email <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <input type="text" id="email" class="form-control" name="email" placeholder="Email" autocomplete="off" required>
              </div>
            </div>
           
            <div class="form-group row">
              <label for="account_number" class="col-md-4 col-form-label">Account Number <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <input type="text" id="account_number" class="form-control" name="account_number" placeholder="Account Number" autocomplete="off" required maxlength="4">
              </div>
            </div>
            
            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Proxy Form No <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <input type="text" id="" class="form-control" name="proxy_form_no" placeholder="Proxy Form No" autocomplete="off" maxlength="4" required>
              </div>
            </div>

            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Suffix <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <input type="number" id="" class="form-control" name="suffix" placeholder="Suffix" autocomplete="off" max="6" required>
              </div>
            </div>

        
            <div class="form-group row">
              <label for="member_type" class="col-md-4 col-form-label">Member Type</label>
              <div class="col-md-7">
                  <input type="text" id="member_type" class="form-control" name="member_type" placeholder="Type" autocomplete="off">
              </div>
            </div>
            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Status</label>
              <div class="col-md-7">
                <select name="status" class="form-control">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
            
          </div>
          
          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" id="btn_submit_member">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- END ADD MEMBER MODAL -->

  <!-- IMPORT MODAL -->
  <div class="modal fade" id="importModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Import Files

          </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <form action="" method="post" enctype="multipart/form-data" id="import_member">
          
          <div class="modal-body">
            <div class="container">
    
              <div class="card-body">
                <h5>Select files from your computer</h5>
              
                <div class="form-inline">
                  <div class="form-group">
                    <input type="file" name="excel_member" id="" required>
                  </div>
        
                </div>
                <br>
                  
      
                <!-- Progress Bar -->
                <div class="progress d-none">
                  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                    <span>60% Complete</span>
                  </div>
                </div>
  
                </div>
              
            </div>
          </div>
        
          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            <button type="" class="btn btn-success">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Record Modal -->
  <div class="modal fade" id="editRecordModal">
    <div class="modal-dialog">
      <div class="modal-content">
  
        <div class="modal-header">
          <h4 class="modal-title">Edit Member</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <form id="form_edit_member">

          <input type="hidden" name="id">
       
        
          <div class="modal-body">
          
            @csrf

            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Stockholder</label>
              <div class="col-md-7">
                <input type="text" id="" class="form-control" name="stockholder" placeholder="Stockholder" autocomplete="off" required>
              </div>
            </div>
            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Email</label>
              <div class="col-md-7">
                <input type="email" id="" class="form-control" name="email" placeholder="Email" autocomplete="off" required>
              </div>
            </div>
            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Account Number</label>
              <div class="col-md-7">
                <input type="text" id="" class="form-control" name="account_number" placeholder="Account Number" autocomplete="off" required maxlength="4">
              </div>
            </div>

            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Proxy Form No</label>
              <div class="col-md-7">
                <input type="text" id="" class="form-control" name="proxy_form_no" placeholder="Proxy Form Number" autocomplete="off" required maxlength="4">
              </div>
            </div>

            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Suffix</label>
              <div class="col-md-7">
                <input type="number" id="" class="form-control" name="suffix" placeholder="Suffix" autocomplete="off" max="6" required="" readonly="">
              </div>
            </div>
          
          
            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Member Type</label>
              <div class="col-md-7">
                <input type="text" id="" class="form-control" name="member_type" placeholder="Type" autocomplete="off">
              </div>
            </div>
            <div class="form-group row">
              <label for="" class="col-md-4 col-form-label">Status</label>
              <div class="col-md-7">
                <select name="status" class="form-control" required>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
          </div>

  
          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" id="btn_submit_member">Submit</button>
          </div>
        </form>
        
      </div>
    </div>
  </div>


  <script>

    $(document).ready(function(){

      load_member(10);

    })

    $(document).on('change', '#no_per_page', function(){

      let noOfRow = $(this).children("option:selected").val();

      load_member(noOfRow);
    
    })

    $(document).on('keyup', '#search_member', function(){

      
      let noOfRow = $('#no_per_page').children("option:selected").val();

      let q = ($(this).val() != "") ? $(this).val() : "null";

      load_member(noOfRow, 1, q);

    })

    $(document).on('click', '.page-item', function(e){

      e.preventDefault();

      let pageNo = $(this).text();


      alert(pageNo);
      let noOfRow = $('#no_per_page').children("option:selected").val();

      load_member(noOfRow, pageNo);
      
    })

    function load_member(noOfRow = null, lastNo = "null", query = "null"){
   
      $.ajax({

        url: "{{asset('datatable')}}/" + lastNo + "/" + noOfRow + "/" + query,
        
        statusCode: {
          200: function(data) {

            if(data.success !== undefined) {

              if(data.success === true) {

                if(data["data"].length > 0) {

                  let table = "";
                  let member = data["data"];
                  let status;
                  let type;

                  let roman = [];

                  roman[1] = "I";
                  roman[2] = "II";
                  roman[3] = "III";
                  roman[4] = "IV";
                  roman[5] = "V";
                  roman[6] = "VI";


                  for(i = 0; i < member.length; i++) {

                    status = (member[i].status === 1) ? '<span class="badge badge-success badge-rounded">Active</span>' : '<span class="badge badge-danger badge-rounded">Inactive</span>';
                    
                    type = (member[i].type === null) ? "" : member[i].type;

                    table += '<tr data-id="' + member[i].userDetailsId + '">';
                    table += '<td class="stockholder">'+member[i].stockholder+'</td>';
                    table += '<td class="email">'+member[i].email+'</td>';               
                    table += '<td class="account-no" data-account-no="' + member[i].accountNo + '" data-suffix="' + member[i].suffix + '">' + member[i].accountNo + ' - ' + roman[member[i].suffix] + '</td>';
                    table += '<td class="proxy-form-no">' + member[i].proxyFormNo + '</td>';
                    table += '<td class="stock">' + member[i].totalShare + '</td>';
                    table += '<td class="">' + (member[i].totalShare * 3) + '</td>';
                    table += '<td class="spa"></td>';
                    table += '<td class="proxy"></td>';
                    table += '<td class="type">' + type + '</td>';
                    table += '<td class="status">' + status + '</td>';
                    table += '<td><button class="btn btn-sm btn-warning btn-edit-member"><i class="far fa-edit text-white"></i></button></td></tr>';                
                                        
                  }

                  
                  $('#member_body').html(table);
                  $('#total_record').text(data.total_record);
                  $('#btn_pages').html(data.pages);


                }
                else {
                  $('#member_body').html('<tr><td colspan="11" class="text-center">No Data</td></tr>');
                }

              }
            }


          }
        }

      })
    
    }
   
    $(document).on('click', '#btn_add_member', function(){

      $('#form_add_member')[0].reset();

      $('#addMemberModal').modal('show');
    
    })


    $(document).on('click', '.btn-edit-member', function(){

      $('#form_edit_member')[0].reset();

      let status, stockholder, email, accountNo, suffix, proxyFormNo, stock, type, modal, thisE, id;
     
      modal = $('#form_edit_member');

      thisE = $(this).closest('tr');

      id = thisE.attr('data-id');

      modal.find('[name=id]').val(id);
      

      stockholder = thisE.find('.stockholder').text();
      email       = thisE.find('.email').text();
      accountNo   = thisE.find('.account-no').attr('data-account-no');
      suffix      = thisE.find('.account-no').attr('data-suffix');
      proxyFormNo = thisE.find('.proxy-form-no').text();
      type        = thisE.find('.type').text();
      status      = thisE.find('.status').text();

      modal.find('[name=stockholder]').val(stockholder);
      modal.find('[name=email]').val(email);
      modal.find('[name=account_number]').val(accountNo);
      modal.find('[name=suffix]').val(suffix);
      modal.find('[name=proxy_form_no]').val(proxyFormNo);
      modal.find('[name=member_type]').val(type);

     
      setStatus = (status == 'Inactive') ? 0 : 1;

      modal.find('[name=status]').val(setStatus).change();  

      $('#editRecordModal').modal('show');


    })


    $(document).on("submit", "#form_add_member", function(e){

      e.preventDefault();

      $.ajax({

        url: '{{asset("admin/member")}}',
        method: 'POST',
        dataType: 'json',
        data: $("#form_add_member").serialize(),

        beforeSend: function(){

          $('#addMemberModal').find('#btn_submit_member').attr('disabled', true);
          
        },

        statusCode: {

          200: function(data) {

            if(data.success !== undefined) {

              if(data.success === true) {

                Swal.fire({
                  icon: 'success',
                  title: 'Success',
                  text: 'Member has been successfully registed!',
                
                }).then(() => {
                  location.reload();
                })
              }

              else {
                Swal.fire({
                  icon: 'info',
                  title: 'Info',
                  text: data.message,
                 
                })
              }
            }

            else {

              alert("Validation error, please try again. If this error persists, please contact the site administrator.");

            }

          },

          500: function() {

            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'INTERNAL SERVER ERROR',
             
            })
          },

          419: function(xhr) {

            alert("Session expired. Please reload the page");

          },

        },

      }).done(function(){
        $('#addMemberModal').find('#btn_submit_member').attr('disabled', false);
      })

    })

    $(document).on('submit', '#form_edit_member', function(e){
      
      e.preventDefault();

      $.ajax({
        
        url: "{{asset('admin/member/edit')}}",
        method: 'POST',
        dataType: 'json',
        data: $(this).serialize(),

        success: function(data){
          if(data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: data.message,
              }).then(() => {
              location.reload();
            })
          }

          else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message,
           
              }).then(() => {
              l
            })
          }
        }
        
      })
    })

    $(document).on('submit', '#import_member', function(e){
			
      e.preventDefault();
  
      var formData = new FormData();
          
      formData.append('excel_member', $('[name=excel_member]')[0].files[0]);;
      
      formData.append('_token', "{{ csrf_token() }}");
  
      $.ajax({
    
        url: "{{asset('member/import')}}",
        method: "POST",
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        data: formData,
  
        // this part is progress bar
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function (evt) {
              if (evt.lengthComputable) {
                var percentComplete = evt.loaded / evt.total;
                percentComplete = parseInt(percentComplete * 100);
                $('#myprogress').text(percentComplete + '%');
                $('#myprogress').css('width', percentComplete + '%');
              }
            }, false);
            return xhr;
          },
      
        beforeSend: function() {
          $('#btn_upload').text("Uploading . . . ").attr('disabled', true);
        },
  
      
        
        statusCode: {
  
          200: function(data){
  
            if(data.success !== undefined) {
  
              if(data.success) {
  
                Swal.fire({
                  icon: 'success',
                  title: 'Uploaded!',
                  text: data.message,
                  //footer: '<a href>Why do I have this issue?</a>'
                }).then(() => {
                    location.reload();
                  })
  
              }
  
              else if (data.success === false){
                
  
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: data.message,
                  //footer: '<a href>Why do I have this issue?</a>'
                }).then(() => {
                    $('#btn_upload').text("Upload").attr('disabled', false);
                })
  
  
              }
  
            }
  
            else {
              alert("Unknown error encountered");
            }
          },
  
          500: function(data){
            alert("Internal Server Error");
          },
  
          419: function(xhr) {
  
            alert("Sesssion expired. Please reload the page");
  
          },
          
          
        },
  
      })
  
  
  
    })

  
    $(document).ready(function(){
      $('.admin-nav-members').addClass('active');

    })
  </script>

                     
@endsection