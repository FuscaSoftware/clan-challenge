<?php
$css_col_width = round(100 / (count($boxes) + 1), 5);
if ($stacking_mode == 1)
	$css_col_width = round(100 / (count($boxes) + 0), 5);
?>
<div class="row boxes layer-<?= $layer ?>">
	<?php if (!$layer): ?>
		<style type="text/css">
			.row.boxes .sb-col-box {
				-ms-flex: 0 0 <?= $css_col_width ?>%;
				flex: 0 0 <?= $css_col_width ?>%;
				max-width: <?= $css_col_width ?>%;
			}

			.row.boxes .sb-col-half {
				-ms-flex: 0 0 <?= $css_col_width /2?>%;
				flex: 0 0 <?= $css_col_width /2 ?>%;
				max-width: <?= $css_col_width /2 ?>%;
			}
		</style>
	<?php endif; ?>
	<?php if ($stacking_mode == 2 && !($layer % 2)): ?>
		<div class="sb-col-half"></div>
	<?php elseif ($stacking_mode == 2 && $parent_box > ((count($boxes) - 1) / 2)): ?>
		<div class="sb-col-box"></div>
	<?php endif; ?>
	<? foreach ($boxes as $k => $value): ?>
		<?
		if ($visual_mode == 1) {
			$number_of_visual_balls = $value * pow(2, count($boxes) - 1);
		} else
			$number_of_visual_balls = $value * $balls;
		?>
		<div class="col-1 sb-col-box">
			<div class="card" id="<?= 'card_' . $layer . '_' . $k ?>">
				<input name='data[box]' type="hidden" value="<?= $k ?>">
				<input name='data[balls]' type="hidden" value="<?= $value * $balls ?>">
				<input name='data[layer]' type="hidden" value="<?= $layer ?>">
				<div class="card-header">
					Box <?= $layer . " / #" . $k ?>
				</div>
				<div class="card-body <?= ($visual_mode == 2) ? 'sb-tiny-balls' : '' ?>">
					<? for ($i = 1; $i <= $number_of_visual_balls; $i++): ?>
						<i class="fa fa-bowling-ball"></i>
					<? endfor; ?>
				</div>
				<div class="card-footer">
					<a href="<?= site_url('challenge/throw_balls') ?>"
					   class="btn btn-primary"
					   onclick="return ajax_submit(this);"
					   data-additional_fields="form#form_settings"
					   data-additional_fields2="div#<?= 'card_' . $layer . '_' . $k ?>"
					><?= round($value * $balls) ?> <i class="fa fa-box-open"></i></a>
				</div>
			</div>
		</div>
	<? endforeach; ?>
	<?php if ($stacking_mode == 2 && !($layer % 2)): ?>
		<div class="sb-col-half"></div>
	<?php endif; ?>
	<div class="col-12 childs-layer-<?= $layer + 1 ?>">
		<div class="row boxes">
		</div>
	</div>
</div>
