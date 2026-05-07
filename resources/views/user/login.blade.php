@extends('layouts.custom')

@section('css')

<link rel="stylesheet" type="text/css" href="{{asset('css/login.css')}}?<?php echo filemtime('css/login.css') ?>">
<link rel="icon" type="image/png" href="{{asset('images/small_logo.png')}}" />

<script src="{{ asset('js/e-voting/app.js') }}" defer></script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

<style>
	/* .div-wrapper {
			background-image: url("{{asset('images/background.jpg')}}"); 
			background-position: center center;
			background-repeat: no-repeat;
			background-attachment: fixed;
			background-size: cover;
	   
		}

		*/
</style>
@endsection

@section('content')

<!-- <nav class="navbar navbar-expand-lg py-3 bg-dark-green shadow-sm border border-danger">
		<div class="container">
			<a href="#" class="navbar-brand">
				<img src="{{asset('images/new_logo.png')}}" alt="Image" class="d-inline-block align-middle image-logo">
			</a>
		</div>
	</nav> -->
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
				<span style="color:#2F4A3C;font-size:1.04em;font-weight:500;">Enter your 4-digit account number and email to log in</span>
			</div>


			<form id="login_form" method="POST">

				@csrf

				<input type="text" name="account_no" class="fadeIn third" placeholder="Account Number" autocomplete="off" required maxlength="4" minlength="4">
				<input type="email" name="email" id="date" class="fadeIn fourth input-time" placeholder="Email" autocomplete="off" required>

				<button type="submit" class="mt-3 fadeIn fourth" value="SUBMIT" id="btn_request_otp">Request OTP <img src="{{asset('images/loading.gif')}}" class="ml-2 login-verify-otp" style="display: none"></button> <br><br>

			</form>


			<div id="formFooter">
				<p style="color: #fff">Login with your account number and registered email address.</p>
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
		<input type="text" name="otp" class="fadeIn second" placeholder="Enter 5-digit OTP" autocomplete="off" maxlength="5" required>

		<!-- <button type="button" class="btn btn-green btn-md rounded-0 mb-4 mt-4">I didn't get the OTP</button> -->
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

		$.ajax({

			url: "{{asset('otp/request')}}",
			method: "POST",
			dataType: 'json',
			data: $(this).serialize(),

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

			},

		}).done(function() {
			setLoadingState($('#btn_request_otp'), false);
		})
	})

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
				otp: $('[name=otp]').val(),
				account_no: $('[name=account_no]').val()
			},

			beforeSend: function() {
				setLoadingState($('#btn_sumbit_otp'), true);
			},

			complete: function() {
				setLoadingState($('#btn_sumbit_otp'), false);
			},

			statusCode: {

				200: function(data) {
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

	//

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