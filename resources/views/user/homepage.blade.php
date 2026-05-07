@extends('layouts.user')

@section('head')
<link rel="stylesheet" type="text/css" href="{{asset('css/user/homepage.css')}}?<?php echo filemtime('css/user/homepage.css') ?>">
@endsection


@section('content')

<div class="row">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="div-accordion m-2">
                    <div id="accordion">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row d-none">
            <div class="col-md-4">
                <div class="wrimagecard wrimagecard-topimage grow" data-toggle="modal" data-target="#allFilesModal">

                    <div class="wrimagecard-topimage_header text-center" style="background-color: #5E7C4C;">
                        <i class="fas fa-file-pdf fas-i-con" style="color: #FFFFFF;"> </i>
                    </div>
                    <div class="wrimagecard-topimage_title text-center">
                        <h6 class="font-weight-bold h4-label">Documents</h6>
                        <button class="btn btn-new btn-dark-success" data-toggle="modal" data-target="#documentsModal">Show</button>
                    </div>

                </div>
            </div>
            <div class="col-md-4">
                <div class="wrimagecard wrimagecard-topimage grow">

                    <div class="wrimagecard-topimage_header text-center" style="background-color: #5E7C4C;">
                        <i class="fas fa-vote-yea fas-i-con" style="color: #FFFFFF;"></i>
                    </div>
                    <div class="wrimagecard-topimage_title text-center">
                        <h6 class="font-weight-bold h4-label">Stockholder Online</h6>
                        <butto class="btn btn-new btn-dark-success" data-toggle="modal" data-target="#requestBallotFormModal">Vote</button>
                    </div>

                </div>
            </div>
            <div class="col-md-4">
                <div class="wrimagecard wrimagecard-topimage grow">

                    <div class="wrimagecard-topimage_header text-center" style="background-color: #5E7C4C;">
                        <i class="fas fa-vote-yea fas-i-con" style="color: #FFFFFF;"></i>
                    </div>
                    <div class="wrimagecard-topimage_title text-center">
                        <h6 class="font-weight-bold h4-label">Proxy Voting</h6>
                        <butto class="btn btn-new btn-dark-success disabled" data-toggle="modal" data-target="#exampleModal">Vote</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="container container-Div">
        <div class="row">
            <div class="col-md-12">
                <h1 class="h2-annual-title text-center" style="">2025 Annual Meeting of Stockholders</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">

                <?php

                use Illuminate\Support\Facades\Auth;
