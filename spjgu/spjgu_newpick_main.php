<?php
function spjgu_newpick_main($arg=NULL, $nama=NULL) {
	$qlike='';
	$limit = 20;
    
	$kodeuk = apbd_getuseruk();
	if ($arg) 
		$jenis = arg(2);
	else
		$jenis = 'gu-spj';
	if ($jenis=='') $jenis = 'gu-spj';
	
	//drupal_set_message($kodeuk);

	$header = array (
		array('data' => 'No','width' => '10px', 'valign'=>'top'),
		array('data' => 'Kegiatan', 'field'=> 'kegiatan',  'valign'=>'top'),
		array('data' => 'Anggaran', 'width' => '100px', 'field'=> 'anggaran',  'valign'=>'top'),
		array('data' => 'Cair', 'width' => '100px', 'valign'=>'top'),
		array('data' => 'Sisa', 'width' => '100px', 'valign'=>'top'),
		array('data' => '', 'width' => '60px', 'valign'=>'top'),
	); 

	
	$query = db_select('kegiatanskpd', 'd')->extend('PagerDefault')->extend('TableSort');;

	# get the desired fields from the database
	$query->fields('d', array('kodekeg',  'kodeuk', 'kegiatan', 'anggaran'));
	$query->condition('d.kodeuk', $kodeuk, '=');
	if ($kodeuk!='00') $query->condition('d.jenis', 2, '=');
	$query->condition('d.inaktif', 0, '=');
	$query->condition('d.anggaran', 0, '>');
	if (isUserPembantu()) $query->condition('d.kodesuk', apbd_getusersuk(), '=');
	if (isUserSeksi()) $query->condition('d.kodepa', apbd_getusersuk(), '=');

	$query->orderByHeader($header);
	$query->orderBy('d.kegiatan', 'ASC');
	$query->limit($limit);

	# execute the query
	$results = $query->execute();
		
	$no=0;

	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		$no = $page * $limit;
	} else {
		$no = 0;
	} 
	$rows = array();
	
	foreach ($results as $data) {
		$no++;  
		
		if (isjurnalsudahuk($data->kodekeg)==false) {
			$cair = 0;
			$editlink = '';
			$kegiatan = $data->kegiatan . '<p style="color:red"><em><small>Ada SP2D yang belum divalidasi oleh Petugas Akuntansi (dijurnal) sehingga SPJ belum bisa dilakukan.</small></em></p>';
			
		} else {
			
			$kegiatan = $data->kegiatan;
			
			$cair = apbd_readrealisasikegiatan($data->kodekeg, apbd_date_create_currdate_form());
			//$editlink = createlink('SPJ','barupost/' . $data->kodekeg . '/' . $jenis);
			$editlink = apbd_button_baru_custom_small('spjgu/barupost/' . $data->kodekeg . '/' . $jenis, 'SPJ');
		}
		
		$rows[] = array(
			array('data' => $no, 'align' => 'right', 'valign'=>'top'),
			array('data' => $kegiatan,'align' => 'left', 'valign'=>'top'),
			array('data' => apbd_fn($data->anggaran),'align' => 'right', 'valign'=>'top'),
			array('data' => apbd_fn($cair),'align' => 'right', 'valign'=>'top'),
			array('data' => apbd_fn($data->anggaran - $cair),'align' => 'right', 'valign'=>'top'),
			$editlink,
		);			
	}

	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');
	
	
	//$output_form = drupal_get_form('spjgu_newpick_main_form');
	//drupal_render($output_form) . $btn . $output . $btn;
	
	
	
	return $output;
	
}

function isjurnalsudahuk($kodekeg) {
	
	/*
	$x = 0;
	
	db_set_active('penatausahaan');
	
	$res = db_query('select count(dokid) as jumlah from {dokumen} where jenisdokumen=1 and kodekeg=:kodekeg', array(':kodekeg'=>$kodekeg));
	foreach ($res as $data) {
		$x = $data->jumlah;
	}
	
	db_set_active();
	
	return ($x==0);
	*/
	return true;
}


function spjgu_newpick_main_form_submit($form, &$form_state) {

	
}


function spjgu_newpick_main_form($form, &$form_state) {

}


?>
