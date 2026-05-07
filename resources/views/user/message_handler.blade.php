@extends('layouts.user')

@section('css')


@endsection

@section('content')


<!-- Modal -->
<div class="modal fade" id="prompt_modal" tabindex="-1" role="dialog" aria-labelledby="prompt_modal_label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="prompt_modal_label">Info</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="modal-message"><?php echo $message; ?></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>

      </div>
    </div>
  </div>
</div>
<script>


		$(document).ready(function(){

			<?php

				if($allow === false) {

					echo "$('#prompt_modal').modal({keyboard: false, backdrop: 'static'});";

				}

			?>


			$(document).on('hidden.bs.modal', '#prompt_modal', function(){
				location.href = BASE_URL;
			})

		})

	
</script>


@endsection