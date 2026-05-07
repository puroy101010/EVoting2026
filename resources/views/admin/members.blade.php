@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}">

<script src="{{asset('js/admin/corporate-ui.js') }}"></script>
<script src="{{asset('js/admin/corporate-ui.modal.js') }}"></script>
<script src="{{asset('js/admin/members.js')}}"></script>
<script src="{{asset('js/admin/proxy-history.js')}}"></script>
<style type="text/css">
  #filter_box .div-text {
    font-size: .85rem;
  }

  /* Fix for modal close button visibility and clickability */
  .modal-header .close {
    position: relative !important;
    z-index: 9999 !important;
    opacity: 1 !important;
    font-size: 1.5rem !important;
    font-weight: bold !important;
    color: #fff !important;
    text-shadow: none !important;
    cursor: pointer !important;
    padding: 0.25rem !important;
    margin: -0.25rem !important;
    background: transparent !important;
    border: none !important;
    outline: none !important;
  }

  .modal-header .close:hover {
    opacity: 0.8 !important;
    color: #fff !important;
  }

  .modal-header .close:focus {
    outline: none !important;
    box-shadow: none !important;
  }

  /* Ensure modal header has proper positioning */
  .modal-header {
    position: relative !important;
    z-index: 1050 !important;
  }
</style>
@endsection


@section('content')
<div class="content-wrapper">
  <section class="content-header" id="section_members">
    <div class="container-fluid">
      <!-- Modern Page Header -->
      <div class="corporate-page-header ultra-compact">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div>
            <h1 class="corporate-page-title ultra-compact">Stockholder Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stockholders</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2 align-items-center">
            <button class="btn corporate-action-btn ultra-compact mr-2" id="btn_add_member"><i class="fas fa-plus"></i> New record</button>
            <button class="btn corporate-action-btn ultra-compact" id="btn_export_record"><i class="fa fa-upload"></i> Export</button>
          </div>
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-md-12" id="cardStockholder">
          <div class="card rounded-0">
            <div class="card-header rounded-0">
              <div class="card-body pl-2 pr-2 mb-2" style="border: 1px solid #dec8c8; border-radius: 5px; background-color: #f5f5f5; display: none " id="filter_box">
                <form id="filter_form">
                  <div class="row form-group">
                    <div class="col-md-2 div-text">Accounts</div>
                    <div class="col-md-2 col-md2-size">
                      <select name="accounts" id="">
                        <option value="">All</option>
                      </select>
                    </div>
                  </div>
                  <div class="row form-group">
                    <div class="col-md-2 div-text">Status</div>
                    <div class="col-md-2 col-md2-size">
                      <select class="" name="status">
                        <option value="">All</option>
                        <option value="0">Active</option>
                        <option value="1">Delinquent</option>
                      </select>
                    </div>
                    <div class="col-md-2 offset-md-4 div-text div-text-right">Proxyholder</div>
                    <div class="col-md-2 col-md2-size">
                      <select name="proxy_assignee">
                        <option value="">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                      </select>
                    </div>
                  </div>
                  <div class="row form-group">
                    <div class="col-md-2 div-text">Account Type</div>
                    <div class="col-md-2 col-md2-size">
                      <select class="" name="account_type">
                        <option value="">ALL</option>
                        <option value="indv">INDIVIDUAL</option>
                        <option value="corp">CORPORATE</option>
                      </select>
                    </div>
                    <div class="col-md-2 offset-md-4 div-text div-text-right">Proxy</div>
                    <div class="col-md-2 col-md2-size">
                      <select class="" name="proxy">
                        <option value="">All</option>
                        <option value="1">With</option>
                        <option value="0">Without</option>
                      </select>
                    </div>
                  </div>
                  <div class="row form-group">
                    <div class="col-md-2 div-text">Role</div>
                    <div class="col-md-2 col-md2-size">
                      <select class="" name="role">
                        <option value="">ALL</option>
                        <option value="stockholder">STOCKHOLDER</option>
                        <option value="corp-rep">CORPORATE REPRESENTATIVE</option>
                      </select>
                    </div>
                    <div class="col-md-2 offset-md-4 div-text div-text-right">Per Page</div>
                    <div class="col-md-2 col-md2-size">
                      <select name="per_page" id="">
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="400">400</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                        <option value="2000">2000</option>
                        <option value="3000">3000</option>
                        <option value="4000">4000</option>
                      </select>
                    </div>
                  </div>
                  <div class="row form-group">
                    <div class="col-md-2 div-text div-text-right">Page</div>
                    <div class="col-md-2 col-md2-size">
                      <select name="active_page" class="active-page">
                        <option value="1">1</option>
                      </select>
                    </div>
                  </div>
                  <div class="div-align">
                    <button type="button" class="btn btn-sm border-secondary" id="hide_filter">Hide Filter</button>
                    <button type="button" class="btn btn-sm border-secondary" id="btn_filter_reset">Reset</button>
                  </div>
                  <p class="mt-0 record-summary text-right"></p>
                  <nav aria-label="" class="float-right">
                    <ul class="pagination">
                      <li class="page-item"><a class="page-link btn-navigator btn-prev" disabled="">Previous</a></li>
                      <li class="page-item"><a class="page-link active-page" href="#"></a></li>
                      <li class="page-item"><a class="page-link btn-navigator btn-next" href="http://localhost/OnlineVotingSystem/admin/member/load/index?page=2">Next</a></li>
                    </ul>
                  </nav>
                </form>
              </div>
              <div class="container-fluid">
                <div class="row">
                  <div class="col-md-4">
                    <div class="dflex-button">
                      <button class="btn btn-sm border-secondary" type="button" id="show_filter">Show Filter</button>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <!-- Action buttons moved to page header -->
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive" style="overflow: auto;">
                <table id="memberTable" class="table corporate-table ultra-compact">
                  <thead class="text-nowrap" style="position: sticky;top: 0; z-index: 1;">
                    <tr>
                      <th class="th-padding">#</th>
                      <th class="th-padding">Account #</th>
                      <th class="th-padding">Stockholder</th>
                      <th>Email</th>
                      <th class="th-padding">Corp. Rep.</th>
                      <th class="th-padding">Corp. Rep. Email</th>
                      <th class="th-padding">Type</th>
                      <th class="th-padding">Status</th>
                      <th class="th-padding text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
              <span class="mt-5 record-summary"></span>
              <nav aria-label="" class="float-right">
                <ul class="pagination">
                  <li class="page-item"><a class="page-link btn-navigator btn-prev" href="#" disabled>Previous</a></li>
                  <li class="page-item"><a class="page-link active-page" href="#"></a></li>
                  <li class="page-item"><a class="page-link btn-navigator btn-next" href="#">Next</a></li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
