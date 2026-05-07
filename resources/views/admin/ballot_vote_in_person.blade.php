@extends('layouts.admin')

@section('css')
	<link rel="stylesheet" type="text/css" href="{{asset('css/admin/admin_ballot_result_view.css')}}?<?php echo filemtime('css/admin/admin_ballot_result_view.css')?>">
@endsection

@section('content')

	<div id="">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6 col-sm-12 col-width-sm">
					<div class="card shadow-lg bg-white rounded">
						<div class="card-header">
							<div class="row">
								<div class="col-md-7">
									<div class="ballot-info">
										<p class="p-title"><?php echo $ballot->ballotType === 'proxy' ? 'VOTE BY PROXY' : 'VOTE IN PERSON'; ?></p>
											<div class="row">
												<div class="col-md-5 col-md6-size">
													<p class="p-info">BALLOT #<?php echo $ballot->ballotNo; ?></p>
													<!-- <p class="p-info">ACCOUNT #<?php //echo $ballot->accountKey; ?></p> -->
													<p class="p-info">Unused votes: <span><?php echo ($ballot->votesAvailable * 3) - $ballot->votesUsed;  ; ?></span></p>
													<!-- <p class="p-info">Revoked: <span>25</span></p> -->
												</div>
												<div class="col-md-7 col-md6-size">
													<p class="p-info">Date Generated: <span><?php echo $ballot->createdAt; ?></span></p>
													<p class="p-info">Date Submitted: <span><?php echo $ballot->submittedAt; ?></span></p>
												</div>
											</div>
									</div>
								</div>
								<div class="col-md-5">
									<p class="p-no-share">Number of shares : <span class="span-divider"><?php echo $ballot->votesAvailable; ?></span></p>
									<h4 class="h4-no-votes">Available No. of Votes : <span class="span-divider" id="total_available_votes"> <?php echo $ballot->votesAvailable * 3; ?> </span></h4>
								</div>
							</div>
						</div>

						<form id="ballot_form">
							<div class="card-body">
								<div class="table-responsive">
									<!-- CANDIDATES BALLOT FORM-->
									<table class="table table-bordered table-hover w-100 table-size" id="candidates_table">
										<thead class="text-center text-nowrap thead-background">
											<tr>
												<th class="th-label-sm">Name of Candidates</th>
												<th class="th-label-sm">In Person</th>
												<th class="th-label-sm">Void</th>
												<th class="th-label-sm">Action</th>
											</tr>
										</thead>
										<tbody class="tbody-details">

											<?php

												// echo '<pre>';

												// print_r($ballotDetails);


												// echo '</pre>';

												$record = "";

												foreach($ballotDetails as $ballotDetail) {

													$fullName = $ballotDetail->firstName . " " . $ballotDetail->middleName . " ". $ballotDetail->lastName;

													$record .= '<tr>';
													$record .= '<td class="td-label-sm">'.$fullName.'</td>';
													$record .= '<td class="td-label-sm text-center">' . $ballotDetail->vote . '</td>';
													$record .= '<td class="td-label-sm text-center">' . $ballotDetail->voidedVote . '</td>';
													$record .= '<td class="td-label-sm text-center"><button class="btn btn-sm btn-custom-darkg btn-void-vote" data-id="'.$ballotDetail->id.'">Void</button></td>';
													$record .= '</tr>';
																				
												}


												echo $record;

											?>
											
										</tbody>
									</table>
								</div>
								
								<button class="btn btn-sm btn-custom-darkg" id="btn_void_amendment">VOID AMENDMENT</button>

								<div class="table-responsive" style="height: 700px; overflow: auto;">
									<!-- CANDIDATES BALLOT FORM-->
									<table class="table table-bordered table-hover w-100 table-size" id="amendments_table">
										<thead class="text-center text-nowrap thead-background" style="position: sticky;top: 0; z-index: 1;">
											<tr>
												<th class="th-label-sm">Amendment</th>
												<th class="th-label-sm">In Favor</th>
												<th class="th-label-sm">Not Favor</th>
												<th class="th-label-sm">Abstain</th>
											</tr>
										</thead>
										<tbody class="tbody-details">

											<?php



												foreach($amendments as $amendment) {

													echo '<tr>
														<td>'.$amendment["amendment"].'</td>
														<td class="text-center"><div class="form-check"><input class="form-check-input" type="radio" '.$amendment["inFavor"].'></div></td>
														<td class="text-center"><div class="form-check"><input class="form-check-input" type="radio" '.$amendment["notFavor"].'></div></td>
														<td class="text-center"><div class="form-check"><input class="form-check-input" type="radio" '.$amendment["abstain"].'></div></td>
													</tr>';

												}
												


								
											?>
											
										</tbody>
									</table>
								</div>


								
								<div class="div-votes bg-dark-green"><h4 class="total-votes">TOTAL VOTES: <span class="votes-span" id="total_vote_counter"><?php echo $ballot->votesUsed; ?></span></h4></div>
								
								
								<!-- <div class="form-group row justify-content-center" style="margin-top: 15px;">
									<button type="button" class="btn btn-danger btn-lg col-md-4 rounded-0" style="margin: 4px;" id="cancel_ballot">CANCEL</button>
									<button type="submit" class="btn btn-success btn-lg col-md-4 rounded-0" style="margin: 4px;" id="btn_submit_vote">SUBMIT</button>		
								</div> -->
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="display"></div>



	<!-- Modal -->
