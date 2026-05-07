@extends('layouts.admin')

@section('head')
<link rel="stylesheet" type="text/css" href="{{asset('css/user/stockholder-online-form.css')}}?<?php echo filemtime('css/user/stockholder-online-form.css') ?>">
@endsection

@section('content')

<!-- Modal -->
<div class="modal fade" id="requestBallotFormModal" tabindex="-1" role="dialog" aria-labelledby="requestBallotFormModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5><i class="fas fa-vote-yea me-2"></i> Ballot No. {{ $ballot->ballotNo }}</h5>
          <small class="text-white-50">{{ $votingType }}</small>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">




        <!-- SUMMARY-->
        <div id="summaryForm" class="ballot-pages active" data-page="3" data-page-name="summary">

          <div class="" id="summary-header-section">
            <div class="summary-header-card">
              <h1 class="h4-amendments mb-2"><i class="fas fa-clipboard-list me-2"></i>Summary</h1>
              <p class="text-muted">
                Please carefully review the summary of your votes. If you wish to make any changes, click the Back button to edit your votes.
              </p>
              <div class="alert alert-warning summary-alert" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="summary-message">{{ $message ?? '' }}</span>
              </div>
            </div>
          </div>
          <div class="row mt-3 mb-3" id="summary-votes-section">
            <div class="col-12 col-md-6 order-md-1 mb-3 mb-md-0">
              <div class="summary-votes-card unused-votes-card">
                <div class="summary-votes-label">
                  <i class="fas fa-inbox me-1"></i> <span>Unused Votes</span>
                </div>
                <div class="summary-votes-value unused-votes-value" id="unusedVotesSummary-display">
                  {{ $unusedVotes }}
                </div>
                <input type="hidden" id="unusedVotesSummary" value="">
              </div>
            </div>
            <div class="col-12 col-md-6 order-md-2">
              <div class="summary-votes-card distributed-votes-card">
                <div class="summary-votes-label">
                  <i class="fas fa-calculator me-1"></i> <span>Total Valid Votes</span>
                </div>
                <div class="summary-votes-value distributed-votes-value" id="totalDistributedVotesSummary-display">
                  {{ $usedVotes }}
                </div>
                <input type="hidden" id="totalDistributedVotesSummary" value="">
              </div>
            </div>
          </div>
          @if($amendmentEnabled === true)
          <h5 class="h5-semiTitle">Amendments</h5>
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table" id="summaryAmendmentTable">
                  <thead class="th-agendaTable table-bg-color text-white">
                    <tr>
                      <th>#</th>
                      <th class="th-sum-amd-col-one">Amendment</th>
                      <th class="th-sum-amd-col-two text-center">Agenda</th>
                    </tr>
                  </thead>
                  <tbody>
                    {!! $amendments !!}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          @endif
          <h5 class="h5-semiTitle"><i class="fas fa-list-check me-2"></i>Agenda Items</h5>
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table" id="agendaTableSummary">
                  <thead class="th-agendaTable table-bg-color text-white">
                    <tr>
                      <th>#</th>
                      <th class="th-col-agd-sum-one">Agenda</th>
                      <th class="th-col-agd-sum-two text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {!! $agendas !!}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <h5 class="h5-semiTitle">Board of Directors</h5>
          <div class="card">
            <div class="card-body">
              <table class="table mt-3" id="candidateTableSummary">
                <thead class="th-agendaTable table-bg-color text-white">
                  <tr>
                    <th width="80%">Candidate</th>
                    <th width="20%" class="text-center">Vote</th>
                  </tr>
                </thead>
                <tbody>
                  {!! $boardOfDirectors !!}
                </tbody>
              </table>
            </div>
          </div>


          <h5 class="h5-semiTitle">Used BOD Accounts</h5>
          <div class="card">
            <div class="card-body">
              <table class="table mt-3" id="candidateTableSummary">
                <thead class="th-agendaTable table-bg-color text-white">
                  <tr>
                    <th width="25%">Stockholder Account</th>
                    <th width="55%" class="text-center">Stockholder</th>
                    <th width="20%" class="text-center">Account Status</th>
                  </tr>
                </thead>
                <tbody>
                  {!! $usedBodAccounts !!}
                </tbody>
              </table>
            </div>
          </div>



          <h5 class="h5-semiTitle">Used Amendment Accounts</h5>
          <div class="card">
            <div class="card-body">
              <table class="table mt-3" id="candidateTableSummary">
                <thead class="th-agendaTable table-bg-color text-white">
                  <tr>
                    <th width="25%">Stockholder Account</th>
                    <th width="55%" class="text-center">Stockholder</th>
                    <th width="20%" class="text-center">Account Status</th>
                  </tr>
                </thead>
                <tbody>
                  {!! $usedAmendmentAccounts !!}
                </tbody>
              </table>
            </div>
          </div>




          <h5 class="h5-semiTitle">Available BOD Accounts</h5>
          <div class="card">
            <div class="card-body">
              <table class="table mt-3" id="candidateTableSummary">
                <thead class="th-agendaTable table-bg-color text-white">
                  <tr>
                    <th width="25%">Stockholder Account</th>
                    <th width="55%" class="text-center">Stockholder</th>
                    <th width="20%" class="text-center">Account Status</th>
                  </tr>
                </thead>
                <tbody>
                  {!! $availableBodAccounts !!}
                </tbody>
              </table>
            </div>
          </div>



          <h5 class="h5-semiTitle">Available Amendment Accounts</h5>
          <div class="card">
            <div class="card-body">
              <table class="table mt-3" id="candidateTableSummary">
                <thead class="th-agendaTable table-bg-color text-white">
                  <tr>
                    <th width="25%">Stockholder Account</th>
                    <th width="55%" class="text-center">Stockholder</th>
                    <th width="20%" class="text-center">Account Status</th>
                  </tr>
                </thead>
                <tbody>
                  {!! $availableAmendmentAccounts !!}
                </tbody>
              </table>
            </div>
          </div>
        </div>




      </div>
      <div class="modal-footer">
        <button type="button" class="btn newBtnlight" data-btn-show-page="0" data-direction="back" id="btnBack" onclick="handlePage(this)" disabled>
          <i class="fas fa-arrow-left me-2"></i> Back
        </button>
        <button type="button" class="btn btn-continue" data-btn-show-page="@if($amendmentEnabled === true) 2 @else 3 @endif" data-direction="next" id="btnSumbitBallot" onclick="handlePage(this)">
          Continue <i class="fas fa-arrow-right ms-2"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Modal Loader -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-body text-center p-5">
        <div class="loading-spinner mb-3">
          <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
        <h5 class="modal-title mb-2" id="loadingModalLabel" style="color: #2F4A3C; font-weight: 600;">
          <i class="fas fa-paper-plane me-2"></i>Submitting Your Ballot
        </h5>
        <p class="text-muted mb-0">Please wait while we process your submission...</p>
        <small class="text-muted">This may take a few moments.</small>
      </div>
    </div>
  </div>
</div>

<!-- Page Loading Overlay -->
<div id="pageLoadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999;">
  <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); text-align: center;">
    <div class="loading-skeleton mb-3">
      <div class="skeleton-pulse" style="width: 100%; height: 20px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading-shimmer 1.5s infinite; border-radius: 4px; margin-bottom: 12px;"></div>
      <div class="skeleton-pulse" style="width: 80%; height: 16px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading-shimmer 1.5s infinite; border-radius: 4px; margin-bottom: 8px;"></div>
      <div class="skeleton-pulse" style="width: 60%; height: 16px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading-shimmer 1.5s infinite; border-radius: 4px;"></div>
    </div>
    <p style="color: #666; margin: 0; font-size: 14px;">Loading page...</p>
  </div>
</div>

<style>
  @keyframes loading-shimmer {
    0% {
      background-position: -200% 0;
    }

    100% {
      background-position: 200% 0;
    }
  }
</style>

@endsection


@section('js')

<script>
  $(document).ready(function() {
    $('#requestBallotFormModal').modal('show');
  });
</script>

@endsection