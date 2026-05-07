<!DOCTYPE html>
<html lang="en">
<head>
  <title>OTP Override</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

	<div class="jumbotron text-center">
	<h1>OTP OVERRIDE</h1>

	</div>
  
	<div class="container">
		<form method="POST" action="{{asset('web-admin/otp/override')}}">


			@csrf
			
			<div class="row form-group">

				<div class="col-md-4">Account No</div>
				<div class="col-md-8"><input type="text" name="account_no" placeholder="Account No" class="form-control form-control-sm"></div>
			</div>
			<div class="row form-group">
				<div class="col-md-4">OTP</div>
				<div class="col-md-8"><input type="text" name="otp" placeholder="OTP" class="form-control form-control-sm"></div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					<button type="submit" class="btn btn-sm btn-danger btn-block">OVERRIDE</button>
				</div>
			</div>
		</form>

	</div>

</body>
</html>
