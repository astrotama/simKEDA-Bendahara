<?php

function kegiatan_bidang_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('kegiatan_bidang_main_form');
	return drupal_render($output_form);// . $output;
	
}

function kegiatan_bidang_main_form($form, &$form_state) {
	
	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjgajilastpage"] = $referer;
	else
		$referer = $_SESSION["spjgajilastpage"];*/
	
	//db_set_active('penatausahaan');
	$kodeuk = arg(2);
	$kodesuk = arg(3);
	
	if ($kodesuk=='') $kodesuk = 'ZZ';

	$form['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);
	
	
	//<a href="/kegiatan/bidang/' . $kodeuk . '/##'. $kodesuk . '">SEMUA</a>
	if ($kodesuk=='ZZ')
		$str_suk = '| <strong>SEMUA</strong> |';
	else
		$str_suk = '| ' . '<a href="/kegiatan/bidang/' . $kodeuk . '/##">SEMUA</a>' . ' |';
	
	$opt_bidang = array();
	$opt_bidang[''] = '- PILIH BIDANG/SEKSI -';
	$query = db_query('SELECT kodesuk,namasuk FROM `subunitkerja` WHERE kodeuk=:kodeuk', array(':kodeuk' => $kodeuk));
	foreach ($query as $data) {
		$opt_bidang[$data->kodesuk]  = $data->namasuk;
		
		$namasuk_menu = str_replace('BIDANG ', '', strtoupper($data->namasuk));
		$namasuk_menu = str_replace('BAGIAN ', '', $namasuk_menu);
		
		if ($kodesuk==$data->kodesuk)
			$str_suk .= apbd_blank_space() . '<strong>' . $namasuk_menu . '</strong> |';
		else
			$str_suk .= apbd_blank_space() . '<a href="/kegiatan/bidang/' . $kodeuk . '/' .  $data->kodesuk . '">' . $namasuk_menu . '</a>' . ' |';
		
		$query_pa = db_query('SELECT kodepa,namapa FROM `PelakuAktivitas` WHERE kodesuk=:kodesuk', array(':kodesuk' => $data->kodesuk));
		foreach ($query_pa as $data_pa) {
			$opt_bidang[$data_pa->kodepa] = '- ' . $data->namasuk . ' (' . $data_pa->namapa . ')';
		}
	}
	
	$form['menu']= array(
		'#type' => 'markup',
		'#markup'=> '<p align="center">' . $str_suk . '</p>', 
	);	 

	$form['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-save" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
		'#suffix' => "&nbsp;<a href='/#' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	//PAJAK	
	$form['formbidang']= array(
		'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="40%">KEGIATAN</th><th>SUMBERDANA</th><th width="90px">ANGGARAN</th><th width="40%">BIDANG/SEKSI</th></tr>',
		 '#suffix' => '</table>',
	);	 
	 
	$i = 0;
	if ($kodesuk=='ZZ')
		$query = db_query('SELECT kodekeg,kegiatan,sumberdana1,kodesuk,anggaran FROM `kegiatanskpd` WHERE kodeuk=:kodeuk order by kodesuk,kegiatan', array(':kodeuk' => $kodeuk));
	else
		$query = db_query('SELECT kodekeg,kegiatan,sumberdana1,kodesuk,anggaran FROM `kegiatanskpd` WHERE kodeuk=:kodeuk and kodesuk=:kodesuk order by kodesuk,kegiatan', array(':kodeuk' => $kodeuk, ':kodesuk' => $kodesuk));
	foreach ($query as $data) {
		
		$i++;
		
		$form['formbidang']['kodekeg' . $i]= array(
				'#type' => 'value',
				'#value' => $data->kodekeg,
		); 

		//error no field kodepa
		/*
		if (strlen($data->kodepa)==6) {
			$kodesuk = $data->kodepa;
			$kegiatan = $data->kegiatan;
		} else {
		*/
			$kodesuk = $data->kodesuk;	
			$kegiatan = '<p style="color:red">' . $data->kegiatan . '</p>';
		//}
		
		$form['formbidang']['e_kodesuk' . $i]= array(
				'#type' => 'value',
				'#value' => $kodesuk,
		); 

		$form['formbidang']['nomor' . $i]= array(
				'#prefix' => '<tr><td>',
				'#markup' => $i,
				//'#size' => 10,
				'#suffix' => '</td>',
		); 
		$form['formbidang']['kegiatan' . $i]= array(
				'#prefix' 	=> '<td>',
				'#markup'=> $kegiatan, 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['sumberdana' . $i]= array(
				'#prefix' 	=> '<td>',
				'#markup'=> '<p align="center">' . $data->sumberdana1 . '</p>', 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['anggaran' . $i]= array(
				'#prefix' 	=> '<td>',
				'#markup'=> '<p align="right">' . apbd_fn($data->anggaran) . '</p>', 
				'#suffix' => '</td>',
		); 
		
		$form['formbidang']['kodesuk' . $i]= array(
			'#type'         => 'select', 
			'#prefix' => '<td>',
			'#options' => $opt_bidang,
			'#default_value'=> $kodesuk, 
			'#suffix' => '</td></tr>',
		); 
	}	

	$form['formbidang']['jumlahkeg']= array(
		'#type' => 'value',
		'#value' => $i,
	);	
	
	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-save" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
		'#suffix' => "&nbsp;<a href='/#' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	return $form;
}

function kegiatan_bidang_main_form_validate($form, &$form_state) {

}
	
function kegiatan_bidang_main_form_submit($form, &$form_state) {
$kodeuk = $form_state['values']['kodeuk'];
$jumlahkeg = $form_state['values']['jumlahkeg'];

for($n=1; $n<=$jumlahkeg; $n++){
	$kodekeg = $form_state['values']['kodekeg' . $n];
	$kodesuk = $form_state['values']['kodesuk' . $n];
	$e_kodesuk = $form_state['values']['e_kodesuk' . $n];

	if ($kodesuk != $e_kodesuk) {
		
		if (strlen($kodesuk)==4) {
			$kodesuk_s = $kodesuk;
			$kodepa_s = '';
			
		} else {
			$kodesuk_s = substr($kodesuk, 0, 4);
			$kodepa_s = $kodesuk;
		}	
		
		$query = db_update('kegiatanskpd') 		// Table name no longer needs {}
		->fields(array(
			'kodesuk' => $kodesuk_s,
			'kodepa' => $kodepa_s,
		))
		->condition('kodekeg', $kodekeg, '=')
		->execute();
	}				

}
	
//drupal_goto('');
	
}



?>