</section>
<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></a>
</div>


<!-- ADD MEMBER MODAL - Modern Design -->
<div class="modal fade" id="addMemberModal">
  <div class="modal-dialog modal-xl">
    <div class="modal-content corporate-modal-content">
      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact">
          <i class="fas fa-user-plus mr-2"></i>Add New Stockholder
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="form_add_member">
        @csrf
        <!-- Modal body -->
        <div class="modal-body corporate-modal-body ultra-compact">
          <!-- Stockholder Details Section -->
          <div class="corporate-section-card ultra-compact mb-4">
            <div class="corporate-section-header">
              <h5 class="corporate-section-title">
                <i class="fas fa-id-card mr-2"></i>Stockholder Details
              </h5>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="corporate-form-group ultra-compact">
                  <label for="account_number" class="corporate-label ultra-compact">
                    Account No <span class="text-danger">*</span>
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-hashtag"></i>
                    </div>
                    <input type="text" id="account_number" class="corporate-input compact"
                      name="account_number" placeholder="Enter account number"
                      required autocomplete="off" maxlength="4">
                  </div>
                </div>
              </div>

              <div class="col-md-8">
                <div class="corporate-form-group ultra-compact">
                  <label for="stockholder" class="corporate-label ultra-compact">
                    Stockholder Name <span class="text-danger">*</span>
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-user"></i>
                    </div>
                    <input type="text" id="stockholder" class="corporate-input compact"
                      name="stockholder" placeholder="Enter stockholder name"
                      required autocomplete="off" maxlength="100">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="account_type" class="corporate-label ultra-compact">
                    Account Type <span class="text-danger">*</span>
                  </label>
                  <select name="account_type" id="account_type" class="corporate-select compact" required>
                    <option value="">Select Account Type</option>
                    <option value="indv">Individual (PM)</option>
                    <option value="corp">Corporation</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="email" class="corporate-label ultra-compact">
                    Email Address
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" id="email" class="corporate-input compact"
                      name="email" placeholder="Enter email address" autocomplete="off">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="vote_in_person" class="corporate-label ultra-compact">
                    Online Voter
                  </label>
                  <select name="vote_in_person" class="corporate-select compact">
                    <option value="stockholder">Stockholder</option>
                    <option value="corp-rep">Corporate Representative</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Corporate Representative Section (Hidden by default) -->
          <div class="corporate-section-card ultra-compact mb-4 form-corp-only" style="display: none;">
            <div class="corporate-section-header">
              <h5 class="corporate-section-title">
                <i class="fas fa-building mr-2"></i>Corporate Representative
              </h5>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="corp_rep" class="corporate-label ultra-compact">
                    Representative Name <span class="text-danger">*</span>
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-user-tie"></i>
                    </div>
                    <input type="text" id="corp_rep" class="corporate-input compact"
                      name="corp_rep" placeholder="Enter representative name" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="corp_rep_email" class="corporate-label ultra-compact">
                    Representative Email <span class="text-danger">*</span>
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" id="corp_rep_email" class="corporate-input compact"
                      name="corp_rep_email" placeholder="Enter representative email" autocomplete="off">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="corporate-form-group ultra-compact">
                  <label for="auth_signatory" class="corporate-label ultra-compact">
                    Authorized Signatory
                  </label>
                  <textarea name="auth_signatory" id="auth_signatory" class="corporate-textarea compact"
                    placeholder="Enter authorized signatory details" rows="3"></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Stock Details Section -->
          <div class="corporate-section-card ultra-compact">
            <div class="corporate-section-header">
              <h5 class="corporate-section-title">
                <i class="fas fa-chart-line mr-2"></i>Stock Details
              </h5>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="suffix" class="corporate-label ultra-compact">
                    Suffix <span class="text-danger">*</span>
                  </label>
                  <select name="suffix" id="suffix" class="corporate-select compact"
                    data-placeholder="Select Suffix" required>
                    <option value="">Select Suffix</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="delinquent" class="corporate-label ultra-compact">
                    Delinquent Status
                  </label>
                  <select name="delinquent" class="corporate-select compact">
                    <option value="0" selected>No</option>
                    <option value="1">Yes</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer corporate-modal-footer ultra-compact">
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" class="btn corporate-btn-primary ultra-compact btn-submit-form" id="btn_submit_member">
            <i class="fas fa-save mr-1"></i> Save Stockholder
          </button>
          <span class="spinner-border corporate-spinner submit-loading" role="status" style="display: none"></span>
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
        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          <button type="" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>




