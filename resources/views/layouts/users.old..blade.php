<!DOCTYPE html>
<html lang="en">

<head>
	<title>Online Voting System</title>
	<meta charset="utf-8">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="{{asset('images/small_logo.png')}}" />
	<!-- No Cache Meta Tags -->
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="0">
	@php
	$__chapaza_ver = @filemtime(public_path('css/user/Chapaza.ttf')) ?: time();
	$__avenir_black_ver = @filemtime(public_path('css/user/AvenirLTStd-Black.otf')) ?: time();
	$__avenir_roman_ver = @filemtime(public_path('css/user/AvenirLTStd-Roman.otf')) ?: time();
	@endphp
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
</head>
<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
	// Global jQuery AJAX setup to prevent caching and set CSRF + cache headers
	$.ajaxSetup({
		cache: false,
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			'Cache-Control': 'no-cache, no-store, must-revalidate',
			'Pragma': 'no-cache',
			'Expires': '0'
		},
		beforeSend: function(xhr) {
			xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
			xhr.setRequestHeader('Pragma', 'no-cache');
			xhr.setRequestHeader('Expires', '0');
		}
	});

	// Append a cache-busting timestamp to every AJAX URL (best-effort)
	$(document).ajaxSend(function(event, jqXHR, options) {
		try {
			if (typeof options.url === 'string') {
				if (options.url.indexOf('?') === -1) {
					options.url += '?_=' + new Date().getTime();
				} else {
					options.url += '&_=' + new Date().getTime();
				}
			}
		} catch (e) {
			// ignore
		}
	});
</script>


