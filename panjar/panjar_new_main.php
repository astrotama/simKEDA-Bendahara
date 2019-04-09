<?php
function panjar_new_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('panjar_new_main_form');
	return drupal_render($output_form);// . $output;
	
}

function panjar_new_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjuplastpage"] = $referer;
	else
		$referer = $_SESSION["spjuplastpage"];*/
	
	$jenis = arg(2);
	if ($jenis=='') $jenis = 'pjr-in';
	
	$kodeuk = apbd_getuseruk();
	$spjno = apbd_getnospj_gu($kodeuk);
	
	$noref = '';
	$spjtgl = mktime(0,0,0,date('m'),date('d'),apbd_tahun());
	
	if ($jenis == 'pjr-in')
		$keperluan = 'Uang Panjar ke Bendahara Pembantu';
	else
		$keperluan = 'Pengembalian Uang Panjar';
	$jumlah = '0';
	
	
	drupal_set_title($keperluan);
	
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
	
	//BIDANG
	$opt_suk = array();
 	$opt_suk[''] = '- Pilih Bidang/Bagian -';

	$results = db_query('select kodesuk, namasuk from {subunitkerja} where kodeuk=:kodeuk order by kodesuk', array(':kodeuk'=>$kodeuk));
	foreach ($results as $data) {
		$opt_suk[$data->kodesuk] = $data->namasuk;
		
		//seksi sekretariat
		if ($data->kodesuk == $kodeuk . '01') {
			$res = db_query('select kodepa, namapa from {PelakuAktivitas} where kodesuk=:kodesuk order by kodepa', array(':kodesuk'=>$data->kodesuk));
			foreach ($res as $datax) {
				$opt_suk[$datax->kodepa] = '- ' . $datax->namapa;
			}
		}
	}
	$form['kodesuk'] = array(
		'#type' => 'select',
		'#title' =>  t('Bidang/Bagian'),
		'#options' => $opt_suk,
		'#default_value' => '',
	);	

	//PANJAR
	$opt_panjar = array();
	$opt_panjar['gu'] = 'Ganti Uang';
	$opt_panjar['tu'] = 'Tambahan Uang';
	$form['jenispanjar'] = array(
		'#type' => 'radios',
		'#title' =>  t('Untuk Belanja'),
		'#options' => $opt_panjar,
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

function panjar_new_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) 
		form_set_error('spjno', 'Nomor Panjar harap diisi dengan benar');

	$kodesuk = $form_state['values']['kodesuk'];
	if (($kodesuk=='') or ($kodesuk=='BARU')) 
		form_set_error('kodesuk', 'Bidang harap diisi dengan benar dari salah satu Bidang yang ada');
	
	$total = $form_state['values']['jumlah'];
	if ($total==0)
		form_set_error('jumlah', 'Jumlah uang panjang agar diisi');
		
}
	
function panjar_new_main_form_submit($form, &$form_state) {
	$jenis = $form_state['values']['jenis'];
	$jenispanjar = $form_state['values']['jenispanjar'];
	
	$noref = $form_state['values']['noref'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	$keperluan = $form_state['values']['keperluan'];
	
	$total = $form_state['values']['jumlah'];

	if (strlen($kodesuk) == 4) {
		$kodepa = '';
		$panjarseksi = 0;
	} else {
		$kodepa = $kodesuk;
		$kodesuk = substr($kodepa, 0, 4);
		$panjarseksi = 1;
	}
	
	//SIMPAN
	$bendid = apbd_getkodespj($kodeuk);
		
	
	if ($jenis=='pjr-in') {
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
				  'panjarseksi' => $panjarseksi,

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
				  'panjarseksi' => $panjarseksi,

				  'kasbendaharamasuk' => $total,
				  'kaspembantukeluar' => $total,
				  
			))
			->execute();
		
	}	
	
	drupal_goto('panjararsip');
	
}


?>