<!-- EDIT MEMBER MODAL - Modern Design -->
<div class="modal fade" id="edit_member_modal">
  <div class="modal-dialog modal-xl">
    <div class="modal-content corporate-modal-content">
      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact">
          <i class="fas fa-user-edit mr-2"></i>Edit Stockholder
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="form_edit_member">
        <input type="hidden" name="id">

        @csrf
        <!-- Modal body -->
        <div class="modal-body corporate-modal-body ultra-compact">
          <!-- Stockholder Details Section -->
          <div class="corporate-section-card ultra-compact mb-4">
            <div class="corporate-section-header">
              <h5 class="corporate-section-title">
                <i class="fas fa-id-card mr-2"></i>Stockholder Details
              </h5>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_account_number" class="corporate-label ultra-compact">
                    Account No <span class="text-danger">*</span>
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-hashtag"></i>
                    </div>
                    <input type="text" id="edit_account_number" class="corporate-input compact"
                      name="account_number" placeholder="Enter account number"
                      required autocomplete="off" maxlength="4" minlength="4">
                  </div>
                </div>
              </div>

              <div class="col-md-8">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_stockholder" class="corporate-label ultra-compact">
                    Stockholder Name <span class="text-danger">*</span>
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-user"></i>
                    </div>
                    <input type="text" id="edit_stockholder" class="corporate-input compact"
                      name="stockholder" placeholder="Enter stockholder name"
                      required autocomplete="off">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_account_type" class="corporate-label ultra-compact">
                    Account Type <span class="text-danger">*</span>
                  </label>
                  <select name="account_type" id="edit_account_type" class="corporate-select compact" required>
                    <option value="indv">Individual (PM)</option>
                    <option value="corp">Corporation</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_email" class="corporate-label ultra-compact">
                    Email Address
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" id="edit_email" class="corporate-input compact"
                      name="email" placeholder="Enter email address" autocomplete="off">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_vote_in_person" class="corporate-label ultra-compact">
                    Online Voter
                  </label>
                  <select name="vote_in_person" id="edit_vote_in_person" class="corporate-select compact">
                    <option value="stockholder">Stockholder</option>
                    <option value="corp-rep">Corporate Representative</option>
                  </select>
                </div>
              </div>
            </div>
          </div>



          <!-- Stock Details Section -->
          <div class="corporate-section-card ultra-compact">
            <div class="corporate-section-header">
              <h5 class="corporate-section-title">
                <i class="fas fa-chart-line mr-2"></i>Stock Details
              </h5>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_suffix" class="corporate-label ultra-compact">
                    Suffix <span class="text-danger">*</span>
                  </label>
                  <select name="suffix" id="edit_suffix" class="corporate-select compact"
                    data-placeholder="Select Suffix" required>
                    <option value="">Select Suffix</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_delinquent" class="corporate-label ultra-compact">
                    Delinquent Status
                  </label>
                  <select name="delinquent" id="edit_delinquent" class="corporate-select compact">
                    <option value="">Select Status</option>
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Corporate Representative Section -->
          <div class="corporate-section-card ultra-compact mb-4 form-corp-only">
            <div class="corporate-section-header">
              <h5 class="corporate-section-title">
                <i class="fas fa-building mr-2"></i>Corporate Representative
              </h5>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_corp_rep" class="corporate-label ultra-compact">
                    Representative Name
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-user-tie"></i>
                    </div>
                    <input type="text" id="edit_corp_rep" class="corporate-input compact"
                      name="corp_rep" placeholder="Enter representative name" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_corp_rep_email" class="corporate-label ultra-compact">
                    Representative Email
                  </label>
                  <div class="corporate-input-group">
                    <div class="corporate-input-icon compact">
                      <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" id="edit_corp_rep_email" class="corporate-input compact"
                      name="corp_rep_email" placeholder="Enter representative email" autocomplete="off">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="corporate-form-group ultra-compact">
                  <label for="edit_auth_signatory" class="corporate-label ultra-compact">
                    Authorized Signatory
                  </label>
                  <textarea name="auth_signatory" id="edit_auth_signatory" class="corporate-textarea compact"
                    placeholder="Enter authorized signatory details" rows="3"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer corporate-modal-footer ultra-compact">
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" class="btn corporate-btn-primary ultra-compact btn-submit-form" id="btn_submit_edit_member">
            <i class="fas fa-save mr-1"></i> Update Stockholder
          </button>
          <span class="spinner-border corporate-spinner submit-loading" role="status" style="display: none"></span>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END EDIT MEMBER MODAL -->

