<?php

class Challenge extends MY_Controller
{
//	public $default_helpers = ['form', 'html', 'url', 'sb_helper', 'sb/gui'];
	public $default_helpers = ['url', 'sb_helper'];
//	public $default_libraries = ['fusca/bootstrap_lib', /*'session',*/ /*'fusca/auth_ldap', 'fusca/acl_lib',*/ 'fusca/smr_breadcrumbs', 'table'];
	public $default_libraries = ['fusca/bootstrap_lib'];
	public $auth_ldap_status = false;
	public $layout = 'common/layout_view';

	public function index() {
		$view_data['site_title'] = "Code Challenge";
		$view_data['page_title'] = "Code Challenge";

//		$data = [];
//		$data['chart_data'] = [];
//		$output[] = $this->get_view('challenge/chart', $data);
		$data = [];
		$data['layer'] = 0;
		$data['form_view_data'] = [
			'max_layer' => 0,
			'layer' => 0,
		];
		$i = 1;
		$data['boxes_view_data'] = [
			'boxes' => [
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
				$i++ => '1',
			],
			'layer' => $data['layer'] ?? 0,
			'max_layer' => 0,
		];
		$output[] = $this->get_view('challenge/index', $data);
		$this->show_page($output, $view_data);
	}

	public function throw_balls() {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		$input_data = $this->input->get_post('data');
//		$this->dump($input_data);
		if (1) {
			$parent_box = $input_data['box'] ?? 0;
			$layer = $input_data['layer'];
//			$parent_layer = $layer - 1;

			if ($layer >= 0) {
				$clicked_card = "card_$layer" . "_$parent_box";
//				$this->dump($clicked_card);
				$this->dom_lib()->removeClass(".boxes.layer-$layer .card:not(#$clicked_card)", "bg-cyan");
				$this->dom_lib()->addClass(".layer-$layer #card_" . $layer . '_' . $input_data['box'], "bg-cyan");
			}
			$parent_box_prefixed = ($parent_box !== false) ? $parent_box . "_" : '';
			$layer = $layer + 1;
			$i = 1;
			$boxes = [];
			$n = $input_data['no_boxes'] - 1;
			$p = $input_data['p'];
			$balls = round($input_data['balls']);
			if ($input_data['stacking_mode'] == 1 || $layer == 0) {
				for ($k = 0; $k < $input_data['no_boxes']; $k++)
//				$boxes[$parent_box_prefixed . $k] = $this->_valueOfBox($n, $k, $p, 1);
					$boxes[$k] = $this->_valueOfBox($n, $k, $p);
				if ($input_data['dump_mode'] ?? 0)
					$this->dump($boxes, 'box details');
//				$boxes[] = [$parent_box_prefixed . $k => $this->_valueOfBox($n, $k, $p)];
			} else
				$boxes = $this->_boxValuesB($parent_box, $n, $p, $input_data['dump_mode'] ?? 0);
			$data['boxes_view_data'] = [
				'balls' => $balls,
				'layer' => $layer,
				'max_layer' => $layer,
				'visual_mode' => $input_data['visual_mode'],
				'stacking_mode' => $input_data['stacking_mode'],
				'parent_box' => $parent_box,
			];
			$child_layer = $layer + 1;
			$data['boxes_view_data']['boxes'] = $boxes;
//			$this->dump($data['boxes_view_data']['boxes']);
			$output = ci()->get_view('challenge/boxes', $data['boxes_view_data']);#
//		$this->dom_lib()->append('.container-fluid', $output);
//			$this->dom_lib()->html2('.childs-layer-' . $layer . ' .row.boxes', $output);
			$this->dom_lib()->html2('.childs-layer-' . $layer, $output);#
			if (!empty($boxes))
				$this->dom_lib()->html2('.chart-container', $this->_chart($layer, $boxes, $balls));#
//			$this->dom_lib()->html2('#html2test', $output);
//			$this->dom_lib()->html2('#html2test', '<i class="fa fa-bowling-ball"></i>');
//			$this->dom_lib()->addClass('.childs-layer-' . $layer . ' .row.boxes', 'bg-cyan');
		}
		$this->message(sprintf("Calculating board with %s balls.", $balls), 'success', 'default', 1500);
		$this->show_ajax_message();
	}

	private function _chart($layer, $boxes, $balls) {
		$view_data = [];
		$view_data['chart_data'] = [

		];
		foreach ($boxes as $k => $v)
			$view_data['chart_data']["Box " . $layer . " / #" . $k] = $v * $balls;
//		$view_data['chart_data'] = $boxes;
		return $this->get_view('challenge/chart', $view_data);
	}

	private function _n_ueber_k($n, $k) {
		if ($k == 0)
			return 1;
		$zaehler = ($this->_iFaculty($n));
		$nenner = ($this->_iFaculty($k) * $this->_iFaculty($n - $k));
		if ($n >= $k)
			return $zaehler / $nenner;
	}
//	function iBinCoeff($a_iN, $a_iK)
//	{
//		 # the binomial coefficient is defined as n! / [ (n-k)! * k! ]
//		return $this->iFaculty($a_iN) / ($this->iFaculty($a_iN - $a_iK) * $this->iFaculty($a_iK));
//	}

