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
            <h1 class="corporate-page-title ultra-compact">Agenda Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Agendas</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact" id="btn_add_agenda" data-toggle="modal" data-target="#addAgendaModal">
              <i class="fas fa-plus"></i>
              Add New Agenda
            </button>
          </div>
        </div>
      </div>
      <!-- Modern Table Container -->
      <div class="corporate-table-container ultra-compact">
        <div class="table-responsive" style="height: 650px; overflow: auto;">
          <table id="agendas_table" class="table corporate-table ultra-compact"">
                  <thead class=" text-nowrap thead-background" style="position: sticky;top: 0; z-index: 1;">
            <tr>
              <th class="th-padding">#</th>
              <th class="th-padding">Code</th>
              <th class="th-padding">Agenda</th>
              <th class="th-padding">Document Link</th>
              <th class="th-padding">Status</th>
              <th class="th-padding">Action</th>
            </tr>
            </thead>
            <tbody>
              <?php

              $counter = 1;

              foreach ($agendas as $agenda) {

                $status = $agenda->isActive ? '<span class="badge badge-mid-green badge-rounded">Active</span>' : '<span class="badge badge-dark-green badge-rounded">Inactive</span>';

                echo '
                                <tr data-id="' . $agenda->agendaId . '">
                                  <td class="td-padding">' . $counter . '</td>
                                  <td class="td-agenda-code td-padding">' . e($agenda->agendaCode) . '</td>
                                  <td class="td-agenda-desc td-padding">' . e($agenda->agendaDesc) . '</td>
                                  <td class="td-agenda-link td-padding">' . $agenda->link . '</td>
                                  <td class="status td-padding">' . $status . '</td>
                                  <td>
                                    <button type="button" class="btn btn-mid-green btn-sm-edit btn-edit-agenda" onclick="editAgenda(this)"><i class="fas fa-edit text-white" data-toggle="tooltip" data-placement="bottom" title="Edit Record!"></i></button>
                                  </td>
                                </tr>';

                $counter++;
              }

              if (count($agendas) === 0) {
                echo ' <tr>
                <td colspan="7" class="corporate-empty-state">
                  <i class="fas fa-users"></i>
                  <h4>No agendas Found</h4>
                  <p>Start by adding your first agenda using the button above.</p>
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


<!-- ADD AGENDA MODAL -->
<div class="modal fade" id="addAgendaModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Agenda</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="add_agenda_form">
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
            <label for="" class="col-md-4 col-form-label">Agenda <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="agenda" placeholder="Agenda" required autocomplete="off" required maxlength="500">
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
<!-- END ADD AGENDA MODAL -->



<!-- EDIT AGENDA MODAL -->
<div class="modal fade" id="editAgendaModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Edit Agenda</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="edit_agenda_form">
        <input type="hidden" name="id" value="">
        @csrf

        <div class="modal-body">
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Agenda <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="agenda" placeholder="Agenda" required maxlength="500" autocomplete="off">
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
<!-- END EDIT AGENDA MODAL -->

<script>
  $(document).ready(function() {

    $('.admin-nav-agendas').addClass('active');

    $('#addAgendaModal').on('shown.bs.modal', function() {
      $('#add_agenda_form [name=code]').focus();
    })

    $('#editAgendaModal').on('shown.bs.modal', function() {
      $('#edit_agenda_form [name=agenda]').focus();
    })


    $('#editAgendaModal').on('hidden.bs.modal', function() {
      $('#edit_agenda_form')[0].reset();
    })

    $('#addAgendaModal').on('hidden.bs.modal', function() {
      $('#add_agenda_form')[0].reset();
    })

  })


  $(document).on('submit', '#add_agenda_form', function(e) {
    e.preventDefault();
    addAgenda($(this));
  })


  $(document).on('submit', '#edit_agenda_form', function(e) {
    e.preventDefault();
    updateAgenda($(this));
  })


  function editAgenda(thisElem) {

    const tr = $(thisElem).closest('tr');
    const id = tr.attr('data-id');
    const modal = $('#editAgendaModal');
    const agendaDesc = tr.find('.td-agenda-desc').text();
    const agendaLink = tr.find('.td-agenda-link').text();
    const statusText = tr.find('.status').text();
    const statusValue = (statusText === 'Active') ? 1 : 0;

    modal.find('[name=id]').val(id);
    modal.find('[name=agenda]').val(agendaDesc);
    modal.find('[name=agenda_link]').val(agendaLink);
    modal.find('[name=status] option[value=' + statusValue + ']').prop('selected', true).siblings().prop('selected', false).change();

    modal.modal('show');
  }



  function addAgenda(thisForm) {

    const modal = $('#addAgendaModal');
    const submitLoading = modal.find('.submit-loading');
    const submitButton = $('#btn_submit_add_form');

    $.ajax({
      url: BASE_URL + 'admin/agenda',
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

  function updateAgenda(thisForm) {

    const modal = $('#editAgendaModal');
    const submitLoading = modal.find('.submit-loading');
    const submitButton = $('#btn_submit_edit_form');

    $.ajax({
      url: BASE_URL + 'admin/agenda/' + modal.find('[name=id]').val(),
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