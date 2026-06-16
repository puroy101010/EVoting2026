@extends('layouts.admin')

@section('css')
<style>
  /* Apple-inspired Settings Design */
  :root {
    --apple-blue: #007AFF;
    --apple-gray: #F2F2F7;
    --apple-gray-2: #E5E5EA;
    --apple-gray-3: #D1D1D6;
    --apple-gray-4: #C7C7CC;
    --apple-gray-5: #AEAEB2;
    --apple-gray-6: #8E8E93;
    --apple-text: #1D1D1F;
    --apple-text-secondary: #6D6D70;
    --apple-border-radius: 12px;
    --apple-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  }

  .content-wrapper {
    background: var(--apple-gray) !important;
    min-height: 100vh;
  }

  .settings-container {
    background: var(--apple-gray);
    min-height: calc(100vh - 60px);
    padding: 15px;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
  }

  .settings-header {
    background: white;
    border-radius: var(--apple-border-radius);
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: var(--apple-shadow);
    border: none;
    width: 100%;
  }

  .settings-title {
    font-size: 28px;
    font-weight: 600;
    color: var(--apple-text);
    margin: 0;
    letter-spacing: -0.3px;
  }

  .settings-subtitle {
    color: var(--apple-text-secondary);
    font-size: 15px;
    margin-top: 4px;
    font-weight: 400;
  }

  .settings-section {
    background: white;
    border-radius: var(--apple-border-radius);
    margin-bottom: 16px;
    overflow: visible;
    box-shadow: var(--apple-shadow);
    border: none;
    width: 100%;
  }

  .section-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--apple-gray-2);
    background: #FBFBFD;
  }

  .section-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--apple-text);
    margin: 0;
    letter-spacing: -0.3px;
  }

  .section-description {
    color: var(--apple-text-secondary);
    font-size: 14px;
    margin-top: 4px;
  }

  .setting-item {
    padding: 16px 20px;
    border-bottom: 1px solid var(--apple-gray-2);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    transition: background-color 0.2s ease;
    flex-wrap: wrap;
    gap: 12px;
  }

  .setting-item:last-child {
    border-bottom: none;
  }

  .setting-item:hover {
    background-color: var(--apple-gray);
  }

  .setting-label {
    flex: 1;
    min-width: 250px;
    margin-right: 16px;
  }

  .setting-title {
    font-size: 16px;
    font-weight: 500;
    color: var(--apple-text);
    margin: 0 0 2px 0;
  }

  .setting-subtitle {
    font-size: 14px;
    color: var(--apple-text-secondary);
    margin: 0;
    line-height: 1.4;
  }

  .setting-control {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
    flex-wrap: wrap;
  }

  .apple-input {
    border: 1px solid var(--apple-gray-3);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 16px;
    background: white;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    min-width: 200px;
  }

  .apple-input:focus {
    outline: none;
    border-color: var(--apple-blue);
    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
  }

  .apple-button {
    background: var(--apple-blue);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  .apple-button:hover {
    background: #0056CC;
  }

  .apple-button.secondary {
    background: var(--apple-gray-2);
    color: var(--apple-text);
  }

  .apple-button.secondary:hover {
    background: var(--apple-gray-3);
  }

  .apple-button.danger {
    background: #FF3B30;
  }

  .apple-button.danger:hover {
    background: #D70015;
  }

  .apple-switch {
    position: relative;
    width: 50px;
    height: 30px;
    background: var(--apple-gray-4);
    border-radius: 15px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .apple-switch.active {
    background: var(--apple-blue);
  }

  .apple-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 26px;
    height: 26px;
    background: white;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }

  .apple-switch.active::after {
    transform: translateX(20px);
  }

  .status-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
  }

  .status-active {
    background: #30D158;
  }

  .status-inactive {
    background: var(--apple-gray-5);
  }

  .date-display {
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    font-size: 14px;
    color: var(--apple-text-secondary);
    background: var(--apple-gray);
    padding: 4px 8px;
    border-radius: 4px;
  }

  .breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 0;
  }

  .breadcrumb-item a {
    color: var(--apple-blue);
    text-decoration: none;
  }

  .breadcrumb-item.active {
    color: var(--apple-text-secondary);
  }

  .calendar-editor {
    min-height: 200px;
    border: 1px solid var(--apple-gray-3);
    border-radius: 8px;
    padding: 16px;
  }

  .votes-per-share-input {
    width: 80px;
    text-align: center;
  }

  /* Terms Preview Styling */
  .terms-preview-container {
    width: 100%;
    background: white;
    border: 1px solid var(--apple-gray-3);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }

  .terms-preview-header {
    background: linear-gradient(135deg, #f8fafe 0%, #f0f4ff 100%);
    padding: 16px 20px;
    border-bottom: 1px solid var(--apple-gray-2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
  }

  .terms-preview-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--apple-text);
    margin: 0;
  }

  .terms-status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .terms-status-badge.configured {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .terms-status-badge.not-configured {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .terms-preview-content {
    padding: 20px;
    max-height: 300px;
    overflow-y: auto;
    line-height: 1.6;
    font-size: 14px;
    color: var(--apple-text);
  }

  .terms-preview-content h1,
  .terms-preview-content h2,
  .terms-preview-content h3,
  .terms-preview-content h4,
  .terms-preview-content h5,
  .terms-preview-content h6 {
    margin-top: 0;
    margin-bottom: 12px;
    color: var(--apple-text);
  }

  .terms-preview-content p {
    margin-bottom: 12px;
  }

  .terms-preview-content ul,
  .terms-preview-content ol {
    margin-bottom: 12px;
    padding-left: 20px;
  }

  .no-content {
    color: var(--apple-text-secondary);
    font-style: italic;
    text-align: center;
    padding: 40px 20px;
    background: var(--apple-gray);
    border-radius: 8px;
    margin: 0;
  }

  /* Button icons */
  .apple-button i {
    font-size: 14px;
    vertical-align: middle;
  }

  /* Responsive improvements */
  @media (max-width: 768px) {
    .terms-preview-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }

    .terms-preview-content {
      padding: 16px;
      max-height: 250px;
    }

    .setting-item .terms-preview-container {
      margin-top: 12px;
    }
  }

  /* Custom scrollbar for preview content */
  .terms-preview-content::-webkit-scrollbar {
    width: 6px;
  }

  .terms-preview-content::-webkit-scrollbar-track {
    background: var(--apple-gray-2);
    border-radius: 3px;
  }

  .terms-preview-content::-webkit-scrollbar-thumb {
    background: var(--apple-gray-5);
    border-radius: 3px;
  }

  .terms-preview-content::-webkit-scrollbar-thumb:hover {
    background: var(--apple-gray-6);
  }

  /* Responsive styles */
  @media (max-width: 768px) {
    .container-fluid {
      padding: 10px;
    }

    .card-apple {
      margin-bottom: 16px;
    }

    .setting-item {
      flex-direction: column;
      align-items: flex-start;
      padding: 16px;
    }

    .setting-label {
      margin-right: 0;
      margin-bottom: 12px;
      min-width: 100%;
    }

    .setting-control {
      width: 100%;
      justify-content: flex-start;
    }

    .apple-input {
      min-width: 150px;
      width: 100%;
      max-width: 300px;
    }
  }

  @media (max-width: 480px) {
    .header-apple h1 {
      font-size: 28px;
    }

    .setting-item {
      padding: 12px;
    }

    .apple-button {
      padding: 10px 16px;
      font-size: 16px;
      width: 100%;
      margin-top: 8px;
    }

    .setting-control {
      flex-direction: column;
      align-items: flex-start;
    }
  }

  /* Utility classes */
  .gap-2 {
    gap: 0.5rem;
  }

  .w-100 {
    width: 100%;
  }

  .d-flex {
    display: flex;
  }

  .align-items-center {
    align-items: center;
  }

  .flex-wrap {
    flex-wrap: wrap;
  }

  .mb-3 {
    margin-bottom: 1rem;
  }

  /* Enhanced date display */
  .date-display {
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    font-size: 13px;
    color: var(--apple-text-secondary);
    background: var(--apple-gray);
    padding: 6px 10px;
    border-radius: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 300px;
    flex-shrink: 1;
  }

  @media (max-width: 768px) {
    .date-display {
      white-space: normal;
      word-break: break-word;
      font-size: 12px;
      max-width: 100%;
      margin-bottom: 8px;
    }

    .d-flex.flex-wrap.gap-2 {
      flex-direction: column;
      align-items: flex-start;
    }
  }

  /* Preview Modal Styling */
  .preview-content {
    padding: 20px;
    line-height: 1.6;
    font-size: 14px;
    color: var(--apple-text);
    max-height: 70vh;
    overflow-y: auto;
  }

  .preview-content h1,
  .preview-content h2,
  .preview-content h3,
  .preview-content h4,
  .preview-content h5,
  .preview-content h6 {
    margin-top: 0;
    margin-bottom: 16px;
    color: var(--apple-text);
  }

  .preview-content p {
    margin-bottom: 16px;
  }

  .preview-content ul,
  .preview-content ol {
    margin-bottom: 16px;
    padding-left: 24px;
  }

  .preview-content li {
    margin-bottom: 8px;
  }

  .modal-header .terms-status-badge {
    margin-left: auto;
    margin-right: 16px;
  }

  /* Button group styling */
  .setting-control .apple-button {
    margin-left: 8px;
  }

  .setting-control .apple-button:first-child {
    margin-left: 0;
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="settings-container">
  <!-- Breadcrumb -->
  <div class="container-fluid mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Settings</li>
      </ol>
    </nav>
  </div>

  <div class="container-fluid">
    <!-- Header -->
    <div class="settings-header">
      <h1 class="settings-title">Settings</h1>
      <p class="settings-subtitle">Configure voting periods, system preferences, and voting rules</p>
    </div>

    <!-- Voting Configuration -->
    <div class="settings-section">
      <div class="section-header">
        <h2 class="section-title">Voting Configuration</h2>
        <p class="section-description">Configure voting rules and share allocations</p>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Votes Per Share</h3>
          <p class="setting-subtitle">Number of votes allocated per share owned</p>
        </div>
        <div class="setting-control">
          <input type="number"
            class="apple-input votes-per-share-input"
            id="votesPerShare"
            value="{{ $config['votes_per_share'] ?? 1 }}"
            min="1"
            max="10">
          <button class="apple-button" onclick="updateVotesPerShare()">Update</button>
        </div>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Amendment Module</h3>
          <p class="setting-subtitle">Enable or disable the amendment voting functionality</p>
        </div>
        <div class="setting-control">
          <button class="apple-switch {{ ($config['amendment_enabled'] ?? true) ? 'active' : '' }}"
            id="amendmentModuleToggle"
            onclick="toggleAmendmentModule()">
          </button>
          <span class="setting-subtitle" id="amendmentModuleStatus">
            {{ ($config['amendment_enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Board of Director Module</h3>
          <p class="setting-subtitle">Enable or disable the Board of Director voting functionality</p>
        </div>
        <div class="setting-control">
          <button class="apple-switch {{ ($config['bod_module_enabled'] ?? true) ? 'active' : '' }}"
            id="bodModuleToggle"
            onclick="toggleBodModule()">
          </button>
          <span class="setting-subtitle" id="bodModuleStatus">
            {{ ($config['bod_module_enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Send Voting Confirmation Receipt</h3>
          <p class="setting-subtitle">Send confirmation receipt to users after they cast their vote</p>
        </div>
        <div class="setting-control">
          <button class="apple-switch {{ ($config['send_voting_confirmation_receipt_enabled'] ?? true) ? 'active' : '' }}"
            id="votingReceiptToggle"
            onclick="toggleVotingReceipt()">
          </button>
          <span class="setting-subtitle" id="votingReceiptStatus">
            {{ ($config['send_voting_confirmation_receipt_enabled'] ?? true) ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">OTP Login</h3>
          <p class="setting-subtitle">Enable one-time password login for enhanced security</p>
        </div>
        <div class="setting-control">
          <button class="apple-switch {{ ($config['otp_login_enabled'] ?? false) ? 'active' : '' }}"
            id="otpLoginToggle"
            onclick="toggleOtpLogin()">
          </button>
          <span class="setting-subtitle" id="otpLoginStatus">
            {{ ($config['otp_login_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Restrict Amendment to General Manager</h3>
          <p class="setting-subtitle">When enabled, only General Managers can propose or modify amendments</p>
        </div>
        <div class="setting-control">
          <button class="apple-switch {{ ($config['amendment_restricted_to_gm'] ?? false) ? 'active' : '' }}"
            id="amendmentRestrictedToggle"
            onclick="toggleAmendmentRestriction()">
          </button>
          <span class="setting-subtitle" id="amendmentRestrictedStatus">
            {{ ($config['amendment_restricted_to_gm'] ?? false) ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Stockholder Online Voting -->
    <div class="settings-section">
      <div class="section-header">
        <h2 class="section-title">Stockholder Online Voting</h2>
        <p class="section-description">Configure online voting periods for stockholders</p>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Voting Period</h3>
          <p class="setting-subtitle">Set start and end dates for online voting</p>
        </div>
        <div class="setting-control">
          @if(!empty($config["vote_in_person_start"]))
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="status-indicator status-active"></span>
            <span class="date-display">
              {{ date('M j, Y g:i A', strtotime($config["vote_in_person_start"])) }} -
              {{ date('M j, Y g:i A', strtotime($config["vote_in_person_end"])) }}
            </span>
            <button class="apple-button danger" onclick="removeDateSetting('vote_in_person')">Remove</button>
          </div>
          @else
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="status-indicator status-inactive"></span>
            <span class="setting-subtitle">Not configured</span>
            <button class="apple-button" onclick="showDateModal('vote_in_person')">Configure</button>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Proxy Voting -->
    <div class="settings-section">
      <div class="section-header">
        <h2 class="section-title">Proxy Voting</h2>
        <p class="section-description">Configure proxy voting periods and rules</p>
      </div>

      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Proxy Voting Period</h3>
          <p class="setting-subtitle">Set start and end dates for proxy voting</p>
        </div>
        <div class="setting-control">
          @if(!empty($config["vote_by_proxy_start"]))
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="status-indicator status-active"></span>
            <span class="date-display">
              {{ date('M j, Y g:i A', strtotime($config["vote_by_proxy_start"])) }} -
              {{ date('M j, Y g:i A', strtotime($config["vote_by_proxy_end"])) }}
            </span>
            <button class="apple-button danger" onclick="removeDateSetting('vote_by_proxy')">Remove</button>
          </div>
          @else
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="status-indicator status-inactive"></span>
            <span class="setting-subtitle">Not configured</span>
            <button class="apple-button" onclick="showDateModal('vote_by_proxy')">Configure</button>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Calendar of Activities -->
    <div class="settings-section">
      <div class="section-header">
        <h2 class="section-title">Message Settings</h2>
        <p class="section-description">Manage terms and conditions for different voting methods</p>
      </div>

      <!-- Online Voting Terms -->
      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Online Voting Terms & Conditions</h3>
          <p class="setting-subtitle">Terms displayed to stockholders voting online</p>
        </div>
        <div class="setting-control">
          <button class="apple-button secondary" onclick="showPreviewModal('online')">
            <i class="fas fa-eye" style="margin-right: 8px;"></i>
            Preview
          </button>
          <button class="apple-button" onclick="showTermsModal('online')">
            <i class="fas fa-edit" style="margin-right: 8px;"></i>
            Edit
          </button>
        </div>
      </div>

      <!-- Proxy Voting Terms -->
      <div class="setting-item">
        <div class="setting-label">
          <h3 class="setting-title">Proxy Voting Terms & Conditions</h3>
          <p class="setting-subtitle">Terms displayed to proxy voters</p>
        </div>
        <div class="setting-control">
          <button class="apple-button secondary" onclick="showPreviewModal('proxy')">
            <i class="fas fa-eye" style="margin-right: 8px;"></i>
            Preview
          </button>
          <button class="apple-button" onclick="showTermsModal('proxy')">
            <i class="fas fa-edit" style="margin-right: 8px;"></i>
            Edit
          </button>
        </div>
      </div>


    </div>
  </div>
</div>

<!-- Date Configuration Modal -->
<div class="modal fade" id="dateConfigModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: var(--apple-border-radius); border: none;">
      <div class="modal-header" style="border-bottom: 1px solid var(--apple-gray-2);">
        <h5 class="modal-title" id="dateModalTitle">Configure Voting Period</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="dateConfigForm">
          <input type="hidden" id="formType" name="form">
          <div class="form-group">
            <label for="startDateTime" class="setting-title">Start Date & Time</label>
            <input type="datetime-local" class="apple-input w-100" id="startDateTime" name="start_date_time" required>
          </div>
          <div class="form-group">
            <label for="endDateTime" class="setting-title">End Date & Time</label>
            <input type="datetime-local" class="apple-input w-100" id="endDateTime" name="end_date_time" required>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="border-top: 1px solid var(--apple-gray-2);">
        <button type="button" class="apple-button secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="apple-button" onclick="submitDateConfig()">Update</button>
      </div>
    </div>
  </div>
</div>


<!-- Online Terms Editor Modal -->
<div class="modal fade" id="termsOnlineModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" style="border-radius: var(--apple-border-radius); border: none;">
      <div class="modal-header" style="border-bottom: 1px solid var(--apple-gray-2);">
        <h5 class="modal-title">Edit Online Voting Terms and Conditions</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div id="onlineTermsEditor" class="calendar-editor">
          {!! $config['terms_and_conditions_online'] ?? '' !!}
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid var(--apple-gray-2);">
        <button type="button" class="apple-button secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="apple-button" onclick="updateTerms('online')">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Proxy Terms Editor Modal -->
<div class="modal fade" id="termsProxyModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" style="border-radius: var(--apple-border-radius); border: none;">
      <div class="modal-header" style="border-bottom: 1px solid var(--apple-gray-2);">
        <h5 class="modal-title">Edit Proxy Voting Terms and Conditions</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div id="proxyTermsEditor" class="calendar-editor">
          {!! $config['terms_and_conditions_proxy'] ?? '' !!}
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid var(--apple-gray-2);">
        <button type="button" class="apple-button secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="apple-button" onclick="updateTerms('proxy')">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Preview Modals -->
<!-- Online Terms Preview Modal -->
<div class="modal fade" id="previewOnlineModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" style="border-radius: var(--apple-border-radius); border: none;">
      <div class="modal-header" style="border-bottom: 1px solid var(--apple-gray-2);">
        <h5 class="modal-title">
          <i class="fas fa-eye" style="margin-right: 8px;"></i>
          Online Voting Terms & Conditions Preview
        </h5>
        <span class="terms-status-badge {{ !empty($config['terms_and_conditions_online']) ? 'configured' : 'not-configured' }}">
          {{ !empty($config['terms_and_conditions_online']) ? 'Configured' : 'Not Configured' }}
        </span>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="preview-content">
          {!! $config['terms_and_conditions_online'] ?? '<p class="no-content">No terms configured yet. Click "Edit" to add content.</p>' !!}
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid var(--apple-gray-2);">
        <button type="button" class="apple-button secondary" data-dismiss="modal">Close</button>
        <button type="button" class="apple-button" onclick="$('#previewOnlineModal').modal('hide'); showTermsModal('online');">
          <i class="fas fa-edit" style="margin-right: 8px;"></i>
          Edit Terms
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Proxy Terms Preview Modal -->
<div class="modal fade" id="previewProxyModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content" style="border-radius: var(--apple-border-radius); border: none;">
      <div class="modal-header" style="border-bottom: 1px solid var(--apple-gray-2);">
        <h5 class="modal-title">
          <i class="fas fa-eye" style="margin-right: 8px;"></i>
          Proxy Voting Terms & Conditions Preview
        </h5>
        <span class="terms-status-badge {{ !empty($config['terms_and_conditions_proxy']) ? 'configured' : 'not-configured' }}">
          {{ !empty($config['terms_and_conditions_proxy']) ? 'Configured' : 'Not Configured' }}
        </span>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="preview-content">
          {!! $config['terms_and_conditions_proxy'] ?? '<p class="no-content">No terms configured yet. Click "Edit" to add content.</p>' !!}
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid var(--apple-gray-2);">
        <button type="button" class="apple-button secondary" data-dismiss="modal">Close</button>
        <button type="button" class="apple-button" onclick="$('#previewProxyModal').modal('hide'); showTermsModal('proxy');">
          <i class="fas fa-edit" style="margin-right: 8px;"></i>
          Edit Terms
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/35.2.0/classic/ckeditor.js"></script>
<script>
  let calendarEditor;
  let onlineTermsEditor;
  let proxyTermsEditor;

  $(document).ready(function() {
    $('.admin-nav-setting').addClass('active');

    // CKEditor configuration with link plugin
    const editorConfig = {
      toolbar: {
        items: [
          'heading',
          '|',
          'bold',
          'italic',
          'link',
          'bulletedList',
          'numberedList',
          '|',
          'outdent',
          'indent',
          '|',
          'imageUpload',
          'blockQuote',
          'insertTable',
          'mediaEmbed',
          'undo',
          'redo'
        ]
      },
      language: 'en',
      image: {
        toolbar: [
          'imageTextAlternative',
          'imageStyle:full',
          'imageStyle:side'
        ]
      },
      table: {
        contentToolbar: [
          'tableColumn',
          'tableRow',
          'mergeTableCells'
        ]
      },
      licenseKey: '',
    };

    // Initialize CKEditor for calendar
    ClassicEditor
      .create(document.querySelector('#calendarEditor'), editorConfig)
      .then(editor => {
        calendarEditor = editor;
      })
      .catch(error => {
        console.error(error);
      });

    // Initialize CKEditor for online terms
    ClassicEditor
      .create(document.querySelector('#onlineTermsEditor'), editorConfig)
      .then(editor => {
        onlineTermsEditor = editor;
      })
      .catch(error => {
        console.error('Online terms editor error:', error);
      });

    // Initialize CKEditor for proxy terms
    ClassicEditor
      .create(document.querySelector('#proxyTermsEditor'), editorConfig)
      .then(editor => {
        proxyTermsEditor = editor;
      })
      .catch(error => {
        console.error('Proxy terms editor error:', error);
      });
  });

  // Update votes per share
  function updateVotesPerShare() {
    const votes = $('#votesPerShare').val();

    if (!votes || votes < 1) {
      Swal.fire('Error', 'Please enter a valid number of votes per share (minimum 1)', 'error');
      return;
    }

    Swal.fire({
      title: 'Update Votes Per Share?',
      text: `Set votes per share to ${votes}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Update',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/votes-per-share/update',
          method: 'POST',
          dataType: 'json',
          data: {
            votes_per_share: votes,
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            Swal.fire('Success!', 'Votes per share updated successfully', 'success');
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Toggle amendment module
  function toggleAmendmentModule() {
    const toggle = $('#amendmentModuleToggle');
    const status = $('#amendmentModuleStatus');
    const isCurrentlyEnabled = toggle.hasClass('active');
    const newStatus = !isCurrentlyEnabled;

    Swal.fire({
      title: newStatus ? 'Enable Amendment Module?' : 'Disable Amendment Module?',
      text: newStatus ?
        'This will enable amendment voting functionality.' : 'This will disable amendment voting functionality.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: newStatus ? 'Enable' : 'Disable',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/amendment-module/update',
          method: 'POST',
          dataType: 'json',
          data: {
            enabled: newStatus,
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (newStatus) {
              toggle.addClass('active');
              status.text('Enabled');
            } else {
              toggle.removeClass('active');
              status.text('Disabled');
            }

            Swal.fire('Success!',
              `Amendment module ${newStatus ? 'enabled' : 'disabled'} successfully`,
              'success'
            );
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Toggle Board of Director module
  function toggleBodModule() {
    const toggle = $('#bodModuleToggle');
    const status = $('#bodModuleStatus');
    const isCurrentlyEnabled = toggle.hasClass('active');
    const newStatus = !isCurrentlyEnabled;

    Swal.fire({
      title: newStatus ? 'Enable Board of Director Module?' : 'Disable Board of Director Module?',
      text: newStatus ?
        'This will enable Board of Director voting functionality.' : 'This will disable Board of Director voting functionality.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: newStatus ? 'Enable' : 'Disable',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/bod-module/update',
          method: 'POST',
          dataType: 'json',
          data: {
            enabled: newStatus,
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (newStatus) {
              toggle.addClass('active');
              status.text('Enabled');
            } else {
              toggle.removeClass('active');
              status.text('Disabled');
            }

            Swal.fire('Success!',
              `Board of Director module ${newStatus ? 'enabled' : 'disabled'} successfully`,
              'success'
            );
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Toggle voting confirmation receipt
  function toggleVotingReceipt() {
    const toggle = $('#votingReceiptToggle');
    const status = $('#votingReceiptStatus');
    const isCurrentlyEnabled = toggle.hasClass('active');
    const newStatus = !isCurrentlyEnabled;

    Swal.fire({
      title: newStatus ? 'Enable Voting Receipt?' : 'Disable Voting Receipt?',
      text: newStatus ?
        'Users will receive confirmation receipts after voting.' : 'Users will not receive confirmation receipts.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: newStatus ? 'Enable' : 'Disable',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/voting-receipt/update',
          method: 'POST',
          dataType: 'json',
          data: {
            enabled: newStatus,
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (newStatus) {
              toggle.addClass('active');
              status.text('Enabled');
            } else {
              toggle.removeClass('active');
              status.text('Disabled');
            }

            Swal.fire('Success!',
              `Voting receipt ${newStatus ? 'enabled' : 'disabled'} successfully`,
              'success'
            );
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Toggle OTP login
  function toggleOtpLogin() {
    const toggle = $('#otpLoginToggle');
    const status = $('#otpLoginStatus');
    const isCurrentlyEnabled = toggle.hasClass('active');
    const newStatus = !isCurrentlyEnabled;

    Swal.fire({
      title: newStatus ? 'Enable OTP Login?' : 'Disable OTP Login?',
      text: newStatus ?
        'Users will need to enter OTP for additional security.' : 'Standard login will be used.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: newStatus ? 'Enable' : 'Disable',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/otp-login/update',
          method: 'POST',
          dataType: 'json',
          data: {
            enabled: newStatus,
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (newStatus) {
              toggle.addClass('active');
              status.text('Enabled');
            } else {
              toggle.removeClass('active');
              status.text('Disabled');
            }

            Swal.fire('Success!',
              `OTP login ${newStatus ? 'enabled' : 'disabled'} successfully`,
              'success'
            );
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Toggle amendment restriction to General Manager
  function toggleAmendmentRestriction() {
    const toggle = $('#amendmentRestrictedToggle');
    const status = $('#amendmentRestrictedStatus');
    const isCurrentlyEnabled = toggle.hasClass('active');
    const newStatus = !isCurrentlyEnabled;

    Swal.fire({
      title: newStatus ? 'Restrict Amendment to General Manager?' : 'Unrestrict Amendment?',
      text: newStatus ?
        'Only General Managers will be able to propose and modify amendments.' : 'Amendment functionality will be available to all eligible users.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: newStatus ? 'Restrict' : 'Unrestrict',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/amendment-restriction/update',
          method: 'POST',
          dataType: 'json',
          data: {
            enabled: newStatus,
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (newStatus) {
              toggle.addClass('active');
              status.text('Enabled');
            } else {
              toggle.removeClass('active');
              status.text('Disabled');
            }

            Swal.fire('Success!',
              `Amendment restriction ${newStatus ? 'enabled' : 'disabled'} successfully`,
              'success'
            );
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Show date configuration modal
  function showDateModal(type) {
    const titles = {
      'vote_in_person': 'Configure Stockholder Online Voting Period',
      'vote_by_proxy': 'Configure Proxy Voting Period'
    };

    $('#dateModalTitle').text(titles[type]);
    $('#formType').val(type);
    $('#startDateTime').val('');
    $('#endDateTime').val('');
    $('#dateConfigModal').modal('show');
  }

  // Submit date configuration
  function submitDateConfig() {
    const formData = $('#dateConfigForm').serialize();

    if (!$('#startDateTime').val() || !$('#endDateTime').val()) {
      Swal.fire('Error', 'Please fill in both start and end dates', 'error');
      return;
    }

    if (new Date($('#startDateTime').val()) >= new Date($('#endDateTime').val())) {
      Swal.fire('Error', 'End date must be after start date', 'error');
      return;
    }

    $.ajax({
      url: BASE_URL + 'admin/setting/date/update',
      method: 'POST',
      dataType: 'json',
      data: formData + '&_token={{ csrf_token() }}',
      success: function(data) {
        $('#dateConfigModal').modal('hide');
        Swal.fire('Success!', 'Voting period updated successfully', 'success')
          .then(() => location.reload());
      },
      error: function(xhr) {
        handleError(xhr);
      }
    });
  }

  // Remove date setting
  function removeDateSetting(type) {
    Swal.fire({
      title: 'Remove Voting Period?',
      text: 'This will disable the voting period. Are you sure?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Remove',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#FF3B30',
      customClass: {
        confirmButton: 'apple-button danger',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/date/remove',
          method: 'POST',
          dataType: 'json',
          data: {
            form: type,
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            Swal.fire('Success!', 'Voting period removed successfully', 'success')
              .then(() => location.reload());
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }



  // Show terms modal
  function showTermsModal(type) {
    if (type === 'online') {
      $('#termsOnlineModal').modal('show');
    } else if (type === 'proxy') {
      $('#termsProxyModal').modal('show');
    }
  }

  // Show preview modal
  function showPreviewModal(type) {
    if (type === 'online') {
      $('#previewOnlineModal').modal('show');
    } else if (type === 'proxy') {
      $('#previewProxyModal').modal('show');
    }
  }

  // Update terms
  function updateTerms(type) {
    let editor = type === 'online' ? onlineTermsEditor : proxyTermsEditor;
    let modalId = type === 'online' ? '#termsOnlineModal' : '#termsProxyModal';

    if (!editor) {
      Swal.fire('Error', 'Editor not initialized', 'error');
      return;
    }

    Swal.fire({
      title: 'Update Terms?',
      text: `Save changes to the ${type} voting terms and conditions?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Save',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/setting/terms-and-conditions/update',
          method: 'POST',
          dataType: 'json',
          data: {
            type: type,
            content: editor.getData(),
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            $(modalId).modal('hide');
            Swal.fire('Success!', `${type.charAt(0).toUpperCase() + type.slice(1)} terms updated successfully`, 'success')
              .then(() => location.reload());
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Update calendar
  function updateCalendar() {
    if (!calendarEditor) {
      Swal.fire('Error', 'Editor not initialized', 'error');
      return;
    }

    Swal.fire({
      title: 'Update Calendar?',
      text: 'Save changes to the calendar of activities?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Save',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'apple-button',
        cancelButton: 'apple-button secondary'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + 'admin/bulletin',
          method: 'POST',
          dataType: 'json',
          data: {
            data: calendarEditor.getData(),
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            $('#termsAndConditionsOnlineModal').modal('hide');
            Swal.fire('Success!', 'Calendar updated successfully', 'success')
              .then(() => location.reload());
          },
          error: function(xhr) {
            handleError(xhr);
          }
        });
      }
    });
  }

  // Enhanced error handling
  function handleError(xhr) {
    let message = 'An error occurred';

    if (xhr.responseJSON && xhr.responseJSON.message) {
      message = xhr.responseJSON.message;
    } else if (xhr.status === 401) {
      message = 'Unauthorized access';
    } else if (xhr.status === 403) {
      message = 'Forbidden';
    } else if (xhr.status === 419) {
      message = 'Session expired. Please refresh the page.';
    } else if (xhr.status === 500) {
      message = 'Server error occurred';
    }

    Swal.fire('Error', message, 'error');
  }

  // Global success handler
  function handleSuccess(data) {
    if (data && data.message) {
      Swal.fire('Success!', data.message, 'success').then(() => {
        location.reload();
      });
    }
  }
</script>

@endsection