@extends('layouts.user')
@section('head')
@endsection
@section('content')
<div class="modern-container">
    <!-- Hero Section with User Profile -->
    <div class="hero-section">
        <div class="hero-content">
            <div class="user-profile-card">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        {{ $userInitials }}
                    </div>
                    <div class="status-indicator"></div>
                </div>
                <div class="profile-info">
                    <h1 class="user-name">{{ Auth::user()->full_name }}</h1>
                    <div class="user-meta">
                        <span class="email">{{ Auth::user()->email }}</span>
                        <span class="separator">•</span>
                        <span class="account-no">{{ Auth::user()->account_no }}</span>
                    </div>
                    <div class="role-badge">
                        <i class="fas fa-shield-alt"></i>
                        {{ $accountRole }}
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-bg">
            <div class="gradient-orb orb-1"></div>
            <div class="gradient-orb orb-2"></div>
            <div class="gradient-orb orb-3"></div>
        </div>
    </div>

    <!-- Voting Actions Section -->
    <div class="voting-section">
        <div class="voting-grid">

            <!-- Stockholder Online Card -->
            <div class="voting-option-card" id="stockholder-card">
                <div class="card-icon">
                    <div class="icon-wrapper">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>Stockholder Online</h3>
                    <p><?php echo $stockholderOnlineTT; ?></p>
                </div>
                <button id="btnVoteStockholderOnline"
                    class="vote-button primary"
                    <?php echo $btnDisableOnlineVoting; ?>>
                    <span>Vote Now</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>

            <!-- Proxy Voting Card -->
            <div class="voting-option-card" id="proxy-card">
                <div class="card-icon">
                    <div class="icon-wrapper">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>Proxy Voting</h3>
                    <p><?php echo $proxyVotingTT; ?></p>
                </div>
                <button id="btnProxyVoting"
                    class="vote-button secondary"
                    <?php echo $btnDisableProxyVoting; ?>>
                    <span>Vote Now</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Reset and Base Styles */
    * {
        box-sizing: border-box;
    }

    .modern-container {
        min-height: 100vh;
        background: #FFFFFF;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        padding: 2rem 1rem;
        overflow: hidden;
        background: #5E7C4C;
        margin-bottom: 2rem;
    }

    .hero-content {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .user-profile-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .profile-avatar {
        position: relative;
    }

    .avatar-circle {
        width: 80px;
        height: 80px;
        background: #2F4A3C;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        color: #FFFFFF;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        letter-spacing: 1px;
    }

    .status-indicator {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 18px;
        height: 18px;
        background: #8DA66E;
        border: 2px solid #FFFFFF;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .profile-info {
        flex: 1;
        color: #FFFFFF;
    }

    .user-name {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0 0 0.3rem 0;
        color: #FFFFFF;
    }

    .user-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.8rem;
        font-size: 0.95rem;
        opacity: 0.9;
    }

    .separator {
        opacity: 0.6;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 40px;
        font-weight: 600;
        font-size: 0.85rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Background Orbs */
    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        overflow: hidden;
        z-index: 1;
    }

    .gradient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(30px);
        opacity: 0.2;
    }

    .orb-1 {
        width: 200px;
        height: 200px;
        background: #2F4A3C;
        top: -100px;
        right: -100px;
        animation: float 6s ease-in-out infinite;
    }

    .orb-2 {
        width: 150px;
        height: 150px;
        background: #8DA66E;
        bottom: -75px;
        left: -75px;
        animation: float 8s ease-in-out infinite reverse;
    }

    .orb-3 {
        width: 100px;
        height: 100px;
        background: #8DA66E;
        top: 30%;
        left: 5%;
        animation: float 10s ease-in-out infinite;
    }

    /* Voting Section */
    .voting-section {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem 2rem;
    }

    .section-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .section-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.3rem;
    }

    .section-header p {
        font-size: 1rem;
        color: #7f8c8d;
        max-width: 500px;
        margin: 0 auto;
    }

    .voting-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .voting-option-card {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 1.8rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 1px solid #8DA66E;
    }

    .voting-option-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #5E7C4C;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .voting-option-card:hover::before {
        transform: scaleX(1);
    }

    .voting-option-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
        border-color: #5E7C4C;
    }

    .card-icon {
        margin-bottom: 1rem;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .icon-wrapper {
        width: 60px;
        height: 60px;
        background: #5E7C4C;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 1.5rem;
        box-shadow: 0 6px 20px rgba(94, 124, 76, 0.3);
    }

    .card-content h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #000000;
        margin-bottom: 0.5rem;
    }

    .card-content p {
        color: #2F4A3C;
        line-height: 1.5;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .vote-button {
        width: 100%;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .vote-button:disabled,
    .vote-button[disabled] {
        background: #e9ecef !important;
        color: #b0b7b1 !important;
        border: 1px solid #d1d5d1 !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
        opacity: 1 !important;
        filter: grayscale(0.2);
    }

    .vote-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transition: left 0.5s ease;
    }

    .vote-button:hover::before {
        left: 100%;
    }

    .vote-button.primary {
        background: #5E7C4C;
        color: #FFFFFF;
        box-shadow: 0 6px 20px rgba(94, 124, 76, 0.3);
    }

    .vote-button.primary:hover {
        background: #2F4A3C;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(47, 74, 60, 0.4);
    }

    .vote-button.secondary {
        background: #2F4A3C;
        color: #FFFFFF;
        box-shadow: 0 6px 20px rgba(47, 74, 60, 0.3);
    }

    .vote-button.secondary:hover {
        background: #1f3229;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(31, 50, 41, 0.4);
    }

    /* Modal Styles */
    .modal-content {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
    }

    .modal-body {
        font-size: 0.95rem;
        line-height: 1.6;
        color: #2c3e50;
    }

    .h4-content {
        font-size: 0.9rem;
        line-height: 1.7;
        margin-bottom: 1rem;
        color: #2c3e50;
        font-weight: 400;
    }

    .h5-title-terms {
        font-size: 1rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 1.5rem 0 0.5rem 0;
    }

    .input_person_user {
        font-size: 0.9rem;
        font-weight: 500;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        margin: 0.5rem 0;
        background-color: #f8f9fa;
    }

    .chiller_cb {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .chiller_cb label {
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0;
        cursor: pointer;
        font-weight: 500;
        color: #2c3e50;
    }

    .form-check-input {
        margin-top: 0.25rem;
        width: 1.1em;
        height: 1.1em;
    }

    /* Responsive Modal Fonts */
    @media (max-width: 768px) {
        .modal-title {
            font-size: 1.1rem;
        }

        .modal-body {
            font-size: 0.85rem;
            padding: 1.5rem !important;
        }

        .h4-content {
            font-size: 0.82rem;
            line-height: 1.6;
        }

        .h5-title-terms {
            font-size: 0.9rem;
        }

        .input_person_user {
            font-size: 0.85rem;
        }

        .chiller_cb label {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .modal-title {
            font-size: 1rem;
        }

        .modal-body {
            font-size: 0.8rem;
            padding: 1rem !important;
        }

        .h4-content {
            font-size: 0.78rem;
            line-height: 1.55;
        }

        .h5-title-terms {
            font-size: 0.85rem;
        }

        .input_person_user {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
        }

        .chiller_cb label {
            font-size: 0.8rem;
        }
    }

    /* Animations */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0) rotate(0deg);
        }

        33% {
            transform: translateY(-15px) rotate(2deg);
        }

        66% {
            transform: translateY(8px) rotate(-1deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-section {
            padding: 1.5rem 0.8rem;
            margin-bottom: 1.5rem;
        }

        .user-profile-card {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
            padding: 1.2rem;
        }

        .avatar-circle {
            width: 70px;
            height: 70px;
            font-size: 1.5rem;
        }

        .user-name {
            font-size: 1.5rem;
        }

        .user-meta {
            flex-direction: column;
            gap: 0.3rem;
            font-size: 0.85rem;
        }

        .separator {
            display: none;
        }

        .role-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        .voting-grid {
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }

        .voting-option-card {
            padding: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.6rem;
        }

        .section-header p {
            font-size: 0.9rem;
        }

        .voting-section {
            padding: 0 0.8rem 1.5rem;
        }

        .section-header {
            margin-bottom: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .hero-section {
            padding: 1rem 0.5rem;
            margin-bottom: 1rem;
        }

        .user-profile-card {
            padding: 1rem;
        }

        .avatar-circle {
            width: 60px;
            height: 60px;
            font-size: 1.3rem;
        }

        .status-indicator {
            width: 15px;
            height: 15px;
            bottom: 2px;
            right: 2px;
        }

        .user-name {
            font-size: 1.3rem;
        }

        .voting-option-card {
            padding: 1.2rem;
        }

        .card-content h3 {
            font-size: 1.2rem;
        }

        .card-content p {
            font-size: 0.9rem;
        }

        .icon-wrapper {
            width: 50px;
            height: 50px;
            font-size: 1.3rem;
        }

        .vote-button {
            padding: 0.7rem 1.2rem;
            font-size: 0.95rem;
        }

        .section-header h2 {
            font-size: 1.4rem;
        }

        .section-header {
            margin-bottom: 1rem;
        }

        .voting-section {
            padding: 0 0.5rem 1rem;
        }
    }

    @media (max-width: 360px) {
        .hero-section {
            padding: 0.8rem 0.5rem;
        }

        .user-profile-card {
            padding: 0.8rem;
            border-radius: 12px;
        }

        .voting-option-card {
            padding: 1rem;
            border-radius: 12px;
        }

        .section-header h2 {
            font-size: 1.25rem;
        }

        .card-features {
            margin-bottom: 1rem;
        }
    }
</style>


<!-- Revoke Proxy Modal -->
<div class="modal fade" id="requestBallotFormModal" tabindex="-1" role="dialog" aria-labelledby="requestBallotFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form id="requestBallotForm">
                <div class="modal-header border-0 p-4" style="background-color: #5E7C4C; border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title text-white fw-bold" id="requestBallotFormModalLabel">
                        </i>Revoke Proxy
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">
                            @if($amendmentEnabled === true)
                            Please choose which proxy to revoke from the options below:
                            @else
                            You have issued proxy(ies). Would you like to revoke them?
                            @endif
                        </h6>
                    </div>
                    <div id="revokeForm" class="ballot-pages active" data-page="1">
                        <div class="radio-section">
                            <div class="list-group list-group-flush">
                                @if($amendmentEnabled === true)
                                <div class="list-group-item border-0 p-0 mb-2 bg-transparent">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="revoke_proxy" id="amendmentProxy" value="amendment" {{ $amendment === false ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-semibold ms-2 {{ $amendment === true ? 'text-dark' : 'text-secondary' }}" for="amendmentProxy">
                                            Amendment
                                        </label>
                                    </div>
                                </div>
                                @endif
                                <div class="list-group-item border-0 p-0 mb-2 bg-transparent">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="revoke_proxy" id="bodProxy" value="bod" {{ $bod === false ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-semibold ms-2 {{ $bod === true ? 'text-dark' : 'text-secondary' }}" for="bodProxy">
                                            @if($amendmentEnabled === false)
                                            Yes
                                            @else
                                            BOD and other items in the agenda
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @if($amendmentEnabled === true)
                                <div class="list-group-item border-0 p-0 mb-2 bg-transparent">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="revoke_proxy" id="bothProxy" value="both" {{ $all === false ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-semibold ms-2 {{ $all === true ? 'text-dark' : 'text-secondary' }}" for="bothProxy">
                                            Both
                                        </label>
                                    </div>
                                </div>
                                @endif
                                <div class="list-group-item border-0 p-0 mb-2 bg-transparent">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="revoke_proxy" id="noneProxy" value="none" {{ $none === false ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-semibold ms-2 {{ $none === true ? 'text-dark' : 'text-secondary' }}" for="noneProxy">
                                            @if($amendmentEnabled === true)
                                            None of the above
                                            @else
                                            No
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-dismiss="modal" style="border-radius: 8px;">Back</button>
                    <button type="submit" class="btn px-4 py-2 fw-semibold" id="btnContinueToBallot" style="background-color: #2F4A3C; color: white; border: none; border-radius: 8px;">Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Stockholder Online Terms Modal -->
<div class="modal fade" id="in_person_agreement_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <!-- Modal Header -->
            <div class="modal-header border-0 p-4" style="background-color: #5E7C4C; border-radius: 12px 12px 0 0;">
                <h4 class="modal-title text-white fw-bold">
                    Terms and Conditions
                </h4>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
            </div>
            <form method="GET" action="" id="">

                <!-- Modal body -->
                <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                    <?php echo $stockholderOnlineTC; ?>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer border-0 p-4 flex-column align-items-start">
                    <div class="form-group w-100 mb-3">
                        <div class="chiller_cb">
                            <input class="form-check-input" id="checkboxInPerson" type="checkbox" required="" style="margin-right: 0.75rem;">
                            <label for="checkboxInPerson"> I have read and agreed to the terms and conditions of the E Voting System.</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end w-100 gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" data-dismiss="modal" style="border-radius: 8px;">Close</button>
                        <button type="submit" class="btn px-4 py-2 fw-semibold ml-1" id="btnProceedToStockholderOnline" style="background-color: #2F4A3C; color: white; border: none; border-radius: 8px;">
                            <i class="fas fa-arrow-right me-2"></i> Proceed
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Proxy Voting Terms Modal -->
<div class="modal fade" id="proxy_agreement_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <!-- Modal Header -->
            <div class="modal-header border-0 p-4" style="background-color: #5E7C4C; border-radius: 12px 12px 0 0;">
                <h4 class="modal-title text-white fw-bold">
                     Terms and Conditions
                </h4>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
            </div>
            <form method="GET" action="" id="">

                <!-- Modal body -->
                <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                    <?php echo $proxyVotingTC; ?>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer border-0 p-4 flex-column align-items-start">
                    <div class="form-group w-100 mb-3">
                        <div class="chiller_cb">
                            <input class="form-check-input" id="checkboxProxyVoting" type="checkbox" required="" style="margin-right: 0.75rem;">
                            <label for="checkboxProxyVoting"> I have read and agreed to the terms and conditions of the E Voting System.</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end w-100 gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" data-dismiss="modal" style="border-radius: 8px;">Close</button>
                        <button type="submit" class="btn px-4 py-2 fw-semibold" id="btnProceedToProxyVoting" style="background-color: #2F4A3C; color: white; border: none; border-radius: 8px;">
                            <i class="fas fa-arrow-right me-2"></i>Proceed
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>




@endsection

@section('js')
<script>
    function generateCurrentDateTime() {
        const now = new Date();

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        return `${year}${month}${day}${hours}${minutes}${seconds}`;
    }

    function createBallot() {

        const currentDateTime = generateCurrentDateTime();

        $.ajax({
            url: BASE_URL + 'user/ballot/proxy-voting?v' + currentDateTime,
            method: 'POST',
            dataType: 'json',
            cache: false,
            success: function(data) {
                location.href = `{{asset("user/ballot/proxy-voting")}}/${data['ballotId']}?v${currentDateTime}`;
            },
            error: function(xhr) {
                if (xhr.status === 421) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        text: xhr.responseJSON.message || 'Validation error on server'
                    }).then(() => {
                        $('#proxy_agreement_modal').modal('hide');

                    });
                    return;
                }

                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: xhr.responseJSON.message || ''
                }).then(() => {
                    $('#proxy_agreement_modal').modal('hide');
                });
            },
        });
    }


    function requestStockholderOnlineForm(revoke = "none") {


        const now = new Date();

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const currentDateTime = `${year}${month}${day}${hours}${minutes}${seconds}`;



        $.ajax({
            url: BASE_URL + 'user/ballot/stockholder-online?v' + currentDateTime,
            method: 'POST',
            dataType: 'json',
            data: {
                revoke: revoke
            },

            success: function(data) {

                location.href = `{{asset("user/ballot/stockholder-online")}}/${data['ballotId']}?v${currentDateTime}`;
            },
            error: function(xhr) {
                handleError(xhr);
            },


        });
    }

    $(document).ready(function() {

        $(function() {
            $('[data-toggle="tooltip"]').tooltip()

        })



        $(document).on('show.bs.modal', '#in_person_agreement_modal', function() {

            $('#checkboxInPerson').prop('checked', false);

        })



        $(document).on('show.bs.modal', '#proxy_agreement_modal', function() {

            $('#checkboxProxyVoting').prop('checked', false);

        })


        $(document).on('show.bs.modal', '#requestBallotFormModal', function() {

            $('#requestBallotForm')[0].reset();

        })





        $(document).on('click', '#btnProxyVoting', function() {

            $('#proxy_agreement_modal').modal('show');


        })



        $(document).on('click', '#btnProceedToProxyVoting', function(e) {

            if ($('#checkboxProxyVoting').is(':checked') === false) {

                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'To proceed, kindly indicate your acceptance of the terms and conditions by ticking the checkbox below.'
                }).then(() => {
                    $('#checkboxProxyVoting').focus();
                });


                return;
            }



            createBallot();

            e.preventDefault();

        })




        $(document).on('click', '#btnVoteStockholderOnline', function() {

            $('#in_person_agreement_modal').modal('show');


        })


        $(document).on('click', '#btnProceedToStockholderOnline', function(e) {


            if ($('#checkboxInPerson').is(':checked') === false) {

                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'To proceed, kindly indicate your acceptance of the terms and conditions by ticking the checkbox below.'
                }).then(() => {
                    $('#checkboxInPerson').focus();
                });



                return;
            }

            e.preventDefault();

            $('#in_person_agreement_modal').modal('hide');

            let issuedProxy = "{{$issuedProxy}}";




            // alert(issuedProxy);

            if (issuedProxy == 0) {
                requestStockholderOnlineForm();
                return false;
            }

            $('#requestBallotFormModal').modal('show');
        })

        $(document).on('submit', '#requestBallotForm', function(e) {

            e.preventDefault();

            let revoke = $("input[name='revoke_proxy']:checked").val();

            if (!revoke) {

                alert("Please choose from the options.");

                return;

            }


            if (revoke !== "none") {

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Please note that this action is final, and your issued proxy will no longer be considered once you continue with the voting process.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2F4A3C',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Back',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        requestStockholderOnlineForm(revoke);

                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $('#requestBallotFormModal').modal('hide');
                    }
                })

                return false;
            }

            requestStockholderOnlineForm(revoke);
            e.preventDefault();

        })
    })
</script>
@endsection