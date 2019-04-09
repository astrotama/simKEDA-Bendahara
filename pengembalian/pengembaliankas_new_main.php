<?php
function pengembaliankas_new_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('pengembaliankas_new_main_form');
	return drupal_render($output_form);// . $output;
	
}

function pengembaliankas_new_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjuplastpage"] = $referer;
	else
		$referer = $_SESSION["spjuplastpage"];*/
	
	$kodeuk = apbd_getuseruk();
	$spjno = apbd_getnospj_gu($kodeuk);
	
	$noref = '';
	$spjtgl = mktime(0,0,0,date('m'),date('d'),apbd_tahun());
	
	$keperluan = 'Pengembalian Kas ke Kas Daerah';
	$jumlah = '0';
	
	
	drupal_set_title($keperluan);
	
	$form['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);
	
	$form['spjno'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. Transaksi'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		//'#required' => TRUE,
		'#default_value' => $spjno,
	);
	$form['spjtgl'] = array(
		'#type' => 'date',
		'#title' =>  t('Tanggal'),
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
		'#title' =>  t('No. Referensi'),
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
	
	//PANJAR
	$opt_panjar = array();
	$opt_panjar['gu'] = 'Ganti Uang';
	$opt_panjar['tu'] = 'Tambahan Uang';
	//$opt_panjar['gaji'] = 'Gaji';
	$form['jenispanjar'] = array(
		'#type' => 'radios',
		'#title' =>  t('Jenis Kas'),
		'#options' => $opt_panjar,
		'#default_value' => 'tu',
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
		'#value' => '<span class="glyphicon glyphicon-file" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
}

function pengembaliankas_new_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) 
		form_set_error('spjno', 'Nomor Panjar harap diisi dengan benar');

	$total = $form_state['values']['jumlah'];
	if ($total==0)
		form_set_error('jumlah', 'Jumlah uang agar diisi');
		
}
	
function pengembaliankas_new_main_form_submit($form, &$form_state) {
	$jenispanjar = $form_state['values']['jenispanjar'];
	
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
			  'dokid' => '000000',
			  'kodekeg' => '000000',
			  'noref' => $noref,
			  'jenis' => 'ret-kas',
			  'jenispanjar' => $jenispanjar,
			  'kodeuk' => $kodeuk,
			  'spjno' => $spjno,
			  'tanggal' => $tanggal,
			  'keperluan' => $keperluan,
			  'total' => $total,

		))
		->execute();

	
	drupal_goto('spjarsip');
	
}


?>
