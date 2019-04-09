<?php
function spjup_newpost_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$dokid = arg(2);	
	$output_form = drupal_get_form('spjup_newpost_main_form');
	return drupal_render($output_form);// . $output;
	
}

function spjup_newpost_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjuplastpage"] = $referer;
	else
		$referer = $_SESSION["spjuplastpage"];*/
	
	$dokid = arg(2);

	db_set_active('penatausahaan');
	$query = db_select('dokumen', 'd');

	# get the desired fields from the database
	$query->fields('d', array('dokid', 'keperluan', 'kodeuk', 'sp2dno', 'sp2dtgl', 'jumlah', 'kodekeg', 'spjsudah', 'jenisdokumen'));
	
	//GAJI
	$query->condition('d.dokid', $dokid, '=');	
	
	//if (isAdministrator()) dpq($query);
	
	
	# execute the query
	$results = $query->execute();
		

	$rows = array();
	$no = 0;
	$spjno = 'BARU';
	foreach ($results as $data) {
		
		$spjno = 'K-' . $data->sp2dno;
		
		$dokid = $data->dokid;
		$noref = $data->sp2dno;
		if (is_null($data->sp2dtgl))
			$spjtgl = mktime(0,0,0,date('m'),date('d'),apbd_tahun());
		else
			$spjtgl = strtotime($data->sp2dtgl);		
		
		$kodeuk = $data->kodeuk;
		$kodekeg = $data->kodekeg;
		$keperluan = $data->keperluan;
		$jumlah = $data->jumlah;
		
		if ($data->jenisdokumen==0)				//UP
			$jenis = 'up';
		else if ($data->jenisdokumen==1)		//GU
			$jenis = 'gu-kas';
		else if ($data->jenisdokumen==2)		//TU
			$jenis = 'tu';
		
	}
	db_set_active();
	
	
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
	$form['jenis'] = array(
		'#type' => 'value',
		'#value' => $jenis,
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
	$form['keperluan'] = array(
		'#type' => 'textfield',
		'#title' =>  t('Keperluan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $keperluan,
	);
	$form['jumlah'] = array(
		'#type' => 'textfield',
		'#title' =>  t('Jumlah'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
		'#default_value' => $jumlah,
	);




	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
}

function spjup_newpost_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor SPJ harap diisi dengan benar');
		
}
	
function spjup_newpost_main_form_submit($form, &$form_state) {
	$dokid = $form_state['values']['dokid'];
	$jenis = $form_state['values']['jenis'];
	
	$kodekeg = $form_state['values']['kodekeg'];
	$noref = $form_state['values']['noref'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$kodeuk = $form_state['values']['kodeuk'];
	$keperluan = $form_state['values']['keperluan'];
	
	$total = $form_state['values']['jumlah'];

	//SIMPAN
	$bendid = apbd_getkodespj($kodeuk);
		

	$query = db_insert('bendahara' . $kodeuk) // Table name no longer needs {}
		->fields(array(
			  'bendid' => $bendid,
			  'noref' => $noref,
			  'jenis' => $jenis,
			  'kodeuk' => $kodeuk,
			  'dokid' => $dokid,
			  'spjno' => $spjno,
			  'kodekeg' => $kodekeg,
			  'tanggal' => $tanggal,
			  'keperluan' => $keperluan,
			  'total' => $total,

			  'kasdakeluar' => $total,
			  'kasbendaharamasuk' => $total,
			  
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
