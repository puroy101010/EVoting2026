@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
@endsection

@section('content')
<div class="content-wrapper">
  <section class="content">
    <div class="container-fluid">
      <!-- Modern Page Header -->
      <div class="corporate-page-header ultra-compact">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div>
            <h1 class="corporate-page-title ultra-compact">Candidates Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Candidates</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact" id="btn_add_candidate" data-toggle="modal" data-target="#addCandidateModal">
              <i class="fas fa-plus"></i>
              Add New Candidate
            </button>
          </div>
        </div>
      </div>

      <!-- Modern Table Container -->
      <div class="corporate-table-container ultra-compact">
        <div class="table-responsive">
          <table id="candidatesTable" class="table corporate-table ultra-compact">
            <thead>
              <tr>
                <th style="width: 40px;">#</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Last Name</th>
                <th>Type</th>
                <th class="text-center" style="width: 80px;">Status</th>
                <th class="text-center" style="width: 60px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($candidates as $index => $candidate)
              <tr data-id="{{ $candidate->candidateId }}">
                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                <td class="first-name fw-semibold">{{ $candidate->firstName }}</td>
                <td class="middle-name">{{ $candidate->middleName ?: '—' }}</td>
                <td class="last-name fw-semibold">{{ $candidate->lastName }}</td>
                <td class="type">
                  <span class="badge bg-light text-dark rounded-pill" style="font-size: 0.65rem; padding: 0.2rem 0.5rem;">{{ ucfirst($candidate->type) }}</span>
                </td>
                <td class="status text-center">
                  @if ($candidate->isActive)
                  <span class="corporate-status-badge ultra-compact active">
                    <i class="fas fa-check-circle"></i>
                    Active
                  </span>
                  @else
                  <span class="corporate-status-badge ultra-compact inactive">
                    <i class="fas fa-times-circle"></i>
                    Inactive
                  </span>
                  @endif
                </td>
                <td class="text-center">
                  <button type="button" class="btn corporate-edit-btn ultra-compact btn-edit-candidate">
                    <i class="fas fa-edit"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="corporate-empty-state">
                  <i class="fas fa-users"></i>
                  <h4>No Candidates Found</h4>
                  <p>Start by adding your first candidate using the button above.</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- Modern Back to Top Button -->
  <button id="back-to-top" class="btn corporate-back-to-top" role="button" aria-label="Scroll to top" style="display: none;">
    <i class="fas fa-chevron-up"></i>
  </button>
</div>


<!-- ADD CANDIDATE MODAL - Enhanced -->
<div class="modal fade" id="addCandidateModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content corporate-modal-content">
      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact">Add New Candidate</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="add_candidate_form">
        @csrf
        <!-- Modal body -->
        <div class="modal-body corporate-modal-body ultra-compact">
          <div class="row">
            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="first_name" class="corporate-label ultra-compact">First Name <span class="text-danger">*</span></label>
                <div class="corporate-input-group">
                  <div class="corporate-input-icon">
                    <i class="fas fa-user"></i>
                  </div>
                  <input type="text" id="first_name" class="corporate-input ultra-compact" name="first_name" placeholder="Enter first name" required autocomplete="off" maxlength="100" value="{{ old('first_name') }}">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="last_name" class="corporate-label ultra-compact">Last Name <span class="text-danger">*</span></label>
                <div class="corporate-input-group">
                  <div class="corporate-input-icon">
                    <i class="fas fa-user"></i>
                  </div>
                  <input type="text" id="last_name" class="corporate-input ultra-compact" name="last_name" placeholder="Enter last name" required autocomplete="off" maxlength="100" value="{{ old('last_name') }}">
                </div>
              </div>
            </div>
          </div>


          <div class="corporate-form-group ultra-compact">
            <label for="middle_name" class="corporate-label ultra-compact">Middle Name</label>
            <div class="corporate-input-group">
              <div class="corporate-input-icon">
                <i class="fas fa-user-circle"></i>
              </div>
              <input type="text" id="middle_name" class="corporate-input ultra-compact" name="middle_name" placeholder="Enter middle name (optional)" autocomplete="off" maxlength="100" value="{{ old('middle_name') }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="type" class="corporate-label ultra-compact">Candidate Type <span class="text-danger">*</span></label>
                <select class="corporate-select ultra-compact" name="type" id="type" required>
                  <option value="">Select candidate type</option>
                  <option value="regular">Regular Candidate</option>
                  <option value="independent">Independent Candidate</option>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="status" class="corporate-label ultra-compact">Status <span class="text-danger">*</span></label>
                <select class="corporate-select ultra-compact" name="status" id="status" required>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer corporate-modal-footer ultra-compact">
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" class="btn corporate-btn-primary ultra-compact btn-submit-form" id="btn_submit_add_form">
            <i class="fas fa-save mr-1"></i> Save Candidate
          </button>
          <span class="spinner-border corporate-spinner submit-loading" role="status" style="display: none"></span>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END ADD CANDIDATE MODAL -->