<style>
	@font-face {
		src: url('{{ asset("css/user/Chapaza.ttf") }}?v={{ $__chapaza_ver }}') format('truetype');
		font-family: chapaza-bold;
	}

	@font-face {
		font-family: 'AvenirLTStd-Black';
		src: url('{{ asset("css/user/AvenirLTStd-Black.otf") }}?v={{ $__avenir_black_ver }}') format('opentype');
	}

	@font-face {
		font-family: AvenirLTStd-Roman;
		src: url('{{ asset("css/user/AvenirLTStd-Roman.otf") }}?v={{ $__avenir_roman_ver }}') format('opentype');

	}

	.navbar-toggler {
		border: 2px solid #ffffff !important;
		margin: 1px;
	}

	body {
		background-color: #c9cdb6;
	}

	.bg-color {
		background-color: #6b8e4e !important;
	}

	.bg-new-green {
		background-color: #304c40;
	}

	.nav-item .nav-link {
		color: #ffffff;
	}

	#myImg {
		width: 150px;
		/* height: 350px; */
	}

	/* .span-userd {
      display: block;
    } */

	span.span-details {
		color: #ffffff;
		font-size: 0.813em;
		text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
		display: block;
	}

	.div-user-details {
		text-align: center;
	}

	.h2-annual-title {
		font-family: chapaza-bold;
		font-size: 1.875em !important;
	}

	.card-header {
		font-size: 1.563em;
		font-family: chapaza-bold;
	}

	#Calendar_table .th-head,
	.td-head {
		font-family: AvenirLTStd-Roman;
	}

	.span-accnt {
		font-size: 0.938em;
		text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
		font-family: AvenirLTStd-Roman;
	}

	.div-user-details {
		border: 1px solid #ffffff;
		padding: 10px;
	}

	.p-username-welcome {
		color: green;
		font-size: 1.438em;
		font-family: chapaza-bold;
	}

	.card-calendar {
		border-radius: 10px;
	}

	.a-welcome-text {
		text-align: center;
		color: #ffffff;
	}

	.a-welcome-text:hover {
		color: #c9cdb6;
	}

	.div-accordion {
		text-align: center;
		margin: 5px;
	}

	.p-registration {
		text-align: center;
		font-family: AvenirLTStd-Roman;
		font-size: 1.5em;
		margin-top: 40px;
	}

	.a-link-guidelines {
		color: #304c40;
		font-weight: bold;
		text-decoration: underline;
	}

	/* .div-accordion {
    display: flex;
    justify-content: center;
  } */

	.h2-desc {
		font-family: AvenirLTStd-Roman;
		font-size: 1.563em;
	}

	.newBtndark {
		color: #fff;
		background-color: #304c40;
		border-color: #304c40;
	}

	.newBtndark:hover {
		color: #fff;
	}

	.newBtnlight {
		color: #fff;
		background-color: #6b8e4e;
		border-color: #6b8e4e;
	}

	.newBtnlight:hover {
		color: #fff;
	}

	.list-group .list-a {
		color: #304c40;
		font-weight: bold;
	}


	/*-----FUNKY CSS RADIO BUTTON-------*/

	/* .radio-section {
    display: flex;
    align-items: center;
    justify-content: center;
  } */

	h1 {
		margin-bottom: 20px;
	}

	.radio-item [type="radio"] {
		display: none;
	}

	.radio-item+.radio-item {
		margin-top: 15px;
	}

	.radio-item label {
		display: block;
		padding: 20px 60px;
		/* background: #1d1d42; */
		/* border: 2px solid rgba(255, 255, 255, 0.1); */
		border: 2px solid #c9cdb6;
		border-radius: 8px;
		cursor: pointer;
		font-size: 16px;
		font-weight: 400;
		min-width: 250px;
		white-space: nowrap;
		position: relative;
		transition: 0.4s ease-in-out 0s;
		font-family: 'AvenirLTStd-Black';
		/* color: #ffffff; */
	}

	.radio-item label:after,
	.radio-item label:before {
		content: "";
		position: absolute;
		border-radius: 50%;
	}

	.radio-item label:after {
		height: 19px;
		width: 19px;
		border: 2px solid #304c40;
		left: 19px;
		top: calc(50% - 12px);
	}

	.radio-item label:before {
		background: #304c40;
		height: 19px;
		width: 19px;
		left: 21px;
		top: calc(50%-5px);
		transform: scale(3);
		opacity: 0;
		visibility: hidden;
		transition: 0.4s ease-in-out 0s;
	}

	.radio-item [type="radio"]:checked~label {
		border-color: #c9cdb6;
		background-color: #6b8e4e;
		color: #fff;
	}

	.radio-item [type="radio"]:checked~label::before {
		opacity: 1;
		visibility: visible;
		transform: scale(1);
	}


	/*------COPY CSS-----*/
	.wrimagecard-topimage_title .h4-label {
		color: #6b8e4e;
	}


	.grow {
		/*  display: inline-block;*/
		transition-duration: 0.3s;
		transition-property: transform;
		-webkit-tap-highlight-color: transparent;
		transform: translateZ(0);
		box-shadow: 0 0 1px transparent;
	}

	.grow:hover {
		transform: scale(1.1);
		color: #ffffff;
	}

	.card-nav {
		color: #000000;
	}

	.h4-label {
		text-align: center;
	}

	.wrimagecard .fas-i-con {
		font-size: 2em;
	}


	.wrimagecard {
		margin-top: 10px;
		margin-bottom: 1.5rem;
		text-align: left;
		position: relative;
		background: #fff;
		box-shadow: 12px 15px 20px 0px rgba(46, 61, 73, 0.15);
		border-radius: 4px;
		width: 100%;
		transition: all 0.3s ease;
	}

	.wrimagecard .fa {
		position: relative;
		font-size: 70px;
	}

	.wrimagecard-topimage_header {
		padding: 15px;
	}

	a.wrimagecard:hover,
	.wrimagecard-topimage:hover {
		box-shadow: 2px 4px 8px 0px rgba(46, 61, 73, 0.2);
	}

	.wrimagecard-topimage a {
		width: 100%;
		height: 100%;
		display: block;
	}

	.wrimagecard-topimage_title {
		padding: 10px 24px;
		height: 85px;
		padding-bottom: 0.75rem;
		position: relative;
		background-color: #ffffff;
	}

	.wrimagecard-topimage a {
		border-bottom: none;
		text-decoration: none;
		color: #525c65;
		transition: color 0.3s ease;
	}

	.card-header:not(.collapsed) .rotate-icon {
		transform: rotate(180deg);
	}

	#accordion .card-header {
		cursor: pointer;
	}

	.btn-dark-success {
		color: #fff;
		background-color: #304c40;
		border-color: #304c40;

	}

	.btn-dark-success:hover {
		color: #fff;
		background-color: #6b8e4e;
		border-color: #fff;
		/*set the color you want here*/
	}

	.btn-new {
		padding: 5px 5px;
		font-size: 16px;
		border-radius: 3px;
		width: 40%;
	}

	.blink-a {
		font-size: 29px;
		font-family: cursive;
		color: #fbe9e7;
		border-bottom: 2px solid #fff;
	}

	.blink-a:hover {
		color: #e0f2f1;
		text-decoration: none;
	}

	.blink {
		animation: blink 1s linear infinite;
	}

	@keyframes blink {
		0% {
			opacity: 0;
		}

		50% {
			opacity: .5;
		}

		100% {
			opacity: 1;
		}
	}


	/*----------Check box Chiller CSS-------------*/
	.span_pseudo,
	.chiller_cb .span-box:before,
	.chiller_cb .span-box:after {
		content: "";
		display: inline-block;
		background: #fff;
		width: 0;
		height: 0.2rem;
		position: absolute;
		transform-origin: 0% 0%;
	}

	.chiller_cb {
		position: relative;
		height: 2rem;
		display: flex;
		align-items: center;
	}

	.chiller_cb input {
		display: none;
	}

	.chiller_cb input:checked~.span-box {
		background: #304c40;
		border-color: #304c40;
	}

	.chiller_cb input:checked~.span-box:before {
		width: 1rem;
		height: 0.15rem;
		transition: width 0.1s;
		transition-delay: 0.3s;
	}

	.chiller_cb input:checked~.span-box:after {
		width: 0.4rem;
		height: 0.15rem;
		transition: width 0.1s;
		transition-delay: 0.2s;
	}

	.chiller_cb input:disabled~.span-box {
		background: #ececec;
		border-color: #dcdcdc;
	}

	.chiller_cb input:disabled~label {
		color: #dcdcdc;
	}

	.chiller_cb input:disabled~label:hover {
		cursor: default;
	}

	.chiller_cb label {
		padding-left: 2rem;
		position: relative;
		z-index: 2;
		color: #304c40;
		font-weight: bold;
		cursor: pointer;
		font-size: 1.125em;
		margin-bottom: 0;
	}

	.chiller_cb .span-box {
		display: inline-block;
		width: 1.2rem;
		height: 1.2rem;
		border: 2px solid #ccc;
		position: absolute;
		left: 0;
		transition: all 0.2s;
		z-index: 1;
		box-sizing: content-box;
	}

	.chiller_cb .span-box:before {
		transform: rotate(-55deg);
		top: 1rem;
		left: 0.37rem;
	}

	.chiller_cb .span-box:after {
		transform: rotate(35deg);
		bottom: 0.35rem;
		left: 0.2rem;
	}


	/*---------Term and Condition CSS-------------*/

	.input_person_user {
		color: #304c40;
		font-family: Avenir LT Std;
		background: transparent;
		border: none;
		border-radius: 0px;
		border-bottom: 1px solid #000000;
		outline: none;
		box-shadow: none;
		font-size: 1.25em;
	}

	.h4-content {
		color: #6b8e4e;
		font-size: 1.25em;
		text-align: justify;
		font-family: AvenirLTStd-Roman;
	}

	#in_person_agreement_modal .form-control:disabled,
	.form-control[readonly] {
		background-color: transparent;
		opacity: 1;
	}

	.h5-title-terms {
		color: #304c40;
		font-family: chapaza-bold;
		font-weight: bold;
		font-size: 1.375em;
	}

	.btn-custom {
		color: #fff;
		background-color: #6b8e4e;
		border-color: #6b8e4e;
		font-family: Avenir LT Std;
	}

	/*--------------Media Query-------------------*/

	/* -----------iPhone X----------- */
	/* Portrait and Landscape */
	@media only screen and (min-device-width: 375px) and (max-device-width: 812px) and (-webkit-min-device-pixel-ratio: 3) {

		.image-valley-logo {
			width: 100%;
		}

		span.span-details {
			text-align: center;
			font-size: 0.875em;
		}

		.div-user-details .span-accnt {
			font-size: 0.813em;
		}

		.p-username-welcome {
			font-size: 1.375em;
		}

		.container-Div .h2-annual-title {
			font-size: 1.375em !important;
		}

		#accordion .card-header .a-welcome-text {
			font-size: 1.125em;
		}

		.wrimagecard {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 70%;
		}

		.modal-revoke-prxy .modal-header {
			padding: 10px;
		}

		.modal-revoke-prxy .h2-desc {
			font-size: 1.063em;
		}

		.modal-revoke-prxy .radio-item label {
			padding: 10px 60px;
			border-radius: 4px;
			font-size: 0.938em !important;
		}

		.modal-revoke-prxy .radio-item label:after {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
		}

		.modal-revoke-prxy .radio-item label:before {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
			transform: scale(3);
			opacity: 0;
			visibility: hidden;
			transition: 0.4s ease-in-out 0s;
		}

		.p-registration {
			font-size: 1.063em;
		}

		.input_person_user {
			font-size: 1em;
		}

		.h5-title-terms {
			font-size: 1.063em;
		}

		.h4-content {
			font-size: 0.938em;
		}

		.chiller_cb label {
			font-size: 0.875em;
		}

		.btn-custom {
			padding: 0.188rem 0.5rem;
			font-size: 0.813rem;
		}

	}

	/* ----------- iPhone 4 and 4S ----------- */

	/* Portrait and Landscape */
	@media only screen and (min-device-width: 320px) and (max-device-width: 480px) and (-webkit-min-device-pixel-ratio: 2) {

		.image-valley-logo {
			width: 80%;
			display: block;
			margin-left: auto;
			margin-right: auto;
		}

		span.span-details {
			text-align: center;
			font-size: 0.625em;
		}

		.p-username-welcome {
			font-size: 1.125em
		}

		.container-Div .h2-annual-title {
			font-size: 1.25em !important;
		}

		#accordion .card-header .a-welcome-text {
			font-size: 0.688em;
		}

		.wrimagecard {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 70%;
		}

		.modal-revoke-prxy .modal-header {
			padding: 10px;
		}

		.modal-revoke-prxy .h2-desc {
			font-size: 1.063em;
		}

		.modal-revoke-prxy .radio-item label {
			padding: 10px 60px;
			border-radius: 4px;
			font-size: 0.688em;
		}

		.modal-revoke-prxy .radio-item label:after {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
		}

		.modal-revoke-prxy .radio-item label:before {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
			transform: scale(3);
			opacity: 0;
			visibility: hidden;
			transition: 0.4s ease-in-out 0s;
		}

		.p-registration {
			font-size: 1.063em;
		}

		.input_person_user {
			font-size: 1em;
		}

		.h5-title-terms {
			font-size: 1.063em;
		}

		.h4-content {
			font-size: 0.938em;
		}

		.chiller_cb label {
			font-size: 0.875em;
		}

		.btn-custom {
			padding: 0.188rem 0.5rem;
			font-size: 0.813rem;
		}
	}


	/* ----------- iPhone 11 and XR ----------- */
	@media only screen and (width: 414px) and (height: 896px) and (-webkit-device-pixel-ratio: 2) {

		.span-details {
			font-size: 0.813em !important;
		}

		.p-username-welcome {
			font-size: 1.125em
		}

		.container-Div .h2-annual-title {
			font-size: 1.375em !important;
		}

		#accordion .card-header {
			padding: 0.75rem 1.25rem !important;
		}

		#accordion .card-header .a-welcome-text {
			font-size: 0.75em !important;
		}

		.wrimagecard .fas-i-con {
			font-size: 2em !important;
		}


		.wrimagecard {
			width: 70% !important;
		}

		.wrimagecard-topimage_header {
			padding: 12px !important;
		}

		.wrimagecard-topimage_title .h4-label {
			font-size: 1em !important;
		}

		.btn-new {
			padding: 5px 5px !important;
			font-size: 0.938em !important;
			width: 40% !important;
		}

		.div-user-details .span-accnt {
			font-size: 0.875em;
		}

		.modal-revoke-prxy .h2-desc {
			font-size: 1.188em;
		}

		.modal-revoke-prxy .radio-item label {
			font-size: 1em;
			padding: 15px 60px;
			border-radius: 4px;
		}

		.p-registration {
			font-size: 1.063em;
		}

	}


	/* ----------- For iPhone 11, 12, 13 and 14 ----------- */
	@media only screen and (width: 390px) and (height: 844px) and (-webkit-device-pixel-ratio: 3) {

		span.span-details {
			text-align: center;
			font-size: 0.813em !important;
		}

		.p-username-welcome {
			font-size: 1.125em
		}

		.container-Div .h2-annual-title {
			font-size: 1.375em !important;
		}

		#accordion .card-header {
			padding: 0.75rem 1.25rem !important;
		}

		#accordion .card-header .a-welcome-text {
			font-size: 0.75em !important;
		}

		.wrimagecard .fas-i-con {
			font-size: 2em !important;
		}


		.wrimagecard {
			width: 70% !important;
		}

		.wrimagecard-topimage_header {
			padding: 12px !important;
		}

		.wrimagecard-topimage_title .h4-label {
			font-size: 1em !important;
		}

		.btn-new {
			padding: 5px 5px !important;
			font-size: 0.938em !important;
			width: 40% !important;
		}

		.div-user-details .span-accnt {
			font-size: 0.875em;
		}

		.modal-revoke-prxy .h2-desc {
			font-size: 1.188em;
		}

		.modal-revoke-prxy .radio-item label {
			padding: 15px 60px;
			border-radius: 4px;
			font-size: 1em;
		}

		.p-registration {
			font-size: 1.063em;
		}

		.input_person_user {
			font-size: 1em;
		}

		.h5-title-terms {
			font-size: 1.063em;
		}

		.h4-content {
			font-size: 0.938em;
		}

		.chiller_cb label {
			font-size: 0.875em;
		}

		.btn-custom {
			padding: 0.188rem 0.5rem;
			font-size: 0.813rem;
		}

	}


	/* ----------- iPhone 14 Pro Max ----------- */
	@media only screen and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) {

		span.span-details {
			text-align: center;
			font-size: 0.813em !important;
		}

		.p-username-welcome {
			font-size: 1.125em
		}

		.container-Div .h2-annual-title {
			font-size: 1.5em !important;
		}

		#accordion .card-header {
			padding: 0.75rem 1.25rem !important;
		}

		#accordion .card-header .a-welcome-text {
			font-size: 0.75em !important;
		}

		.wrimagecard .fas-i-con {
			font-size: 2em !important;
		}


		.wrimagecard {
			width: 70% !important;
		}

		.wrimagecard-topimage_header {
			padding: 12px !important;
		}

		.wrimagecard-topimage_title .h4-label {
			font-size: 1em !important;
		}

		.btn-new {
			padding: 5px 5px !important;
			font-size: 0.938em !important;
			width: 40% !important;
		}

		.div-user-details .span-accnt {
			font-size: 0.875em;
		}

		.modal-revoke-prxy .h2-desc {
			font-size: 1.188em;
		}

		.modal-revoke-prxy .radio-item label {
			padding: 15px 60px;
			border-radius: 4px;
			font-size: 1em;
		}

		.p-registration {
			font-size: 1.063em;
		}

		.input_person_user {
			font-size: 1em;
		}

		.h5-title-terms {
			font-size: 1.063em;
		}

		.h4-content {
			font-size: 0.938em;
		}

		.chiller_cb label {
			font-size: 0.875em;
		}

		.btn-custom {
			padding: 0.188rem 0.5rem;
			font-size: 0.813rem;
		}

	}



	/*------JioPhone 2--------*/
	@media only screen and (max-device-width: 240px) {
		.image-valley-logo {
			width: 80%;
			display: block;
			margin-left: auto;
			margin-right: auto;
		}

		span.span-details {
			text-align: center;
			font-size: 0.625em;
		}

		.container-Div .h2-annual-title {
			font-size: 0.938em;
		}

		/* 
    #accordion .card-header .a-welcome-text {
      font-size: 0.938em;
    } */

		#accordion .icon-size {
			font-size: 0.563em;
		}

		#accordion .card-header {
			padding: 5px;
		}

		.wrimagecard {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 100%;
		}

		.span-accnt {
			font-size: 0.75em;
		}

		.wrimagecard-topimage_title .h4-label {
			font-size: 0.875em;
		}

		.btn-new {
			padding: 5px 3px;
			font-size: 0.875em;
			width: 35%;
		}

		.wrimagecard-topimage_header {
			padding: 5px;
		}


		.wrimagecard .fas-i-con {
			font-size: 1.563em;
		}

		.modal-revoke-prxy .modal-header {
			padding: 10px;
		}

		.modal-revoke-prxy .h2-desc {
			font-size: 0.875em;
		}

		.modal-revoke-prxy .radio-item label {
			padding: 10px 60px;
			border-radius: 4px;
			font-size: 0.625em !important;
			min-width: 150px;
		}

		.modal-revoke-prxy .radio-item label:after {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
		}

		.modal-revoke-prxy .radio-item label:before {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
		}

	}


	/*---------Galaxy Fold-----------*/
	@media only screen and (min-device-width: 280px) and (max-device-width: 653px) and (-webkit-min-device-pixel-ratio: 2) {

		.image-valley-logo {
			width: 80%;
			display: block;
			margin-left: auto;
			margin-right: auto;
		}

		span.span-details {
			text-align: center;
			font-size: 0.625em;
		}

		.container-Div .h2-annual-title {
			font-size: 0.938em;
		}

		/* #accordion .card-header .a-welcome-text {
      font-size: 0.563em;
    } */

		#accordion .icon-size {
			font-size: 0.563em;
		}

		#accordion .card-header {
			padding: 5px;
		}

		.wrimagecard {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 100%;
		}

		.span-accnt {
			font-size: 0.75em;
		}

		.wrimagecard-topimage_title .h4-label {
			font-size: 0.875em;
		}

		.btn-new {
			padding: 5px 3px;
			font-size: 0.875em;
			width: 35%;
		}

		.wrimagecard-topimage_header {
			padding: 5px;
		}

		.wrimagecard .fas-i-con {
			font-size: 1.563em;
		}

		.modal-revoke-prxy .modal-header {
			padding: 10px;
		}

		.modal-revoke-prxy .h2-desc {
			font-size: 0.875em;
		}

		.modal-revoke-prxy .radio-item label {
			padding: 10px 60px;
			border-radius: 4px;
			/* font-size: 0.625em; */
			min-width: 150px;
		}

		.modal-revoke-prxy .radio-item label:after {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
		}

		.modal-revoke-prxy .radio-item label:before {
			height: 15px;
			width: 15px;
			left: 19px;
			top: calc(30%);
		}

	}


	@media (min-width: 1200px) {
		.my-custom-container {
			width: 900px;
		}
	}
