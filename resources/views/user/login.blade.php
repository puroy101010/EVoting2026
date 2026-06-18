@extends('layouts.custom')

@section('css')

<link rel="stylesheet" type="text/css" href="{{asset('css/login.css')}}?<?php echo filemtime('css/login.css') ?>">
<link rel="icon" type="image/png" href="{{asset('images/small_logo.png')}}" />

<script src="{{ asset('js/e-voting/app.js') }}" defer></script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

<style>
/* OTP form alignment and resend button styles */
#formContent2 form {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 10px;
	padding: 8px 24px 18px;
}

#formContent2 input[name=otp] {
	width: 76%;
	max-width: 360px;
	text-align: center;
	padding: 12px 14px;
	border-radius: 8px;
	border: 1px solid #e6e6e6;
	box-shadow: none;
	font-size: 1rem;
}

.btn-resend-otp {
	background: #ffffff;
	border: none;
	padding: 8px 18px;
	border-radius: 6px;
	cursor: pointer;
	color: #304c40;
	font-weight: 600;
	box-shadow: none;
}
.btn-resend-otp:disabled { opacity: 0.6; cursor: not-allowed; }

#otp-rate-limit-alert {
	display: none;
	background: #fff4ce;
	color: #7a5b00;
	border-radius: 8px;
	padding: 10px 12px;
	margin: 6px auto 0;
	width: 88%;
	max-width: 420px;
	text-align: center;
	font-size: 0.95em;
}

#otp-expiration-text { color: #ff6b6b; font-weight:600; }

.lable-otp strong { display:block; margin-top:4px; }
</style>
@endsection

@section('content')

<div id="particles-js"></div>
<div class="div-wrapper" id="login_form_wrapper">
	<div class="wrapper fadeInDown">
		<div id="formContent">
			<div class="fadeIn first">
				<img src="{{asset('images/logo-login.png')}}" id="icon" alt="User Icon" class="m-1 login-logo">
			</div>

			<div class="welcome-evoting mb-3" style="display:flex;align-items:center;justify-content:center;gap:0.7em;">
				<span style="font-size:1.15em;font-weight:600;color:#2F4A3C;letter-spacing:0.5px;">Welcome to the E-Voting System</span>
			</div>

			<div class="login-helper fadeIn second mb-3" style="display:flex;align-items:center;justify-content:center;gap:0.6em;">
				<span style="color:#2F4A3C;font-size:1.04em;font-weight:500;">Enter your email to log in</span>
			</div>
			<form id="login_form" method="POST">
				@csrf
				<input type="email" name="email" id="date" class="fadeIn fourth input-time" placeholder="Email" autocomplete="off" required>
				<button type="submit" class="mt-3 fadeIn fourth" value="SUBMIT" id="btn_request_otp">Request OTP <img src="{{asset('images/loading.gif')}}" class="ml-2 login-verify-otp" style="display: none"></button> <br><br>
			</form>
			<div id="formFooter">
				<p style="color: #fff">Login with your registered email address.</p>
				<p style="color: #fff; font-size: 0.85em; margin-top: 10px;">Need assistance? Call <strong>8658-4901</strong></p>
			</div>
		</div>
	</div>
</div>

<div id="formContent2" class="wrappers mx-auto" style="display: none;">
	<form id="form_login_otp" method="POST"><br>
		<div class="lable-otp text-center mb-3" style="display:flex;flex-direction:column;align-items:center;gap:0.5em;font-family:'Segoe UI', 'Roboto', 'Arial', sans-serif;">
			<span style="font-size:.9em;"><i class="fas fa-envelope-open-text"></i></span>
			<span style="font-size:.9em;font-weight:500;">A 5-digit numeric OTP has been sent to</span>
			<strong id="user-email" style="color:#304c40;font-size:1.08em;font-family:'Segoe UI','Roboto','Arial',sans-serif;"></strong>
		</div>

		<div id="otp-rate-limit-alert" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
			<!-- <strong>Please Wait</strong> -->
			<!-- <div id="otp-wait-message">This is a message</div> -->

		</div>


		<div style="text-align: center; margin-bottom: 15px; font-size: 0.85em; color: #ff6b6b;">
			<span id="otp-expiration-text">OTP expires in <strong id="otp-expiration-timer">05:00</strong></span>
		</div>
		<input type="text" name="otp" class="fadeIn second" placeholder="Enter 5-digit OTP" autocomplete="off" maxlength="5" required>
		<div style="text-align: center; margin-top: 10px; width:100%;">
			<button type="button" class="btn-resend-otp" id="btn_resend_otp">Request new OTP</button>
		</div>
		<button type="submit" class="btn btn-green btn-md rounded-0 mb-4 mt-4" id="btn_sumbit_otp">Submit</button>
		
	</form>

	<div id="formFooter">
		<p class="underlineHover text-white footer-text">
			Your OTP has been sent from Valley’s authorized email address. If you do not receive the email, please check your spam folder or call <strong>8658-4901</strong> for assistance.
		</p>
	</div>
