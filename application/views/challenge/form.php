<?php
?>
<form id="form_settings" action="<?= site_url('challenge/throw_balls') ?>" onsubmit="return ajax_submit(this);">
	<input name='data[max_layer]' type="hidden" value="<?= $max_layer ?? 0 ?>">
	<input name='data[layer]' type="hidden" value="<?= -1 ?>">
	<input name='data[box]' type="hidden" value="<?= 0 ?>">
	<div class="row">
		<div class="col-6">
			<div class="form-group">
				<label for="data[balls]">Balls</label>
				<input type="text" class="form-control" id="data[balls]" name="data[balls]" value="10000">
			</div>
			<div class="form-group">
				<label for="data[no_boxes]">Boxes</label>
				<input type="text" class="form-control" id="data[no_boxes]" name="data[no_boxes]" value="10">
			</div>
			<div class="form-group">
				<label for="data[boxes]">p</label>
				<input type="text" class="form-control" id="data[p]" name="data[p]" value="0.5" disabled>
			</div>
		</div>
		<div class="col-6">
			<div class="form-group">
				<label>Visualisation</label>
				<div class="form-group">
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="data[visual_mode]" id="data[visual_mode]1" value="1" checked>
						<label class="form-check-label" for="data[visual_mode]1">relative ball count</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="data[visual_mode]" id="data[visual_mode]2" value="2">
						<label class="form-check-label" for="data[visual_mode]2">real ball count</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label>Stacking</label>
				<div class="form-group">
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="data[stacking_mode]" id="data[stacking_mode]1" value="1">
						<label class="form-check-label" for="data[stacking_mode]1">always start in the middle</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="data[stacking_mode]" id="data[stacking_mode]2" value="2" checked>
						<label class="form-check-label" for="data[stacking_mode]2">start 1 step to middle</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label>Dump box details</label>
				<div class="form-group">
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="data[dump_mode]" id="data[dump_mode]1" value="0" checked>
						<label class="form-check-label" for="data[dump_mode]1">no dump</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="data[dump_mode]" id="data[dump_mode]2" value="1">
						<label class="form-check-label" for="data[dump_mode]2">dump box details</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--	<div class="form-group">-->
	<!--		<label for="data[visual_mode]">Anzeige-Modus</label>-->
	<!--		<input type="text" class="form-control" id="data[visual_mode]" name="data[visual_mode]" value="2" disabled>-->
	<!--	</div>-->

	<button type="submit" class="btn btn-primary">Start</button>
</form>
