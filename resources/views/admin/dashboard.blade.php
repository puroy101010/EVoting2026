@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
<style>
  .dashboard-header {
    background: #6b8e4e;
    color: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(107, 142, 78, 0.2);
  }

  .dashboard-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
  }

  .dashboard-title i {
    color: rgba(255, 255, 255, 0.9);
    margin-right: 0.75rem;
  }

  .welcome-text {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
    margin: 0.5rem 0 0 0;
  }

  .compact-card {
    background: white;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
    height: 100%;
  }

  .compact-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  .compact-card.clickable {
    cursor: pointer;
  }

  .card-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
    color: white;
  }

  .card-icon.primary {
    background: #6b8e4e;
  }

  .card-icon.info {
    background: #17a2b8;
  }

  .card-icon.warning {
    background: #ffc107;
  }

  .card-icon.danger {
    background: #dc3545;
  }

  .card-title {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .card-number {
    font-size: 1.5rem;
    font-weight: 600;
    color: #495057;
    line-height: 1;
  }

  .section-divider {
    background: #6b8e4e;
    color: white;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    margin: 1.5rem 0 1rem 0;
    box-shadow: 0 2px 8px rgba(107, 142, 78, 0.2);
  }

  .section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
  }

  .section-title i {
    color: rgba(255, 255, 255, 0.9);
    margin-right: 0.5rem;
  }

  .compact-modal .modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  }

  .compact-modal .modal-header {
    background: #6b8e4e;
    color: white;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 1rem 1.5rem;
  }

  .compact-modal .modal-title {
    font-weight: 600;
    font-size: 1.1rem;
  }

  .compact-modal .close {
    color: white;
    opacity: 0.8;
  }

  .compact-modal .close:hover {
    opacity: 1;
  }

  .compact-table {
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin: 0;
  }

  .compact-table th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 0.75rem;
    border: none;
    font-size: 0.9rem;
  }

  .compact-table td {
    padding: 0.75rem;
    border-top: 1px solid #dee2e6;
    font-size: 0.9rem;
  }

  .compact-badge {
    padding: 0.3rem 0.6rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 500;
  }

  .compact-badge.warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
  }
</style>
@endsection

@section('content')
<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Auth;
?>

