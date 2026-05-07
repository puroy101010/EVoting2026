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
            <h1 class="corporate-page-title ultra-compact">Non-Members Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Non-Members</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact" id="btn_add_non_member" data-toggle="modal" data-target="#addNonMemberModal">
              <i class="fas fa-plus"></i>
              Add New Non-Member
            </button>
          </div>
        </div>
      </div>


      <div class="corporate-table-container ultra-compact">
        <div class="table-responsive">
          <table id="NonmemberTable" class="table corporate-table ultra-compact">
            <thead class="text-nowrap">
              <tr>
                <th width="5%" class="th-padding">#</th>
                <th width="25%" class="th-padding">First Name</th>
                <th width="25%" class="th-padding">Middle Name</th>
                <th width="25%" class="th-padding">Last Name</th>
                <th width="25%" class="th-padding">Email</th>
                <th width="10%" class="th-padding">Account No</th>
                <th width="10%" class="text-center th-padding">Status</th>
                <th width="10%" class="text-center th-padding">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($data as $index => $nonMember)
              <tr data-id="{{ $nonMember->userId }}">
                <td class="td-padding">{{ $index + 1 }}</td>
                <td class="first-name td-padding" data-first-name="{{ $nonMember->firstName }}">
                  @if($nonMember->isGM === 1)
                  <span class="badge badge-success">GM</span>
                  @endif
                  {{ $nonMember->firstName }}
                </td>
                <td class="middle-name td-padding">{{ $nonMember->middleName }}</td>
                <td class="last-name td-padding">{{ $nonMember->lastName }}</td>
                <td class="email td-padding">{{ $nonMember->user->email }}</td>
                <td class="account-no td-padding">{{ $nonMember->nonmemberAccountNo }}</td>
                <td class="text-center td-padding">
                  @if($nonMember->isActive)
                  <span class="badge badge-rounded badge-mid-green member-status">Active</span>
                  @else
                  <span class="badge badge-rounded badge-dark-green member-status">Inactive</span>
                  @endif
                </td>
                <td class="text-center td-padding">
                  <button class="btn btn-mid-green btn-sm-edit btn-edit-non-member">
                    <i class="fas fa-edit text-white" data-toggle="tooltip" data-placement="bottom" title="Edit Record!"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="corporate-empty-state">
                  <i class="fas fa-users"></i>
                  <h4>No non-members Found</h4>
                  <p>Start by adding your first non-member using the button above.</p>
                </td>
              </tr>
              @endforelse
            </tbody>

          </table>
        </div>
      </div>

    </div>
</div>
</div>
</section>
<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></a>
</div>


<!-- ADD NON MEMBER MODAL -->
<div class="modal fade" id="addNonMemberModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title">Add Non-Member</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="form_add_non_member">
        <!-- Modal body -->
        <div class="modal-body">
          <div class="form-group row">
            <label for="account_number" class="col-md-4 col-form-label">Account Number <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="account_number" class="form-control" name="account_number" placeholder="Account Number" autocomplete="off" required maxlength="4" minlength="4">
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">First Name <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="firstName" placeholder="First Name" autocomplete="off" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Middle Name</label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="middleName" placeholder="Middle Name" autocomplete="off">
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Last Name <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="lastName" placeholder="Last Name" autocomplete="off" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label">Email <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="email" id="email" class="form-control" name="email" placeholder="Email" autocomplete="off" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Is GM</label>
            <div class="col-md-7">
              <select name="isGM" class="form-control">
                <option value="1">Yes</option>
                <option value="0" selected>No</option>
              </select>
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
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" class="btn corporate-btn-primary ultra-compact btn-submit-form" id="btn_submit_non_member"><i class="fas fa-save mr-1"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END ADD MEMBER MODAL -->



<!-- Edit Non-Member Modal -->
<div class="modal fade" id="editNonMemberModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title">Edit Non-Member</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="form_edit_non_member">
        <input type="hidden" name="id">
        <div class="modal-body">
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Account Number</label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="account_number" placeholder="Account Number" autocomplete="off" required maxlength="4" disabled>
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">First Name <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="firstName" placeholder="First Name" autocomplete="off" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Middle Name</label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="middleName" placeholder="Middle Name" autocomplete="off">
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Last Name <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" id="" class="form-control" name="lastName" placeholder="Last Name" autocomplete="off" required>
            </div>
          </div>

          <div class="form-group row">
            <label for="" class="col-md-4 col-form-label">Email</label>
            <div class="col-md-7">
              <input type="email" id="" class="form-control" name="email" placeholder="Email" autocomplete="off" required>
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
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" class="btn corporate-btn-primary ultra-compact btn-submit-form" id="btn_submit_non_member"><i class="fas fa-save mr-1"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // done 2023-08-31
  $(document).ready(function() {
    $(document).on('shown.bs.modal', '#addNonMemberModal', function() {
      $(this).find('[name=account_number]').focus();
    })

    $(document).on('shown.bs.modal', '#editNonMemberModal', function() {
      $(this).find('[name=firstName]').focus();
    })

    $(document).on('hidden.bs.modal', '#addNonMemberModal', function() {
      $('#form_add_non_member')[0].reset();
    })
  })


  // done 2023-08-31
  $(document).on('click', '.btn-edit-non-member', function() {

    $('#form_edit_non_member')[0].reset();

    let form = $('#form_edit_non_member');
    let tr = $(this).closest('tr');
    let id = tr.attr('data-id');

    let status = tr.find('.member-status').text() == 'Active' ? 1 : 0;

    form.find('[name=id]').val(id);
    form.find('[name=firstName]').val(tr.find('.first-name').attr('data-first-name'));
    form.find('[name=middleName]').val(tr.find('.middle-name').text());
    form.find('[name=lastName]').val(tr.find('.last-name').text());
    form.find('[name=email]').val(tr.find('.email').text());
    form.find('[name=account_number]').val(tr.find('.account-no').text());

    form.find('[name=status] option[value=' + status + ']').attr('selected', true).siblings().attr('selected', false).change();

    $('#editNonMemberModal').modal('show');


  })

  // done 2023-08-31
  $(document).on("submit", "#form_add_non_member", function(e) {

    e.preventDefault();

    $.ajax({
      url: BASE_URL + 'admin/non-member',
      method: 'POST',
      dataType: 'json',
      data: $("#form_add_non_member").serialize(),
      beforeSend: function() {
        $('#addNonMemberModal').find('#btn_submit_non_member').text('Submitting . . . ').attr('disabled', true);
      },
      complete: function() {
        $('#addNonMemberModal').find('#btn_submit_non_member').text('Submit').attr('disabled', false);
      },
      success: function(data) {
        handleSuccess(data);
      },
      error: function(xhr) {
        handleError(xhr);
      }
    })
  })


  // done 2023-08-31
  $(document).on('submit', '#form_edit_non_member', function(e) {
    e.preventDefault();
    $.ajax({
      url: BASE_URL + 'admin/non-member/' + $(this).find('[name=id]').val(),
      method: 'PUT',
      dataType: 'json',
      data: $(this).serialize(),
      success: function(data) {
        handleSuccess(data);
      },
      error: function(xhr) {


        if (xhr.status === 422) {

          Swal.fire({
            title: 'Validation Error',
            text: xhr.responseJSON.message,
            icon: 'error',
            confirmButtonText: 'OK'
          });
          return;
        }

        handleError(xhr);
      }
    })
  })





  $(document).ready(function() {
    $('.admin-nav-non-member').addClass('active');
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>



@endsection