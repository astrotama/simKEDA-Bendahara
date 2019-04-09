<?php
function panjarseksi_new_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('panjarseksi_new_main_form');
	return drupal_render($output_form);// . $output;
	
}

function panjarseksi_new_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjuplastpage"] = $referer;
	else
		$referer = $_SESSION["spjuplastpage"];*/
	
	$jenis = arg(2);
	if ($jenis=='') $jenis = 'seksi-in';
	
	$kodeuk = apbd_getuseruk();
	$kodesuk = apbd_getusersuk();
	$spjno = apbd_getnospj_gu($kodeuk);
	
	$noref = '';
	$spjtgl = mktime(0,0,0,date('m'),date('d'),apbd_tahun());
	
	if ($jenis == 'seksi-in')
		$keperluan = 'Uang Panjar ke PPTK';
	else
		$keperluan = 'Pengembalian Uang Panjar dari PPTK';
	$jumlah = '0';
	
	
	drupal_set_title($keperluan);
	
	$form['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);
	$form['kodesuk'] = array(
		'#type' => 'value',
		'#value' => $kodesuk,
	);
	$form['jenis'] = array(
		'#type' => 'value',
		'#value' => $jenis,
	);

	
	$form['spjno'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. Panjar'),
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
	
	//SEKSI
	$opt_suk = array();
	# execute the query
	$results = db_query('select kodepa,namapa from {PelakuAktivitas} where kodesuk=:kodesuk order by kodepa', array(':kodesuk'=>$kodesuk));
	
	$opt_suk[''] = '- Pilih PPTK -';
	foreach ($results as $data) {
		$opt_suk[$data->kodepa] = $data->namapa;
	}
	$form['kodepa'] = array(
		'#type' => 'select',
		'#title' =>  t('PPTK'),
		'#options' => $opt_suk,
		'#default_value' => '',
	);	

	//PANJAR
	$opt_panjarseksi = array();
	$opt_panjarseksi['gu'] = 'Ganti Uang';
	$opt_panjarseksi['tu'] = 'Tambahan Uang';
	$form['jenispanjar'] = array(
		'#type' => 'radios',
		'#title' =>  t('Untuk Belanja'),
		'#options' => $opt_panjarseksi,
		'#default_value' => 'gu',
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

function panjarseksi_new_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) 
		form_set_error('spjno', 'Nomor Panjar harap diisi dengan benar');

	$kodepa = $form_state['values']['kodepa'];
	if (($kodepa=='') or ($kodepa=='BARU')) 
		form_set_error('kodepa', 'PPTK harap diisi dengan benar dari salah satu PPTK yang ada');
	 
	$total = $form_state['values']['jumlah'];
	if ($total==0)
		form_set_error('jumlah', 'Jumlah uang panjang agar diisi');
		
} 
	
function panjarseksi_new_main_form_submit($form, &$form_state) {
	$jenis = $form_state['values']['jenis'];
	$jenispanjar = $form_state['values']['jenispanjar'];
	
	$noref = $form_state['values']['noref'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	$kodepa = $form_state['values']['kodepa'];
	$keperluan = $form_state['values']['keperluan'];
	
	$total = $form_state['values']['jumlah'];

	//SIMPAN
	$bendid = apbd_getkodespj($kodeuk);
		 
	
	if ($jenis=='seksi-in') {
		$query = db_insert('bendahara' . $kodeuk) // Table name no longer needs {}
			->fields(array(
				  'bendid' => $bendid,
				  'dokid' => '000000',
				  'kodekeg' => '000000',
				  'noref' => $noref,
				  'jenis' => $jenis,
				  'jenispanjar' => $jenispanjar,
				  'kodeuk' => $kodeuk,
				  'kodesuk' => $kodesuk,
				  'kodepa' => $kodepa,
				  'spjno' => $spjno,
				  'tanggal' => $tanggal,
				  'keperluan' => $keperluan,
				  'total' => $total,
				  'panjarseksi' => '1',

				  'kasbendaharakeluar' => $total,
				  'kaspembantumasuk' => $total,
				  
			))
			->execute();

	} else {
		$query = db_insert('bendahara' . $kodeuk) // Table name no longer needs {}
			->fields(array(
				  'bendid' => $bendid,
				  'dokid' => '000000',
				  'kodekeg' => '000000',
				  'noref' => $noref,
				  'jenis' => $jenis,
				  'jenispanjar' => $jenispanjar,
				  'kodeuk' => $kodeuk,
				  'kodesuk' => $kodesuk,
				  'kodepa' => $kodepa,
				  'spjno' => $spjno,
				  'tanggal' => $tanggal,
				  'keperluan' => $keperluan,
				  'total' => $total,
				  'panjarseksi' => '1',

				  'kasbendaharamasuk' => $total,
				  'kaspembantukeluar' => $total,
				  
			))
			->execute();
		
	}	
	
	drupal_goto('panjarseksiarsip');
	
}


?>
