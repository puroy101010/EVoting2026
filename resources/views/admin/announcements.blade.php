@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
<!-- Summernote CSS removed -->
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Modern Page Header -->
            <div class="corporate-page-header ultra-compact">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <h1 class="corporate-page-title ultra-compact">Announcements Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Announcements</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <button class="btn corporate-action-btn ultra-compact" id="btn_add_announcement" data-toggle="modal" data-target="#addAnnouncementModal">
                            <i class="fas fa-plus"></i>
                            Add New Announcement
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modern Table Container -->
            <div class="corporate-table-container ultra-compact">
                <div class="table-responsive">
                    <table id="announcementsTable" class="table corporate-table ultra-compact">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th style="width: 120px;">Created Date</th>
                                <th class="text-center" style="width: 80px;">Status</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($announcements as $index => $announcement)
                            <tr data-id="{{ $announcement->id }}">
                                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $announcement->title }}</td>
                                <td class="text-muted">
                                    {{ Str::limit(strip_tags($announcement->content), 80) }}
                                </td>
                                <td class="text-muted">
                                    {{ $announcement->created_at ? $announcement->created_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="status text-center">
                                    @if (!$announcement->trashed())
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
                                    <button type="button" class="btn corporate-edit-btn ultra-compact btn-edit-announcement" data-id="{{ $announcement->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn corporate-delete-btn ultra-compact btn-delete-announcement" data-id="{{ $announcement->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="corporate-empty-state">
                                    <i class="fas fa-bullhorn"></i>
                                    <h4>No Announcements Found</h4>
                                    <p>Start by adding your first announcement using the button above.</p>
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


<!-- ADD ANNOUNCEMENT MODAL - Enhanced -->
<div class="modal fade" id="addAnnouncementModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content corporate-modal-content">
            <!-- Modal Header -->
            <div class="modal-header corporate-modal-header ultra-compact">
                <h4 class="modal-title corporate-modal-title ultra-compact">Add New Announcement</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="add_announcement_form">
                @csrf
                <!-- Modal body -->
                <div class="modal-body corporate-modal-body ultra-compact">
                    <div class="corporate-form-group ultra-compact">
                        <label for="announcement_title" class="corporate-label ultra-compact">Announcement Title <span class="text-danger">*</span></label>
                        <div class="corporate-input-group">
                            <div class="corporate-input-icon">
                                <i class="fas fa-heading"></i>
                            </div>
                            <input type="text" id="announcement_title" class="corporate-input ultra-compact" name="title" placeholder="Enter announcement title" required autocomplete="off" maxlength="255" value="{{ old('title') }}">
                        </div>
                    </div>

                    <div class="corporate-form-group ultra-compact">
                        <label for="announcement_content" class="corporate-label ultra-compact">Content <span class="text-danger">*</span></label>
                        <textarea id="announcement_content" name="content" class="form-control" rows="10" placeholder="Enter announcement content..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="corporate-form-group ultra-compact">
                                <label for="announcement_status" class="corporate-label ultra-compact">Status</label>
                                <div class="corporate-input-group">
                                    <div class="corporate-input-icon">
                                        <i class="fas fa-toggle-on"></i>
                                    </div>
                                    <select id="announcement_status" class="corporate-input ultra-compact" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="corporate-form-group ultra-compact">
                                <label for="announcement_priority" class="corporate-label ultra-compact">Priority</label>
                                <div class="corporate-input-group">
                                    <div class="corporate-input-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <select id="announcement_priority" class="corporate-input ultra-compact" name="priority">
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer corporate-modal-footer ultra-compact">
                    <button type="button" class="btn corporate-cancel-btn ultra-compact" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn corporate-save-btn ultra-compact">
                        <i class="fas fa-save"></i>
                        Save Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT ANNOUNCEMENT MODAL - Enhanced -->
<div class="modal fade" id="editAnnouncementModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content corporate-modal-content">
            <!-- Modal Header -->
            <div class="modal-header corporate-modal-header ultra-compact">
                <h4 class="modal-title corporate-modal-title ultra-compact">Edit Announcement</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit_announcement_form">
                <input type="hidden" name="id" id="edit_announcement_id">
                @csrf
                @method('PUT')
                <!-- Modal body -->
                <div class="modal-body corporate-modal-body ultra-compact">
                    <div class="corporate-form-group ultra-compact">
                        <label for="edit_announcement_title" class="corporate-label ultra-compact">Announcement Title <span class="text-danger">*</span></label>
                        <div class="corporate-input-group">
                            <div class="corporate-input-icon">
                                <i class="fas fa-heading"></i>
                            </div>
                            <input type="text" id="edit_announcement_title" class="corporate-input ultra-compact" name="title" placeholder="Enter announcement title" required autocomplete="off" maxlength="255">
                        </div>
                    </div>

                    <div class="corporate-form-group ultra-compact">
                        <label for="edit_announcement_content" class="corporate-label ultra-compact">Content <span class="text-danger">*</span></label>
                        <textarea id="edit_announcement_content" name="content" class="form-control" rows="10" placeholder="Enter announcement content..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="corporate-form-group ultra-compact">
                                <label for="edit_announcement_status" class="corporate-label ultra-compact">Status</label>
                                <div class="corporate-input-group">
                                    <div class="corporate-input-icon">
                                        <i class="fas fa-toggle-on"></i>
                                    </div>
                                    <select id="edit_announcement_status" class="corporate-input ultra-compact" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="corporate-form-group ultra-compact">
                                <label for="edit_announcement_priority" class="corporate-label ultra-compact">Priority</label>
                                <div class="corporate-input-group">
                                    <div class="corporate-input-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <select id="edit_announcement_priority" class="corporate-input ultra-compact" name="priority">
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer corporate-modal-footer ultra-compact">
                    <button type="button" class="btn corporate-cancel-btn ultra-compact" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn corporate-save-btn ultra-compact">
                        <i class="fas fa-save"></i>
                        Update Announcement
                    </button>
                </div>
            </form>
        </div>
        @endsection

        @section('js')
        <!-- Summernote JS removed -->
        <script>
            $(document).ready(function() {
                // Initialize DataTable


                // Add Announcement Form Submit
                $('#add_announcement_form').on('submit', function(e) {
                    e.preventDefault();

                    const formData = $(this).serialize();
                    console.log('Form data:', formData);
                    console.log('Content value:', $('#announcement_content').val());

                    $.ajax({
                        url: BASE_URL + 'admin/announcement',
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            $('#addAnnouncementModal').modal('hide');
                            Swal.fire('Success!', 'Announcement created successfully', 'success')
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            handleError(xhr);
                        }
                    });
                });

                // Edit Announcement Button Click
                $(document).on('click', '.btn-edit-announcement', function() {
                    const announcementId = $(this).data('id');

                    $.ajax({
                        url: BASE_URL + 'admin/announcement/' + announcementId,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            const announcement = response.data;

                            $('#edit_announcement_id').val(announcement.id);
                            $('#edit_announcement_title').val(announcement.title);
                            $('#edit_announcement_status').val(announcement.status);
                            $('#edit_announcement_priority').val(announcement.priority);

                            // Set content in Summernote
                            $('#edit_announcement_content').val(announcement.content || '');

                            $('#editAnnouncementModal').modal('show');
                        },
                        error: function(xhr) {
                            handleError(xhr);
                        }
                    });
                });

                // Edit Announcement Form Submit
                $('#edit_announcement_form').on('submit', function(e) {
                    e.preventDefault();

                    const formData = $(this).serialize();
                    const announcementId = $('#edit_announcement_id').val();

                    $.ajax({
                        url: BASE_URL + 'admin/announcement/' + announcementId,
                        method: 'PUT',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            $('#editAnnouncementModal').modal('hide');
                            Swal.fire('Success!', 'Announcement updated successfully', 'success')
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            handleError(xhr);
                        }
                    });
                });

                // Delete Announcement Button Click
                $(document).on('click', '.btn-delete-announcement', function() {
                    const announcementId = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: BASE_URL + 'admin/announcement/' + announcementId,
                                method: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                dataType: 'json',
                                success: function(response) {
                                    Swal.fire('Deleted!', 'Announcement has been deleted.', 'success')
                                        .then(() => location.reload());
                                },
                                error: function(xhr) {
                                    handleError(xhr);
                                }
                            });
                        }
                    });
                });

                // Reset form when modal is closed
                $('#addAnnouncementModal').on('hidden.bs.modal', function() {
                    $('#add_announcement_form')[0].reset();
                });

                $('#editAnnouncementModal').on('hidden.bs.modal', function() {
                    $('#edit_announcement_form')[0].reset();
                });

                // Error handling function
                function handleError(xhr) {
                    let message = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        message = xhr.responseText;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        </script>
        @endsection