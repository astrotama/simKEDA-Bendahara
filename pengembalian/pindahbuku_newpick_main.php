<?php
function pindahbuku_newpick_main($arg=NULL, $nama=NULL) {
	$qlike='';
	$limit = 20;
    
	$kodeuk = apbd_getuseruk();

	$header = array (
		array('data' => 'No','width' => '10px', 'valign'=>'top'),
		array('data' => 'Kegiatan', 'field'=> 'kegiatan',  'valign'=>'top'),
		array('data' => 'Anggaran', 'width' => '100px', 'field'=> 'jumlah',  'valign'=>'top'),
		array('data' => 'Cair', 'width' => '100px', 'valign'=>'top'),
		array('data' => 'Sisa', 'width' => '100px', 'valign'=>'top'),
		array('data' => '', 'width' => '60px', 'valign'=>'top'),
	); 

	//drupal_set_message($kodeuk);
	$query = db_select('kegiatanskpd', 'd')->extend('PagerDefault')->extend('TableSort');;

	# get the desired fields from the database
	$query->fields('d', array('kodekeg',  'kodeuk', 'kegiatan', 'anggaran'));
	$query->condition('d.kodeuk', $kodeuk, '=');
	//if ($kodeuk!='00') $query->condition('d.jenis', 2, '=');
	$query->condition('d.inaktif', 0, '=');
	$query->condition('d.anggaran', 0, '>');
	if (isUserPembantu()) $query->condition('d.kodesuk', apbd_getusersuk(), '=');

	$query->orderByHeader($header);
	$query->orderBy('d.kegiatan', 'ASC');
	$query->limit($limit);

	# execute the query
	$results = $query->execute();
	dpq($query);
	$no=0;

	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		$no = $page * $limit;
	} else {
		$no = 0;
	} 
	$rows = array();
	
	foreach ($results as $data) {
		//drupal_set_message($kodeuk);
		$cair = apbd_readrealisasikegiatan($data->kodekeg, $tanggal);
		//drupal_set_message($cair);
		//if ($cair>0) {
			$no++;  
			
			$editlink = apbd_button_baru_custom_small('pindahbuku/barupost/' . $data->kodekeg . '/' . $jenis, 'SPJ');

			$rows[] = array(
				array('data' => $no, 'align' => 'right', 'valign'=>'top'),
				array('data' => $data->kegiatan,'align' => 'left', 'valign'=>'top'),
				array('data' => apbd_fn($data->anggaran),'align' => 'right', 'valign'=>'top'),
				array('data' => apbd_fn($cair),'align' => 'right', 'valign'=>'top'),
				array('data' => apbd_fn($data->anggaran - $cair),'align' => 'right', 'valign'=>'top'),
				$editlink,
			);			
		//}
		
	}
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');
	
	
	//$output_form = drupal_get_form('pindahbuku_newpick_main_form');
	//drupal_render($output_form) . $btn . $output . $btn;
	
	
	
	return $output;
	
}


function pindahbuku_newpick_main_form_submit($form, &$form_state) {

	
}


function pindahbuku_newpick_main_form($form, &$form_state) {

}


?>