<!-- EDIT CANDIDATE MODAL - Enhanced -->
<div class="modal fade" id="editCandidateModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content corporate-modal-content">
      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact">Edit Candidate</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="edit_candidate_form">
        <input type="hidden" name="id">
        @csrf

        <div class="modal-body corporate-modal-body ultra-compact">
          <div class="row">
            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="edit_first_name" class="corporate-label ultra-compact">First Name <span class="text-danger">*</span></label>
                <div class="corporate-input-group">
                  <div class="corporate-input-icon">
                    <i class="fas fa-user"></i>
                  </div>
                  <input type="text" id="edit_first_name" class="corporate-input ultra-compact" name="first_name" placeholder="Enter first name" required maxlength="100" autocomplete="off">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="edit_last_name" class="corporate-label ultra-compact">Last Name <span class="text-danger">*</span></label>
                <div class="corporate-input-group">
                  <div class="corporate-input-icon">
                    <i class="fas fa-user"></i>
                  </div>
                  <input type="text" id="edit_last_name" class="corporate-input ultra-compact" name="last_name" placeholder="Enter last name" required maxlength="100" autocomplete="off">
                </div>
              </div>
            </div>

          </div>

          <div class="corporate-form-group ultra-compact">
            <label for="edit_middle_name" class="corporate-label ultra-compact">Middle Name</label>
            <div class="corporate-input-group">
              <div class="corporate-input-icon">
                <i class="fas fa-user-circle"></i>
              </div>
              <input type="text" id="edit_middle_name" class="corporate-input ultra-compact" name="middle_name" placeholder="Enter middle name (optional)" maxlength="100" autocomplete="off">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="edit_type" class="corporate-label ultra-compact">Candidate Type <span class="text-danger">*</span></label>
                <select class="corporate-select ultra-compact" name="type" id="edit_type" required>
                  <option value="regular">Regular Candidate</option>
                  <option value="independent">Independent Candidate</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="corporate-form-group ultra-compact">
                <label for="edit_status" class="corporate-label ultra-compact">Status <span class="text-danger">*</span></label>
                <select class="corporate-select ultra-compact" name="status" id="edit_status" required>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer corporate-modal-footer ultra-compact">
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" class="btn corporate-btn-primary ultra-compact" id="btn_submit_edit_form">
            <i class="fas fa-save mr-1"></i> Update Candidate
          </button>
          <span class="spinner-border corporate-spinner submit-loading" role="status" style="display: none"></span>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END EDIT CANDIDATE MODAL -->