<div class="modal fade" id="modalVoid" tabindex="-1" role="dialog" aria-labelledby="voidModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="voidModal">Void Vote</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<div class="row">
				<label for="">No. of voided votes</label>
				<input type="number" class="form-control form-control-sm" name="void" placeholder="No. of voided votes">
			</div>
			<div class="row">
				<label for="">Remarks</label>
				<input type="text" class="form-control form-control-sm" name="remarks"placeholder="Remarks">
			</div>
			<input type="hidden" value=""  name="id">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btn_submit_void">Submit</button>
      </div>
    </div>
  </div>
</div>



	<!-- Modal -->
	<div class="modal fade" id="modalVoidAmendment" tabindex="-1" role="dialog" aria-labelledby="voidModalAmendment" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="voidModalAmendment">Void Vote</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
					<div class="row">
						<label for="">No. of voided votes</label>
						<input type="number" class="form-control form-control-sm" name="void" placeholder="No. of voided votes">
					</div>
					<div class="row">
						<label for="">Remarks</label>
						<input type="text" class="form-control form-control-sm" name="remarks"placeholder="Remarks">
					</div>
					<input type="hidden" value="<?php echo $ballot->ballotId; ?>"  name="id">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="btn_submit_amendment_void">Submit</button>
			</div>
			</div>
		</div>
	</div>



<script>
		$(document).ready(function(){
		$('.admin-nav-ballots').addClass('active');



		$(document).on('click', '.btn-void-vote', function(e){

			e.preventDefault();

			$('#modalVoid').modal('show');
			
			$('[name=id]').val($(this).attr('data-id'));

			

		})


		$(document).on('click', '#btn_void_amendment', function(e){

			e.preventDefault()

			$('#modalVoidAmendment').modal('show');

		})




		$(document).on('click', '#btn_submit_amendment_void', function(){
			if(confirm("Are you sure?")) {
				void_amendment_vote();
			}
		})


		$(document).on('click', '#btn_submit_void', function(){

			if(confirm("Are you sure?")) {
				void_vote();
			}
		})


		function void_vote() {
			$.ajax({

				url: BASE_URL + 'admin/void/vote', 
				method: 'POST', 
				dataType: 'json', 

				data: {
					id: $('#modalVoid [name=id]').val(),
					remarks: $('#modalVoid [name=remarks]').val(),
					void: $('#modalVoid [name=void]').val(),
				}, 


				beforeSend: function(){

					$('#btn_submit_void').text('Processing  . . .').attr('disabled', true);
					

				}, 


				complete: function(){

					$('#btn_submit_void').text('Submit').attr('disabled', false);
				

				},

				statusCode: {

					200: function(data){

						Swal.fire({icon: 'success', title: 'Success!', text: data.message}).then(() => {
							location.reload();
						})
					}, 

					400: function(data){
						alert(data["responseJSON"]["message"]);
					},

					401: function(){
						alert(UNAUTHORIZED);
					}, 

					403: function(){
						alert(FORBIDDEN);
					}, 

					419: function(){
						alert(SESSION_TIMEOUT);
					}, 

					500: function(){
						alert(SERVER_ERROR);
					}
				}
				})
		}
		function void_amendment_vote() {
			$.ajax({

				url: BASE_URL + 'admin/void/amendment', 
				method: 'POST', 
				dataType: 'json', 

				data: {
					id: $('#modalVoidAmendment [name=id]').val(),
					remarks: $('#modalVoidAmendment [name=remarks]').val(),
					void: $('#modalVoidAmendment [name=void]').val(),
				}, 


				beforeSend: function(){

					$('#btn_submit_amendment_void').text('Processing  . . .').attr('disabled', true);
					

				}, 


				complete: function(){

					$('#btn_submit_amendment_void').text('Submit').attr('disabled', false);
				

				},

				statusCode: {

					200: function(data){

						Swal.fire({icon: 'success', title: 'Success!', text: data.message}).then(() => {
							location.reload();
						})
					}, 

					400: function(data){
						alert(data["responseJSON"]["message"]);
					},

					401: function(){
						alert(UNAUTHORIZED);
					}, 

					403: function(){
						alert(FORBIDDEN);
					}, 

					419: function(){
						alert(SESSION_TIMEOUT);
					}, 

					500: function(){
						alert(SERVER_ERROR);
					}
				}
				})
		}
	})
</script>	

   





@endsection