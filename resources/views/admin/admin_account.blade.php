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
            <h1 class="corporate-page-title ultra-compact">Admin Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Admin Accounts</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact @if( !Auth::check()) d-none  @endif" id="btn_add_admin" data-toggle="modal" data-target="#create_admin_modal">
              <i class="fas fa-plus"></i>
              Add New Admin
            </button>
          </div>
        </div>
      </div>
      <!-- Modern Table Container -->
      <div class="corporate-table-container ultra-compact">
        <div class="table-responsive">
          <table class="table corporate-table ultra-compact" id="adminAccountTable">
            <thead class="text-nowrap">
              <tr>
                <th scope="col" class="th-padding">Name</th>
                <th scope="col" class="th-padding">Email</th>
                <th scope="col" class="th-padding">Role</th>
                <th scope="col" class="th-padding text-center">Status</th>
                <th scope="col" class="th-padding text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($admins as $account)
              @php
              $btnEdit = Auth::user()->role === 'admin' ? 'disabled' : '';
              $isActive = $account->adminAccount && $account->adminAccount->isActive;

              Log::info('Admin account status check', ['account_id' => $account->id, 'isActive' => $isActive]);
              $status = $isActive === false
              ? '<span class="badge badge-rounded badge-dark-green admin-status">Inactive</span>'
              : '<span class="badge badge-rounded badge-mid-green admin-status">Active</span>';
              @endphp
              <tr data-first-name="{{ e($account->adminAccount->firstName ?? '') }}"
                data-last-name="{{ e($account->adminAccount->lastName ?? '') }}"
                data-middle-name="{{ e($account->adminAccount->middleName ?? '') }}"
                data-role="{{ e($account->getRoleNames()->first() ?? '') }}"
                data-status="{{ $isActive ? '1' : '0' }}">
                <td class="admin-name td-padding text-nowrap" scope="row">
                  {{ $account->adminAccount->fullname ?? '-' }}
                </td>
                <td class="email td-padding email">{{ $account->email }}</td>
                <td class="role td-padding text-center">{{ $account->roles->pluck('name')->join(', ') ?? 'N/A' }}</td>
                <td class="td-padding text-center">{!! $status !!}</td>
                <td class="text-center">
                  <div class="btn-group" role="group" aria-label="Actions">
                    <button data-id="{{ $account->id }}"
                      class="btn btn-sm-edit btn-mid-green btn-edit-admin-account {{ $btnEdit }}"
                      {{ $btnEdit ? 'disabled' : '' }}>
                      Edit
                    </button>
                    <button data-id="{{ $account->id }}" class="btn btn-sm btn-warning btn-reset-password {{ $btnEdit }}" {{ $btnEdit ? 'disabled' : '' }}>
                      Reset Password
                    </button>
                  </div>
                </td>
              </tr> @empty
              <tr>
                <td colspan="5" class="corporate-empty-state">
                  <i class="fas fa-users"></i>
                  <h4>No Admin Accounts Found</h4>
                  <p>Start by adding your first admin using the button above.</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
  </a>
</div>

<!-- Admin Accounts Modal -->
<div class="modal fade" id="create_admin_modal">
  <div class="modal-dialog">
    <div class="modal-content corporate-modal-content">

      <!-- Modal Header -->
      <div class=" modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact"">New Account</h4>
        <button type=" button" class="close" data-dismiss="modal">&times;</button>
      </div>


      <form id="create_admin_form">

        <!-- Modal body -->
        <div class="modal-body corporate-modal-body ultra-compact">

          <div class="form-group row">
            <label for="" class="col-sm-4 col-form-label">First Name</label>
            <div class="col-sm-8">
              <input type="text" name="firstName" class="form-control" placeholder="First Name" autocomplete="off" required>
            </div>
          </div>

          <div class="form-group row">
            <label for="" class="col-sm-4 col-form-label">Middle Name</label>
            <div class="col-sm-8">
              <input type="text" name="middleName" class="form-control" placeholder="Middle Name" autocomplete="off">
            </div>
          </div>

          <div class="form-group row">
            <label for="" class="col-sm-4 col-form-label">Last Name</label>
            <div class="col-sm-8">
              <input type="text" name="lastName" class="form-control" placeholder="Last Name" autocomplete="off" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-4 col-form-label">Email</label>
            <div class="col-sm-8">
              <input type="email" name="email" class="form-control" id="email" placeholder="Email" autocomplete="off" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="password" class="col-sm-4 col-form-label">Password</label>
            <div class="col-sm-8">
              <div class="input-group">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" autocomplete="off" minlength="6" maxlength="20">
                <button class="btn btn-outline-secondary" type="button" id="btn_generate_password" title="Generate password">
                  <i class="fas fa-random"></i>
                </button>
              </div>

            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-sm-4 col-form-label">Role</label>
            <div class="col-sm-8">
              <select name="role" class="form-control" id="" required>
                <option value="">-select role-</option>
                @foreach($roles as $role)

                <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-custom-darkg" data-dismiss="modal">Close</button>
          <button type="submit" id="btn_save_account" class="btn btn-custom-darkg">Submit</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- Edit Admin Accounts Modal -->
