<?php
?>
<a class="hidden-xs hidden-sm" href="https://github.com/FuscaSoftware/clan-challenge" target="_blank">
	<img style="position: absolute; top: -64px; right: 0; border: 0; z-index: 50; transform: rotate(90deg);"
		 src="https://s3.amazonaws.com/github/ribbons/forkme_left_green_007200.png" alt="Fork me on GitHub">
</a>
<style type="text/css">
	.row.boxes .card .card-body {
		padding: 0.5rem;
		font-size: 12px;
		letter-spacing: -3px;
	}

	.row.boxes .card .card-body.sb-tiny-balls {
		padding: 0.2rem;
		font-size: 9px;
		letter-spacing: -3px;
	}
</style>
<div class="chart-container"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div class="row">
	<div class="col">
		<div class="card">
			<div class="card-body">
				<?= ci()->get_view('challenge/form', $form_view_data) ?>
				<script type="application/javascript">
					$(document).ready(function () {
						$('#form_settings').submit();
					});
				</script>
			</div>
		</div>
	</div>
</div>
<div class="childs-layer-<?= $layer ?>">
</div>
