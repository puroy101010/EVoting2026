<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Valley Golf and Country Club, Inc. | Admin Login</title>
	<link rel="icon" type="image/png" href="{{ asset('images/small_logo.png') }}" />
	<link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<script src="https://code.jquery.com/jquery-3.5.1.js" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
	<meta name="theme-color" content="#304c40">
	<meta name="apple-mobile-web-app-status-bar-style" content="#304c40">
	<style>
		:root {
			--corporate-primary: #304c40;
			--corporate-accent: #6b8e4e;
		}

		body {
			min-height: 100vh;
			background: linear-gradient(135deg, var(--corporate-primary) 60%, var(--corporate-accent) 100%);
			display: flex;
			align-items: center;
			justify-content: center;
			font-family: 'Nunito', sans-serif;
		}

		.login-container {
			background: #fff;
			border-radius: 18px;
			box-shadow: 0 8px 32px rgba(48, 76, 64, 0.18);
			padding: 2.5rem 2rem 2rem 2rem;
			width: 100%;
			max-width: 370px;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		.login-logo {
			margin-bottom: 1.5rem;
		}

		.login-logo img {
			width: 110px;
			display: block;
			margin: 0 auto;
		}

		.login-title {
			font-size: 1.3rem;
			font-weight: 700;
			color: var(--corporate-primary);
			margin-bottom: 1.2rem;
			text-align: center;
			letter-spacing: 1px;
		}

		.login-avatar {
			width: 60px;
			height: 60px;
			border-radius: 50%;
			margin: 0 auto 1.2rem auto;
			display: block;
			box-shadow: 0 2px 8px rgba(48, 76, 64, 0.10);
		}

		.login-form {
			width: 100%;
			display: flex;
			flex-direction: column;
			gap: 1rem;
		}

		.input-group {
			position: relative;
			display: flex;
			align-items: center;
		}

		.input-group i {
			position: absolute;
			left: 14px;
			color: var(--corporate-accent);
			font-size: 1.1rem;
		}

		.login-form input[type="email"],
		.login-form input[type="password"] {
			border: 1px solid #e0e0e0;
			border-radius: 6px;
			padding: 0.75rem 1rem 0.75rem 2.5rem;
			font-size: 1rem;
			outline: none;
			transition: border-color 0.2s;
			width: 100%;
		}

		.login-form input[type="email"]:focus,
		.login-form input[type="password"]:focus {
			border-color: var(--corporate-accent);
		}

		.login-form input[type="submit"] {
			background: linear-gradient(90deg, var(--corporate-primary) 60%, var(--corporate-accent) 100%);
			color: #fff;
			border: none;
			border-radius: 6px;
			padding: 0.75rem 1rem;
			font-size: 1.1rem;
			font-weight: 700;
			cursor: pointer;
			transition: background 0.2s;
			letter-spacing: 1px;
		}

		.login-form input[type="submit"]:hover {
			background: linear-gradient(90deg, var(--corporate-accent) 60%, var(--corporate-primary) 100%);
		}

		.login-error {
			color: #d9534f;
			background: #fbeaea;
			border: 1px solid #f5c6cb;
			border-radius: 5px;
			padding: 0.5rem 1rem;
			margin-bottom: 1rem;
			display: none;
			font-size: 0.98rem;
		}

		@media (max-width: 480px) {
			.login-container {
				padding: 1.5rem 0.5rem 1.5rem 0.5rem;
			}
		}
	</style>
</head>

<body>
	<div class="login-container" role="main" aria-label="Admin Login">
		<div class="login-logo">
			<img src="{{ asset('images/new_logo.png') }}" alt="Valley Golf Logo">
		</div>
		<div class="login-title">ADMIN LOGIN</div>
		<img src="{{ asset('images/avatar.png') }}" class="login-avatar" alt="User Icon">
		<form id="login_form" class="login-form" method="POST" autocomplete="off" aria-label="Admin Login Form">
			@csrf
			<div id="login-error" class="login-error" role="alert"></div>
			<div class="input-group">
				<i class="fas fa-envelope"></i>
				<input type="email" name="email" placeholder="Email" required autofocus aria-label="Email">
			</div>
			<div class="input-group">
				<i class="fas fa-lock"></i>
				<input type="password" name="password" placeholder="Password" required aria-label="Password">
			</div>
			<input type="submit" value="LOGIN" id="btn_login">
		</form>
	</div>
	<script>
		$(document).on('submit', '#login_form', function(e) {
			e.preventDefault();
			$('#login-error').hide();
			$.ajax({
				url: "{{ asset('admin/login') }}",
				method: 'POST',
				dataType: 'json',
				data: $(this).serialize(),
				beforeSend: function() {
					$('#btn_login').val("Logging in . . .").attr('disabled', true);
				},
				complete: function() {
					$('#btn_login').val("LOGIN").attr('disabled', false);
				},
				success: function() {
					location.href = "{{ asset('admin') }}";
				},
				error: function(data) {
					let msg = 'Login failed. Please try again.';
					if (data.responseJSON && data.responseJSON.message) {
						msg = data.responseJSON.message;
					}
					$('#login-error').text(msg).fadeIn();
				},
			})
		});
		// Allow pressing Enter to submit on any input
		$(document).on('keypress', '#login_form input', function(e) {
			if (e.which === 13) {
				$('#login_form').submit();
			}
		});
	</script>
</body>

</html>