@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/admin_proxydocuments.css')}}?v={{ time() }}">
<script src="{{asset('js/admin/documents.js')}}?v={{ time() }}"></script>

@endsection

@section('content')

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <div class="dflex-button">
            <button class="btn btn-sm border-secondary dflex-button" id="show_filter">Show Filter</button>
          </div>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right dflex-button">
            <li class="breadcrumb-item"><a href="{{asset('admin')}}" style="color:#6b8e4e;">Dashboard</a></li>
            <li class="breadcrumb-item actives">Proxy Documents</li>
          </ol>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="card-body pl-2 pr-2" style="border: 1px solid #dec8c8; border-radius: 5px; background-color: #f5f5f5; display: none" id="filter_box">
            <form id="filter_form">
              <div class="row form-group">
                <div class="col-md-2 div-text">Search account no.</div>
                <div class="col-md-2 col-md2-size">
                  <input type="search" value="" placeholder="Search account no here..." name="search" class="form-control form-control-sm" id="search">
                </div>
              </div>
              <div class="row form-group">
                <div class="col-md-2 div-text">Status</div>
                <div class="col-md-2 col-md2-size">
                  <select class="form-control form-control-sm" name="status">
                    <option value="">All</option>
                    <option value="0">Active</option>
                    <option value="1">Delinquent</option>
                  </select>
                </div>
                <div class="col-md-2 offset-md-4 div-text">Uploads</div>
                <div class="col-md-2 col-md2-size">
                  <select class="form-control form-control-sm" name="upload">
                    <option value="">All</option>
                    <option value="1">Verified</option>
                    <option value="0">Unverified</option>
                  </select>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-md-2 div-text">Account Type</div>
                <div class="col-md-2 col-md2-size">
                  <select class="form-control form-control-sm" name="account_type">
                    <option value="">All</option>
                    <option value="corp">Corporate</option>
                    <option value="indv">Individual</option>
                  </select>
                </div>
                <div class="col-md-2 offset-md-4 div-text">Allow Download</div>
                <div class="col-md-2 col-md2-size">
                  <select class="form-control form-control-sm" name="allow_download">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                  </select>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-md-2 div-text">Role</div>
                <div class="col-md-2 col-md2-size">
                  <select class="form-control form-control-sm" name="role">
                    <option value="">All</option>
                    <option value="stockholder">Stockholder</option>
                    <option value="corp-rep">Corporate Representative</option>
                  </select>
                </div>
                <div class="col-md-2 offset-md-4 div-text">Per Page</div>
                <div class="col-md-2 col-md2-size">
                  <select name="per_page" id="">


                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                  </select>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-md-2 div-text">Assignee</div>
                <div class="col-md-2 col-md2-size">
                  <select class="form-control form-control-sm" name="assignee">
                    <option value="">All</option>
                    <option value="1">With</option>
                    <option value="0">Without</option>
                  </select>
                </div>
                <div class="col-md-2 offset-md-4 div-text">Active Page</div>
                <div class="col-md-2 col-md2-size">
                  <select name="active_page" class="active-page" id="">
                    <option value="1">1</option>
                  </select>
                </div>
              </div>

              <button class="btn btn-sm border-secondary" id="hide_filter">Hide Filter</button>

              <button class="btn btn-sm border-secondary" id="btn_filter_reset">Reset</button>

            </form>
          </div>
        </div>
      </div>

      <div class="row m-1">
        <div class="col-md-12">
          <div class="card rounded-0">
            <div class="card-header rounded-0">

              <div class="row">
                <div class="col-md-4">
                  <h1 class="card-title text-white">Proxy Documents</h1>
                </div>
                <div class="col-md-8">

                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="table-responsive" style="height: 700px; overflow: auto;">
                <table id="memberTable" class="table table-bordered">
                  <thead class="text-nowrap" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding">#</th>
                      <th class="th-padding">Stockholder</th>
                      <th class="th-padding">Account No</th>
                      <th class="th-padding">Proxy Form</th>
                      <th class="th-padding">Download Count</th>
                      <th class="th-padding">Allow Download</th>
                      <th class="th-padding">Uploads</th>
                      <th class="th-padding">Status</th>
                      <th class="th-padding">Action</th>

                    </tr>
                  </thead>
                  <tbody>


                  </tbody>

                </table>
              </div>
              <br>
              <span class="mt-5" id="record_summary"></span>
              <nav aria-label="" class="float-right">
                <ul class="pagination">
                  <li class="page-item btn-prev"><a class="page-link" href="#">Previous</a></li>
                  <li class="page-item"><a class="page-link" href="#" id="active_page"></a></li>
                  <li class="page-item btn-next"><a class="page-link" href="#">Next</a></li>
                </ul>
              </nav>
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



<!-- Modal -->
<div class="modal fade" id="upload_proxy_modal" tabindex="-1" role="dialog" aria-labelledby="upload_proxy_modal_label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="upload_proxy_modal_label">Upload Proxy</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <form method="POST" action="" enctype="multipart/form-data" id="upload_form">
          <input type="hidden" value="" name="account_key">
          <label class="col-form-label font-weight-bold">Please upload your file in PDF or image format.</label><br><br>
          <input type="file" id="proxy_doc" name="proxy_doc" accept="application/pdf,image/jpeg,image/png" required="">

          <br><br>
          <div class="form-group">
            <div class="progress">
              <div class="progress-bar progress-bar-success" id="myprogress" role="progressbar" style="width:0%">0%</div>
            </div>
            <div class="msg"></div>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-next btn-custom-darkg" id="btn_upload"><i class="fa fa-paper-plane"></i> Upload</button>
          </div>

        </form>


      </div>

    </div>
  </div>
