@extends('layouts.admin')

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
<style>
    .modern-search-container {
        min-height: 50vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        margin: 10px 0;
        position: relative;
        overflow: hidden;
    }

    .modern-search-container::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        animation: float 20s infinite linear;
    }

    @keyframes float {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .search-card {
        background: #FFFFFF;
        backdrop-filter: blur(20px);
        border-radius: 15px;
        border: 1px solid rgba(47, 74, 60, 0.2);
        padding: 1.5rem;
        box-shadow: 0 10px 25px rgba(47, 74, 60, 0.08);
        width: 100%;
        max-width: 500px;
        text-align: center;
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease;
    }

    .search-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(47, 74, 60, 0.12);
    }

    .search-title {
        font-size: 1rem;
        font-weight: 600;
        color: #2F4A3C;
        margin-bottom: 0.5rem;
        text-shadow: none;
    }

    .search-subtitle {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 1.5rem;
        line-height: 1.4;
    }

    .modern-search-box {
        position: relative;
        margin-bottom: 1rem;
    }

    .modern-search-input {
        width: 100%;
        padding: 0.8rem 1rem 0.8rem 2.5rem;
        font-size: 0.8rem;
        border: 2px solid #e9ecef;
        border-radius: 25px;
        background: #FFFFFF;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
    }

    .modern-search-input:focus {
        outline: none;
        border-color: #2F4A3C;
        box-shadow: 0 0 0 2px rgba(47, 74, 60, 0.1), 0 5px 15px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .search-icon {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 1rem;
        transition: color 0.3s ease;
    }

    .modern-search-input:focus+.search-icon {
        color: #2F4A3C;
    }

    .search-results {
        margin-top: 1rem;
        max-height: 250px;
        overflow-y: auto;
        border-radius: 10px;
        background: #FFFFFF;
        border: 1px solid #dee2e6;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        display: none;
    }

    .search-result-item {
        padding: 0.8rem 1rem;
        border-bottom: 1px solid #f1f3f4;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .search-result-item:hover {
        background: #f8f9fa;
        transform: translateX(3px);
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .result-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .result-name {
        font-weight: 600;
        color: #2F4A3C;
        font-size: 0.9rem;
    }

    .result-account {
        color: #6c757d;
        font-size: 0.75rem;
        margin-top: 0.2rem;
    }

    .result-select-btn {
        background: linear-gradient(135deg, #2F4A3C 0%, #5E7C4C 100%);
        color: #FFFFFF;
        border: none;
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .result-select-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 3px 10px rgba(47, 74, 60, 0.3);
        background: linear-gradient(135deg, #1f3129 0%, #4a6639 100%);
    }

    .no-results {
        text-align: center;
        padding: 1.5rem;
        color: #6c757d;
        font-style: italic;
        font-size: 0.9rem;
    }

    .loading-spinner {
        text-align: center;
        padding: 1.5rem;
        color: #2F4A3C;
        font-size: 0.9rem;
    }

    .selected-stockholder {
        background: #f8f9fa;
        color: #2F4A3C;
        padding: 0.8rem 1rem;
        border-radius: 10px;
        border: 1px solid #dee2e6;
        margin-top: 1rem;
        display: none;
    }

    .selected-stockholder h4 {
        margin: 0 0 0.3rem 0;
        font-size: .85rem;
        color: #2F4A3C;
    }

    .selected-stockholder p {
        margin: 0;
        opacity: 0.8;
        color: #2F4A3C;
        font-size: 0.85rem;
    }

    /* Vote Details Styles */
    .vote-details {
        margin-top: .5rem;
        width: 100%;
        max-width: 700px;
    }

    .vote-info-card {
        background: #FFFFFF;
        border: 1px solid #dee2e6;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .vote-info-header {
        background: #FFFFFF;
        color: #2F4A3C;
        padding: 1rem 1.5rem;
        text-align: center;
        border-bottom: 1px solid #dee2e6;
    }

    .vote-info-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        color: #2F4A3C;
    }

    .vote-info-content {
        padding: .5rem;
    }

    .info-group {
        margin-bottom: 0.8rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f3f4;
    }

    .info-group label {
        font-weight: 600;
        color: #495057;
        margin: 0;
        font-size: 0.85rem;
    }

    .info-group span {
        color: #2F4A3C;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .proxy-stats {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #dee2e6;
    }

    .proxy-stats h5 {
        color: #2F4A3C;
        margin-bottom: 0.8rem;
        text-align: center;
        font-weight: 600;
        font-size: 1rem;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* margin-bottom: 0.5rem;
        padding: 0.3rem 0; */
    }

    .stat-item.highlight {
        background: #fff;
        padding: 0.8rem;
        border-radius: 8px;
        border: 2px solid #2F4A3C;
        box-shadow: 0 3px 8px rgba(47, 74, 60, 0.08);
    }

    .stat-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.7rem;
    }

    .stat-value {
        font-weight: bold;
        padding: 0.1rem 0.3rem;
        border-radius: 15px;
        min-width: 35px;
        text-align: center;
        font-size: 0.8rem;
        margin: .1rem
    }

    .stat-value.valid {
        background: #d4edda;
        color: #155724;
    }

    .stat-value.warning {
        background: #fff3cd;
        color: #856404;
    }

    .stat-value.danger {
        background: #f8d7da;
        color: #721c24;
    }

    .stat-value.primary {
        background: #2F4A3C;
        color: #FFFFFF;
    }

    .available-votes {
        text-align: center;
        background: #fff;
        color: #2F4A3C;
        padding: .5rem;
        border-radius: 15px;
        border: 2px solid #2F4A3C;
        box-shadow: 0 5px 15px rgba(47, 74, 60, 0.08);
    }

    .available-votes h4 {
        margin-bottom: 0.8rem;
        font-size: 1.1rem;
        color: #2F4A3C;
        font-weight: 600;
    }

    .vote-counter {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2F4A3C;
        text-shadow: none;
    }

    .vote-counter small {
        font-size: 0.9rem;
        opacity: 0.7;
        margin-left: 0.5rem;
        color: #6c757d;
    }
</style>
@endsection

@section('content')

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Modern Page Header -->
            <div class="corporate-page-header ultra-compact">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <h1 class="corporate-page-title ultra-compact">Available Vote Inquiry</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Vote Inquiry</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Modern Search Container -->
            <div class="modern-search-container">
                <div class="search-card">
                    <h6 class="search-title">
                        <i class="fas fa-vote-yea mr-3" style="color: #2F4A3C;"></i>
                        Find Stockholder
                    </h6>


                    <div class="modern-search-box">
                        <input type="text"
                            id="stockholder_search"
                            class="modern-search-input"
                            placeholder="Type stockholder name, account number, or email..."
                            autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                    </div>

                    <div id="search_results" class="search-results"></div>

                    <div id="selected_stockholder" class="selected-stockholder">

                        <p id="selected_info"></p>
                    </div>

                    <!-- Detailed Vote Information Display -->
                    <div id="vote_details" class="vote-details" style="display: none;">
                        <div class="vote-info-card">

                            <div class="vote-info-content">
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="proxy-stats">
                                            <h5><i class="fas fa-users mr-2"></i>Proxy Statistics</h5>
                                            <div class="stat-item">
                                                <span class="stat-label">Valid Proxy:</span>
                                                <span class="stat-value valid" id="detail_valid_proxy">0</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-label">Delinquent Proxy:</span>
                                                <span class="stat-value warning" id="detail_delinquent_proxy">0</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-label">Revoked Proxy:</span>
                                                <span class="stat-value danger" id="detail_revoked_proxy">0</span>
                                            </div>
                                            <div class="stat-item highlight mt-1">
                                                <span class="stat-label">Net Available Proxy:</span>
                                                <span class="stat-value primary" id="detail_net_proxy">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="available-votes">
                                            <h6><i class="fas fa-ballot-check mr-2"></i>Available Votes</h6>
                                            <div class="vote-counter">
                                                <span id="detail_available_vote">0</span>
                                                <small>votes</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modern Back to Top Button -->
    <button id="back-to-top" class="btn corporate-back-to-top" role="button" aria-label="Scroll to top" style="display: none;">
        <i class="fas fa-chevron-up"></i>
    </button>
</div>


<script src="{{ asset('js/admin/corporate-ui.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/admin/corporate-ui.modal.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/admin/roles_and_permissions.js') }}?v={{ time() }}"></script>

<script>
    // Stockholders array object populated from PHP
    const stockholders = @json($users);

    $(document).ready(function() {
        let searchTimeout;

        // Search functionality
        $('#stockholder_search').on('input', function() {
            const searchTerm = $(this).val().trim();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            // Clear details and selection when user starts typing
            clearStockholderDetails();

            if (searchTerm.length >= 2) {
                // Show loading
                showLoading();

                // Search with delay
                searchTimeout = setTimeout(function() {
                    searchStockholders(searchTerm);
                }, 300);
            } else if (searchTerm.length === 0) {
                hideResults();
            }
        });

        // Handle Enter key
        $('#stockholder_search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const searchTerm = $(this).val().trim();
                if (searchTerm.length >= 2) {
                    searchStockholders(searchTerm);
                }
            }
        });
    });

    function showLoading() {
        const loadingHtml = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Searching stockholders...</p>
            </div>
        `;
        $('#search_results').html(loadingHtml).show();
    }

    function hideResults() {
        $('#search_results').hide().empty();
        $('#selected_stockholder').hide();
    }

    function clearStockholderDetails() {
        $('#selected_stockholder').hide();
        $('#vote_details').hide();
        $('#selected_info').empty();
    }

    function searchStockholders(searchTerm) {
        // Filter stockholders array
        const filteredStockholders = stockholders.filter(stockholder => {
            const lowerSearchTerm = searchTerm.toLowerCase();
            const name = stockholder.name || '';
            const accountNo = stockholder.accountNo || '';
            const email = stockholder.email || '';

            return name.toLowerCase().includes(lowerSearchTerm) ||
                accountNo.toLowerCase().includes(lowerSearchTerm) ||
                email.toLowerCase().includes(lowerSearchTerm);
        });

        displayResults(filteredStockholders, searchTerm);
    }

    function displayResults(results, searchTerm) {
        const resultsContainer = $('#search_results');

        if (results.length === 0) {
            const noResultsHtml = `
                <div class="no-results">
                    <i class="fas fa-search fa-3x mb-3" style="color: #bdc3c7;"></i>
                    <p>No stockholders found matching "<strong>${searchTerm}</strong>"</p>
                    <p class="small">Try searching with a different name, account number, or email.</p>
                </div>
            `;
            resultsContainer.html(noResultsHtml).show();
            return;
        }

        let resultsHtml = '';
        results.forEach(stockholder => {
            const accountTypeIcon = stockholder.role === 'corporate' ? 'fas fa-building' : 'fas fa-user';
            const accountTypeBadge = stockholder.role === 'corporate' ?
                '<span class="badge badge-info ml-2">Corporate</span>' :
                '<span class="badge badge-success ml-2">Individual</span>';

            const name = stockholder.name || 'N/A';
            const accountNo = stockholder.accountNo || 'N/A';
            const email = stockholder.email || 'N/A';

            resultsHtml += `
                <div class="search-result-item" data-stockholder-id="${stockholder.id}">
                    <div class="result-info">
                        <div class="result-name">
                            <i class="${accountTypeIcon} mr-2"></i>
                            ${name}
                            ${accountTypeBadge}
                        </div>
                        <div class="result-account">
                            Account: ${accountNo} | ${email}
                        </div>
                    </div>
                    <button class="result-select-btn" onclick="selectStockholder(${stockholder.id})">
                        <i class="fas fa-check mr-1"></i> Select
                    </button>
                </div>
            `;
        });

        resultsContainer.html(resultsHtml).show();

        // Add click handler for result items
        $('.search-result-item').on('click', function() {
            const stockholderId = $(this).data('stockholder-id');
            selectStockholder(stockholderId);
        });
    }

    function selectStockholder(stockholderId) {
        const stockholder = stockholders.find(s => s.id === stockholderId);

        if (!stockholder) {
            console.error('Stockholder not found');
            return;
        }

        // Show loading state
        showLoadingStockholderDetails();

        // Update selected stockholder display
        const accountTypeIcon = stockholder.role === 'corporate' ? 'fas fa-building' : 'fas fa-user';
        const name = stockholder.name || 'N/A';
        const accountNo = stockholder.accountNo || 'N/A';
        const email = stockholder.email || 'N/A';

        $('#selected_info').html(`
            <i class="${accountTypeIcon} mr-2"></i>
            <strong>${name}</strong><br>
            Account: ${accountNo} | ${email}
        `);

        $('#selected_stockholder').show();
        $('#search_results').hide();

        // Clear search input
        $('#stockholder_search').val('');

        // Fetch detailed vote information via AJAX
        fetchStockholderVoteDetails(stockholderId);
    }

    function showLoadingStockholderDetails() {
        $('#vote_details').show();
        $('.vote-info-content').html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-3x" style="color: #2F4A3C;"></i>
                <p class="mt-3" style="color: #5E7C4C;">Fetching stockholder vote details...</p>
            </div>
        `);
    }

    function fetchStockholderVoteDetails(stockholderId) {
        // Simulate AJAX call - replace with actual endpoint
        $.ajax({
            url: BASE_URL + 'admin/available-vote-inquiry/' + stockholderId, // Replace with actual endpoint
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                displayStockholderVoteDetails(response);

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Details Loaded',
                    text: `Vote details loaded for ${response.name}`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            },
            error: function(xhr) {
                console.error('Error fetching stockholder details:', xhr);

                // For demo purposes, use mock data when AJAX fails
                const mockData = {
                    accountNo: "0001",
                    role: "stockholder",
                    name: "John Doe",
                    email: "john.doe@example.com",
                    validProxy: 5,
                    delinquentProxy: 1,
                    revokedProxy: 0,
                    netAvailableProxy: 4,
                    availableVote: 100
                };

                displayStockholderVoteDetails(mockData);

                // Show info message about using demo data
                Swal.fire({
                    icon: 'info',
                    title: 'Demo Data',
                    text: 'Displaying sample data (AJAX endpoint not available)',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        });
    }

    function displayStockholderVoteDetails(data) {
        // Restore the original vote details structure
        $('.vote-info-content').html(`
            <div class="row">
            
                <div class="col-md-12">
                    <div class="proxy-stats">
                        <h5><i class="fas fa-users mr-2"></i>Proxy Votes</h5>
                        <div class="stat-item">
                            <span class="stat-label">Valid Proxy:</span>
                            <span class="stat-value valid" id="detail_valid_proxy">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Delinquent Proxy:</span>
                            <span class="stat-value warning" id="detail_delinquent_proxy">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Revoked/Used Proxy:</span>
                            <span class="stat-value danger" id="detail_revoked_proxy">0</span>
                        </div>
                        <div class="stat-item highlight mt-1">
                            <span class="stat-label">Net Available Proxy:</span>
                            <span class="stat-value primary" id="detail_net_proxy">0</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="available-votes">
                        <h6><i class="fas fa-ballot-check mr-2"></i>Available Votes</h6>
                        <div class="vote-counter">
                            <span id="detail_available_vote">0</span>
                            <small>votes</small>
                        </div>
                    </div>
                </div>
            </div>
        `);

        // Populate the data with animation
        setTimeout(() => {
            $('#detail_account_no').text(data.accountNo);
            $('#detail_name').text(data.name);
            $('#detail_account_type').text(data.role.charAt(0).toUpperCase() + data.role.slice(1));
            $('#detail_email').text(data.email);

            // Animate proxy statistics
            animateNumber('#detail_valid_proxy', data.validProxy);
            animateNumber('#detail_delinquent_proxy', data.delinquentProxy);
            animateNumber('#detail_revoked_proxy', data.revokedProxy);
            animateNumber('#detail_net_proxy', data.netAvailableProxy);

            // Animate available votes with larger number
            animateNumber('#detail_available_vote', data.availableVote, 1500);
        }, 300);

        console.log('Stockholder vote details:', data);
    }

    function animateNumber(selector, targetValue, duration = 1000) {
        const element = $(selector);
        const startValue = 0;
        const increment = targetValue / (duration / 50);
        let currentValue = startValue;

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= targetValue) {
                currentValue = targetValue;
                clearInterval(timer);
            }
            element.text(Math.floor(currentValue));
        }, 50);
    }
</script>


@endsection