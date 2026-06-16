@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
@endsection

@section('content')

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <!-- Modern Page Header -->
      <div class="corporate-page-header ultra-compact">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div>
            <h1 class="corporate-page-title ultra-compact">Amendment Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Amendments</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact" id="btn_add_amendment" data-toggle="modal" data-target="#addAmendmentModal">
              <i class="fas fa-plus"></i>
              Add Amendment
            </button>
          </div>
        </div>
      </div>
      <!-- Modern Table Container -->
      <div class="corporate-table-container ultra-compact">
        <div class="table-responsive" style="height: 650px; overflow: auto;">
          <table id="amendments_table" class="table corporate-table ultra-compact"">
                  <thead class=" text-nowrap thead-background" style="position: sticky;top: 0; z-index: 1;">
            <tr>
              <th class="th-padding">#</th>
              <th class="th-padding">Code</th>
              <th class="th-padding">Amendment</th>
              <th class="th-padding">Document Link</th>
              <th class="th-padding">Status</th>
              <th class="th-padding">Action</th>
            </tr>
            </thead>
            <tbody>
              <?php

              $counter = 1;

              foreach ($amendments as $amendment) {

                $status = $amendment->isActive ? '<span class="badge badge-mid-green badge-rounded">Active</span>' : '<span class="badge badge-dark-green badge-rounded">Inactive</span>';

                echo '
                                <tr data-id="' . $amendment->amendmentId . '">
                                  <td class="td-padding">' . $counter . '</td>
                                  <td class="td-amendment-code td-padding">' . e($amendment->amendmentCode) . '</td>
                                  <td class="td-amendment-desc td-padding">' . e($amendment->amendmentDesc) . '</td>
                                  <td class="td-amendment-link td-padding">' . $amendment->link . '</td>
                                  <td class="status td-padding">' . $status . '</td>
                                  <td>
                                    <button type="button" class="btn btn-mid-green btn-sm-edit btn-edit-amendment" onclick="editAmendment(this)"><i class="fas fa-edit text-white" data-toggle="tooltip" data-placement="bottom" title="Edit Record!"></i></button>
                                  </td>
                                </tr>';

                $counter++;
              }

              if (count($amendments) === 0) {
                echo ' <tr>
                <td colspan="7" class="corporate-empty-state">
                  <i class="fas fa-users"></i>
                  <h4>No amendments Found</h4>
                  <p>Start by adding your first amendment using the button above.</p>
                </td>
              </tr>';
              }

              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></a>
</div>


<!-- ADD AMENDMENT MODAL -->
<div class="modal fade" id="addAmendmentModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Amendment</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="add_amendment_form">
        @csrf
        <!-- Modal body -->
        <div class="modal-body">
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Code <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="code" placeholder="Code" required autocomplete="off" required maxlength="10">
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Amendment <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="amendment" placeholder="Amendment" required autocomplete="off" required maxlength="5000">
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Status</label>
            <div class="col-md-7">
              <select class="form-control" name="status" required>
                <option value="1">Active</option>
                <option value="0">Deleted</option>
              </select>
            </div>
          </div>
        </div>
        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-custom-darkg" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-custom-darkg btn-submit-form" id="btn_submit_add_form">Submit</button>
          <span class="spinner-border submit-loading" role="status" style="display: none"></span>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END ADD AMENDMENT MODAL -->



<!-- EDIT AMENDMENT MODAL -->
<div class="modal fade" id="editAmendmentModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Edit Amendment</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="edit_amendment_form">
        <input type="hidden" name="id" value="">
        @csrf

        <div class="modal-body">
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Amendment <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="amendment" placeholder="Amendment" required maxlength="5000" autocomplete="off">
            </div>
          </div>

          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Status</label>
            <div class="col-md-7">
              <select class="form-control" name="status" required>
                <option value="1">Active</option>
                <option value="0">Deleted</option>
              </select>
            </div>
          </div>
        </div>
        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-custom-darkg" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-custom-darkg" id="btn_submit_edit_form">Submit</button>
          <span class="spinner-border submit-loading" role="status" style="display: none"></span>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END EDIT AMENDMENT MODAL -->

<script>
  $(document).ready(function() {

    $('.admin-nav-amendments').addClass('active');

    $('#addAmendmentModal').on('shown.bs.modal', function() {
      $('#add_amendment_form [name=code]').focus();
    })

    $('#editAmendmentModal').on('shown.bs.modal', function() {
      $('#edit_amendment_form [name=amendment]').focus();
    })


    $('#editAmendmentModal').on('hidden.bs.modal', function() {
      $('#edit_amendment_form')[0].reset();
    })

    $('#addAmendmentModal').on('hidden.bs.modal', function() {
      $('#add_amendment_form')[0].reset();
    })

  })


  $(document).on('submit', '#add_amendment_form', function(e) {
    e.preventDefault();
    addAmendment($(this));
  })


  $(document).on('submit', '#edit_amendment_form', function(e) {
    e.preventDefault();
    updateAmendment($(this));
  })


  function editAmendment(thisElem) {

    const tr = $(thisElem).closest('tr');
    const id = tr.attr('data-id');
    const modal = $('#editAmendmentModal');
    const amendmentDesc = tr.find('.td-amendment-desc').text();


    const amendmentLink = tr.find('.td-amendment-link').text();
    const statusText = tr.find('.status').text();
    const statusValue = (statusText === 'Active') ? 1 : 0;

    modal.find('[name=id]').val(id);
    modal.find('[name=amendment]').val(amendmentDesc);
    modal.find('[name=amendment_link]').val(amendmentLink);
    modal.find('[name=status] option[value=' + statusValue + ']').prop('selected', true).siblings().prop('selected', false).change();

    modal.modal('show');
  }



  function addAmendment(thisForm) {

    const modal = $('#addAmendmentModal');
    const submitLoading = modal.find('.submit-loading');
    const submitButton = $('#btn_submit_add_form');

    $.ajax({
      url: BASE_URL + 'admin/amendment',
      method: 'POST',
      dataType: 'json',
      data: thisForm.serialize(),
      beforeSend: function() {
        submitLoading.show();
        submitButton.attr('disabled', true);
      },
      complete: function() {
        submitLoading.hide();
        submitButton.attr('disabled', false);
      },
      success: function(xhr) {
        handleSuccess(xhr);
      },
      error: function(xhr) {
        handleError(xhr);
      }
    });
  }

  function updateAmendment(thisForm) {

    const modal = $('#editAmendmentModal');
    const submitLoading = modal.find('.submit-loading');
    const submitButton = $('#btn_submit_edit_form');
    const id = modal.find('[name=id]').val();

    $.ajax({
      url: BASE_URL + 'admin/amendment/' + id,
      method: 'PUT',
      dataType: 'json',
      data: thisForm.serialize(),
      beforeSend: function() {
        submitButton.attr('disabled', true);
        submitLoading.show();
      },
      complete: function() {
        submitButton.attr('disabled', false);
        submitLoading.hide();
      },
      success: function(xhr) {
        handleSuccess(xhr);
      },
      error: function(xhr) {
        handleError(xhr);
      }
    });
  }
</script>

@endsection