?>
                @if(Auth::check()) 

                    @if(Auth::user()->isVoterGroup())
                            <div class="divButton vote-button-prominent" onclick="vote()">
                            <i class="fas fa-vote-yea"></i> VOTE HERE
                            <div class="button-glow"></div>
                          </div>
                    @endif
                @else 
                                   <div class="alert alert-info text-center mb-4" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; font-size: 1.1em; font-weight: 500; border-radius: 8px; padding: 15px;">
                            <i class="fas fa-info-circle"></i> You need to login before you can cast your vote
                          </div>
                    <div class="divButton login-button-prominent" onclick="login()">
                            <i class="fas fa-sign-in-alt"></i> LOGIN HERE
                            <div class="button-glow"></div>
                          </div>
                @endif
            <div class="text-center mt-5">
                        <div class="divButton register-link-style" onclick="register()">
                            <i class="fas fa-user-plus"></i> REGISTER FOR ONSITE MEETING
                        </div>
                      </div>
                

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="div-documents">

                    <h3 class="text-center h3-documents">Documents</h3>
                    @foreach($documents as $document)
                    <p><a href="public-documents/{{ $document->documentId }}" target="_blank" class="a-documents"><i class="far fa-file-pdf doc-pdf-icon"></i> {{ $document->title }}</a></p>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
    <div class="col-12 d-flex justify-content-center">
        <div class="card mb-4 border-0 modern-posts-card w-100" style="max-width:900px;">
            <div class="card-body px-4 py-4 bg-white">
                <div class="d-flex align-items-center mb-4">
                    <span class="modern-posts-icon d-flex align-items-center justify-content-center mr-3">
                        <i class="fas fa-bullhorn"></i>
                    </span>
                    <h3 class="mb-0 modern-posts-title">Posts</h3>
                </div>
                @if(isset($announcements) && count($announcements) > 0)
                @foreach($announcements as $item)
                <div class="modern-post-item mb-4">
                    <div class="modern-post-header">
                        <h4 class="modern-post-title">{{ $item->title ?? 'Post' }}</h4>
                        @if(!empty($item->created_at))
                        <div class="modern-post-date">{{ \Carbon\Carbon::parse($item->created_at)->format('F d, Y') }}</div>
                        @endif
                    </div>
                    <div class="modern-post-content">{!! $item->content !!}</div>
                </div>
                @endforeach
                @else
                <div class="modern-empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No posts at this time.</p>
                </div>
                @endif
            </div>
        </div>
        <style>
            .modern-posts-card {
                background: linear-gradient(145deg, #ffffff, #f8fafc);
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.06);
                border-radius: 20px;
                backdrop-filter: blur(10px);
            }

            .modern-posts-icon {
                width: 48px;
                height: 48px;
                background: linear-gradient(135deg, #5E7C4C, #8DA66E);
                border-radius: 16px;
                color: #FFFFFF;
                font-size: 1.4em;
                box-shadow: 0 4px 15px rgba(94, 124, 76, 0.3);
            }

            .modern-posts-title {
                color: #000000;
                font-weight: 600;
                font-size: 1.5em;
                letter-spacing: -0.02em;
            }

            .modern-post-item {
                background: linear-gradient(145deg, #FFFFFF, #f7fafc);
                border-radius: 16px;
                padding: 20px;
                border: 1px solid #8DA66E;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .modern-post-item::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                background: linear-gradient(135deg, #5E7C4C, #8DA66E);
            }

            .modern-post-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            .modern-post-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 12px;
            }

            .modern-post-title {
                color: #000000;
                font-weight: 600;
                font-size: 1.2em;
                margin: 0;
                line-height: 1.4;
            }

            .modern-post-date {
                color: #2F4A3C;
                font-size: 0.9em;
                font-weight: 500;
                background: #8DA66E;
                color: #FFFFFF;
                padding: 4px 12px;
                border-radius: 20px;
                white-space: nowrap;
            }

            .modern-post-content {
                color: #2F4A3C;
                font-size: 1em;
                line-height: 1.7;
                margin: 0;
            }

            .modern-empty-state {
                text-align: center;
                padding: 40px 20px;
                color: #2F4A3C;
            }

            .modern-empty-state i {
                font-size: 3em;
                margin-bottom: 16px;
                opacity: 0.6;
            }

            .modern-empty-state p {
                font-size: 1.1em;
                margin: 0;
            }

            .vote-button-prominent {
                background: linear-gradient(135deg, #2F4A3C, #5E7C4C) !important;
                border: 4px solid #FFFFFF !important;
                box-shadow: 0 12px 40px rgba(47, 74, 60, 0.4), 0 0 30px rgba(94, 124, 76, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
                font-size: 1.2em !important;
                font-weight: 800 !important;
                color: #FFFFFF !important;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5) !important;
                transform: scale(1.15) !important;
                animation: pulse-vote-corporate 2s infinite !important;
                position: relative !important;
                overflow: hidden !important;
                transition: all 0.3s ease !important;
                white-space: nowrap !important;
                min-width: 320px !important;
                padding: 18px 35px !important;
                border-radius: 15px !important;
                text-transform: uppercase !important;
                letter-spacing: 1.5px !important;
            }

            .vote-button-prominent::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                transition: left 0.8s ease;
            }

            .vote-button-prominent:hover::before {
                left: 100%;
            }

            .vote-button-prominent:hover {
                background: linear-gradient(135deg, #5E7C4C, #8DA66E) !important;
                transform: scale(1.2) !important;
                box-shadow: 0 15px 50px rgba(47, 74, 60, 0.6), 0 0 40px rgba(94, 124, 76, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
                border-color: #8DA66E !important;
            }

            .vote-button-prominent i {
                margin-right: 12px;
                font-size: 1.2em;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            }

            @keyframes pulse-vote-corporate {

                0%,
                100% {
                    box-shadow: 0 12px 40px rgba(47, 74, 60, 0.4), 0 0 30px rgba(94, 124, 76, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2);
                }

                50% {
                    box-shadow: 0 15px 50px rgba(47, 74, 60, 0.6), 0 0 40px rgba(94, 124, 76, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.3);
                }
            }

            .login-button-prominent {
                background: linear-gradient(135deg, #5E7C4C, #8DA66E) !important;
                border: 3px solid #FFFFFF !important;
                box-shadow: 0 8px 25px rgba(94, 124, 76, 0.4), 0 0 20px rgba(94, 124, 76, 0.3) !important;
                font-size: 1.4em !important;
                font-weight: bold !important;
                color: #FFFFFF !important;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3) !important;
                transform: scale(1.1) !important;
                animation: pulse-glow-corporate 2s infinite !important;
                position: relative !important;
                overflow: hidden !important;
                transition: all 0.3s ease !important;
                white-space: nowrap !important;
                min-width: 300px !important;
                padding: 15px 30px !important;
                border-radius: 12px !important;
                text-transform: uppercase !important;
                letter-spacing: 1px !important;
            }

            .login-button-prominent:hover {
                background: linear-gradient(135deg, #2F4A3C, #5E7C4C) !important;
                transform: scale(1.15) !important;
                box-shadow: 0 12px 35px rgba(47, 74, 60, 0.6), 0 0 30px rgba(47, 74, 60, 0.5) !important;
            }

            @keyframes pulse-glow-corporate {

                0%,
                100% {
                    box-shadow: 0 8px 25px rgba(94, 124, 76, 0.4), 0 0 20px rgba(94, 124, 76, 0.3);
                }

                50% {
                    box-shadow: 0 8px 25px rgba(94, 124, 76, 0.6), 0 0 30px rgba(94, 124, 76, 0.5);
                }
            }

            .compact-tile::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                background: #5E7C4C;
                transition: width 0.3s ease;
            }

            .compact-tile:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                border-color: #5E7C4C;
            }

            .compact-tile.voting-period::before {
                background: #dc3545;
            }

            .compact-tile.meeting-day::before {
                background: #8DA66E;
            }

            .tile-date {
                font-weight: 700;
                font-size: 1.1rem;
                color: #000000;
                margin-bottom: 0.75rem;
                line-height: 1.3;
            }

            .tile-day {
                font-weight: 500;
                color: #2F4A3C;
                font-size: 0.9rem;
            }

            .tile-activity {
                color: #2F4A3C;
                line-height: 1.6;
                font-size: 0.95rem;
            }

            .voting-period {
                border-color: #fed7d7;
                background: linear-gradient(145deg, #fff5f5, #fed7d7);
            }

            .meeting-day {
                border-color: #c6f6d5;
                background: linear-gradient(145deg, #f0fff4, #c6f6d5);
            }

            @media (max-width: 768px) {
                .compact-tiles-container {
                    grid-template-columns: 1fr;
                    gap: 0.75rem;
                }

                .compact-tile {
                    padding: 1rem;
                }

                .tile-date {
                    font-size: 1rem;
                    margin-bottom: 0.5rem;
                }

                .tile-activity {
                    font-size: 0.9rem;
                }
            }

            .register-link-style {
                background: transparent !important;
                border: none !important;
                color: #2F4A3C !important;
                font-size: 1em !important;
                font-weight: 400 !important;
                text-transform: none !important;
                letter-spacing: normal !important;
                border-radius: 0 !important;
                padding: 5px 0 !important;
                width: auto !important;
                min-width: auto !important;
                box-shadow: none !important;
                transition: color 0.2s ease !important;
                position: relative !important;
                cursor: pointer !important;
                text-decoration: underline !important;
                display: inline !important;
                white-space: nowrap !important;
                transform: none !important;
                animation: none !important;
            }

            .register-link-style:hover {
                background: transparent !important;
                transform: none !important;
                box-shadow: none !important;
                border: none !important;
                color: #5E7C4C !important;
                text-decoration: underline !important;
                animation: none !important;
            }

            .register-link-style:active,
            .register-link-style:focus {
                background: transparent !important;
                transform: none !important;
                box-shadow: none !important;
                border: none !important;
                outline: none !important;
                animation: none !important;
            }

            .register-link-style::before {
                display: none !important;
            }

            .register-link-style i {
                margin-right: 6px;
                font-size: 0.9em;
                vertical-align: middle;
                color: inherit;
            }
        </style>
    </div>

</div>



<!-- Modal -->
<div class="modal fade" id="requestBallotFormModal" tabindex="-1" role="dialog" aria-labelledby="requestBallotFormModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="requestBallotForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestBallotFormModalLabel">Revoke Proxy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div id="revokeForm" class="ballot-pages active" data-page="1">

                        <section class="radio-section">
                            <div class="radio-list">
                                <h2 class="h2-desc">Please choose the proxy to revoke from the options below.</h2>
                                <div class="radio-item"><input name="revoke_proxy" value="bod" id="bodProxy" type="radio"><label for="bodProxy">BOD Only</label></div>
                                <div class="radio-item"><input name="revoke_proxy" value="amendment" id="amendmentProxy" type="radio"><label for="amendmentProxy">Amendment Only</label></div>
                                <div class="radio-item"><input name="revoke_proxy" value="both" id="bothProxy" type="radio"><label for="bothProxy">Both BOD and Amendment</label></div>
                                <div class="radio-item"><input name="revoke_proxy" value="none" id="noneProxy" type="radio"><label for="noneProxy">None</label></div>
                            </div>
                        </section>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn newBtnlight" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn newBtndark" id="btnContinueToBallot">Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- In Person Modal -->
<div class="modal fade" id="in_person_agreement_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Terms and Conditions</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="GET" action="{{asset('user/vote/in_person/form')}}" id="agreement_form">

                <!-- Modal body -->
                <div class="modal-body">
                    <br class="inper_br">
                    <h4 class="h4-content">
                        I,<br>
                        <input type="text" class="form-control input_person_user" id="input-proxy" readonly="" value="Paul Asuncion"><br>
                        do hereby constitute or, in the absence and/or non-attendance of my PROXY the Chairman of the Meeting,(EXCEPT THAT THE CHAIRMAN MAY NOT VOTE FOR CANDIDATES to the Board of Directors), as my attorney-in-fact and proxy, to represent me at the ANNUAL GENERAL MEETING of Valley Golf & Country Club, Inc., to be held on 25 September 2022 at 4:00pm, and/or any postponements or adjournment(s) thereof, as fully and to all intents and purposes as I lawfully might or could do if present and voting in person, hereby ratifying and confirming any and all actions taken on matters which may properly come before him during such meeting or adjournment(s) thereof.
                    </h4>
                    <h4 class="h4-content">
                        I also declare and signify my intent to participate by remote communication and exercise the right to vote in absentia in the September 25, 2022 Annual Stockholders’ Meeting of Valley Golf & Country Club, Inc.
                    </h4>
                    <h4 class="h4-content">
                        By participating in the on-line voting and in compliance with Republic Act No. 10173 (or the Data Privacy Act of 2012) and its Implementing Rules and Regulations (IRR) effective since September 8, 2016, I freely and voluntarily authorize Valley Golf & Country Club, Inc. to collect, record, organize, use, disclose and/or otherwise process any personal information submitted in connection with this declaration only for the purpose of allowing me to participate in the stockholders’ meeting by remote communication and vote in absentia. I understand that by giving this consent, I am not waiving any of my rights other than as authorized herein under the Data Privacy Act of 2012 and other applicable laws.
                    </h4><br>
                    <h5 class="h5-title-terms">Voting Rules:</h5>
                    <h4 class="h4-content">
                        Each shareholder owning one share, or his Proxy is entitled to cast one vote for as many positions for directors as are being voted upon or cumulate the votes and cast it in favor one or two candidates. Example –If there are 3 seats for directors open for voting, the shareholder or his proxy is entitled to 3 votes. He/she may either distribute the 3 votes among 3 candidates of his/her choice, cast all 3 votes in favor of one candidate or cast 2 votes in favor of 1 candidate and 1 vote for another candidate. If the shareholder owns more than one share, each share shall be entitled to 3 votes which he/she may distribute evenly or cumulate in favor of just one or two candidates.
                    </h4><br>
                    <h5 class="h5-title-terms">Disclaimer:</h5>
                    <h4 class="h4-content">
                        The information contained given on this site has been compiled for the convenience and general information of the stockholders. Valley Golf and Country Club, Inc. has no right to change the information you provided on the website. Although Valley Golf provided the voting platform to facilitate e-voting system for stockholders related matter and we have endeavored to ensure appropriate check are in place and to enable true and fair voting and that data is not accessed by any person when the voting window is open. However, Valley Golf shall not be liable in case of any unauthorized access by any person.
                    </h4>
                    <br class="inper_br">
                    <div class="form-group">
                        <div class="chiller_cb">
                            <input class="form-check-input" id="checkbox" type="checkbox" required="">
                            <label for="checkbox">I have read and agreed to the terms and conditions of the E Voting System.</label>
                            <span class="span-box"></span>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-dismiss="modal">Close</button>
                    <a href="{{asset('vote/in_person/true')}}"><button type="submit" class="btn btn-custom">Proceed</button></a>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- Proxy Modal -->
<div class="modal fade" id="proxy_agreement_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Terms and Conditions</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="GET" action="{{asset('user/vote/proxy/form')}}" id="">

                <!-- Modal body -->
                <div class="modal-body">
                    <br class="inper_br">
                    <h4 class="h4-content">
                        I,<br>
                        <input type="text" class="form-control input_person_user" id="input-proxy" readonly="" value="Paul Asuncion"><br>
                        do hereby constitute or, in the absence and/or non-attendance of my PROXY the Chairman of the Meeting,(EXCEPT THAT THE CHAIRMAN MAY NOT VOTE FOR CANDIDATES to the Board of Directors), as my attorney-in-fact and proxy, to represent me at the ANNUAL GENERAL MEETING of Valley Golf & Country Club, Inc., to be held on 25 September 2022 at 4:00pm, and/or any postponements or adjournment(s) thereof, as fully and to all intents and purposes as I lawfully might or could do if present and voting in person, hereby ratifying and confirming any and all actions taken on matters which may properly come before him during such meeting or adjournment(s) thereof.
                    </h4>
                    <h4 class="h4-content">
                        I also declare and signify my intent to participate by remote communication and exercise the right to vote in absentia in the September 25, 2022 Annual Stockholders’ Meeting of Valley Golf & Country Club, Inc.
                    </h4>
                    <h4 class="h4-content">
                        By participating in the on-line voting and in compliance with Republic Act No. 10173 (or the Data Privacy Act of 2012) and its Implementing Rules and Regulations (IRR) effective since September 8, 2016, I freely and voluntarily authorize Valley Golf & Country Club, Inc. to collect, record, organize, use, disclose and/or otherwise process any personal information submitted in connection with this declaration only for the purpose of allowing me to participate in the stockholders’ meeting by remote communication and vote in absentia. I understand that by giving this consent, I am not waiving any of my rights other than as authorized herein under the Data Privacy Act of 2012 and other applicable laws.
                    </h4><br>
                    <h5 class="h5-title-terms">Voting Rules:</h5>
                    <h4 class="h4-content">
                        Each shareholder owning one share, or his Proxy is entitled to cast one vote for as many positions for directors as are being voted upon or cumulate the votes and cast it in favor one or two candidates. Example –If there are 3 seats for directors open for voting, the shareholder or his proxy is entitled to 3 votes. He/she may either distribute the 3 votes among 3 candidates of his/her choice, cast all 3 votes in favor of one candidate or cast 2 votes in favor of 1 candidate and 1 vote for another candidate. If the shareholder owns more than one share, each share shall be entitled to 3 votes which he/she may distribute evenly or cumulate in favor of just one or two candidates.
                    </h4><br>
                    <h5 class="h5-title-terms">Disclaimer:</h5>
                    <h4 class="h4-content">
                        The information contained given on this site has been compiled for the convenience and general information of the stockholders. Valley Golf and Country Club, Inc. has no right to change the information you provided on the website. Although Valley Golf provided the voting platform to facilitate e-voting system for stockholders related matter and we have endeavored to ensure appropriate check are in place and to enable true and fair voting and that data is not accessed by any person when the voting window is open. However, Valley Golf shall not be liable in case of any unauthorized access by any person.
                    </h4>
                    <br class="inper_br">
                    <div class="form-group">
                        <div class="chiller_cb">
                            <input class="form-check-input" id="checkbox_2" type="checkbox" required="">
                            <label for="checkbox_2">I have read and agreed to the terms and conditions of the E Voting System.</label>
                            <span class="span-box"></span>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-custom">Proceed</button>
                </div>

            </form>

        </div>
    </div>
</div>



@endsection


@section('js')
<style>
    .login-button-prominent {
        background: linear-gradient(135deg, #5E7C4C, #8DA66E) !important;
        border: 3px solid #FFFFFF !important;
        box-shadow: 0 8px 25px rgba(94, 124, 76, 0.4), 0 0 20px rgba(94, 124, 76, 0.3) !important;
        font-size: 1.4em !important;
        font-weight: bold !important;
        color: #FFFFFF !important;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3) !important;
        transform: scale(1.1) !important;
        animation: pulse-glow-corporate 2s infinite !important;
        position: relative !important;
        overflow: hidden !important;
        transition: all 0.3s ease !important;
        white-space: nowrap !important;
        min-width: 300px !important;
        padding: 15px 30px !important;
        border-radius: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
    }

    .login-button-prominent:hover {
        background: linear-gradient(135deg, #2F4A3C, #5E7C4C) !important;
        transform: scale(1.15) !important;
        box-shadow: 0 12px 35px rgba(47, 74, 60, 0.6), 0 0 30px rgba(47, 74, 60, 0.5) !important;
    }

    @keyframes pulse-glow-corporate {

        0%,
        100% {
            box-shadow: 0 8px 25px rgba(94, 124, 76, 0.4), 0 0 20px rgba(94, 124, 76, 0.3);
        }

        50% {
            box-shadow: 0 8px 25px rgba(94, 124, 76, 0.6), 0 0 30px rgba(94, 124, 76, 0.5);
        }
    }

    .compact-tile::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #5E7C4C;
        transition: width 0.3s ease;
    }

    .compact-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #5E7C4C;
    }

    .compact-tile.voting-period::before {
        background: #dc3545;
    }

    .compact-tile.meeting-day::before {
        background: #8DA66E;
    }

    .tile-date {
        font-weight: 700;
        font-size: 1.1rem;
        color: #000000;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .tile-day {
        font-weight: 500;
        color: #2F4A3C;
        font-size: 0.9rem;
    }

    .tile-activity {
        color: #2F4A3C;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .voting-period {
        border-color: #fed7d7;
        background: linear-gradient(145deg, #fff5f5, #fed7d7);
    }

    .meeting-day {
        border-color: #c6f6d5;
        background: linear-gradient(145deg, #f0fff4, #c6f6d5);
    }

    @media (max-width: 768px) {
        .compact-tiles-container {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .compact-tile {
            padding: 1rem;
        }

        .tile-date {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .tile-activity {
            font-size: 0.9rem;
        }
    }
</style>
<script>
    function login() {
        location.href = BASE_URL + 'user/login';
    }

    function vote() {
        location.href = BASE_URL + 'user/vote';
    }

    function register() {
        location.href = BASE_URL + 'user/onsite-register';
    }


    $(document).ready(function() {

        $(document).on('submit', '#requestBallotForm', function(e) {

            e.preventDefault();

            let revoke = $("input[name='revoke_proxy']:checked").val();

            if (!revoke) {

                alert("Please choose from the options.");

                return;

            }

            e.preventDefault();

            $.ajax({
                url: BASE_URL + 'user/ballot/stockholder-online',
                method: 'POST',
                dataType: 'json',
                data: {
                    revoke: revoke
                },
                statusCode: {
                    200: function(data) {


                        location.href = `{{asset("user/ballot/stockholder-online")}}/${data['ballotId']}`;

                    }
                },

            });

        })
    })
</script>
@endsection