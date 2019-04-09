<?php
function pengembalianspj_newpost_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('pengembalianspj_newpost_main_form');
	return drupal_render($output_form);// . $output;
	
}

function pengembalianspj_newpost_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjgulastpage"] = $referer;
	else
		$referer = $_SESSION["spjgulastpage"];*/
	
	$kodekeg = arg(2);
	
	$kodeuk = apbd_getuseruk();

	$query = db_select('kegiatanskpd', 'd');
	# get the desired fields from the database
	$query->fields('d', array('kodekeg',  'kodeuk', 'kegiatan'));
	$query->condition('d.kodekeg', $kodekeg, '=');
	$query->condition('d.kodeuk', $kodeuk, '=');
	$results = $query->execute();
	$no = 0;
	foreach ($results as $data) {
		$keperluan = 'Pengembalian SPJ ' . $data->kegiatan;
	}
	$spjtgl = mktime(0,0,0,date('m'),date('d'),apbd_tahun());

	
	drupal_set_title($keperluan);
	
	$form['kodekeg'] = array(
		'#type' => 'value',
		'#value' => $kodekeg,
	);	

	$form['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);

	
	$form['spjno'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. SPJ'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		//'#required' => TRUE,
		'#default_value' => apbd_getnospj_gu($kodeuk),
	);
	$form['spjtgl'] = array(
		'#type' => 'date',
		'#title' =>  t('Tanggal SPJ'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $spjtgl,
		'#default_value'=> array(
			'year' => format_date($spjtgl, 'custom', 'Y'),
			'month' => format_date($spjtgl, 'custom', 'n'), 
			'day' => format_date($spjtgl, 'custom', 'j'), 
		  ), 
		
	);

	//JENIS KAS
	$opt_panjar = array();
	$opt_panjar['gu'] = 'Ganti Uang';
	$opt_panjar['tu'] = 'Tambahan Uang';
	$opt_panjar['ls'] = 'Langsung/Gaji';
	$form['jenispanjar'] = array(
		'#type' => 'radios',
		'#title' =>  t('Jenis SPJ (GU/TU)'),
		'#options' => $opt_panjar,
		'#default_value' => 'gu',
	);	
	$form['keperluan'] = array(
		'#type' => 'textfield',
		'#title' =>  t('Keperluan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $keperluan,
	);


	//REKENING	
	$form['formdokumen'] = array (
		'#type' => 'fieldset',
		//'#title'=> 'PAJAK<em class="text-info pull-right">' . apbd_fn($pajak) . '</em>',
		'#title'=> 'REKENING',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
	
	$form['formdokumen']['tablerekening']= array(
		'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="75px">KODE</th><th>URAIAN</th><th width="110px">ANGGARAN</th><th width="110px">CAIR</th><th width="120px">JUMLAH</th><th width="200px">KETERANGAN</th></tr>',
		 '#suffix' => '</table>',
	);	
	$i = 0;
	$query = db_query('SELECT d.kodero,ro.uraian,d.anggaran FROM `anggperkeg` as d inner join rincianobyek as ro on d.kodero=ro.kodero WHERE kodekeg=:kodekeg', array(':kodekeg'=>$kodekeg));
	//$results = $query->execute();
	foreach ($query as $data) {
		
		$cair = apbd_readrealisasikegiatan_rekening($kodekeg, $data->kodero, $spjtgl);
		
		if ($cair>0) {
			$i++; 
			$kode = $data->kodero;
			$uraian = $data->uraian;
			$anggaran = $data->anggaran;
			
			
			$form['formdokumen']['tablerekening']['kodero' . $i]= array(
					'#type' => 'value',
					'#value' => $kode,
			); 
			
			$form['formdokumen']['tablerekening']['nomor' . $i]= array(
					'#prefix' => '<tr><td>',
					'#markup' => $i,
					//'#size' => 10,
					'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['kode' . $i]= array(
					'#prefix' => '<td>',
					'#markup' => $kode,
					//'#size' => 10,
					'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['uraian' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> $uraian, 
				'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['anggaran' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> '<p align="right">' . apbd_fn($anggaran) . '</p>' , 
				'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['cair' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> '<p align="right">' . apbd_fn($cair) . '</p>' , 
				'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['jumlah' . $i]= array(
				'#type'         => 'textfield', 
				'#prefix' => '<td>',
				'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
				'#default_value'=> '0', 
				'#suffix' => '</td>',
			);	
			$form['formdokumen']['tablerekening']['keterangan' . $i]= array(
				'#type'         => 'textfield', 
				'#prefix' => '<td>',
				'#size' => 25,
				'#default_value'=> '', 
				'#suffix' => '</td></tr>',
			);	
		}	
		
	}
	$form['formdokumen']['jumlahrekening']= array(
		'#type' => 'value',
		'#value' => $i,
	);	

	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-file" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
}

function pengembalianspj_newpost_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor SPJ harap diisi dengan benar');

	
}
	
function pengembalianspj_newpost_main_form_submit($form, &$form_state) {
	$kodekeg = $form_state['values']['kodekeg'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$kodeuk = $form_state['values']['kodeuk'];
	$jenispanjar = $form_state['values']['jenispanjar'];
	$keperluan = $form_state['values']['keperluan'];

	$jumlahrekening = $form_state['values']['jumlahrekening'];

	//SIMPAN
	$bendid = apbd_getkodespj($kodeuk);
		
	//rekening	
	$num_deleted = db_delete('bendaharaitem' . $kodeuk)
		  ->condition('bendid', $bendid)
		  ->execute();		
	
	$total = 0;
	for($n=1; $n<=$jumlahrekening; $n++){
		$kodero = $form_state['values']['kodero' . $n];
		$keterangan = $form_state['values']['keterangan' . $n];
		$jumlah = $form_state['values']['jumlah' . $n];
		
		if ($jumlah!=0) {
			$total += $jumlah;
			$query = db_insert('bendaharaitem' . $kodeuk) // Table name no longer needs {}
				->fields(array(
					  'bendid' => $bendid,
					  'kodero' => $kodero,
					  'jumlah' => $jumlah,
					  'keterangan' => $keterangan,				  
				))
				->execute();
		}
	}

	$num_deleted = db_delete('bendahara' . $kodeuk)
		  ->condition('bendid', $bendid)
		  ->execute();
	

	$query = db_insert('bendahara' . $kodeuk) // Table name no longer needs {}
		->fields(array(
			'bendid' => $bendid,
			'dokid' => '000000',
			'noref' => '000000',
			'jenis' => 'ret-spj',
			'kodeuk' => $kodeuk,
			'spjno' => $spjno,
			'kodekeg' => $kodekeg,
			'tanggal' => $tanggal,
			'keperluan' => $keperluan,
			'total' => $total,

		  
		))
		->execute();	
	
	/*
	//AKUNTANSI
	db_set_active('akuntansi');
	
	//rekening	
	$num_deleted = db_delete('bendaharaitem')
		  ->condition('bendid', $bendid)
		  ->execute();		
	
	for($n=1; $n<=$jumlahrekening; $n++){
		$kodero = $form_state['values']['kodero' . $n];
		$keterangan = $form_state['values']['keterangan' . $n];
		$jumlah = $form_state['values']['jumlah' . $n];
		
		if ($jumlah!=0) {
			$query = db_insert('bendaharaitem') // Table name no longer needs {}
				->fields(array(
					  'bendid' => $bendid,
					  'kodero' => $kodero,
					  'jumlah' => $jumlah,
					  'keterangan' => $keterangan,				  
				))
				->execute();
		}
	}

	$num_deleted = db_delete('bendahara')
		  ->condition('bendid', $bendid)
		  ->execute();
	

	$query = db_insert('bendahara') // Table name no longer needs {}
		->fields(array(
			'bendid' => $bendid,
			'dokid' => '000000',
			'noref' => '000000',
			'jenis' => 'ret-spj',
			'kodeuk' => $kodeuk,
			'spjno' => $spjno,
			'kodekeg' => $kodekeg,
			'tanggal' => $tanggal,
			'keperluan' => $keperluan,
			'total' => $total,

		  
		))
		->execute();	
		
	db_set_active();
	*/
	
	drupal_goto('spjarsip');
	
}


?>