<div class="modal fade" id="edit_admin_modal">
  <div class="modal-dialog">
    <div class="modal-content corporate-modal-content">
      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact">Edit Account</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="edit_admin_form">
        <input type="hidden" name="id">
        <!-- Modal body -->
        <div class="modal-body corporate-modal-body ultra-compact">
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">First Name</label>
            <div class="col-sm-8">
              <input type="text" name="firstName" class="form-control" placeholder="First Name" autocomplete="off">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Middle Name</label>
            <div class="col-sm-8">
              <input type="text" name="middleName" class="form-control" placeholder="Middle Name" autocomplete="off">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Last Name</label>
            <div class="col-sm-8">
              <input type="text" name="lastName" class="form-control" placeholder="Last Name" autocomplete="off">
            </div>
          </div>
          <div class="form-group row">
            <label for="admin_role" class="col-sm-4 col-form-label">Admin Role</label>
            <div class="col-sm-8">
              <select name="role" class="form-control">
                <option value="">-select role-</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
              </select>
            </div>
          </div>


          <div class="form-group row">
            <label for="" class="col-sm-4 col-form-label">Status</label>
            <div class="col-sm-8">
              <select name="status" class="form-control">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>

        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-custom-darkg" data-dismiss="modal">Cancel</button>
          <button type="submit" id="" class="btn btn-custom-darkg">Submit</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="reset_password_modal">
  <div class="modal-dialog">
    <div class="modal-content corporate-modal-content">
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact">Reset Password</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="reset_password_form">
        <input type="hidden" name="id" id="reset_account_id">
        <div class="modal-body corporate-modal-body ultra-compact">
          <div class="form-group row">
            <label for="reset_password" class="col-sm-4 col-form-label">New Password</label>
            <div class="col-sm-8">
              <div class="input-group">
                <input type="password" name="password" class="form-control" id="reset_password" placeholder="New Password" autocomplete="off" minlength="6" maxlength="20" required>
                <button class="btn btn-outline-secondary" type="button" id="btn_generate_password_reset" title="Generate password">
                  <i class="fas fa-random"></i>
                </button>
                <button class="btn btn-outline-secondary" type="button" id="btn_toggle_password_reset" title="Show/Hide password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <small class="form-text text-muted">Click the icon to auto-generate a secure password or to toggle visibility.</small>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-custom-darkg" data-dismiss="modal">Cancel</button>
          <button type="submit" id="btn_reset_password_submit" class="btn btn-custom-darkg">Reset</button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // Initialize page
    initializePage();

    // Event handlers
    bindEventHandlers();
  });

  function initializePage() {
    // Set active navigation
    $('.admin-nav-account').addClass('active');
  }

  function bindEventHandlers() {
    // Modal events
    $(document).on('shown.bs.modal', '#create_admin_modal', handleCreateModalShow);

    // Form submissions
    $(document).on('submit', '#create_admin_form', handleCreateAdminSubmit);
    $(document).on('submit', '#edit_admin_form', handleEditAdminSubmit);
    $(document).on('submit', '#reset_password_form', handleResetPasswordSubmit);

    // Button clicks
    $(document).on('click', '.btn-edit-admin-account', handleEditButtonClick);
    $(document).on('click', '.btn-reset-password', handleResetPasswordOpenModal);

    // Generate password
    $(document).on('click', '#btn_generate_password', handleGeneratePassword);
    $(document).on('click', '#btn_generate_password_reset', handleGeneratePasswordReset);

    // Toggle password visibility for reset modal
    $(document).on('click', '#btn_toggle_password_reset', handleTogglePasswordReset);
  }

  function handleCreateModalShow() {
    $('#create_admin_form')[0].reset();
    $('[name=firstName]').focus();
  }

  function handleGeneratePassword(e) {
    e.preventDefault();
    const password = generatePassword(12);
    const $pw = $('#password');
    $pw.val(password);
    // briefly show the password as text for 4 seconds so admin can copy it
    $pw.attr('type', 'text');
    setTimeout(() => $pw.attr('type', 'password'), 4000);
  }

  function generatePassword(length = 12) {
    const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+';
    let ret = '';
    const cryptoObj = window.crypto || window.msCrypto;
    if (cryptoObj && cryptoObj.getRandomValues) {
      const values = new Uint32Array(length);
      cryptoObj.getRandomValues(values);
      for (let i = 0; i < length; i++) {
        ret += charset[values[i] % charset.length];
      }
    } else {
      for (let i = 0; i < length; i++) {
        ret += charset[Math.floor(Math.random() * charset.length)];
      }
    }
    return ret;
  }

  function handleCreateAdminSubmit(e) {
    e.preventDefault();

    const formData = $(this).serialize();
    const submitBtn = $('#btn_save_account');

    makeAjaxRequest({
      url: BASE_URL + 'admin/admin-account',
      method: 'POST',
      data: formData,
      submitBtn: submitBtn,
      successCallback: handleSuccess,
      errorCallback: handleError
    });
  }

  function handleEditAdminSubmit(e) {
    e.preventDefault();

    const id = $('#edit_admin_modal [name=id]').val();
    const formData = $(this).serialize();
    const submitBtn = $('#edit_admin_modal [type=submit]');

    makeAjaxRequest({
      url: BASE_URL + `admin/admin-account/${id}`,
      method: 'PUT',
      data: formData,
      submitBtn: submitBtn,
      successCallback: handleSuccess,
      errorCallback: handleError
    });
  }

  function handleEditButtonClick() {
    const $this = $(this);
    const $form = $('#edit_admin_modal');
    const $tr = $this.closest('tr');
    const id = $this.data('id');

    // Extract data from table row using data attributes
    const adminData = {
      id: id,
      role: $tr.data('role') || $tr.find('.role').text().trim(),
      status: $tr.data('status') || ($tr.find('.admin-status').text().trim() === 'Active' ? 1 : 0),
      email: $tr.find('.email').text().trim(),
      firstName: $tr.data('first-name'),
      middleName: $tr.data('middle-name'),
      lastName: $tr.data('last-name')
    };

    // Populate form fields
    populateEditForm($form, adminData);

    // Show modal
    $form.modal('show');
  }

  function populateEditForm($form, data) {
    $form.find('[name=id]').val(data.id);
    $form.find('[name=email]').val(data.email);
    $form.find('[name=firstName]').val(data.firstName);
    $form.find('[name=middleName]').val(data.middleName);
    $form.find('[name=lastName]').val(data.lastName);

    // Set select options
    $form.find(`[name=role] option[value="${data.role}"]`).prop('selected', true);
    $form.find(`[name=status] option[value="${data.status}"]`).prop('selected', true);
  }

  function makeAjaxRequest(options) {
    $.ajax({
      url: options.url,
      method: options.method,
      data: options.data,
      dataType: 'json',
      beforeSend: function() {
        if (options.submitBtn) {
          options.submitBtn.prop('disabled', true);
        }
      },
      complete: function() {
        if (options.submitBtn) {
          options.submitBtn.prop('disabled', false);
        }
      },
      success: function(data) {
        if (options.successCallback) {
          options.successCallback(data);
        }
      },
      error: function(xhr) {
        if (options.errorCallback) {
          options.errorCallback(xhr);
        }
      }
    });
  }

  function handleResetPasswordOpenModal() {
    const id = $(this).data('id');
    $('#reset_password_form')[0].reset();
    $('#reset_account_id').val(id);
    $('#reset_password').attr('type', 'password');
    // ensure eye icon shows correct state
    $('#btn_toggle_password_reset i').removeClass('fa-eye-slash').addClass('fa-eye');
    $('#reset_password_modal').modal('show');
  }

  function handleResetPasswordSubmit(e) {
    e.preventDefault();
    const $form = $('#reset_password_form');
    const id = $('#reset_account_id').val();

    const pwd = $form.find('[name=password]').val();
    if (!pwd || pwd.length < 6) {
      alert('Password must be at least 6 characters.');
      return;
    }

    const submitBtn = $('#btn_reset_password_submit');

    makeAjaxRequest({
      url: BASE_URL + `admin/password/reset`,
      method: 'POST',
      data: $form.serialize(),
      submitBtn: submitBtn,
      successCallback: function(data) {
        alert('Password has been reset. An email has been sent to the admin if configured.');
        $('#reset_password_modal').modal('hide');
      },
      errorCallback: function(xhr) {
        alert('Failed to reset password.');
      }
    });
  }

  function handleGeneratePasswordReset(e) {
    e.preventDefault();
    const password = generatePassword(12);
    const $pw = $('#reset_password');
    $pw.val(password);
    $pw.attr('type', 'text');
    setTimeout(() => $pw.attr('type', 'password'), 4000);
  }

  function handleTogglePasswordReset(e) {
    e.preventDefault();
    const $pw = $('#reset_password');
    const $icon = $('#btn_toggle_password_reset i');
    if ($pw.attr('type') === 'password') {
      $pw.attr('type', 'text');
      $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      $pw.attr('type', 'password');
      $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  }
</script>
@endsection