<div class="content-wrapper">
  <section class="content">
    <div class="container-fluid">
      <!-- Dashboard Header -->
      <div class="dashboard-header">
        <h1 class="dashboard-title">
          <i class="fas fa-tachometer-alt"></i>
          Dashboard Overview
        </h1>
        <p class="welcome-text">Welcome back, {{ Auth::user()->firstName }}!</p>
      </div>

      <!-- Statistics Cards -->
      <div class="row">
        <!-- Stockholders Card -->
        <div class="col-sm-6 col-lg-3">
          <div class="compact-card">
            <div class="card-icon primary">
              <i class="fa-solid fa-people-roof"></i>
            </div>
            <div class="card-title">Stockholders</div>
            <div class="card-number">{{ number_format($stockholders, 0) }}</div>
          </div>
        </div>

        <!-- Total Stocks Card -->
        <div class="col-sm-6 col-lg-3">
          <div class="compact-card">
            <div class="card-icon info">
              <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
            <div class="card-title">Total Stocks</div>
            <div class="card-number">{{ number_format($totalStocks, 0) }}</div>
          </div>
        </div>

        <!-- Active Stocks Card -->
        <div class="col-sm-6 col-lg-3">
          <div class="compact-card">
            <div class="card-icon primary">
              <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="card-title">Active Stocks</div>
            <div class="card-number">{{ number_format($activeStocks, 0) }}</div>
          </div>
        </div>

        <!-- Delinquent Card -->
        <div class="col-sm-6 col-lg-3">
          <div class="compact-card">
            <div class="card-icon warning">
              <i class="fa-solid fa-scale-unbalanced"></i>
            </div>
            <div class="card-title">Delinquent</div>
            <div class="card-number">{{ $delinquentStocks }}</div>
          </div>
        </div>

        <!-- Candidates Card -->
        <div class="col-sm-6 col-lg-3">
          <div class="compact-card">
            <div class="card-icon primary">
              <i class="fa-solid fa-people-group"></i>
            </div>
            <div class="card-title">Candidates</div>
            <div class="card-number">{{ $candidate }}</div>
          </div>
        </div>

        <?php if (Auth::user()->hasRole('superadmin') or Auth::user()->hasRole('admin')) { ?>
          <!-- BOD Proxy Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-file-contract"></i>
              </div>
              <div class="card-title">BOD Proxy</div>
              <div class="card-number">{{ $proxyBod }}</div>
            </div>
          </div>

          <!-- Amendment Proxy Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-file-contract"></i>
              </div>
              <div class="card-title">Amendment Proxy</div>
              <div class="card-number">{{ $proxyAmendment }}</div>
            </div>
          </div>



          <!-- Amendment Proxy Quorum Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-file-contract"></i>
              </div>
              <div class="card-title">BOD Quorum Proxy</div>
              <div class="card-number">{{ $bodQuorumCount }}</div>
            </div>
          </div>



          <!-- Amendment Proxy Quorum Card -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-file-contract"></i>
              </div>
              <div class="card-title">Amendment Quorum Proxy</div>
              <div class="card-number">{{ $amendmentQuorumCount }}</div>
            </div>
          </div>


          <!-- Stockholder Online Attendance -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-file-contract"></i>
              </div>
              <div class="card-title">Stockholder Online Attendance</div>
              <div class="card-number">{{ $stockholderOnlineAttendance }}</div>
            </div>
          </div>


          <!-- Proxy Voting Attendance -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-file-contract"></i>
              </div>
              <div class="card-title">Proxy Voting Attendance</div>
              <div class="card-number">{{ $proxyVotingAttendance }}</div>
            </div>
          </div>




        <?php } ?>
      </div>

      <?php if (Auth::user()->hasRole('superadmin')) { ?>
        <!-- Voting Statistics Section -->
        <div class="section-divider">
          <div class="section-title">
            <i class="fas fa-vote-yea"></i>
            Voting Statistics
          </div>
        </div>

        <div class="row">
          <!-- Stockholder Ballot -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon primary">
                <i class="fa-solid fa-ballot-check"></i>
              </div>
              <div class="card-title">Stockholder Ballot</div>
              <div class="card-number">{{ $stockholderBallot }}</div>
            </div>
          </div>

          <!-- Proxy Ballot -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon primary">
                <i class="fa-solid fa-file-contract"></i>
              </div>
              <div class="card-title">Proxy Ballot</div>
              <div class="card-number">{{ $proxyBallot }}</div>
            </div>
          </div>

          <!-- Used BOD Accounts -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-user-check"></i>
              </div>
              <div class="card-title">Used BOD Accounts</div>
              <div class="card-number">{{ $usedBod }}</div>
            </div>
          </div>

          <!-- Used Amendment Accounts -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon info">
                <i class="fa-solid fa-user-check"></i>
              </div>
              <div class="card-title">Used Amendment Accounts</div>
              <div class="card-number">{{ $usedAmendment }}</div>
            </div>
          </div>

          <!-- Revoked BODs -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon danger">
                <i class="fa-solid fa-user-times"></i>
              </div>
              <div class="card-title">Revoked BODs</div>
              <div class="card-number">{{ $revokedBod }}</div>
            </div>
          </div>

          <!-- Revoked Amendments -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card">
              <div class="card-icon danger">
                <i class="fa-solid fa-user-times"></i>
              </div>
              <div class="card-title">Revoked Amendments</div>
              <div class="card-number">{{ $revokedAmendment }}</div>
            </div>
          </div>

          <!-- Unused Votes Online -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card clickable" data-toggle="modal" data-target="#unusedVotesOnlineModal">
              <div class="card-icon warning">
                <i class="fa-solid fa-exclamation-triangle"></i>
              </div>
              <div class="card-title">Unused Votes Online</div>
              <div class="card-number">{{ $unusedVotesOnline }}</div>
            </div>
          </div>

          <!-- Unused Votes Proxy -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card clickable" data-toggle="modal" data-target="#unusedVotesProxyModal">
              <div class="card-icon warning">
                <i class="fa-solid fa-exclamation-triangle"></i>
              </div>
              <div class="card-title">Unused Votes Proxy</div>
              <div class="card-number">{{ $unusedVotesProxy }}</div>
            </div>
          </div>

          <!-- UV w/o BOD Proxy -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card clickable" data-toggle="modal" data-target="#unusedVotesWithoutBodProxy">
              <div class="card-icon warning">
                <i class="fa-solid fa-exclamation-circle"></i>
              </div>
              <div class="card-title">UV w/o BOD Proxy</div>
              <div class="card-number">{{ $unusedVoteWithoutBodProxy }}</div>
            </div>
          </div>

          <!-- UV w/o Amendment Proxy -->
          <div class="col-sm-6 col-lg-3">
            <div class="compact-card clickable" data-toggle="modal" data-target="#unusedVotesWithoutAmendmentProxy">
              <div class="card-icon warning">
                <i class="fa-solid fa-exclamation-circle"></i>
              </div>
              <div class="card-title">UV w/o Amendment Proxy</div>
              <div class="card-number">{{ $unusedVoteWithoutAmendmentProxy }}</div>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </section>
</div>

<!-- Unused Votes Online Modal -->
<div class="modal fade compact-modal" id="unusedVotesOnlineModal" tabindex="-1" role="dialog" aria-labelledby="unusedVotesOnlineModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="unusedVotesOnlineModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Unused Votes Online Details
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table compact-table">
          <thead>
            <tr>
              <th>Ballot No</th>
              <th>Unused Votes</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($unusedVotesOnlineList as $unusedVotes) {
              echo '<tr>
                  <td><a href="' . asset('admin/ballots/preview') . '/' . $unusedVotes->ballotId . '" class="text-decoration-none">' . $unusedVotes->ballotNo . '</a></td>
                  <td><span class="compact-badge warning">' . $unusedVotes->unusedVotesBod . '</span></td>
                </tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Unused Votes Proxy Modal -->
<div class="modal fade compact-modal" id="unusedVotesProxyModal" tabindex="-1" role="dialog" aria-labelledby="unusedVotesProxyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="unusedVotesProxyModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Unused Votes Proxy Details
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table compact-table">
          <thead>
            <tr>
              <th>Ballot No</th>
              <th>Unused Votes</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($unusedVotesProxyList as $unusedVotes) {
              echo '<tr>
                        <td><a href="' . asset('admin/ballots/preview') . '/' . $unusedVotes->ballotId . '" class="text-decoration-none">' . $unusedVotes->ballotNo . '</a></td>
                        <td><span class="compact-badge warning">' . $unusedVotes->unusedVotesBod . '</span></td>
                      </tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('.admin-nav-dashboard').addClass('active');
  })
</script>

@endsection