</style>


@yield('head')

<script>
	const BASE_URL = "{{ asset('/') }}";



	function handleSuccess(xhr, reload = true) {
		Swal.fire({
			icon: 'success',
			title: 'Success!',
			text: xhr.message
		}).then(() => {
			location.reload();
		});
	}

	function handleError(xhr) {

		let icon = 'error';
		let title = 'Error!';
		let message = xhr.responseJSON.message || '';

		if (xhr.status === 400) {
			icon = 'info';
			title = 'Info!';
		}



		if (xhr.status === 403) {
			message = 'Insufficient privileges';
			icon = 'info';
			title = 'Info!';
		}


		if (xhr.status === 500) {

			message = "An unexpected error was encountered. Please contact your administrator if the error persists.";

		}

		Swal.fire({
			icon: icon,
			title: title,
			text: message
		});
	}
</script>
</head>

<body>
	<nav class="navbar navbar-expand-sm bg-color navbar-dark p-0">
		<div class="container">
			<div class="col-md-8">
				<a href="{{asset('')}}"><img src="{{asset('images/home_logo.png')}}" id="" class="image-valley-logo"></a>
			</div>
			<div class="col-md-4">
				<span class="span-details">Don Celso S. Tuason Ave.</span>
				<span class="span-details">Antipolo City 1870</span>
				<span class="span-details">Philippines</span>
				<span class="span-details">Trunk Lines: 8658 4901 to 03</span>
				<span class="span-details">Facsimile No: 8658 4919</span>
			</div>
		</div>
		</div>
	</nav>
	<nav class="navbar navbar-expand-lg bg-new-green navbar-light sticky-top navbar-shadow p-0">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarvalley">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="navbar-collapse collapse" id="navbarvalley">
			<ul class="navbar-nav nav-fill ml-auto  user-navigation">
				<li class="nav-item">
					<a class="nav-link" href="{{asset('')}}" id="nav_btn_back" style="display: none;"><i class="far fa-hand-point-left"></i> BACK</a>
				</li>
				<li class="nav-item">


					@if(Auth::check())
					<a class="nav-link text-white" href="{{ asset('logout') }}"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
					@endif

				</li>
			</ul>
		</div>
	</nav>

	<div class="container my-custom-container">


		@yield('content')


	</div>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	@yield('js')

</body>

</html>