<script>
  $(function() {
    // Initialize page
    initializePage();
    bindEvents();
  });

  // Page initialization
  function initializePage() {
    $('.admin-nav-candidates').addClass('active');
    initBackToTop();
  }

  // Event binding
  function bindEvents() {
    // Modal events
    $('#addCandidateModal').on('shown.bs.modal', () => $('#first_name').focus());
    $('#editCandidateModal').on('shown.bs.modal', () => $('#edit_first_name').focus());
    $('#addCandidateModal, #editCandidateModal').on('hidden.bs.modal', resetModalForm);

    // Form events
    $(document).on('submit', '#add_candidate_form', handleAddForm);
    $(document).on('submit', '#edit_candidate_form', handleEditForm);
    $(document).on('click', '.btn-edit-candidate', showEditModal);
  }

  // Back to top functionality
  function initBackToTop() {
    $(window).scroll(function() {
      $('#back-to-top').toggle($(this).scrollTop() > 100);
    });

    $('#back-to-top').click(function() {
      $('html, body').animate({
        scrollTop: 0
      }, 600);
      return false;
    });
  }

  // Reset modal form
  function resetModalForm() {
    const $form = $(this).find('form');
    $form[0].reset();
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('input, select, textarea').prop('disabled', false);
  }

  // Handle add form submission
  function handleAddForm(e) {



    e.preventDefault();
    const $form = $(this);


    if (!validateForm($form)) return;

    submitCandidateForm($form, 'create');
  }

  // Handle edit form submission
  function handleEditForm(e) {




    e.preventDefault();
    const $form = $(this);

    console.log('serializing form:', $form.serialize());

    if (!validateForm($form)) return;

    Swal.fire({
      title: 'Update Candidate?',
      text: 'Are you sure you want to update this candidate?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: 'var(--corporate-primary)',
      confirmButtonText: 'Yes, update!'
    }).then((result) => {
      if (result.isConfirmed) {
        submitCandidateForm($form, 'update', $form.find('[name=id]').val());
      }
    });
  }

  // Show edit modal with data
  function showEditModal() {
    const $tr = $(this).closest('tr');
    const $modal = $('#editCandidateModal');

    // Extract and populate data
    const data = {
      id: $tr.data('id'),
      firstName: $tr.find('.first-name').text().trim(),
      lastName: $tr.find('.last-name').text().trim(),
      middleName: $tr.find('.middle-name').text().trim().replace('—', ''),
      type: $tr.find('.type .badge').text().toLowerCase().trim(),
      status: $tr.find('.status').text().includes('Active') ? '1' : '0'
    };

    if (!data.id || !data.firstName || !data.lastName) {
      showError('Data Error', 'Unable to load candidate information. Please refresh and try again.');
      return;
    }

    populateEditForm($modal, data);
    $modal.modal('show');
  }

  // Populate edit form
  function populateEditForm($modal, data) {
    $modal.find('[name=id]').val(data.id);
    $modal.find('#edit_first_name').val(data.firstName);
    $modal.find('#edit_last_name').val(data.lastName);
    $modal.find('#edit_middle_name').val(data.middleName);
    $modal.find('#edit_type').val(data.type);
    $modal.find('#edit_status').val(data.status);
  }

  // Form validation
  function validateForm($form) {
    console.log('Validating form:', $form[0]);

    const required = {
      '#first_name, #edit_first_name': 'First Name',
      '#last_name, #edit_last_name': 'Last Name',
      '#type, #edit_type': 'Candidate Type'
    };

    for (const [selector, label] of Object.entries(required)) {
      const $field = $form.find(selector);
      console.log(`Checking field ${selector}:`, $field.val());

      if (!$field.val() || !$field.val().trim()) {
        showError('Missing Information', `${label} is required.`);
        $field.focus();
        return false;
      }
    }

    console.log('Form validation passed');
    return true;
  }

  // Submit candidate form
  function submitCandidateForm($form, action, id = null) {

    const $modal = $form.closest('.modal');
    const $btn = $form.find('button[type="submit"]');
    const $spinner = $modal.find('.submit-loading');

    setLoadingState($btn, $spinner, true);

    // Serialize BEFORE disabling
    const formData = $form.serialize();
    disableForm($form, true);

    let url;
    if (action === 'create') {
      url = 'admin/candidate';
    } else {
      url = 'admin/candidate/' + id;
    }

    $.ajax({
        url: BASE_URL + url,
        method: action === 'create' ? 'POST' : 'PUT',
        data: formData,
        timeout: 30000,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      })
      .done(response => {
        showSuccess(response.message || `Candidate ${action}d successfully.`);
        $modal.modal('hide');
        setTimeout(() => window.location.reload(), 1500);
      })
      .fail(xhr => {
        handleAjaxError(xhr);
      })
      .always(() => {
        setLoadingState($btn, $spinner, false);
        disableForm($form, false);
      });
  }

  // Set loading state
  function setLoadingState($btn, $spinner, loading) {
    $btn.prop('disabled', loading);
    $spinner.toggle(loading);
  }

  // Disable/enable form
  function disableForm($form, disabled) {
    $form.find('input, select, textarea').prop('disabled', disabled);
  }

  // Handle AJAX errors
  function handleAjaxError(xhr) {
    let title = 'Error!';
    let message = 'An unexpected error occurred.';

    if (xhr.status === 422) {
      title = 'Validation Error';
      const errors = xhr.responseJSON;
      if (errors) {
        message = xhr.responseJSON.message || 'Please correct the errors and try again.';
      }
    } else if (xhr.status === 403) {
      title = 'Access Denied';
      message = 'You do not have permission to perform this action.';
    } else if (xhr.status >= 500) {
      title = 'Server Error';
      message = 'A server error occurred. Please try again later.';
    }

    showError(title, message);
  }

  // Show success message
  function showSuccess(message) {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: message,
      confirmButtonColor: 'var(--corporate-primary)',
      timer: 2000,
      timerProgressBar: true
    });
  }

  // Show error message
  function showError(title, message) {
    Swal.fire({
      icon: 'error',
      title: title,
      text: message,
      confirmButtonColor: 'var(--corporate-primary)'
    });
  }
</script>

@endsection