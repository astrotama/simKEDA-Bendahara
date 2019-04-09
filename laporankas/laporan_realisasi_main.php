<?php
function laporan_realisasi_main($arg=NULL, $nama=NULL) {
	$kodeuk = arg(1);
	$bulan = arg(2);
	
	$output = getlaporan($kodeuk, $bulan);
	if(arg(3)=='pdf'){	
		apbd_ExportPDF('L', 'F4', $output, 'Register SP2D.pdf');

		//$output_form = drupal_get_form('laporan_realisasi_main_form');
		//return drupal_render($output_form) . $output;// . $output;
		
	} else if(arg(3) == 'excel'){
		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename= Laporan_Realisasi.xls" );
		header("Pragma: no-cache"); 
		header("Expires: 0");
		echo $output;
	} else if(arg(3) == 'cetak'){
		$output_form = drupal_get_form('laporan_realisasi_main_form');

		return drupal_render($output_form).$output;
	}else{
	
		
		$output_form = drupal_get_form('laporan_realisasi_main_form');
		return drupal_render($output_form);// . $output;
	}		
	
}

function laporan_realisasi_main_form($form, &$form_state) {

	$kodeuk = arg(1);
	$bulan = arg(2);	
	
	if ($kodeuk=='') $kodeuk = 'ZZ';
	if ($bulan=='') $bulan = date('n');	

	$opt_skpd['ZZ'] = 'SELURUH SKPD'; 
	$results=db_query('SELECT kodeuk,namasingkat  FROM  {unitkerja} ORDER BY kodedinas');
	foreach($results as $data){
		$opt_skpd[$data->kodeuk] = $data->namasingkat; 
		$form['kodeuk']= array(
			'#type' => 'select',
			'#title' => 'SKPD',
			'#options' => $opt_skpd,
			'#default_value'=> $kodeuk, 
		);	
	}	
	
	$arr_bulan=array('Setahun', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
	$form['bulan']= array(
		'#type' => 'select',
		'#title' => 'Bulan',
		'#options' => $arr_bulan,
		'#default_value'=> $bulan, 
	);	
	$form['cetak']= array(
		'#type' => 'submit',
		'#value' => 'CETAK',
	);
	$form['cetakpdf']= array(
		'#type' => 'submit',
		'#value' => 'PDF',
	);

	$form['cetakexcel']= array(
		'#type' => 'submit',
		'#value' => 'EXCEL',
	);

	return $form;
}
	
function laporan_realisasi_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$bulan = $form_state['values']['bulan'];
	
	if($form_state['clicked_button']['#value'] == $form_state['values']['cetakpdf']){
		drupal_goto('laporan_realisasi/' . $kodeuk . '/' . $bulan . '/' . '/pdf');
	} else if($form_state['clicked_button']['#value'] == $form_state['values']['cetak']){
		drupal_goto('laporan_realisasi/' . $kodeuk . '/' . $bulan . '/' . '/cetak');
	} else if($form_state['clicked_button']['#value'] == $form_state['values']['cetakexcel']){
		drupal_goto('laporan_realisasi/' . $kodeuk . '/' . $bulan . '/' . '/excel');
	}
}

function getlaporan($kodeuk, $bulan){
	set_time_limit(0);
	ini_set('memory_limit','920M');

	if(isset($kodeuk) && $kodeuk != 'ZZ'){
		$query=db_query("SELECT namasingkat FROM unitkerja WHERE kodeuk = $kodeuk");
		foreach($query as $data){
			$skpd = $data->namasingkat;
		}
	}else{
		$skpd = "SELURUH SKPD";
	}

	$arr_bulan=array('Setahun', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');

	$header=array();
	$rows[]=array(
		array('data' => 'LAPORAN REALISASI KEGIATAN', 'width' => '875px','align'=>'center','style'=>'font-size:100%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '875px','align'=>'left','style'=>'font-size:100%;'),
	);
	$rows[]=array(
		array('data' => "SKPD : $skpd", 'width' => '875px','align'=>'left','style'=>'font-size:100%;'),
	);
	$rows[]=array(
		array('data' => "Bulan : ".$arr_bulan[$bulan], 'width' => '875px','align'=>'left','style'=>'font-size:100%;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'NO', 'rowspan' => 2, 'width' => '20px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'KEGIATAN', 'rowspan' => 2, 'width' => '360px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'ANGGARAN', 'rowspan' => 2, 'width' => '100px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'REALISASI', 'colspan' => 3, 'width' => '300px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'SISA', 'rowspan' => 2, 'width' => '100px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);
	$header[]=array(
		array('data' => 'SPJ', 'width' => '100px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Out', 'rowspan' => 2, 'width' => '100px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => '%', 'rowspan' => 2, 'width' => '100px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);



	//Content
	$query = db_select('kegiatanskpd', 'k');
	$query->fields('k', array('kegiatan', 'anggaran'));
	if($kodeuk != 'ZZ'){
		$query->condition('k.kodeuk', $kodeuk, '=');
	}
	// if($kodeuk != 0){
	// 	$query->condition('k.kodeuk', $kodeuk, '=');
	// }

	$results = $query->execute();
	
	foreach($results as $data){
		$n++;
			
		$rows[]=array(
			array('data' => $n, 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => $data->kegiatan, 'width' => '360px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($data->anggaran), 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => '', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => '', 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => '', 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => '', 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
		);

		$tot_anggaran += $data->anggaran;
	}
	$rows[]=array(
			array('data' => "TOTAL ", 'width' => '380px','align'=>'left','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:80%;border-top:1px solid black;'),
			array('data' => apbd_fn($tot_anggaran), 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;border-bottom:1px solid black;border-top:1px solid black;'),
			array('data' => '', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:80%;border-top:1px solid black;'),
			array('data' => '', 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;border-top:1px solid black;'),
			array('data' => '', 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;border-top:1px solid black;'),
			array('data' => '', 'width' => '100px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;border-top:1px solid black;'),
		);
	// $output .= theme('table', array('header' => $header, 'rows' => $rows ));
	$output.=createT($header, $rows);
	return $output;
}
?>