<!-- BOD PROXY MODAL - Modern Corporate Design -->
<div class="modal fade" id="assignProxyBodModal" tabindex="-1" role="dialog" aria-labelledby="assignProxyBodModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content corporate-modal-content">
      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact" id="assignProxyBodModalTitle">
          <i class="fas fa-handshake mr-2"></i>BOD Proxy Assignment
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="assignProxyForm">
        <input type="hidden" name="accountToAssign">

        <!-- Modal Body -->
        <div class="modal-body corporate-modal-body ultra-compact">
          <!-- Assign Stock Form Section -->
          <div class="assign-stock-form">
            <div class="corporate-section-card ultra-compact mb-4">
              <div class="corporate-section-header">
                <h5 class="corporate-section-title">
                  <i class="fas fa-chart-bar mr-2"></i>Proxy Assignment Details
                </h5>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">
                      Stock to Assign <span class="text-danger">*</span>
                    </label>
                    <div class="corporate-input-group">
                      <div class="corporate-input-icon compact">
                        <i class="fas fa-certificate"></i>
                      </div>
                      <input type="text" class="corporate-input compact account-to-assign"
                        placeholder="Stock information" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">
                      Proxy Form No <span class="text-danger">*</span>
                    </label>
                    <div class="corporate-input-group">
                      <div class="corporate-input-icon compact">
                        <i class="fas fa-file-alt"></i>
                      </div>
                      <input type="text" name="proxyFormNo" class="corporate-input compact"
                        placeholder="Enter proxy form number" maxlength="7" required>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">
                      Assignor <span class="text-danger">*</span>
                    </label>
                    <select name="assignor" class="corporate-select compact"
                      data-placeholder="Select Assignor" required>
                      <option value="">Select Assignor</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">
                      Assignee <span class="text-danger">*</span>
                    </label>
                    <select name="assignee" class="corporate-select compact"
                      data-placeholder="Select Assignee" required>
                      <option value="">Select Assignee</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Assignee Details Section (for existing proxies) -->
          <div class="assignee-details">
            <div class="corporate-section-card ultra-compact">
              <div class="corporate-section-header">
                <h5 class="corporate-section-title">
                  <i class="fas fa-info-circle mr-2"></i>Current Proxy Details
                </h5>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Stock</div>
                    <div class="corporate-info-value assignee-details-stock"></div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Proxy Form No</div>
                    <div class="corporate-info-value assignee-details-form-no"></div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Assignor</div>
                    <div class="corporate-info-value assignee-details-assignor"></div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Assignee</div>
                    <div class="corporate-info-value assignee-details-assignee"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer corporate-modal-footer ultra-compact">
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Close
          </button>
          <button type="button" class="btn corporate-btn-danger ultra-compact assignee-details"
            data-id="" id="btnCancelBodProxy" onclick="cancel_bod_proxy(this)">
            <i class="fas fa-ban mr-1"></i> Cancel Proxy
          </button>
          <button type="button" class="btn corporate-btn-primary ultra-compact assign-stock-form"
            onclick="assign_bod_proxy(this)">
            <i class="fas fa-handshake mr-1"></i> Assign Proxy
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="assignProxyAmendmentModal" tabindex="-1" role="dialog" aria-labelledby="assignProxyAmendmentModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content corporate-modal-content">
      <!-- Modal Header -->
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title corporate-modal-title ultra-compact" id="assignProxyAmendmentModalTitle">
          <i class="fas fa-file-signature mr-2"></i>AMENDMENT PROXY
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="assignProxyFormAmendment">
        <input type="hidden" name="accountToAssign">

        <div class="modal-body corporate-modal-body ultra-compact">
          <div class="assign-stock-form">
            <div class="corporate-section-card ultra-compact mb-4">
              <div class="corporate-section-header">
                <h5 class="corporate-section-title">
                  <i class="fas fa-chart-bar mr-2"></i>Proxy Assignment Details
                </h5>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">
                      Stock to assign <span class="text-danger">*</span>
                    </label>
                    <div class="corporate-input-group">
                      <div class="corporate-input-icon compact">
                        <i class="fas fa-certificate"></i>
                      </div>
                      <input type="text" class="corporate-input compact account-to-assign" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-2">
                <div class="col-md-6">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">Ref No: </label>
                    <input type="text" name="refNo" class="corporate-input compact" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">Assignor</label>
                    <select name="assignor" class="corporate-select compact" data-placeholder="Assignor">
                      <option value="">Select Assignor</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row mt-2">
                <div class="col-md-12">
                  <div class="corporate-form-group ultra-compact">
                    <label class="corporate-label ultra-compact">Assignee</label>
                    <select name="assignee" class="corporate-select compact" data-placeholder="Assignee">
                      <option value="">Select Assignee</option>
                    </select>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <div class="assignee-details">
            <div class="corporate-section-card ultra-compact">
              <div class="corporate-section-header">
                <h5 class="corporate-section-title">
                  <i class="fas fa-info-circle mr-2"></i>Current Proxy Details
                </h5>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Stock</div>
                    <div class="corporate-info-value assignee-details-stock"></div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Ref No</div>
                    <div class="corporate-info-value assignee-details-form-no"></div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Assignor</div>
                    <div class="corporate-info-value assignee-details-assignor"></div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="corporate-info-card ultra-compact">
                    <div class="corporate-info-label">Assignee</div>
                    <div class="corporate-info-value assignee-details-assignee"></div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
        <div class="modal-footer corporate-modal-footer ultra-compact">
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">Close</button>
          <button type="button" class="btn corporate-btn-danger ultra-compact assignee-details" data-id="" id="btnCancelAmendmentProxy" onclick="cancel_amendment_proxy(this)">Cancel Proxy</button>
          <button type="button" class="btn corporate-btn-primary ultra-compact assign-stock-form btn-assign-amendment-proxy">Assign Proxy</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
  $(document).ready(function() {

    $("#form_add_member select").chosen({
      width: "100%"
    });

    $("#form_edit_member select").chosen({
      width: "100%"
    });

    // Initialize proxy modal selects
    $("#assignProxyBodModal select").chosen({
      width: "100%",
      allow_single_deselect: true
    });

    $("#assignProxyAmendmentModal select").chosen({
      width: "100%",
      allow_single_deselect: true
    });

    $("#assign_proxy_modal .search-stockholder-proxy-spa, #assign_spa_modal .search-stockholder-proxy-spa").chosen({
      width: "100%",
      allow_single_deselect: true
    });

    $('.admin-nav-members').addClass('active');
    $('[data-toggle="tooltip"]').tooltip();

  })
</script>
@endsection