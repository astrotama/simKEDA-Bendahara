<?php
function pengembaliankas_edit_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$bendid = arg(2);	
	if(arg(3)=='pdf'){		
	
	} else {
	
		//$btn = l('Cetak', '');
		//$btn .= "&nbsp;" . l('Excel', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary')));
		
		//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('pager');
		$output_form = drupal_get_form('pengembaliankas_edit_main_form');
		return drupal_render($output_form);// . $output;
	}		
	
}

function pengembaliankas_edit_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	$current_url = url(current_path(), array('absolute' => TRUE));
	$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjlastpage"] = $referer;
	else
		$referer = $_SESSION["spjlastpage"];
	
	//db_set_active('penatausahaan');
	$bendid = arg(2);
	$kodeuk = apbd_getuseruk();
	$query = db_select('bendahara' . $kodeuk, 'd');

	# get the desired fields from the database
	$query->fields('d', array('bendid','keperluan', 'kodeuk', 'noref', 'spjno', 'tanggal',  'jenis', 'jenispanjar', 'total'));
	$query->condition('d.bendid', $bendid, '=');
	
	# execute the query
	$results = $query->execute();
		
	$rows = array();
	$no = 0;
	foreach ($results as $data) {
		
		$bendid = $data->bendid;
		
		$spjtgl = strtotime($data->tanggal);		
		$spjno=$data->spjno;
		
		$noref = $data->noref;
		
		$kodeuk = $data->kodeuk;
		$keperluan = $data->keperluan;

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

	
	//JENIS KAS
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
		'#value' => '<span class="glyphicon glyphicon-file" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
		'#suffix' => "&nbsp;<a href='" . $referer . "' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	return $form;
}

function pengembaliankas_edit_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor Panjar harap diisi dengan benar');

	$total = $form_state['values']['jumlah'];
	if ($total==0)
		form_set_error('jumlah', 'Jumlah uang agar diisi');	
}
	
function pengembaliankas_edit_main_form_submit($form, &$form_state) {
	$bendid = $form_state['values']['bendid'];
	$jenispanjar = $form_state['values']['jenispanjar'];

	$noref = $form_state['values']['noref'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal=$spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$kodeuk = $form_state['values']['kodeuk'];
	$keperluan = $form_state['values']['keperluan'];
	
	$total = $form_state['values']['jumlah'];


	$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
	->fields(array(
		'spjno' => $spjno,
		'tanggal' => $tanggal,
		'noref' => $noref,
		'kodesuk' => $kodesuk,
		'keperluan' => $keperluan,
		'jenispanjar' => $jenispanjar,			
		'total' => $total,

		  
	))
	->condition('bendid', $bendid, '=')
	->execute();
		

	$referer = $_SESSION["spjlastpage"];
	drupal_goto($referer);
}



?>
