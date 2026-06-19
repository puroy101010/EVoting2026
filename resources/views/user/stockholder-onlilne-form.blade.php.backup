@extends('layouts.user')

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
          <h5><i class="fas fa-vote-yea me-2"></i> Ballot No. {{ $ballotNo }}</h5>
          <small class="text-white-50">Stockholder Online Voting</small>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="amendmentForm" class="ballot-pages @if(config('evoting.enableAmendment') === true) active @else d-none @endif" data-page="1" data-page-name="amendment">
          <form>

            <h2 class="h2-stockholder section-title"><span class="span-divider">Amendments</span></h2>
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="info-card">
                  <div class="info-label">
                    <i class="fas fa-user-tie me-1"></i> Number of Share(s)
                  </div>
                  <div class="info-value">{{ number_format($availableSharesAmendment) }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-card">
                  <div class="info-label">
                    <i class="fas fa-vote-yea me-1"></i> Total Votes Available(s)
                  </div>
                  <div class="info-value">{{ number_format($availableVotesAmendment) }}</div>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table" id="amendmentTable">
                <thead class="th-agendaTable">
                  <tr>
                    <th>#</th>
                    <th class="th-amendColOne">Amendment</th>
                    <th class="th-amendColTwo text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  {!! $amendmentForm !!}
                </tbody>
              </table>
            </div>
          </form>
        </div>

        <div id="boardOfDirectorForm" class="ballot-pages @if($amendmentEnabled === false) active @endif" data-page="2" data-page-name="bod">
          <form>
            <h2 class="h2-stockholder section-title"><span class="span-divider">Board of Directors</span></h2>
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="info-card">
                  <div class="info-label">
                    <i class="fas fa-user-tie me-1"></i> Number of Share(s)
                  </div>
                  <div class="info-value">{{ number_format($availableSharesBod) }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-card">
                  <div class="info-label">
                    <i class="fas fa-vote-yea me-1"></i> Total Votes Available(s)
                  </div>
                  <div class="info-value" id="totalAvailableVotes-display">{{ number_format($availableVotesBod) }}</div>
                  <input type="hidden" id="totalAvailableVotes" value="{{ $availableVotesBod }}">
                </div>
              </div>
            </div>

            <h3 class="h3-summary-title"><i class="fas fa-list-check me-2"></i> Agenda Items</h3>

            <div class="table-responsive">
              <table class="table" id="agendaTable">
                <thead class="th-agendaTable">
                  <tr>
                    <th style="width: 70px;">#</th>
                    <th class="th-agd-col-one">Agenda</th>
                    <th style="" class="th-agd-col-two text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  {!! $agendaForm !!}
                </tbody>
              </table>
            </div>

            <h3 class="h3-summary-title">Candidates</h3>
            <div class="table-responsive">
              <table class="table bod-votes-table" id="">
                <thead class="th-agendaTable">
                  <tr>
                    <th>#</th>
                    <th class="th-bod-col-one">Candidate</th>
                    <th class="th-bod-col-two text-center">Vote</th>
                  </tr>
                </thead>
                <tbody>
                  <?php

                  $counter = 1;

                  foreach ($candidates as $candidate) {

                    $disabled = '';
                    $tooltipBod = '';

                    if ($availableSharesBod === 0) {

                      $disabled = 'disabled';

                      $tooltipBod = 'data-toggle="tooltip" title="No available votes for BOD."';
                    }


                    echo '<tr data-id="' . $candidate->candidateId . '">
                              <td class="td-amend">' . $counter . '</td><td class="td-amend"><span class="font-weight-bold">' . $candidate->lastName . ', ' . $candidate->firstName . ' ' . $candidate->middleName . '</span><br><small class="text-muted">' . $candidate->type . '</small></td>
                              <td>
                                <div class="div-bodInputNo">
                                  <input type="number" class="form-control form-control-sm bod-vote" min="0" step="1" placeholder="Enter vote here ..." ' . $disabled . ' ' . $tooltipBod . '>
                                </div>
                              </td>
                          </tr>';

                    $counter++;
                  }
                  ?>
                </tbody>
              </table>
            </div>


            <div class="row mt-3">
              <div class="col-md-6">
                <div class="info-card">
                  <div class="info-label">
                    <i class="fas fa-calculator me-1"> </i>Total Distributed Votes
                  </div>
                  <div class="info-value" id="totalDistributedVotes-display">0</div>
                  <input type="hidden" id="totalDistributedVotes" value="0">
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-card">
                  <div class="info-label">
                    <i class="fas fa-inbox me-1"></i> Unused Votes
                  </div>
                  <div class="info-value" id="unusedVotes-display">{{ number_format($availableVotesBod) }}</div>
                  <input type="hidden" id="unusedVotes" value="{{ $availableVotesBod }}">
                </div>
              </div>
            </div>
          </form>
        </div>



        <!-- SUMMARY-->
        <div id="summaryForm" class="ballot-pages" data-page="3" data-page-name="summary">

          <div class="" id="summary-header-section">
            <div class="summary-header-card">
              <h1 class="h4-amendments mb-2"><i class="fas fa-clipboard-list me-2"></i>Summary</h1>
              <p class="text-muted">
                Please carefully review the summary of your votes. If you wish to make any changes, click the Back button to edit your votes.
              </p>
              <div class="alert alert-warning summary-alert" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="summary-message"></span>
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
                  <i class="fas fa-coins me-1"></i><span id="unusedVotesSummary-display-number"></span>
                </div>
                <input type="hidden" id="unusedVotesSummary" value="">
              </div>
            </div>
            <div class="col-12 col-md-6 order-md-2">
              <div class="summary-votes-card distributed-votes-card">
                <div class="summary-votes-label">
                  <i class="fas fa-calculator me-1"></i> <span>Total Valid Votes</span>
                </div>
                <div class="summary-votes-value distributed-votes-value" id="totalDistributedVotesSummary-display"></div>
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
                    <!-- Amendment summary details go here -->
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
                    <!-- Agenda summary details go here -->
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
                  <!-- Candidate summary details go here -->
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
  // ==================================================
  // BALLOT VOTING SYSTEM - STOCKHOLDER ONLINE FORM
  // ==================================================

  'use strict';

  // ============================================================================
  // CONFIGURATION & CONSTANTS
  // ============================================================================
  const BALLOT_CONFIG = {
    totalPages: $('.ballot-pages').length,
    ballotId: "{{ $ballotId }}",
    availableBoardOfDirectorVotes: Number('{{ $availableVotesBod }}'),
    availableAmendmentVotes: Number('{{ $availableVotesAmendment }}'),
    isAmendmentEnabled: @if(config('evoting.enableAmendment') === true) true @else false @endif,
    pageTransitionDelayMs: 500,
    smoothScrollDurationMs: 400
  };

  // ============================================================================
  // STOCKHOLDER ONLINE VOTING APPLICATION
  // ============================================================================
  const StockholderVotingSystem = {

    // Initialize the voting application
    initialize() {
      this.setupEventListeners();
      this.displayBallotModal();
      this.enableTooltips();
    },

    // ========================================================================
    // EVENT LISTENERS & INITIALIZATION
    // ========================================================================
    setupEventListeners() {
      // Modal close redirect event
      $('#requestBallotFormModal').on('hidden.bs.modal', () => {
        location.href = BASE_URL + 'user/vote';
      });

      // Vote button selection events for amendments and agendas
      $(document).on('click', '.btn-amendment, .btn-agenda', function() {
        $(this).addClass('btn-success-custom');
        $(this).siblings().removeClass('btn-success-custom');
      });

      // Board of Director vote input validation events
      $(document).on('change', '.bod-vote', function(e) {
        StockholderVotingSystem.onBoardOfDirectorVoteChanged.call(this, e);
      });
      $(document).on('input', '.bod-vote', function(e) {
        StockholderVotingSystem.onBoardOfDirectorVoteInput.call(this, e);
      });
      $(document).on('keypress', '.bod-vote', function(e) {
        StockholderVotingSystem.onBoardOfDirectorVoteKeyPressed.call(this, e);
      });
      $(document).on('paste', '.bod-vote', function(e) {
        StockholderVotingSystem.onBoardOfDirectorVotePasted.call(this, e);
      });
    },

    // Display the main ballot modal
    displayBallotModal() {
      $('#requestBallotFormModal').modal('show');
    },

    // Initialize Bootstrap tooltips
    enableTooltips() {
      $('[data-toggle="tooltip"]').tooltip();
    },

    // ========================================================================
    // PAGE NAVIGATION & TRANSITIONS
    // ========================================================================
    switchBallotPage(currentPageNumber, targetPageNumber) {
      $(`.ballot-pages[data-page=${currentPageNumber}]`).removeClass('active').hide();
      $(`.ballot-pages[data-page=${targetPageNumber}]`).addClass('active').show();
    },

    displayLoadingOverlay() {
      $('#pageLoadingOverlay').fadeIn(200);
      $('#requestBallotFormModal .modal-body, #requestBallotFormModal .modal-body *, #requestBallotFormModal .modal-footer, #requestBallotFormModal .modal-footer *').css('visibility', 'hidden');
    },

    hideLoadingOverlay() {
      $('#pageLoadingOverlay').fadeOut(200);
      $('#requestBallotFormModal .modal-body, #requestBallotFormModal .modal-body *, #requestBallotFormModal .modal-footer, #requestBallotFormModal .modal-footer *').css('visibility', 'visible');

      // Smooth scroll modal body to top
      $('#requestBallotFormModal .modal-body').animate({
        scrollTop: 0
      }, BALLOT_CONFIG.smoothScrollDurationMs);
    },

    processNextPageTransition(previousPageNumber, nextPageNumber, totalPageCount) {
      this.displayLoadingOverlay();

      setTimeout(() => {
        $('[data-direction=back]').attr('disabled', previousPageNumber === 0);

        if (nextPageNumber > totalPageCount) {
          $('[data-direction=next]').text('Confirm and Submit');
        }

        this.hideLoadingOverlay();
      }, BALLOT_CONFIG.pageTransitionDelayMs);
    },

    processBackPageTransition(previousPageNumber, nextPageNumber, totalPageCount) {
      this.displayLoadingOverlay();

      setTimeout(() => {
        $('[data-direction=next]').text('Continue');
        $('[data-direction=back]').attr('disabled', previousPageNumber === (BALLOT_CONFIG.isAmendmentEnabled ? 0 : 1));
        $('[data-direction=next]').attr('disabled', nextPageNumber > totalPageCount);

        this.hideLoadingOverlay();
      }, BALLOT_CONFIG.pageTransitionDelayMs);
    },

    // ========================================================================
    // BALLOT SUBMISSION & COMPLETION
    // ========================================================================
    async submitBallotData() {
      try {
        // Show loading modal immediately
        $('#loadingModal').modal('show');

        const ballotSubmissionData = {
          ballotId: BALLOT_CONFIG.ballotId,
          amendment: this.collectAmendmentVotes(),
          bod: this.collectBoardOfDirectorVotes(),
          agenda: this.collectAgendaVotes(),
          confirmationId: $('#btnSumbitBallot').attr('data-confirmation-id')
        };

        const serverResponse = await $.ajax({
          url: BASE_URL + 'user/ballot/stockholder-online/submit',
          method: 'POST',
          dataType: 'json',
          data: ballotSubmissionData
        });

        // Hide loading modal and show success
        $('#loadingModal').modal('hide');
        this.displaySubmissionSuccessAlert(serverResponse.message);

      } catch (serverError) {
        // Hide loading modal and show error
        $('#loadingModal').modal('hide');
        this.displaySubmissionErrorAlert(serverError.responseJSON?.message || 'An error occurred');
      }
    },

    displaySubmissionSuccessAlert(successMessage) {
      Swal.fire({
        icon: 'success',
        title: '<span style="color:#2F4A3C;font-weight:bold;">Ballot Submitted!</span>',
        html: `<div style='font-size:1.1em;margin-bottom:8px;'>${successMessage}</div><div style='color:#888;'>Thank you for participating in the election.</div>`,
        showConfirmButton: true,
        confirmButtonColor: '#2F4A3C',
        confirmButtonText: '<i class="fas fa-check-circle me-2"></i> Return to Dashboard',
        background: '#f8f9fa',
        customClass: {
          popup: 'swal2-border-radius',
          title: 'swal2-title-custom',
          confirmButton: 'swal2-confirm-custom'
        }
      }).then(() => {
        location.href = BASE_URL;
      });
    },

    displaySubmissionErrorAlert(errorMessage) {
      Swal.fire({
        icon: 'warning',
        title: '<span style="color:#b85c00;font-weight:bold;">Submission Failed</span>',
        html: `<div style='font-size:1.1em;'>${errorMessage}</div>`,
        showConfirmButton: true,
        confirmButtonColor: '#b85c00',
        confirmButtonText: '<i class="fas fa-arrow-left me-2"></i>Back to Form',
        background: '#fff8e1',
        customClass: {
          popup: 'swal2-border-radius',
          title: 'swal2-title-custom',
          confirmButton: 'swal2-confirm-custom'
        }
      });
    },

    // ========================================================================
    // PAGE NAVIGATION CONTROLLER
    // ========================================================================
    async navigateToPage(navigationElement) {
      const currentPageNumber = Number($('.ballot-pages.active').attr('data-page'));
      const navigationDirection = $(navigationElement).attr('data-direction');
      const targetPageNumber = Number($(navigationElement).attr('data-btn-show-page'));
      const currentPageName = $('.ballot-pages.active').attr('data-page-name');

      let nextPageNumber = targetPageNumber + 1;
      let previousPageNumber = targetPageNumber - 1;

      if (navigationDirection === 'next') {
        switch (currentPageName) {
          case 'amendment':
            if (!this.validateAmendmentSelections()) return;
            break;

          case 'bod':
            if (!this.validateAgendaSelections() || !this.validateBoardOfDirectorVotes()) return;

            // Show loading overlay while generating summary
            this.displayLoadingOverlay();

            try {
              const summaryGenerated = await this.generateBallotSummary();
              if (summaryGenerated === false) {
                this.hideLoadingOverlay();
                return;
              }
            } catch (error) {
              this.hideLoadingOverlay();
              return;
            }
            break;

          case 'summary':
            this.displayFinalConfirmationDialog();
            return;
        }
      }

      // Only switch pages after successful validation/summary generation
      this.switchBallotPage(currentPageNumber, targetPageNumber);

      if (navigationDirection === 'next') {
        // Don't show loading overlay again if we're coming from BOD page (already shown during summary generation)
        if (currentPageName !== 'bod') {
          this.processNextPageTransition(previousPageNumber, nextPageNumber, BALLOT_CONFIG.totalPages);
        } else {
          // Hide the loading overlay that was shown during summary generation
          this.hideLoadingOverlay();

          // Update navigation buttons without showing loading overlay again
          $('[data-direction=back]').attr('disabled', previousPageNumber === 0);
          if (nextPageNumber > BALLOT_CONFIG.totalPages) {
            $('[data-direction=next]').text('Confirm and Submit');
          }
        }
      } else {
        this.processBackPageTransition(previousPageNumber, nextPageNumber, BALLOT_CONFIG.totalPages);
      }

      // Update navigation button attributes
      $('[data-direction=next]').attr('data-btn-show-page', nextPageNumber);
      $('[data-direction=back]').attr('data-btn-show-page', previousPageNumber);

      // Update button text if we're now on the summary page
      if (targetPageNumber === 3) { // Summary page
        $('[data-direction=next]').text('Confirm and Submit');
      } else if (navigationDirection === 'back') {
        $('[data-direction=next]').html('Continue <i class="fas fa-arrow-right ms-2"></i>');
      }

      // Smooth scroll to top of page
      $("html, body").animate({
        scrollTop: 0
      }, "slow");
    },

    displayFinalConfirmationDialog() {
      Swal.fire({
        title: 'Info',
        text: $('#btnSumbitBallot').attr('data-confirmation-message'),
        icon: 'info',
        showCancelButton: true,
        reverseButtons: true,
        cancelButtonColor: '#d33',
        confirmButtonColor: '#2F4A3C',
        cancelButtonText: 'Back',
        confirmButtonText: 'Confirm and Submit'
      }).then((userChoice) => {
        if (userChoice.isConfirmed) {
          this.submitBallotData();
        } else {
          $('#btnBack').click();
        }
      });
    },

    // ========================================================================
    // VOTE DATA COLLECTION
    // ========================================================================
    collectBoardOfDirectorVotes() {
      return this.getBodVote();
    },

    collectAmendmentVotes() {
      return this.getAmendmentVote();
    },

    collectAgendaVotes() {
      return this.getAgendaVote();
    },

    getBodVote() {
      const bodData = [];
      $('.bod-votes-table tbody tr').each(function() {
        const candidateId = $(this).attr('data-id');
        const vote = Number($(this).find('.bod-vote').val()) || 0;
        bodData.push({
          candidateId,
          vote
        });
      });
      return bodData;
    },

    getAmendmentVote() {
      if (!this.validAmendment()) {
        throw new Error("Unexpected error encountered. Please reload the page.");
      }

      const amendmentData = [];
      $('#amendmentTable tbody tr').each(function() {
        const amendmentId = $(this).attr('data-id');
        const yes = $(this).find('.btn-amendment.btn-yes').hasClass('btn-success-custom') ? 1 : 0;
        const no = $(this).find('.btn-amendment.btn-no').hasClass('btn-success-custom') ? 1 : 0;
        amendmentData.push({
          amendmentId,
          yes,
          no
        });
      });
      return amendmentData;
    },

    getAgendaVote() {
      if (!this.validAgenda()) {
        throw new Error("Unexpected error encountered. Please reload the page.");
      }

      const agendaData = [];
      $('#agendaTable tbody tr').each(function() {
        const agendaId = $(this).attr('data-id');
        const favor = $(this).find('.btn-agenda.btn-favor').hasClass('btn-success-custom') ? 1 : 0;
        const notFavor = $(this).find('.btn-agenda.btn-not-favor').hasClass('btn-success-custom') ? 1 : 0;
        const abstain = $(this).find('.btn-agenda.btn-abstain').hasClass('btn-success-custom') ? 1 : 0;
        agendaData.push({
          agendaId,
          favor,
          notFavor,
          abstain
        });
      });
      return agendaData;
    },

    validAmendment() {
      return this.validateAmendmentSelections();
    },

    validAgenda() {
      return this.validateAgendaSelections();
    },


    // ========================================================================
    // VALIDATION METHODS
    // ========================================================================
    // VALIDATION METHODS
    // ========================================================================
    validateBoardOfDirectorVotes() {
      const distributedVotes = Number($('#totalDistributedVotes').val());

      if (distributedVotes > BALLOT_CONFIG.availableBoardOfDirectorVotes) {
        Swal.fire({
          icon: 'error',
          title: 'Exceeded Votes',
          text: `The total distributed votes should not exceed ${BALLOT_CONFIG.availableBoardOfDirectorVotes.toLocaleString()} votes. Please adjust your votes to match your available total.`,
          showConfirmButton: true,
          confirmButtonColor: '#2F4A3C',
          confirmButtonText: 'Back',
          customClass: {
            popup: 'swal2-border-radius',
            title: 'swal2-title-custom',
            confirmButton: 'swal2-confirm-custom'
          }
        });
        return false;
      }
      return true;
    },

    validateAmendmentSelections() {
      let isValid = true;
      let counter = 1;

      $('#amendmentTable tbody tr').each(function() {
        if ($(this).find('.btn-success-custom').length === 0 && BALLOT_CONFIG.availableAmendmentVotes !== 0) {
          isValid = false;
          Swal.fire({
            icon: 'info',
            title: 'Required',
            confirmButtonColor: '#2F4A3C',
            confirmButtonText: 'OK',
            text: `Please select from the actions in the amendment number ${counter}.`
          });
          return false;
        }
        counter++;
      });

      return isValid;
    },

    validateAgendaSelections() {
      let isValid = true;
      let counter = 1;

      if (BALLOT_CONFIG.availableBoardOfDirectorVotes > 0) {
        $('#agendaTable tbody tr').each(function() {
          if ($(this).find('.btn-success-custom').length === 0) {
            isValid = false;
            Swal.fire({
              icon: 'error',
              title: 'Required',
              text: `Please select from the actions in the agenda number ${counter}.`
            });
            return false;
          }
          counter++;
        });
      }

      return isValid;
    },

    // ========================================================================
    // SUMMARY & DISPLAY METHODS
    // ========================================================================
    async generateBallotSummary() {
      try {
        const response = await $.ajax({
          url: BASE_URL + `user/ballot/stockholder-online/${BALLOT_CONFIG.ballotId}/summary`,
          method: 'POST',
          dataType: 'json',
          data: {
            ballotId: BALLOT_CONFIG.ballotId,
            amendment: this.collectAmendmentVotes(),
            bod: this.collectBoardOfDirectorVotes(),
            agenda: this.collectAgendaVotes()
          }
        });

        // Update summary information
        $('#summary-message').text(response.info);
        $('#totalDistributedVotesSummary').val(response.bodVotes);
        $('#totalDistributedVotesSummary-display').text(Number(response.bodVotes).toLocaleString());
        $('#unusedVotesSummary').val(response.unusedVotes);
        $('#unusedVotesSummary-display').text(Number(response.unusedVotes).toLocaleString());
        $('#btnSumbitBallot').attr('data-confirmation-id', response.confirmationId);
        $('#btnSumbitBallot').attr('data-confirmation-message', response.message);

        // Display summaries
        this.renderAmendmentSummary(response);
        this.renderAgendaSummary(response);
        this.renderCandidateSummary(response);

        return true;
      } catch (error) {
        const message = error.responseJSON?.message || 'An error occurred';
        Swal.fire({
          icon: 'warning',
          title: 'Info',
          text: message
        });
        throw error; // Re-throw the error so the calling function can handle it
      }
    },

    renderAmendmentSummary(data) {
      let amendments = '';
      let counter = 1;

      for (const amendment of data.amendment) {
        const yes = amendment.vote.yes == 1 ? 'btn-success-custom' : '';
        const no = amendment.vote.no == 1 ? 'btn-success-custom' : '';

        amendments += `<tr>
          <td class="counter td-amend">${counter}</td>
          <td class="amendment td-amend">${amendment.amendment}</td>
          <td class="text-center">
            <button type="button" class="btn border-success-custom btn-amendment disabled btn-yes ${yes}">Favor</button>
            <button type="button" class="btn border-success-custom btn-amendment disabled btn-no ${no}">Not in favor</button>
          </td>
        </tr>`;
        counter++;
      }

      $('#summaryAmendmentTable tbody').html(amendments);
    },

    renderAgendaSummary(data) {
      let counter = 1;
      let agendas = '';

      for (const agenda of data.agenda) {
        const favor = agenda.vote.favor == 1 ? 'btn-success-custom' : '';
        const notFavor = agenda.vote.notFavor == 1 ? 'btn-success-custom' : '';
        const abstain = agenda.vote.abstain == 1 ? 'btn-success-custom' : '';

        agendas += `<tr>
          <td class="counter td-amend">${counter}</td>
          <td class="amendment td-amend">${agenda.agenda}</td>
          <td class="text-center">
            <button type="button" class="btn border-success-custom disabled btn-amendment btn-favor ${favor}">Yes</button>
            <button type="button" class="btn border-success-custom disabled btn-amendment btn-not-favor ${notFavor}">No</button>
            <button type="button" class="btn border-success-custom disabled btn-amendment btn-abstain ${abstain}">Abstain</button>
          </td>
        </tr>`;
        counter++;
      }

      $('#agendaTableSummary tbody').html(agendas);
    },

    renderCandidateSummary(data) {
      let bods = '';
      let hasVotes = false;

      for (const bod of data.bod) {
        const votes = parseInt(bod.vote) || 0;
        if (votes === 0) continue;

        hasVotes = true;
        bods += `<tr class="td-amend">
          <td>
            <span class="font-weight-bold">${bod.name}</span><br>
            <small class="text-muted">${bod.type}</small>
          </td>
          <td class="text-center">${votes}</td>
        </tr>`;
      }

      if (!hasVotes) {
        bods = `<tr>
          <td colspan="2" class="text-center">
            <div class="no-votes-candidate-summary">
              <div class="no-votes-icon">
                <i class="fas fa-user-slash"></i>
              </div>
              <div class="no-votes-title">No Votes Distributed</div>
              <div class="no-votes-desc">You have not assigned any votes to Board of Director candidates.</div>
            </div>
          </td>
        </tr>`;
      }

      $('#candidateTableSummary tbody').html(bods);
    },


    // ========================================================================
    // BOARD OF DIRECTOR VOTE INPUT HANDLERS
    // ========================================================================
    onBoardOfDirectorVoteChanged(e) {
      StockholderVotingSystem.recalculateVoteTotals();
    },

    onBoardOfDirectorVoteInput(e) {
      const inputElement = $(this);
      const enteredValue = parseFloat(inputElement.val());

      // Remove invalid class first
      inputElement.removeClass('is-invalid is-valid');
      inputElement.siblings('.invalid-feedback').remove();

      // Check for negative values
      if (enteredValue < 0 || inputElement.val().includes('-')) {
        inputElement.val('');
        inputElement.addClass('is-invalid');
        inputElement.after('<div class="invalid-feedback">Negative values are not allowed</div>');
        return;
      }

      // Check for decimal values
      if (enteredValue % 1 !== 0 && !isNaN(enteredValue)) {
        inputElement.val(Math.floor(enteredValue));
        inputElement.addClass('is-invalid');
        inputElement.after('<div class="invalid-feedback">Only whole numbers are allowed</div>');
        setTimeout(() => {
          inputElement.removeClass('is-invalid');
          inputElement.siblings('.invalid-feedback').remove();
        }, 2000);
        return;
      }

      // Validate against available votes
      let totalDistributedVotes = 0;
      $('.bod-votes-table tbody .bod-vote').each(function() {
        const voteValue = Number($(this).val()) || 0;
        totalDistributedVotes += voteValue;
      });

      if (totalDistributedVotes > BALLOT_CONFIG.availableBoardOfDirectorVotes) {
        //inputElement.addClass('is-invalid');
        //inputElement.after('<div class="invalid-feedback">Exceeds available votes</div>');
      } else if (enteredValue >= 0 && enteredValue % 1 === 0) {
        //inputElement.addClass('is-valid');
      }

      // Update totals
      StockholderVotingSystem.recalculateVoteTotals();
    },

    onBoardOfDirectorVoteKeyPressed(e) {
      // Prevent minus sign
      if (e.which === 45) {
        e.preventDefault();
        return false;
      }

      // Allow only numbers
      if (e.which < 48 || e.which > 57) {
        if (e.which !== 8 && e.which !== 0 && e.which !== 13) {
          e.preventDefault();
          return false;
        }
      }
    },

    onBoardOfDirectorVotePasted(e) {
      setTimeout(() => {
        const inputElement = $(this);
        const pastedValue = parseFloat(inputElement.val());

        if (pastedValue < 0 || inputElement.val().includes('-')) {
          inputElement.val('');
          inputElement.addClass('is-invalid');
          inputElement.after('<div class="invalid-feedback">Negative values are not allowed</div>');
        }
      }, 10);
    },

    recalculateVoteTotals() {
      let totalDistributedVotes = 0;

      $('.bod-votes-table tbody .bod-vote').each(function() {
        const voteValue = Number($(this).val()) || 0;
        totalDistributedVotes += voteValue;
      });

      const unusedVotes = Math.max(BALLOT_CONFIG.availableBoardOfDirectorVotes - totalDistributedVotes, 0);

      // Update hidden inputs
      $('#totalDistributedVotes').val(totalDistributedVotes);
      $('#unusedVotes').val(unusedVotes);

      // Update display elements
      $('#totalDistributedVotes-display').text(totalDistributedVotes.toLocaleString());
      $('#unusedVotes-display').text(unusedVotes.toLocaleString());

      // Update styling based on total
      const distributedCard = $('#totalDistributedVotes-display').closest('.info-card');
      const unusedCard = $('#unusedVotes-display').closest('.info-card');

      if (totalDistributedVotes > BALLOT_CONFIG.availableBoardOfDirectorVotes) {
        distributedCard.css('border-color', '#dc3545');
        unusedCard.css('border-color', '#dc3545');
      } else {
        distributedCard.css('border-color', '#28a745');
        unusedCard.css('border-color', '#28a745');
      }
    }
  };

  // ============================================================================
  // GLOBAL FUNCTION WRAPPERS (for backward compatibility)
  // ============================================================================
  function togglePageView(currentPageNo, showPageNo) {
    return StockholderVotingSystem.switchBallotPage(currentPageNo, showPageNo);
  }

  function handleNextButton(prevPage, nextPage, ballotPageCount) {
    return StockholderVotingSystem.processNextPageTransition(prevPage, nextPage, ballotPageCount);
  }

  function handleBackButton(prevPage, nextPage, ballotPageCount) {
    return StockholderVotingSystem.processBackPageTransition(prevPage, nextPage, ballotPageCount);
  }

  function submitBallot() {
    return StockholderVotingSystem.submitBallotData();
  }

  function handlePage(element) {
    // Handle async navigation
    StockholderVotingSystem.navigateToPage(element).catch(error => {
      console.error('Navigation error:', error);
    });
  }

  function getBodVote() {
    return StockholderVotingSystem.collectBoardOfDirectorVotes();
  }

  function validBod() {
    return StockholderVotingSystem.validateBoardOfDirectorVotes();
  }

  function getAmendmentVote() {
    return StockholderVotingSystem.collectAmendmentVotes();
  }

  function getAgendaVote() {
    return StockholderVotingSystem.collectAgendaVotes();
  }

  function validAmendment() {
    return StockholderVotingSystem.validateAmendmentSelections();
  }

  function validAgenda() {
    return StockholderVotingSystem.validateAgendaSelections();
  }

  function requestSummary() {
    return StockholderVotingSystem.generateBallotSummary().catch(error => {
      console.error('Summary generation error:', error);
      return false;
    });
  }

  function displayAmendmentSummary(data) {
    return StockholderVotingSystem.renderAmendmentSummary(data);
  }

  function displayAgendaSummary(data) {
    return StockholderVotingSystem.renderAgendaSummary(data);
  }

  function displayCandidateSummary(data) {
    return StockholderVotingSystem.renderCandidateSummary(data);
  }

  function updateVoteTotals() {
    return StockholderVotingSystem.recalculateVoteTotals();
  }

  // ============================================================================
  // APPLICATION INITIALIZATION
  // ============================================================================
  $(document).ready(function() {
    StockholderVotingSystem.initialize();
  });
</script>

@endsection