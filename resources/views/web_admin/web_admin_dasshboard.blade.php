@extends('layouts.web_admin')

@section('css')
  <style type="text/css">
  	.tableFixHead { 
  		overflow-y: auto; 
  		height: 750px;
  	}
	.tableFixHead thead th { 
		position: sticky; 
		top: 0; 
	}

	/* Just common table stuff. Really. */
	table  {  
		border-collapse: collapse; 
		width: 100%; 
	}
	th, td { 
		padding: 8px 16px; 
	}
	th { 
		background-color: #c7eed8;
	}
  </style>
    
@endsection

@section('content')
	<div class="card rounded-0">
		<div class="card-header">
		    <h3 class="font-weight-bold">System logs</h3>
		 </div>
		<div class="card-body">
		    <div class="tableFixHead">
		    <table class="table table-bordered table-hover"> 
				<thead>
					<th>User</th>
					<th>Status Code</th>
					<th>Action</th>
					<th>Message</th>
					<th>Description</th>
					<th>IP Address</th>
					<th>Datetime</th>
				</thead>
					<tbody>
						<?php
							try {

								$data = "";

								foreach($system_logs as $key => $systemLog) {

									$data .= '<tr>';
									$data .= '<td>'.$systemLog->stockholder.'</td>';
									$data .= '<td>' . $systemLog->statusCode .'</td>';
									$data .= '<td>' . $systemLog->action .'</td>';
									$data .= '<td>' . $systemLog->message .'</td>';
									$data .= '<td>' . $systemLog->dataLog .'</td>';
									$data .= '<td>' . $systemLog->ipAddress .'</td>';
									$data .= '<td>' . $systemLog->createdAt .'</td>';
									$data .= '</tr>';
								}

								echo $data;
							}
							catch(Exception $e) {
								
							}
						?>
					</tbody>
			</table>
		    </div>
		</div>
	</div>

@endsection