<?php
function spjls_newpost_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$dokid = arg(2);	
	$output_form = drupal_get_form('spjls_newpost_main_form');
	return drupal_render($output_form);// . $output;
	
}

function spjls_newpost_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjlslastpage"] = $referer;
	else
		$referer = $_SESSION["spjlslastpage"];*/
	
	$dokid = arg(2);

	db_set_active('penatausahaan');
	$query = db_select('dokumen', 'd');

	# get the desired fields from the database
	$query->fields('d', array('dokid', 'keperluan', 'kodeuk', 'sp2dno', 'sp2dtgl', 'jumlah', 'kodekeg', 'spjsudah', 'penerimanama', 'penerimanpwp', 'nokontrak'));
	
	//GAJI
	$query->condition('d.dokid', $dokid, '=');	
	
	//if (isAdministrator()) dpq($query);
	
	
	# execute the query
	$results = $query->execute();
		

	$rows = array();
	$no = 0;
	$spjno = 'BARU';
	foreach ($results as $data) {
		
		$dokid = $data->dokid;
		$spjno = 'K-' . $data->sp2dno;
		$noref = $data->sp2dno;
		if (is_null($data->sp2dtgl))
			$spjtgl = mktime(0,0,0,date('m'),date('d'),apbd_tahun());
		else
			$spjtgl = strtotime($data->sp2dtgl);		

		$kodeuk = $data->kodeuk;
		$kodekeg = $data->kodekeg;
		$keperluan = $data->keperluan;
		$jumlah = $data->jumlah;
		
		$penerimanama = $data->penerimanama;
		$penerimanpwp = $data->penerimanpwp;
		$nokontrak = $data->nokontrak;
		
	}
	
	drupal_set_title($keperluan);
	
	$form['dokid'] = array(
		'#type' => 'value',
		'#value' => $dokid,
	);	
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
		'#default_value' => $spjno,
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

	$form['noref'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. SP2D'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $noref,
	);
	$form['nokontrak'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. Kontrak/SPK'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $nokontrak,
	);
	$form['keperluan'] = array(
		'#type' => 'textfield',
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
			'#default_value'=> $penerimanama, 
		);				
		$form['formpenerima']['penerimanpwp']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('NPWP'),
			//'#required' => TRUE,
			'#default_value'=> $penerimanpwp, 
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
			'#prefix' => '<div class="table-responsive"><table class="table"><tr><th width="10px">NO</th><th width="90px">KODE</th><th>URAIAN</th><th width="150px">JUMLAH</th><th width="200px">KETERANGAN</th></tr>',
			 '#suffix' => '</table></div>',
		);	
		$i = 0;
		$query = db_query('SELECT d.kodero,ro.uraian,d.jumlah FROM `dokumenrekening` as d inner join rincianobyek as ro on d.kodero=ro.kodero WHERE d.jumlah>0 and dokid=:dokid', array(':dokid'=>$dokid));
		//$results = $query->execute();
		foreach ($query as $data) {

			$i++; 
			$kode = $data->kodero;
			$uraian = $data->uraian;
			$jumlah = $data->jumlah;
			//$tidakada = $data->tidakada;
			
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
			$form['formdokumen']['tablerekening']['jumlah' . $i]= array(
				'#type'         => 'textfield', 
				'#prefix' => '<td>',
				'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
				'#default_value'=> $jumlah, 
				'#suffix' => '</td>',
			);	
			$form['formdokumen']['tablerekening']['keterangan' . $i]= array(
				'#type'  => 'textfield', 
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
		$query = db_select('dokumenpajak', 'dp');
		$query->join('ltpajak', 'p', 'dp.kodepajak=p.kodepajak');
		$query->fields('p', array('kodepajak', 'uraian'));
		$query->fields('dp', array('jumlah', 'keterangan'));
		$query->condition('dp.dokid', $dokid, '=');
		$query->orderBy('dp.kodepajak', 'ASC');
		$results = $query->execute();
		foreach ($results as $data) {

			$i++; 
			$kode = $data->kodepajak;
			$uraian = $data->uraian;
			$jumlah = $data->jumlah;
			$keterangan = $data->keterangan;
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
		
		if ($i==0) {
			$query = db_select('ltpajak', 'p');
			$query->fields('p', array('kodepajak', 'uraian'));
			$results = $query->execute();
			foreach ($results as $data) {
				
				$i++; 
				$kode = $data->kodepajak;
				$uraian = $data->uraian;
				$jumlah = 0;
				$keterangan = '';
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
		}
		
		$form['jumlahrekpajak']= array(
			'#type' => 'value',
			'#value' => $i,
		);    
	
	db_set_active();

	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
}

function spjls_newpost_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor SPJ harap diisi dengan benar');

	$penerimanama = $form_state['values']['penerimanama'];
	if ($penerimanama=='') form_set_error('penerimanama', 'Nama penerima pembayaran harap diisi dengan benar');

	$penerimanpwp = $form_state['values']['penerimanpwp'];
	if ($penerimanpwp=='') form_set_error('penerimanpwp', 'NPWP penerima pembayaran harap diisi dengan benar');
	
}
	
function spjls_newpost_main_form_submit($form, &$form_state) {
	$dokid = $form_state['values']['dokid'];
	$noref = $form_state['values']['noref'];
	$kodekeg = $form_state['values']['kodekeg'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$kodeuk = $form_state['values']['kodeuk'];
	$keperluan = $form_state['values']['keperluan'];

	$nokontrak = $form_state['values']['nokontrak'];
	$penerimanama = $form_state['values']['penerimanama'];
	$penerimanpwp = $form_state['values']['penerimanpwp'];
	
	$jumlahrekening = $form_state['values']['jumlahrekening'];
	$jumlahrekpajak = $form_state['values']['jumlahrekpajak'];

	//drupal_set_message($noref);
	
	
	//SIMPAN
	$bendid = apbd_getkodespj($kodeuk);
		
	//rekening
	$total = 0;	
	for($n=1;$n<=$jumlahrekening;$n++){
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

	$query = db_insert('bendahara' . $kodeuk) // Table name no longer needs {}
		->fields(array(
			'bendid' => $bendid,
			'jenis' => 'ls',
			'kodeuk' => $kodeuk,
			'dokid' => $dokid,
			'noref' => $noref,
			'spjno' => $spjno,
			'kodekeg' => $kodekeg,
			'tanggal' => $tanggal,
			'keperluan' => $keperluan,
			'nokontrak' => $nokontrak,
			'penerimanama' => $penerimanama,
			'penerimanpwp' => $penerimanpwp,

			'total' => $total,
			'pajak' => $pajak,

			'kasdakeluar' => $total,
			'kasbendaharamasuk' => $total,
			'kasbendaharakeluar' => $total,
		))
		->execute();
	
	//update sp2d
	db_set_active('penatausahaan');

	$query = db_update('dokumen')
	->fields(
			array(
				'spjsudah' => 1,
			)
		);
	$query->condition('dokid', $dokid, '=');
	$res = $query->execute();
	db_set_active();
	
	
	drupal_goto('spjarsip');
	
}


?>
