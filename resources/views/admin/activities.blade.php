@extends('layouts.admin')

@section('css')

<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
<script src="{{asset('js/admin/activity_logs.js')}}?v={{ time() }}"></script>

@endsection

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">


      <!-- Modern Page Header -->
      <div class="corporate-page-header ultra-compact">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div>
            <h1 class="corporate-page-title ultra-compact">Activity Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Activities</li>
              </ol>
            </nav>
          </div>
          <div class="mt-3 mt-md-0">
            <button class="btn corporate-action-btn ultra-compact d-none" id="" data-toggle="modal" data-target="#">
              <i class="fas fa-plus"></i>
              Add New
            </button>
          </div>
        </div>
      </div>


      <div class="row mb-2 row-border">
        <div class="col-sm-6">
          <div class="dflex-button">
            <button class="btn btn-sm border-secondary" id="show_filter">Show Filter</button>
          </div>
        </div>

      </div>
      <div class="card-body pl-2 pr-2 mb-1" style="border: 1px solid #dec8c8; border-radius: 5px; background-color: #f5f5f5; display: none" id="filter_box">
        <form id="filter_form">
          <div class="row form-group">
            <div class="col-md-2 div-text">Search</div>
            <div class="col-md-2 col-md2-size">
              <input type="search" value="" name="search" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 offset-md-4 div-text">Category</div>
            <div class="col-md-2 col-md2-size">
              <select class="form-control form-control-sm" name="category">
                <option value="">ALL</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-2 div-text">Account</div>
            <div class="col-md-2 col-md2-size">
              <select class="form-control form-control-sm" name="user">
                <option value="">ALL</option>
              </select>
            </div>
            <div class="col-md-2 offset-md-4 div-text">Action</div>
            <div class="col-md-2 col-md2-size">
              <select class="form-control form-control-sm" name="action">
                <option value="">ALL</option>
                <option value="1">YES</option>
                <option value="0">NO</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-2 div-text">Membership Type</div>
            <div class="col-md-2 col-md2-size">
              <select class="form-control form-control-sm" name="account_type">
                <option value="">ALL</option>
                <option value="corp">CORPORATE</option>
                <option value="indv">INDIVIDUAL</option>
              </select>
            </div>
            <div class="col-md-2 offset-md-4 div-text">Active Page</div>
            <div class="col-md-2 col-md2-size">
              <select class="form-control form-control-sm" name="active_page">
                <option value="1">1</option>

              </select>
            </div>

          </div>
          <div class="row form-group">
            <div class="col-md-2 div-text">Role</div>
            <div class="col-md-2 col-md2-size">
              <select class="form-control form-control-sm" name="role">
                <option value="">ALL</option>
                <option value="stockholder">STOCKHOLDER</option>
                <option value="corp-rep">CORPORATE REPRESENTATIVE</option>
                <option value="non-member">NON-MEMBER</option>
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
          <button class="btn btn-sm border-secondary" id="hide_filter">Hide Filter</button>
          <button class="btn btn-sm border-secondary" id="btn_filter_reset">Reset</button>
        </form>
      </div>



      <!-- Modern Table Container -->
      <div class="corporate-table-container ultra-compact">

        <div class="table-responsive" style="height: 700px; overflow: auto;">
          <table id="activityTable" class="table corporate-table ultra-compact">
            <thead class="text-nowrap table-light" style="position: sticky;top: 0; z-index: 1;">
              <tr>
                <th class="th-padding">Code</th>
                <th class="th-padding">Date & Time</th>
                <th class="th-padding">Created By</th>
                <th class="th-padding">Account</th>
                <th class="th-padding">Description</th>
                <th class="th-padding">Category</th>
                <th class="th-padding">Action</th>
              </tr>
            </thead>
            <tbody>
              <!-- content goes here -->
            </tbody>
          </table>
        </div>
        <br>
        <span class="mt-5 record-summary"></span>
        <nav aria-label="" class="float-right">
          <ul class="pagination">
            <li class="page-item"><a class="page-link btn-navigator btn-prev" disabled="">Previous</a></li>
            <li class="page-item"><a class="page-link active-page" href="#"></a></li>
            <li class="page-item"><a class="page-link btn-navigator btn-next" href="">Next</a></li>
          </ul>
        </nav>


      </div>
    </div>
  </section>
  <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
  </a>
</div>


<div class="modal fade" id="login_details_modal" tabindex="-1" role="dialog" aria-labelledby="login_details_label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="login_details_label">Login Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger message-alert" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24" role="alert">

        </div>
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Account No</th>
              <th>Stockholder</th>
              <th>Email (SH)</th>
              <th>Corp. Rep.</th>
              <th>Email (Corp. Rep.)</th>
            </tr>
          </thead>
          <tbody>
            <tr>

            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">

      </div>
    </div>
  </div>
</div>




<script>
  $(document).ready(function() {
    $('.admin-nav-activity').addClass('active');
  })
</script>

@endsection