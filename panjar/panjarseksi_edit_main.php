<?php
function panjarseksi_edit_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$bendid = arg(2);	
	if(arg(3)=='pdf'){		
	
	} else {
	
		//$btn = l('Cetak', '');
		//$btn .= "&nbsp;" . l('Excel', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary')));
		
		//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('pager');
		$output_form = drupal_get_form('panjarseksi_edit_main_form');
		return drupal_render($output_form);// . $output;
	}		
	
}

function panjarseksi_edit_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	$current_url = url(current_path(), array('absolute' => TRUE));
	$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["panjarlastpage"] = $referer;
	else
		$referer = $_SESSION["panjarlastpage"];
	
	//db_set_active('penatausahaan');
	$bendid = arg(2);
	$kodeuk = apbd_getuseruk();
	$query = db_select('bendahara' . $kodeuk, 'd');

	# get the desired fields from the database
	$query->fields('d', array('bendid','keperluan', 'kodeuk', 'noref', 'spjno', 'tanggal',  'kodesuk', 'kodepa', 'jenis', 'jenispanjar', 'total'));
	$query->condition('d.bendid', $bendid, '=');
	
	# execute the query
	$results = $query->execute();
		
	$rows = array();
	$no = 0;
	foreach ($results as $data) {
		
		$bendid = $data->bendid;
		
		//$spjtgl = strtotime($data->tanggal);

		$spjtgl = dateapi_convert_timestamp_to_datetime($data->tanggal);
		
		$spjno=$data->spjno;
		
		$noref = $data->noref;
		
		$kodeuk = $data->kodeuk;
		$kodesuk = $data->kodesuk;
		$kodepa = $data->kodepa;
		$keperluan = $data->keperluan;

		$jenis = $data->jenis;
		$jenispanjar = $data->jenispanjar;
		$total = $data->total;
		
	}
	
	drupal_set_title($keperluan);
	
	$form['bendid'] = array(
		'#type' => 'value',
		'#value' => $bendid,
	);	
	$form['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);
	$form['kodesuk'] = array(
		'#type' => 'value',
		'#value' => $kodesuk,
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
	/*$form['spjtgl'] = array(
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
	);*/
	
	$form['tanggaltitle'] = array(
	'#markup' => 'tanggal',
	);
	$form['spjtgl']= array(
		 '#type' => 'date_select', // types 'date_select, date_text' and 'date_timezone' are also supported. See .inc file.
		 '#default_value' => $spjtgl, 
				
		 //'#default_value'=> array(
		//	'year' => format_date($TANGGAL, 'custom', 'Y'),
		//	'month' => format_date($TANGGAL, 'custom', 'n'), 
		//	'day' => format_date($TANGGAL, 'custom', 'j'), 
		 // ), 
		 
		 '#date_format' => 'd-m-Y',
		 '#date_label_position' => 'within', // See other available attributes and what they do in date_api_elements.inc
		 '#date_timezone' => 'America/Chicago', // Optional, if your date has a timezone other than the site timezone.
		 //'#date_increment' => 15, // Optional, used by the date_select and date_popup elements to increment minutes and seconds.
		 '#date_year_range' => '-30:+1', // Optional, used to set the year range (back 3 years and forward 3 years is the default).
		 //'#description' => 'Tanggal',
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
		'#default_value' => $kodepa,
	);		

	//PANJAR
	$opt_panjar = array();
	$opt_panjar['gu'] = 'Ganti Uang';
	$opt_panjar['tu'] = 'Tambahan Uang';
	$form['jenispanjar'] = array(
		'#type' => 'radios',
		'#title' =>  t('Jenis Kas (GU/TU)'),
		'#options' => $opt_panjar,
		'#default_value' => $jenispanjar,
	);	
	
	$form['jumlah'] = array(
		'#type' => 'textfield',
		'#title' =>  t('Jumlah'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
		'#default_value' => $total,
	);
	
	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
		'#suffix' => "&nbsp;<a href='" . $referer . "' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	return $form;
}

function panjarseksi_edit_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor Panjar harap diisi dengan benar');

	$kodepa = $form_state['values']['kodepa'];
	if (($kodepa=='') or ($kodepa=='BARU')) 
		form_set_error('kodepa', 'PPTK harap diisi dengan benar dari salah satu PPTK yang ada');
	
	$total = $form_state['values']['jumlah'];
	if ($total==0)
		form_set_error('jumlah', 'Jumlah uang panjang agar diisi');	
}
	 
function panjarseksi_edit_main_form_submit($form, &$form_state) {
	$bendid = $form_state['values']['bendid'];
	$jenispanjar = $form_state['values']['jenispanjar'];

	$noref = $form_state['values']['noref'];
	$spjno = $form_state['values']['spjno'];
	//$spjtgl = $form_state['values']['spjtgl'];
	//$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	
	$tanggal = dateapi_convert_timestamp_to_datetime($form_state['values']['spjtgl']);
	
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	$kodepa = $form_state['values']['kodepa'];
	$keperluan = $form_state['values']['keperluan'];
	
	$total = $form_state['values']['jumlah'];


	$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
	->fields(array(
		'spjno' => $spjno,
		'tanggal' => $tanggal,
		'noref' => $noref,
		'kodepa' => $kodepa,
		'keperluan' => $keperluan,
		'jenispanjar' => $jenispanjar,			
		'total' => $total,

		  
	))
	->condition('bendid', $bendid, '=')
	->execute();
		

	$referer = $_SESSION["panjarlastpage"];
	drupal_goto($referer);
}



?>
