<?php
$i = 1;
$dataPoints = [];

//$dataPoints = array(
//		array("y" => 373.64, "label" => "#" . $i++),
//		array("y" => 435.94, "label" => "#" . $i++),
//		array("y" => 842.55, "label" => "#" . $i++),
//		array("y" => 828.55, "label" => "#" . $i++),
//		array("y" => 039.99, "label" => "#" . $i++),
//		array("y" => 65.215, "label" => "#" . $i++),
//		array("y" => 12.453, "label" => "#" . $i++),
//		array("y" => 12.453, "label" => "#" . $i++),
//		array("y" => 12.453, "label" => "#" . $i++),
//		array("y" => 12.453, "label" => "#" . $i++),
//);

foreach ($chart_data as $k => $column) {
	$dataPoints[] = ["y" => $column, "label" => "$k"];
}
//ci()->dump($dataPoints);
/*
 *  required: 	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
 *
 * */

?>
<div class="row">
	<div class="col-6">
		<div class="card">
			<div class="card-body">
				<script>
					if (typeof fn_chart == 'undefined') {
						let fn_chart;
						let chart_data;
					}
					chart_data = <?= json_encode($dataPoints, JSON_NUMERIC_CHECK) ?>;
					fn_chart = function () {

						var chart = new CanvasJS.Chart("chartContainer", {
							animationEnabled: true,
							theme: "light2",
							title: {
								// text: ""
							},
							axisY: {
								title: "Balls"
							},
							data: [{
								type: "column",
								yValueFormatString: "#,##0.## balls",
								dataPoints: chart_data
							}]
						});
						chart.render();
					}
					window.setTimeout(function () {
						// window.onload = fn_chart;
						fn_chart();
					}, 1000);

				</script>
				<div id="chartContainer" style="height: 370px; width: 100%;"></div>
			</div>
		</div>
	</div>
</div>
