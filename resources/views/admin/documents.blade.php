@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/admin_bulletin.css')}}?v={{ time() }}">
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
            <li class="breadcrumb-item actives">Documents</li>
          </ol>
        </div>
      </div>
      <div class="row mb-2">

        <div class="card w-100">
          <div class="card-header rounded-0">
            <h1 class="card-title">Documents</h1>
            <div class="float-sm-right">
              <button type="button" class="btn btn-custom-darkg btn-button rounded-0 btn-add-button border border-light" data-toggle="modal" data-target="#modal_upload"><i class="fas fa-plus"></i> Upload</button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered" id="table_document">
                <thead class="text-nowrap">
                  <tr>
                    <th class="th-padding">Title</th>
                    <th class="th-padding">Uploaded By</th>
                    <th class="th-padding">Date Uploaded</th>
                    <th class="th-padding">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data as $document) {
                    $status = $document['deletedAt'] !== null ? '<span class="badge badge-danger">Inactive</span>' : '<span class="badge badge-success">Active</span>';
                    echo '<tr>
                        <td>' . $document['title'] . '</td>
                        <td>' . $document['created_by']['admin_account']['firstName'] . ' ' . $document['created_by']['admin_account']['lastName'] . '</td>
                        <td>' . $document['createdAt'] . '</td>
                        <td>' . $status . '</td>
                        </tr>';
                  }

                  if (count($data) === 0) {
                    echo '<tr><td colspan="4" class="text-center">No document found</td><tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<!-- 
  // admin/document/view -->

<!-- Modal -->
<div class="modal fade" id="modal_upload" tabindex="-1" role="dialog" aria-labelledby="modal_uploadLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_uploadLabel">Upload Document</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <label for="">Title</label>
        <input type="text" class="form-control form-control-sm mb-2" id="document_title" require>
        <form id="file_upload">
          <input type="file" required>
          <div class="form-group m-0">
            <div class="progress" style="display: none;">
              <div class="progress-bar progress-bar-success my-progress" role="progressbar" style="width:0%">0%</div>
            </div>
            <div class="msg"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-custom-darkg" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-custom-darkg" id="btn_submit_file">Submit</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).on('click', '#btn_submit_file', function(e) {

    e.preventDefault();

    let fileToUpload = $('#modal_upload [type=file]')[0].files[0];
    let formData = new FormData();
    let form = $('#file_upload');
    let title = $('#document_title').val();

    formData.append('file', fileToUpload);
    formData.append('title', title);
    formData.append('_token', "{{ csrf_token() }}");

    $.ajax({
      url: "{{asset('admin/document')}}",
      method: "POST",
      dataType: "json",
      contentType: false,
      cache: false,
      processData: false,
      data: formData,
      xhr: function() {
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener("progress", function(evt) {
          if (evt.lengthComputable) {
            var percentComplete = evt.loaded / evt.total;
            percentComplete = parseInt(percentComplete * 100);
            form.find('.my-progress').text(percentComplete + '%').css('width', percentComplete + '%');;
          }
        }, false);
        return xhr;
      },
      beforeSend: function() {
        form.find('.progress').show();
        form.find('.btn-upload-file').attr('disabled', true).val('Uploading . . .');
        $('#btn_upload').text("Uploading . . . ").attr('disabled', true);
      },
      complete: function() {
        $('#btn_upload').text("Upload ").attr('disabled', false);
        form.find('.my-progress').text(0 + '%').css('width', 0 + '%');
        form.find('.progress').hide();
        form.find('.btn-upload-file').attr('disabled', false).val('Upload');
      },
      success: function(data) {
        handleSuccess(data);
      },
      error: function(xhr) {
        handleError(xhr);
      }
    })
  })

  $(document).ready(function() {
    $('.admin-nav-bulletin').addClass('active');
  })
</script>

@endsection