<?php
function panjar_edit_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$bendid = arg(2);	
	if(arg(3)=='pdf'){		
	
	} else {
	
		//$btn = l('Cetak', '');
		//$btn .= "&nbsp;" . l('Excel', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary')));
		
		//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('pager');
		$output_form = drupal_get_form('panjar_edit_main_form');
		return drupal_render($output_form);// . $output;
	}		
	
}

function panjar_edit_main_form($form, &$form_state) {

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
	$query->fields('d', array('bendid','keperluan', 'kodeuk', 'noref', 'spjno', 'tanggal',  'kodesuk', 'jenis', 'jenispanjar', 'total', 'panjarseksi', 'kodepa'));
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
		
		if ($data->panjarseksi)
			$kodesuk = $data->kodepa;
		else
			$kodesuk = $data->kodesuk;

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
		'#default_value' => $kodesuk,
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

function panjar_edit_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor Panjar harap diisi dengan benar');

	$kodesuk = $form_state['values']['kodesuk'];
	if (($kodesuk=='') or ($kodesuk=='BARU')) 
		form_set_error('kodesuk', 'Bidang harap diisi dengan benar dari salah satu Bidang yang ada');
	
	$total = $form_state['values']['jumlah'];
	if ($total==0)
		form_set_error('jumlah', 'Jumlah uang panjang agar diisi');	
}
	
function panjar_edit_main_form_submit($form, &$form_state) {
	$bendid = $form_state['values']['bendid'];
	$jenispanjar = $form_state['values']['jenispanjar'];

	$noref = $form_state['values']['noref'];
	$spjno = $form_state['values']['spjno'];
	//$spjtgl = $form_state['values']['spjtgl'];
	//$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	
	$tanggal = dateapi_convert_timestamp_to_datetime($form_state['values']['spjtgl']);
	
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

	$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
	->fields(array(
		'spjno' => $spjno,
		'tanggal' => $tanggal,
		'noref' => $noref,
		'kodesuk' => $kodesuk,
		'kodepa' => $kodepa,
		'keperluan' => $keperluan,
		'jenispanjar' => $jenispanjar,			
		'total' => $total,
		'panjarseksi' => $panjarseksi,
		  
	))
	->condition('bendid', $bendid, '=')
	->execute();
		

	$referer = $_SESSION["panjarlastpage"];
	drupal_goto($referer);
}



?>