	/**
	 * @param $n    n = rows
	 * @param $k    k = box
	 * @param $p    p = Wahrscheinlichkeit
	 *
	 * @return float|int
	 */
	private function _valueOfBox($n, $k, $p) {
		return $this->_n_ueber_k($n, $k) * pow($p, $n);
	}

	private function _boxValuesB($parent_box, $n, $p, $verbose = 0) {
		$middle = ($n + 1) / 2;
		$shift = 0;
		if (($n % 2)) { # n ungerade // gerade Zahl Boxen
//			$middle_i = $middle;
			if ($parent_box == $middle)
				$shift = 0;
			else if ($parent_box > $middle)
				$shift = $middle - $parent_box;
			else if ($parent_box < $middle)
				$shift = $middle - $parent_box - 1;
		} else { # n gerade // ungerade Zahl Boxen
			$middle_i = (int)round($middle, 0, PHP_ROUND_HALF_DOWN);
			if ((int)$parent_box == $middle_i)
				$shift = 0;
			else
//			else if ($parent_box > $middle_i)
//				$shift1 = $middle_i - $parent_box;
//			else if ($parent_box < $middle_i)
				$shift = $middle_i - $parent_box;
		}

		$boxes = [];
		$cut_right = [];
		$max_line_box_i = $n - abs(round($shift));
//		$min_line_box_i = $shift - 1;
//		$this->dump($n, 'n');
//		$this->dump($parent_box, 'parent_box');
//		$this->dump($middle, 'middle');
//		$this->dump($middle_i, 'middle_i');
//		$this->dump(round($middle,0, PHP_ROUND_HALF_DOWN), 'middle_down');
//		$this->dump($shift, 'shift');
//		$this->dump($max_line_box_i);
		for ($i = 0; $i < $n; $i++) {
			$line_boxes = $i + 2;
//			if ($shift < 0)
//				$cut_right[$i] = max(0, round(abs($shift) - (($n - $i - 1) * 0.5)));
//			if ($shift > 0)
//				$cut_left[$i] = max(0, round(abs($shift) - (($n - $i - 1) * 0.5)));
			if ($line_boxes == 2)
				$boxes[$i][0] = $boxes[$i][1] = 1;
			else
				for ($j = 0; $j < $line_boxes; $j++) {

					$a = (@$boxes[$i - 1][$j - 1]) ?: 0;
//						$b = (@$boxes[$i - 1][$j]) ?: 0;
					$b = (@$boxes[$i - 1][$j]) ?: 0;
//					if ($j == ($line_boxes - $cut_right[$i])) {
//					if ($shift < 0 && $line_boxes > $max_line_box_i && $j == ($max_line_box_i)) {
					if ($shift != 0 && $line_boxes > $max_line_box_i && $j == ($max_line_box_i)) {
//						$this->dump([
//							'i' => $i,
//							'j' => $j,
//							'lb' => $line_boxes,
//							'cr' => $cut_right[$i],
//							'a' => $a,
//							'b' => $b,
//						], 'cut');

//						$boxes[$i][$j - 1] = $boxes[$i][$j - 1] + $boxes[$i - 1][$j - 1];
						$boxes[$i][$j] = $a + 2 * $b;
//						break;
//					} elseif ($j >= ($line_boxes - $cut_right[$i]))
//					} else if ($shift > 0 && $line_boxes > $max_line_box_i && $j == ($min_line_box_i)) {
//						$boxes[$i][$j] = 2 * $a + $b;
//						$boxes[$i][$j] = 100;
//					} elseif ($shift != 0 && $j < $min_line_box_i) {
//						continue;
					} elseif ($shift != 0 && $j > $max_line_box_i)
						continue;
					else
						$boxes[$i][$j] = $a + $b;
				}
		}
//		foreach ($boxes[$n-1] as $k => $v) {
//			if (($k + $shift) < 0)
//				$boxes[$n-1]
//		}
		$box_values = [];
		for ($i = 0; $i <= $n; $i++)
			$box_values[$i] = 0;
		foreach ($boxes[$n - 1] as $k => $v) {
			if ($shift <= 0)
				$box_values[$k - round($shift)] = $v / (pow(2, $n));
			else { # reverse index for shift to left
				$ri = $n - $k - round($shift);
//				$this->dump($ri);
				if ($ri >= 0)
					$box_values[$ri] = $v / (pow(2, $n));
//					$box_values[$ri] = $v;
			}
		}

		if ($verbose)
			$this->dump($boxes, 'boxes');
//		$this->dump($cut_right, 'cut_right');
//		$this->dump($box_values);
		return $box_values;
	}

	private
	function _binomial_coeff($n, $k) {
		$j = $res = 1;
		if ($k < 0 || $k > $n)
			return 0;
		if (($n - $k) < $k)
			$k = $n - $k;
		while ($j <= $k) {
			$res *= $n--;
			$res /= $j++;
		}
		return $res;
	}

	private
	function _iFaculty($a_iFac) {
		if ($a_iFac > 0) {
			return $a_iFac * $this->_iFaculty($a_iFac - 1);
		} elseif ($a_iFac == 0) {
			return 1;
		} else {
			return 0;  // Wrong argument!
		}
	}

}