</div>




<!--View Documents Modal-->
<div class="modal fade" id="viewDocumentModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Attachment</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-hovered table-sm" id="modal_table_attachment">
          <tbody>
          </tbody>
        </table>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success">Submit</button>
      </div>
    </div>
  </div>
</div>



<!-- EDIT DOCUMENT MODAL -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="exampleModalLongTitle">EDIT PROXY DOCUMENT</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="" method="POST" id="edit_document_form">
        @csrf
        <input type="hidden" name="id">
        <div class="modal-body">

          <label>Document Status</label>
          <div class="row form-group">
            <select name="proxy_status" id="" class="form-control rounded-0">
              <option value="0">No File Uploaded</option>
              <option value="1">For Verification</option>
              <option value="2">Verified</option>
              <option value="3">Invalid Document</option>
            </select>
          </div>

          <label>Form Download </label>
          <div class="row form-group">
            <select name="proxy_download" id="" class="form-control rounded-0">
              <option value="1">Allowed</option>
              <option value="0">Not Allowed</option>
            </select>
          </div>


          <!-- <label>Remarks: </label>
              <div class="row form-group">
                <textarea class="form-control rounded-0" name="proxy_remarks"></textarea>
              </div> -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="summit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="proxy_download_history" tabindex="-1" role="dialog" aria-labelledby="proxy_download_label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="proxy_download_label">Proxy Downloads History</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>Name</th>
              <th>Account Type</th>
              <th>Email</th>
              <th>Proxy Form No.</th>
              <th>Date Downloaded</th>
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



<!-- Modal -->
<div class="modal fade" id="proxy_uploads_modal" tabindex="-1" role="dialog" aria-labelledby="proxy_uploads_modal__label" aria-hidden="true">
  <div class="modal-dialog modal-set-size" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="proxy_uploads_modal_label">PROXY UPLOADS</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="text-nowrap">
              <tr>
                <th class="th-padding">File</th>
                <th class="th-padding">Name</th>
                <th class="th-padding">Email</th>
                <th class="th-padding">Account Type</th>
                <th class="th-padding">Date Uploaded</th>
                <th class="th-padding">Date Verified</th>
                <th class="th-padding">Verified By</th>
                <th class="th-padding">Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-button btn-danger" data-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>

<script>
  $(document).on('click', '.btn-edit-document', function() {

    let id = $(this).closest('tr').attr('data-id');
    let tr = $(this).closest('tr');
    let modal = $('#edit_document_form');

    let proxyStatus = tr.find('.proxy-status').text();
    let allowDownload = tr.find('.allow-download').text();
    let setProxyStatus;


    $('#edit_document_form').find('[name=id]').val(id);

    if (proxyStatus == 'No Uploaded File') {

      setProxyStatus = 0;

    } else if (proxyStatus == 'For Verification') {

      setProxyStatus = 1;

    } else if (proxyStatus == 'Verified') {

      setProxyStatus = 2;

    } else if (proxyStatus == 'Invalid Document') {

      setProxyStatus = 3;

    }

    $('[name=proxy_download]').val((allowDownload == 'Allowed') ? 1 : 0).change();

    $('[name=proxy_status]').val(setProxyStatus).change();

    $('#editDocumentModal').modal('show');

  })


  $(document).on('submit', '#edit_document_form', function(e) {

    e.preventDefault();

    let id = 1;

    $.ajax({

      url: "{{asset('admin/document/edit')}}",
      method: "POST",
      dataType: "json",
      data: $(this).serialize(),

      success: function(data) {

        if (data.success !== undefined) {

          if (data.success === true) {

            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: data.message,

            }).then(() => {
              location.reload();
            })



          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message,

            })
          }

        } else {

          alert("Unknown error encountered.");

        }
      }

    })

  })



  $(document).on('click', '.btn-view-doc', function() {

    let id = $(this).attr('data-id');

    $.ajax({

      url: "{{asset('admin/document/list')}}/" + id,

      dataType: 'json',


      success: function(data) {

        let attachment = '';

        if (data.length > 0) {

          for (i = 0; i < data.length; i++) {

            attachment += '<tr></tr><td><a href="' + "{{asset('admin/file/')}}/" + data[i].id + '" target="_blank" class="text-success"> ' + data[i].orig_name + '</a></td>';
            attachment += '<td>' + data[i].date_uploaded + '</td>';
            attachment += '<td class="text-center"><button class="btn btn-primary btn-sm rounded-0"><i class="fas fa-download"></i></button></td></tr>';

          }

          $('#modal_table_attachment tbody').html(attachment);

          $('#viewDocumentModal').modal('show');

        } else {
          alert("No attachment found.");
        }


      }
    })



  })

  $(document).ready(function() {
    $('.admin-nav-documents').addClass('active');


  })
</script>
</script>

@endsection