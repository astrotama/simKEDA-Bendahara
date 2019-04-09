<?php
function laporan_pajak_main($arg=NULL, $nama=NULL) {
	
	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = 'XX';
	$bulan = arg(2);
	if ($bulan=='') $bulan = date('n');
	
	$output_form = drupal_get_form('laporan_pajak_form');
	
	$output = tampilkan_pajak($kodeuk, $bulan);
	return drupal_render($output_form) . $output ;
	
	
}

function tampilkan_pajak($kodeuk, $bulan) {

	$header = array(
		array('data' => 'No','width' => '5px', 'valign'=>'top'),
		array('data' => 'Kode', 'align' => 'center', 'field'=> 'kode', 'width' => '100px', 'valign'=>'top'),
		array('data' => 'Uraian', 'field'=> 'uraian','valign'=>'top'), 
		array('data' => 'Jumlah', 'field'=> 'jumlah', 'width' => '100px', 'valign'=>'top'),
	);	
	
	$rows = array();
	$n = 0;
	$respajak = db_query('select kodepajak, uraian from {ltpajak} order by kodepajak');
	foreach ($respajak as $datapajak) {
		
		$jumlah = 0;
		if ($kodeuk=='ZZ') {
			$result = db_query('select sum(bi.jumlah) as jumlah from {bendaharapajak' . $kodeuk . '} as bi inner join {bendahara' . $kodeuk . '} as b on b.bendid=bi.bendid where month(b.tanggal)<=:bulan and bi.kodepajak=:kodepajak', array(':bulan'=>$bulan, ':kodepajak'=>$datapajak->kodepajak));
		} else {
			$result = db_query('select sum(bi.jumlah) as jumlah from {bendaharapajak' . $kodeuk . '} as bi inner join {bendahara' . $kodeuk . '} as b on b.bendid=bi.bendid where b.kodeuk=:kodeuk and month(b.tanggal)=:bulan and bi.kodepajak=:kodepajak', array(':kodeuk'=>$kodeuk, ':bulan'=>$bulan, ':kodepajak'=>$datapajak->kodepajak));
		}
		foreach ($result as $data) {
			$jumlah = $data->jumlah;
			$total += $jumlah;
			
			$n++;
			$rows[] = array(
				array('data' => $n, 'width' => '10px', 'align' => 'right', 'valign'=>'top'),

				array('data' => $datapajak->kodepajak, 'align' => 'center', 'valign'=>'top'),
				array('data' => $datapajak->uraian, 'align' => 'left', 'valign'=>'top'),
				array('data' => apbd_fn($jumlah), 'align' => 'right', 'valign'=>'top'),
			);	
	
		}
	}
	

	
	$rows[] = array(
		array('data' => '', 'width' => '10px', 'align' => 'right', 'valign'=>'top'),

		array('data' => '', 'align' => 'center', 'valign'=>'top'),
		array('data' => '<strong>TOTAL</strong>', 'align' => 'left', 'valign'=>'top'),
		array('data' => '<strong>' . apbd_fn($total) . '</strong>', 'align' => 'right', 'valign'=>'top'),
	);	
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');
	return $output;
}

function laporan_pajak_form($form, &$form_state) {	

	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = 'ZZ';
	$bulan = arg(2);
	if ($bulan=='') $bulan = date('n');

	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=>  'PILIH PAJAK',	
		'#collapsible' => FALSE,
		'#collapsed' => FALSE,        
	);

	$form['kodeuk']= array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);	
	//BULAN
	$option_bulan =array('Setahun', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
	$form['formdata']['bulan'] = array(
		'#type' => 'select',
		'#title' =>  t('Bulan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		'#options' => $option_bulan,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $bulan,
	);
	
	$form['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Cetak',
		'#attributes' => array('class' => array('btn btn-primary btn-sm pull-right')),
	);
	return $form;
}



function laporan_pajak_form_submit($form, &$form_state){
	$bulan = $form_state['values']['bulan'];
	$kodeuk = $form_state['values']['kodeuk'];
	
	$url = "laporanpajak/" . $kodeuk . "/" . $bulan;
	drupal_goto($url);
}


?>