</div>

<script type="text/javascript">
	// Add loading state management
	function setLoadingState(button, isLoading) {
		if (isLoading) {
			button.attr('disabled', true);
			button.find('.login-verify-otp').show();
			button.addClass('loading');
		} else {
			button.attr('disabled', false);
			button.find('.login-verify-otp').hide();
			button.removeClass('loading');
		}
	}

	// Add input validation feedback
	function validateInput(input) {
		const value = input.val().trim();
		const minLength = parseInt(input.attr('minlength')) || 0;
		const isValid = value.length >= minLength;

		if (isValid) {
			input.removeClass('error').addClass('valid');
		} else {
			input.removeClass('valid').addClass('error');
		}
		return isValid;
	}

	// Real-time validation
	$('input[type="text"], input[type="email"]').on('input', function() {
		validateInput($(this));
	});

	$(document).on('submit', '#login_form', function(e) {

		e.preventDefault();

		// Validate inputs before submission
		let isValid = true;
		$(this).find('input[required]').each(function() {
			if (!validateInput($(this))) {
				isValid = false;
			}
		});

		if (!isValid) {
			Swal.fire({
				icon: 'warning',
				title: 'Validation Error',
				text: 'Please fill in all required fields correctly.',
				confirmButtonColor: '#304c40'
			});
			return;
		}

		handleRequestOTP($(this));
	})


	$(document).on('click', '#btn_resend_otp', function() {
		// Clear alerts and request a new OTP
		$('#otp-rate-limit-alert').hide().text('');
		handleRequestOTP($('#login_form'));
	})


	// Helper function to format seconds to mm:ss
	function formatTime(seconds) {
		const mins = Math.floor(seconds / 60);
		const secs = seconds % 60;
		return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
	}

	// Start OTP expiration timer and handle expiry UI changes
	function startOtpExpirationTimer(initialSeconds) {
		let otpExpirationSeconds = parseInt(initialSeconds, 10) || 0;
		$('#otp-expiration-timer').text(formatTime(otpExpirationSeconds));

		const otpExpirationInterval = setInterval(function() {
			otpExpirationSeconds--;
			$('#otp-expiration-timer').text(formatTime(otpExpirationSeconds));

			if (otpExpirationSeconds <= 0) {
				clearInterval(otpExpirationInterval);
				$('#otp-rate-limit-alert').text('OTP has expired. Please click resend to request a new OTP.').show();
				$('#otp-expiration-text').hide();
				$('[name=otp]').val('').attr('disabled', false);
				$('#btn_resend_otp').attr('disabled', false).show();
				$('#btn_sumbit_otp').attr('disabled', true);
			}
		}, 1000);

		// Store interval ID for cleanup
		$(document).data('otpExpirationInterval', otpExpirationInterval);
	}

	function handleRequestOTP(form) {
		$.ajax({
			url: "{{asset('otp/request')}}",
			method: "POST",
			dataType: 'json',
			data: form.serialize(),
			beforeSend: function() {
				setLoadingState($('#btn_request_otp'), true);
			},
			complete: function() {
				setLoadingState($('#btn_request_otp'), false);
			},
			statusCode: {
				200: function(data) {
					// Get the email value and display it
					const userEmail = $('[name=email]').val();
					$('#user-email').text(userEmail);

					// Simple transition without fade effect
					$('#login_form_wrapper').hide();
					$('#formContent2').show();

					$('#otp-rate-limit-alert').hide().text('');
			
					$('#otp-expiration-text').show();
					$('[name=otp]').val('').attr('disabled', false);
					$('#btn_resend_otp').attr('disabled', true);
					$('#btn_sumbit_otp').attr('disabled', false);




					// Calculate OTP expiration seconds from server expiry time and start timer
					const expiryTime = new Date(data.otpExpiresAt);
					const now = new Date();
					let otpExpirationSeconds = Math.floor((expiryTime - now) / 1000);
					if (otpExpirationSeconds < 0) otpExpirationSeconds = 0;
					startOtpExpirationTimer(otpExpirationSeconds);

					// Success feedback
					Swal.fire({
						icon: 'success',
						title: 'OTP Sent!',
						text: 'Please check your email for the verification code.',
						timer: 2000,
						showConfirmButton: false,
						confirmButtonColor: '#304c40'
					});
				},


				400: function(data) {
					Swal.fire({
						icon: 'info',
						title: 'Info',
						text: data["responseJSON"]["message"],
						confirmButtonColor: '#304c40'
					});
				},

				404: function(data) {
					Swal.fire({
						icon: 'info',
						title: 'Info',
						text: data["responseJSON"]["message"],
						confirmButtonColor: '#304c40'
					});
				},

				429: function(data) {

					// Get the email value and display it
					const userEmail = $('[name=email]').val();
					$('#user-email').text(userEmail);

					// Simple transition without fade effect
					$('#login_form_wrapper').hide();
					$('#formContent2').show();

					// Reset OTP input
					$('[name=otp]').val('');

					// Helper function to format seconds to mm:ss
					// function formatTime(seconds) {
					// 	const mins = Math.floor(seconds / 60);
					// 	const secs = seconds % 60;
					// 	return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
					// }




					// Calculate expiry and start timer
					const expiryTime = new Date(data.responseJSON.otpExpiresAt);
					const now = new Date();
					let otpExpirationSeconds = Math.floor((expiryTime - now) / 1000);
					if (otpExpirationSeconds < 0) otpExpirationSeconds = 0;
					startOtpExpirationTimer(otpExpirationSeconds);

					$('#otp-rate-limit-alert').text(data["responseJSON"]["message"]).show();
					$('#otp-expiration-text').show();
					$('[name=otp]').val('').attr('disabled', false);
					$('#btn_resend_otp').hide().attr('disabled', true);
					$('#btn_sumbit_otp').attr('disabled', false);



				},

				401: function() {
					Swal.fire({
						icon: 'error',
						title: 'Unauthorized',
						text: 'Authentication failed. Please try again.',
						confirmButtonColor: '#304c40'
					});
				},

				403: function() {
					Swal.fire({
						icon: 'error',
						title: 'Forbidden',
						text: 'Access denied.',
						confirmButtonColor: '#304c40'
					});
				},

				419: function() {
					// Get the email from the form
					const userEmail = $('[name=email]').val();

					if (userEmail) {
						// Show OTP form
						$('#user-email').text(userEmail);
						$('#login_form_wrapper').hide();
						$('#formContent2').show();

						Swal.fire({
							icon: 'info',
							title: 'Session Refreshed',
							text: 'Your session has been refreshed. Please enter the OTP to continue.',
							confirmButtonColor: '#304c40'
						});
					} else {
						// Fallback to reload if no email
						Swal.fire({
							icon: 'warning',
							title: 'Session Timeout',
							text: 'Your session has expired. Please refresh the page.',
							confirmButtonColor: '#304c40'
						}).then(() => {
							location.reload();
						});
					}
				},

				500: function() {
					Swal.fire({
						icon: 'error',
						title: 'Server Error',
						text: 'An unexpected error occurred. Please try again.',
						confirmButtonColor: '#304c40'
					});
				}

			},

		}).done(function() {
			setLoadingState($('#btn_request_otp'), false);
		});
	}



	$(document).on('submit', '#form_login_otp', function(e) {

		e.preventDefault();

		// Validate OTP input
		const otpInput = $('[name=otp]');
		if (!validateInput(otpInput)) {
			Swal.fire({
				icon: 'warning',
				title: 'Invalid OTP',
				text: 'Please enter a valid 5-digit OTP.',
				confirmButtonColor: '#304c40'
			});
			return;
		}

		$.ajax({
			url: "{{asset('otp/verify')}}",
			method: 'POST',
			dataType: 'json',
			data: {
				email: $('[name=email]').val(),
				otp: $('[name=otp]').val()
			},
			statusCode: {
				200: function(data) {
					// Clear timer
					const otpExpirationInterval = $(document).data('otpExpirationInterval');
					if (otpExpirationInterval) clearInterval(otpExpirationInterval);

					Swal.fire({
						icon: 'success',
						title: 'Login Successful!',
						text: 'Redirecting to dashboard...',
						timer: 1500,
						showConfirmButton: false,
						confirmButtonColor: '#304c40'
					}).then(() => {
						location.href = "{{asset('/')}}";
					});
				},

				400: function(data) {
					Swal.fire({
						icon: 'error',
						title: 'Verification Failed',
						text: data["responseJSON"]["message"],
						confirmButtonColor: '#304c40'
					});
				},

				401: function() {
					Swal.fire({
						icon: 'error',
						title: 'Unauthorized',
						text: 'Authentication failed.',
						confirmButtonColor: '#304c40'
					});
				},

				403: function() {
					Swal.fire({
						icon: 'error',
						title: 'Forbidden',
						text: 'Access denied.',
						confirmButtonColor: '#304c40'
					});
				},

				419: function() {
					Swal.fire({
						icon: 'warning',
						title: 'Session Timeout',
						text: 'Your session has expired. Please refresh the page.',
						confirmButtonColor: '#304c40'
					});
				},

				500: function() {
					Swal.fire({
						icon: 'error',
						title: 'Server Error',
						text: 'An unexpected error occurred. Please try again.',
						confirmButtonColor: '#304c40'
					});
				}
			}

		}).done(function() {
			setLoadingState($('#btn_sumbit_otp'), false);
		})
	})


	particlesJS("particles-js", {
		"particles": {
			"number": {
				"value": 355,
				"density": {
					"enable": true,
					"value_area": 789.1476416322727
				}
			},
			"color": {
				"value": "#ffffff"
			},
			"shape": {
				"type": "circle",
				"stroke": {
					"width": 0,
					"color": "#000000"
				},
				"polygon": {
					"nb_sides": 5
				},
				"image": {
					"src": "img/github.svg",
					"width": 100,
					"height": 100
				}
			},
			"opacity": {
				"value": 0.48927153781200905,
				"random": false,
				"anim": {
					"enable": true,
					"speed": 0.9,
					"opacity_min": 0,
					"sync": false
				}
			},
			"size": {
				"value": 2,
				"random": true,
				"anim": {
					"enable": true,
					"speed": 2,
					"size_min": 0,
					"sync": false
				}
			},
			"line_linked": {
				"enable": false,
				"distance": 150,
				"color": "#ffffff",
				"opacity": 0.4,
				"width": 1
			},
			"move": {
				"enable": true,
				"speed": 0.2,
				"direction": "none",
				"random": true,
				"straight": false,
				"out_mode": "out",
				"bounce": false,
				"attract": {
					"enable": false,
					"rotateX": 600,
					"rotateY": 1200
				}
			}
		},
		"interactivity": {
			"detect_on": "canvas",
			"events": {
				"onhover": {
					"enable": true,
					"mode": "bubble"
				},
				"onclick": {
					"enable": true,
					"mode": "push"
				},
				"resize": true
			},
			"modes": {
				"grab": {
					"distance": 400,
					"line_linked": {
						"opacity": 1
					}
				},
				"bubble": {
					"distance": 83.91608391608392,
					"size": 1,
					"duration": 3,
					"opacity": 1,
					"speed": 3
				},
				"repulse": {
					"distance": 200,
					"duration": 0.4
				},
				"push": {
					"particles_nb": 4
				},
				"remove": {
					"particles_nb": 2
				}
			}
		},
		"retina_detect": true
	});
</script>


@endsection