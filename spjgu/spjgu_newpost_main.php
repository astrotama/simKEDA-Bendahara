<?php
function spjgu_newpost_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('spjgu_newpost_main_form');
	return drupal_render($output_form);// . $output;
	
}

function spjgu_newpost_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjgulastpage"] = $referer;
	else
		$referer = $_SESSION["spjgulastpage"];*/
	
	$kodekeg = arg(2);
	$jenis = arg(3);
	if ($jenis=='') $jenis = 'gu-spj';
	
	//if (isUserSeksi()) drupal_set_message('seksi');
	//$kodepa = apbd_getusersuk();
	//drupal_set_message($kodepa);
	
	$kodeuk = apbd_getuseruk();

	$query = db_select('kegiatanskpd', 'd');
	# get the desired fields from the database
	$query->fields('d', array('kodekeg',  'kodeuk', 'kegiatan'));
	$query->condition('d.kodekeg', $kodekeg, '=');
	$query->condition('d.kodeuk', $kodeuk, '=');
	$results = $query->execute();
	$no = 0;
	foreach ($results as $data) {
		$keperluan=$data->kegiatan;
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

	$opt_jenis['gu-spj'] = 'GU (GANTI UANG)';
	$opt_jenis['tu-spj'] = 'TU (TAMBAHAN UANG)';
	$form['jenis'] = array(
		'#type' => 'select',
		'#title' =>  t('Jenis SPJ'),
		'#options' => $opt_jenis,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $jenis,
	);	
	$form['nokontrak'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. Nota/Invoice'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => '',
	);
	$form['keperluan'] = array(
		'#type' => 'textarea',
		'#title' =>  t('Keperluan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $keperluan,
	);

	//PENERIMA
	$form['formpenerima'] = array (
		'#type' => 'fieldset',
		'#title'=> 'PENERIMA',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
		$form['formpenerima']['penerimanama']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Nama'),
			//'#required' => TRUE,
			'#default_value'=> '', 
		);				
		$form['formpenerima']['penerimanpwp']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('NPWP'),
			//'#required' => TRUE,
			'#default_value'=> '', 
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
		'#prefix' => '<div class="table-responsive"><table class="table"><tr><th width="10px">NO</th><th width="75px">KODE</th><th>URAIAN</th><th width="110px">ANGGARAN</th><th width="110px">CAIR</th><th width="120px">JUMLAH</th><th width="200px">KETERANGAN</th></tr>',
		 '#suffix' => '</table></div>',
	);	
	$i = 0;
	$query = db_query('SELECT d.kodero,ro.uraian,d.anggaran FROM `anggperkeg` as d inner join rincianobyek as ro on d.kodero=ro.kodero WHERE d.anggaran>0 and d.kodekeg=:kodekeg', array(':kodekeg'=>$kodekeg));
	//$results = $query->execute();
	foreach ($query as $data) {

		$i++; 
		$kode = $data->kodero;
		$uraian = $data->uraian;
		$anggaran = $data->anggaran;
		
		$cair = 0;
		$cair = apbd_readrealisasikegiatan_rekening($kodekeg, $data->kodero, $spjtgl);
		
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
	$form['formdokumen']['jumlahrekening']= array(
		'#type' => 'value',
		'#value' => $i,
	);	

	//PAJAK	
	$form['formpajak'] = array (
		'#type' => 'fieldset',
		//'#title'=> 'PAJAK<em class="text-info pull-right">' . apbd_fn($pajak) . '</em>',
		'#title'=> 'PAJAK',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
	$form['formpajak']['tablepajak']= array(
		'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="90px">KODE</th><th>URAIAN</th><th width="150px">JUMLAH</th><th width="200px">KETERANGAN</th></tr>',
		 '#suffix' => '</table>',
	);	 
	$i = 0;
	$query = db_select('ltpajak', 'p');
	$query->fields('p', array('kodepajak', 'uraian'));
	$results = $query->execute();
	foreach ($results as $data) {

		$i++; 
		$kode = $data->kodepajak;
		$uraian = $data->uraian;
		$jumlah = 0;
		$keterangan = $data->uraian;
		$form['formpajak']['tablepajak']['kodepajak' . $i]= array(
				'#type' => 'value',
				'#value' => $kode,
		); 
		
		$form['formpajak']['tablepajak']['nomor' . $i]= array(
				'#prefix' => '<tr><td>',
				'#markup' => $i,
				//'#size' => 10,
				'#suffix' => '</td>',
		); 
		$form['formpajak']['tablepajak']['kode' . $i]= array(
				'#prefix' => '<td>',
				'#markup' => $kode,
				'#size' => 10,
				'#suffix' => '</td>',
		); 
		$form['formpajak']['tablepajak']['uraianpajak' . $i]= array(
			//'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#markup'=> $uraian, 
			'#suffix' => '</td>',
		); 
		$form['formpajak']['tablepajak']['jumlahpajak' . $i]= array(
			'#type'         => 'textfield', 
			'#default_value'=> $jumlah, 
			'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
			'#size' => 25,
			'#prefix' => '<td>',
			'#suffix' => '</td>',
		);	
		$form['formpajak']['tablepajak']['keteranganpajak' . $i]= array(
			'#type'         => 'textfield', 
			'#default_value'=> '', 
			'#size' => 25,
			'#prefix' => '<td>',
			'#suffix' => '</td></tr>',
		);	
		
	}
	$form['jumlahrekpajak']= array(
		'#type' => 'value',
		'#value' => $i,
	);    
	
	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
}

function spjgu_newpost_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor SPJ harap diisi dengan benar');

	$penerimanama = $form_state['values']['penerimanama'];
	if ($penerimanama=='') form_set_error('penerimanama', 'Nama penerima pembayaran harap diisi dengan benar');

	$penerimanpwp = $form_state['values']['penerimanpwp'];
	if ($penerimanpwp=='') form_set_error('penerimanpwp', 'NPWP penerima pembayaran harap diisi dengan benar');
	
}
	
function spjgu_newpost_main_form_submit($form, &$form_state) {
	$kodekeg = $form_state['values']['kodekeg'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$kodeuk = $form_state['values']['kodeuk'];
	$jenis = $form_state['values']['jenis'];
	$keperluan = $form_state['values']['keperluan'];
	
	if (isUserSeksi()) {
		$kodepa = apbd_getusersuk();
		//drupal_set_message('p. ' . $kodepa);
		$kodesuk = substr($kodepa,0,4);
		//drupal_set_message('s. ' . $kodesuk);
		
	} elseif (isUserPembantu()) {
		$kodesuk = apbd_getusersuk();
		$kodepa = '';
	} else {
		$kodesuk = '0000';
	}
	
	$nokontrak = $form_state['values']['nokontrak'];
	$penerimanama = $form_state['values']['penerimanama'];
	$penerimanpwp = $form_state['values']['penerimanpwp'];
	
	$jumlahrekening = $form_state['values']['jumlahrekening'];
	$jumlahrekpajak = $form_state['values']['jumlahrekpajak'];

	
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
		
		if ($jumlah>0) {
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

	//pajak	
	$num_deleted = db_delete('bendaharapajak' . $kodeuk)
		  ->condition('bendid', $bendid)
		  ->execute();
	
	$pajak = 0;
	for($n=1; $n<=$jumlahrekpajak; $n++){
		
		$kodepajak = $form_state['values']['kodepajak' . $n];
		$keterangan = $form_state['values']['keteranganpajak' . $n];
		$jumlahpajak = $form_state['values']['jumlahpajak' . $n];
		
		if ($jumlahpajak>0) {
			$pajak += $jumlahpajak;
			$query = db_insert('bendaharapajak' . $kodeuk) // Table name no longer needs {}
						->fields(array(
						  'bendid' => $bendid,
						  'kodepajak' => $kodepajak,
						  'jumlah' => $jumlahpajak,
						  'keterangan' => $keterangan,				  
				))
				->execute();
		}	
	}

	$num_deleted = db_delete('bendahara' . $kodeuk)
		  ->condition('bendid', $bendid)
		  ->execute();
	
	if (isUserPembantu() or isUserSeksi()) {
		$query = db_insert('bendahara' . $kodeuk) // Table name no longer needs {}
			->fields(array(
				'bendid' => $bendid,
				'dokid' => '000000',
				'noref' => '000000',
				'jenis' => $jenis,
				'kodeuk' => $kodeuk,
				'kodesuk' => $kodesuk,
				'kodepa' => $kodepa,
				'spjno' => $spjno,
				'kodekeg' => $kodekeg,
				'tanggal' => $tanggal,
				'keperluan' => $keperluan,
				'nokontrak' => $nokontrak,
				'penerimanama' => $penerimanama,
				'penerimanpwp' => $penerimanpwp,
				'total' => $total,
				'pajak' => $pajak,

				'kaspembantukeluar' => $total,
			  
			))
			->execute();
			
	} else {
		$query = db_insert('bendahara' . $kodeuk) // Table name no longer needs {}
			->fields(array(
				'bendid' => $bendid,
				'dokid' => '000000',
				'noref' => '000000',
				'jenis' => $jenis,
				'kodeuk' => $kodeuk,
				'kodesuk' => $kodesuk,
				'spjno' => $spjno,
				'kodekeg' => $kodekeg,
				'tanggal' => $tanggal,
				'keperluan' => $keperluan,
				'nokontrak' => $nokontrak,
				'penerimanama' => $penerimanama,
				'penerimanpwp' => $penerimanpwp,
				'total' => $total,
				'pajak' => $pajak,

				'kasbendaharakeluar' => $total,
			  
			))
			->execute();
	}	
	
	
	drupal_goto('spjarsip');
	
}


?>
