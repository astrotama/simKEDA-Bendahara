<?php

function laporanbk6_main($arg=NULL, $nama=NULL) {

	if ($arg) {

		$kodeuk = arg(1);
		$kodekeg = arg(2);
		$kodero = arg(3);

		$tglawal = arg(4);
		$tglakhir = arg(5);

		$exportpdf = arg(6);
		$marginatas = arg(7);
		$tglcetak = arg(8);
		$pptknama = arg(9);
		$pptknip = arg(10);


	} 
	
	//drupal_set_message($exportpdf);
	if ($exportpdf=='pdf')  {

		if (($kodero=='ZZ') or ($kodero==''))
			$output = getLaporanbk6($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		else
			$output = getLaporanbk6_rekening($kodeuk, $kodekeg, $kodero, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);

		apbd_ExportPDF_P($output, $marginatas, 'BK6_' . $kodeuk . '-' . $kodekeg . '-' . $kodero  . '-' . $tglawal . '-' . $tglakhir . '.PDF');


	} else if ($exportpdf=='xls')  {
		if ($kodero=='ZZ'){
			$output = getLaporanbk6_tmpl($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		}else{
			$output = getLaporanbk6_rekening_tmpl($kodeuk, $kodekeg, $kodero, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		}
		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename=Laporan_BK6-" . $kodeuk . "-" . $kodekeg . "-" . $kodero . "-" . $tglawal . "-" . $tglakhir . ".xls" );
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $output;

	}else if ($exportpdf=='tmpl')  {
		if ($kodero=='ZZ'){
			$output = getLaporanbk6_tmpl($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		}else{
			$output = getLaporanbk6_rekening_tmpl($kodeuk, $kodekeg, $kodero, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		}
		$output_form = drupal_get_form('laporanbk6_main_form');
		return drupal_render($output_form). '<p align="center">. . . . .</p>' . $output;

	} else {
		$output_form = drupal_get_form('laporanbk6_main_form');
		return drupal_render($output_form);

	}

}

function laporanbk6_main_form($form, &$form_state) {
    //$argum = arg(8);
    
	$kodeuk = arg(1);
	//if (($kodeuk=='ajax') or ($kodeuk=='')) $kodeuk = $_SESSION["laporan_kas_kodeuk"]; //$kodeuk = apbd_getuseruk();
	if (($kodeuk=='ajax') or ($kodeuk=='')) $kodeuk = apbd_getuseruk();

	$kodekeg = arg(2);
	$kodero = arg(3);
	$tglawal = arg(4);
	$tglakhir = arg(5);
	
	//drupal_set_message(arg(3));
	
	if ($tglawal == '') {
		if(!isset($_SESSION["laporan_kas_tgl_awal"])) $_SESSION["laporan_kas_tgl_awal"] = apbd_date_create_dateone_form();
		$tglawal_form = $_SESSION["laporan_kas_tgl_awal"];
		
		if(!isset($_SESSION["laporan_kas_tgl_akhir"])) $_SESSION["laporan_kas_tgl_akhir"] = apbd_date_create_currdate_form();
		$tglakhir_form = $_SESSION["laporan_kas_tgl_akhir"];	
		
	} else {
		
		$tglawal_form =  apbd_date_convert_db2form($tglawal);	//mktime(0, 0, 0, $arr_awal[1], $arr_awal[2], $arr_awal[0]);
		$tglakhir_form =  apbd_date_convert_db2form($tglakhir);	
		
	}

	$form['kodeuk']= array(
		'#type' 	=> 'value',
		'#value'	=> $kodeuk,
	);

	//AJAX
	// Rekening dropdown list
	$form['kodekeg'] = array(
		'#title' => t('Kegiatan'),
		'#type' => 'select',
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',
		'#options' => _load_kegiatan($kodeuk),
		'#default_value' => $kodekeg,
		'#validated' => TRUE,
		'#ajax' => array(
			'event'=>'change',
			'callback' =>'_ajax_rekening',
			'wrapper' => 'rekening-wrapper',
		),
	);

	// Wrapper for rekdetil dropdown list
	$form['wrapperrekening'] = array(
		'#prefix' => '<div id="rekening-wrapper">',
		'#suffix' => '</div>',
	);

	// Options for rekdetil dropdown list
	$options = array('- Pilih Rekening -');
	if (isset($form_state['values']['kodekeg'])) {
		// Pre-populate options for rekdetil dropdown list if rekening id is set
		$options = _load_rekening($form_state['values']['kodekeg']);
	} else
		$options = _load_rekening($kodekeg);

	// Detil dropdown list
	$form['wrapperrekening']['kodero'] = array(
		'#title' => t('Rekening'),
		'#type' => 'select',
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',
		'#options' => $options,
		'#default_value' => $kodero,
		'#validated' => TRUE,
	);

	//END AJAX

	$form['tglawaljdl']= array(
		'#type'         => 'item',
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		'#markup'=> '<b>Periode laporan, mulai tanggal</b>',
	);
	$form['tglakhirjdl']= array(
		'#type'         => 'item',
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		'#markup'=> '<b>Sampai dengan tanggal</b>',
	);
	$form['tglawal'] = array(
		'#type' => 'date_popup',
		'#date_format' => 'd-m-Y',
		'#date_label_position' => 'within',
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		//'#title' =>  t('Periode laporan, mulai tanggal')
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		//'#default_value' => $tglawal,
		'#default_value'=> array(
			'year' => format_date($tglawal_form, 'custom', 'Y'),
			'month' => format_date($tglawal_form, 'custom', 'n'),
			'day' => format_date($tglawal_form, 'custom', 'j'),
		  ),
	);

	$form['tglakhir'] = array(
		'#type' => 'date_popup',
		'#date_format' => 'd-m-Y',
		'#date_label_position' => 'within',
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		//'#title' =>  t('Sampai dengan tanggal'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		//'#default_value' => $tglakhir,
		'#default_value'=> array(
			'year' => format_date($tglakhir_form, 'custom', 'Y'),
			'month' => format_date($tglakhir_form, 'custom', 'n'),
			'day' => format_date($tglakhir_form, 'custom', 'j'),
		  ),
	);

	$form['pptknama']= array(
		'#type'         => 'textfield',
		'#title'        => 'Nama PPTK',
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '',
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		'#maxlength'    => 50,
		//'#size'         => 20,
		//'#required'     => !$disabled,
		'#disabled'     => false,
		'#default_value'=> '',
	);
	$form['pptknip']= array(
		'#type'         => 'textfield',
		'#title'        => 'NIP PPTK',
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '',
		'#maxlength'    => 50,
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		//'#size'         => 20,
		//'#required'     => !$disabled,
		'#disabled'     => false,
		'#default_value'=> '',
	);

	$form['batasprinter']= array(
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',	
		'#type' => 'item',
		'#markup' => '</br>',
	);
	$form['formprinter'] = array (
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',	
		'#type' => 'fieldset',
		'#title'=>  'Setting Printer',	
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);
	
	$form['formprinter']['tglcetak']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Tanggal Cetak', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		//'#size'         => 20, 
		//'#required'     => !$disabled, 
		'#disabled'     => false, 
		'#default_value'=> date('j F Y'), 
	);
	
	$form['formprinter']['marginatas']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Margin Atas', 
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		//'#size'         => 20, 
		//'#required'     => !$disabled, 
		'#disabled'     => false, 
		'#default_value'=> '15', 
	);
	
	//BUTTON
	$form['batas']= array(
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',	
		'#type' => 'item',
		'#markup' => '</br>',
	);

	$form['button']= array(
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',	
	);

	$form['button']['submittmpl']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> Tampilkan',
		'#attributes' => array('class' => array('btn btn-primary btn-sm')),
	);
	$form['button']['submitxls']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> Excel ',
		'#attributes' => array('class' => array('btn btn-primary btn-sm')),
	);
	$form['button']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Cetak',
		'#attributes' => array('class' => array('btn btn-primary btn-sm')),
	);


	return $form;
}

function _ajax_rekening($form, $form_state) {
	// Return the dropdown list including the wrapper
	return $form['wrapperrekening'];
}

function _load_kegiatan($kodeuk) {
	$kegiatan = array('- Pilih Kegiatan -');


	// Select table
	$query = db_select("kegiatanskpd", "k");
	// Selected fields
	$query->fields("k", array('kodekeg', 'kegiatan'));
	// Filter the active ones only
	$query->condition("k.kodeuk", $kodeuk, '=');

	if (isUserPembantu()) $query->condition("k.kodesuk", apbd_getusersuk(), '=');

	// Order by name
	$query->orderBy("k.kegiatan");
	// Execute query
	$result = $query->execute();

	while($row = $result->fetchObject()){
		// Key-value pair for dropdown options
		$kegiatan[$row->kodekeg] = $row->kegiatan;
	}

	return $kegiatan;
}

function _load_rekening($kodekeg) {
	$rekening = array('- Pilih Rekening -');
	//$rekening = array($kodekeg);

	if ($kodekeg=='') $kodekeg = $_SESSION["bk6-kodekeg"];

	// Select table
	$query = db_select("anggperkeg", "a");
	$query->innerJoin("rincianobyek", "r", "a.kodero=r.kodero");
	// Selected fields
	$query->fields("r", array('kodero', 'uraian'));
	// Filter the active ones only
	$query->condition("a.kodekeg", $kodekeg, "=");
	// Order by name
	$query->orderBy("r.kodero");
	// Execute query
	$result = $query->execute();

	while($row = $result->fetchObject()){
		// Key-value pair for dropdown options
		$rekening[$row->kodero] = $row->kodero . ' - ' . $row->uraian;
	}

	return $rekening;
}

function laporanbk6_main_form_validate($form, &$form_state) {

	$kodekeg = $form_state['values']['kodekeg'];
	if ($kodekeg == '') form_set_error('kodekeg', 'Kegiatan belum dipilih');
	
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);

	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);
	if ($tglakhirx < $tglawalx) form_set_error('tglakhir', 'Tanggal laporan harus diisi dengan benar, dimana tanggal akhir tidak boleh lebih kecil daripada tanggal awal');


	$tglawaly = strtotime($tglawal);
	$tglakhiry = strtotime($tglakhir);
	$datediff =round (($tglakhiry - $tglawaly)/ (60 * 60 * 24));
	if ($datediff > 93) form_set_error('tglakhir', 'Periode maksimal laporan adalah 3 bulan, atur tanggal awal dan tanggal akhir sehingga periode laporannya tidak lebih dari dua bulan.');
	
}

function laporanbk6_main_form_submit($form, &$form_state) {
		$kodeuk = $form_state['values']['kodeuk'];
		$kodekeg = $form_state['values']['kodekeg'];
		$kodero = $form_state['values']['kodero'];
		if ($kodero=='0') $kodero = 'ZZ';

		$tglawal = dateapi_convert_timestamp_to_datetime($form_state['values']['tglawal']);
		$_SESSION["laporan_kas_tgl_awal"] = apbd_date_convert_db2form($tglawal);
		
		$tglakhir = dateapi_convert_timestamp_to_datetime($form_state['values']['tglakhir']);
		$_SESSION["laporan_kas_tgl_akhir"] = apbd_date_convert_db2form($tglakhir);

		$tglcetak = $form_state['values']['tglcetak'];
		$marginatas = $form_state['values']['marginatas'];

		$pptknama = $form_state['values']['pptknama'];
		$pptknip = $form_state['values']['pptknip'];

		$_SESSION["bk6-kodekeg"] = $kodekeg;

		if($form_state['clicked_button']['#value'] == $form_state['values']['submit']){
			$uri = '/laporanbk6/' . $kodeuk . '/' . $kodekeg . '/'  . $kodero . '/'  . $tglawal . '/' . $tglakhir . '/pdf/' . $marginatas . '/' . $tglcetak . '/' . $pptknama . '/' . $pptknip;
		}elseif($form_state['clicked_button']['#value'] == $form_state['values']['submittmpl']){
			$uri = '/laporanbk6/' . $kodeuk . '/' . $kodekeg . '/'  . $kodero . '/'  . $tglawal . '/' . $tglakhir . '/tmpl/' . $marginatas . '/' . $tglcetak . '/' . $pptknama . '/' . $pptknip;
		}else{
			$uri = '/laporanbk6/' . $kodeuk . '/' . $kodekeg . '/'  . $kodero . '/'  . $tglawal . '/' . $tglakhir . '/xls/' . $marginatas . '/' . $tglcetak . '/' . $pptknama . '/' . $pptknip;
		}

		drupal_goto($uri);
}


function getLaporanbk6_rekening($kodeuk, $kodekeg, $kodero, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip){

	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '25px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BUKU REKAPITULASI PENGELUARAN', 'width' => '450px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-6', 'width' => '25px','align'=>'center','style'=>'border:none;font-size:60%;'),
	);
	$rows[]=array(
		array('data' => 'PER RINCIAN OBYEK', 'width' => '500px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;'),
	);

	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'pimpinannama', 'pimpinanjabatan', 'pimpinanpangkat', 'pimpinannip', 'bendaharanama', 'bendaharanip', 'ppknama', 'ppknip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	# execute the query
	$results = $query->execute();
	foreach ($results as $data) {
		$namauk = $data->namauk;
		$pimpinannama = $data->pimpinannama;
		$pimpinanjabatan = $data->pimpinanjabatan;
		$pimpinanpangkat = $data->pimpinanpangkat;
		$pimpinannip = $data->pimpinannip;

		$bendaharanama = $data->bendaharanama;
		$bendaharanip = $data->bendaharanip;

		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;
	}

	//KEGIATAN
	$query = db_select('kegiatanskpd', 'k');
	$query->fields('k', array('kegiatan', 'kodesuk'));
	$query->condition('k.kodekeg', $kodekeg, '=');
	# execute the query
	$results = $query->execute();
	foreach ($results as $data) {
		$kegiatan = $data->kegiatan;
		$kodesuk = $data->kodesuk;
	}

	//BPEMBANTU
	//bila tidak ada pembantu, langsung bendahara
	$bpnama = $bendaharanama;
	$bpnip = $bendaharanip;
	$bpjabatan = 'BENDAHARA PENGELUARAN';

	//if (isUserPembantu())
	if (($kodesuk!='') and isUserPembantu()) {
		$query = db_select('subunitkerja', 'u');
		$query->fields('u', array('bpnama', 'bpnip', 'bpjabatan'));
		$query->condition('u.kodesuk', $kodesuk, '=');
		# execute the query
		$results = $query->execute();
		foreach ($results as $data) {
			$bpnama = $data->bpnama;
			$bpnip = $data->bpnip;
			$bpjabatan = 'BENDAHARA PENGELUARAN PEMBANTU';
		}
	}


	$rows[]=array(
		array('data' => 'SKPD', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => $namauk, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);
	$rows[]=array(
		array('data' => 'Kegiatan', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => $kegiatan, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);
	$rows[]=array(
		array('data' => 'Tahun Anggaran', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => apbd_tahun(), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);
	$rows[]=array(
		array('data' => 'Bulan/Tanggal', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);


	$output = createT(null, $rows);

	//Rekening
	$rows=null;
	$header=null;
	$header[]=array(
		array('data' => 'No.', 'width' => '20px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Tanggal', 'width' => '50px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Uraian', 'width' => '130px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Keterangan', 'width' => '100px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),

		array('data' => 'JUMLAH SPJ', 'width' => '200px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);
	$header[]=array(
		array('data' => 'LS', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'UP/GU', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'TU', 'width' => '70px','align'=>'center','style'=>'border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);


	$query = db_select('anggperkeg', 'a');
	$query->innerJoin('rincianobyek', 'ro', 'a.kodero=ro.kodero');
	$query->fields('a', array('anggaran'));
	$query->fields('ro', array('kodero', 'uraian'));
	$query->condition('a.kodekeg', $kodekeg, '=');
	$query->condition('a.kodero', $kodero, '=');

	# execute the query
	$res_rek = $query->execute();
	foreach ($res_rek as $data_rek) {

		$rows[]=array(
			array('data' => 'Kode Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => $data_rek->kodero, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),
		);
		$rows[]=array(
			array('data' => 'Nama Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => $data_rek->uraian, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

		);
		$rows[]=array(
			array('data' => 'Kredit APBD', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => apbd_fn($data_rek->anggaran), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

		);
		$rows[]=array(
			array('data' => '', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => '', 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

		);

		$output .= createT(null, $rows);

		//transaksi
		$rows=null;
		$query = db_select('bendahara' . $kodeuk, 'b');
		$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
		$query->fields('b', array('tanggal', 'keperluan', 'jenis', 'jenispanjar', 'penerimanama'));
		$query->fields('bi', array('jumlah', 'keterangan'));
		$query->condition('b.kodekeg', $kodekeg, '=');
		$query->condition('bi.kodero', $data_rek->kodero, '=');
		$query->condition('bi.jumlah', 0, '<>');

		$query->condition('b.tanggal', $tglawal, '>=');
		$query->condition('b.tanggal', $tglakhir, '<=');

		$or = db_or();
		$or->condition('b.jenis', 'gaji', '=');
		$or->condition('b.jenis', 'ls', '=');
		$or->condition('b.jenis', 'tu-spj', '=');
		$or->condition('b.jenis', 'gu-spj', '=');
		$or->condition('b.jenis', 'ret-spj', '=');
		$or->condition('b.jenis', 'pindahbuku', '=');
		$query->condition($or);

		$query->orderBy('b.tanggal', 'ASC');
		//dpq($query);

		$total_ls = 0; $total_gu = 0; $total_tu = 0;

		# execute the query
		$no = 0;
		$res_spj = $query->execute();

		foreach ($res_spj as $data_spj) {
			$no++;

			$ls = 0; $gu = 0; $tu = 0;
			if ($data_spj->jenis == 'gu-spj')
				$gu = $data_spj->jumlah;
			else if (($data_spj->jenis == 'ls') or ($data_spj->jenis == 'gaji'))
				$ls += $data_spj->jumlah;
			else if ($data_spj->jenis == 'ret-spj') {

				if ($data_spj->jenispanjar == 'gu')
					$gu = -$data_spj->jumlah;
				else if ($data_spj->jenispanjar == 'ls')
					$ls = -$data_spj->jumlah;
				else
					$tu = -$data_spj->jumlah;

			} else if ($data_spj->jenis == 'pindahbuku') {

				if ($data_spj->jenispanjar == 'gu')
					$gu = $data_spj->jumlah;
				else if ($data_spj->jenispanjar == 'ls')
					$ls = $data_spj->jumlah;
				else
					$tu = $data_spj->jumlah;

			} else
				$tu = $data_spj->jumlah;

			$total_ls += $ls; $total_gu += $gu; $total_tu += $tu;

			$ketdetil = $data_spj->penerimanama;
			if ($data_spj->keterangan<>'') $ketdetil .= ' (' . $data_spj->keterangan . ')';

			$rows[] = array(
				array('data' => $no, 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
				array('data' => apbd_fd($data_spj->tanggal), 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => $data_spj->keperluan, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => $ketdetil, 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);

		}
		if ($no==0) {
			$rows[] = array(
				array('data' => '', 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
				array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => 'Tidak ada', 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => 'Tidak ada', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

				array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);
		}

		//SEBELUMNYA

		$ls_lalu = 0; $gu_lalu = 0; $tu_lalu = 0;
		read_sebelumnya($kodekeg, $data_rek->kodero, $tglawal, $ls_lalu, $gu_lalu, $tu_lalu);

		$rows[]=array(
			array('data' => 'Jumlah periode ini', 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;font-size:80%;'),

			array('data' => apbd_fn($total_ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
		);
		$rows[]=array(
			array('data' => 'Jumlah sampai dengan periode sebelumnya', 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

			array('data' => apbd_fn($ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
		);

		$rows[]=array(
			array('data' => 'Jumlah sampai dengan periode ini', 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

			array('data' => apbd_fn($total_ls + $ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_gu + $gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_tu + $tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
		);
		$rows[]=array(
			array('data' => 'Jumlah Total Pengeluaran', 'width' => '300px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_ls+$total_gu+$total_tu + $ls_lalu + $gu_lalu + $tu_lalu), 'width' => '200px','align'=>'right','style'=>'border-top:1px solid black;border-bottom:1px solid black;font-weight:bold;border-right:1px solid black;font-size:80%;'),
		);

		//space
		$rows[]=array(
			array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;font-size:80%;'),
		);

		//render
		$output.=createT($header, $rows);
		$rows = null;

	}	//end rekening

	$rows[] = array(
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => 'Jepara, ' . $tglcetak,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => 'Mengesahkan','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => 'PEJABAT PELAKSANA TEKNIS KEGIATAN','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => $bpjabatan,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => $pptknama,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;text-decoration:underline;'),
					array('data' => $bpnama,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;text-decoration:underline;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $pptknip,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => 'NIP. ' . $bpnip ,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);

	$output.=createT(null, $rows);
	//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		return $output;

}


function getLaporanbk6_rekening_tmpl($kodeuk, $kodekeg, $kodero, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip){
	$header[]=array(
		array('data' => 'No.', 'width' => '20px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Tanggal', 'width' => '50px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Uraian', 'width' => '130px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Keterangan', 'width' => '100px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),

		array('data' => 'JUMLAH SPJ','colspan'=>3,  'width' => '200px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);
	$header[]=array(
		array('data' => 'LS', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'UP/GU', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'TU', 'width' => '70px','align'=>'center','style'=>'border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);


	$query = db_select('anggperkeg', 'a');
	$query->innerJoin('rincianobyek', 'ro', 'a.kodero=ro.kodero');
	$query->fields('a', array('anggaran'));
	$query->fields('ro', array('kodero', 'uraian'));
	$query->condition('a.kodekeg', $kodekeg, '=');
	$query->condition('a.kodero', $kodero, '=');

	# execute the query
	$res_rek = $query->execute();
	foreach ($res_rek as $data_rek) {
		$rows[]=array(
			array('data' => 'Kode Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => $data_rek->kodero, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),
		);
		$rows[]=array(
			array('data' => 'Nama Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => $data_rek->uraian, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

		);
		$rows[]=array(
			array('data' => 'Kredit APBD', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => apbd_fn($data_rek->anggaran), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

		);
		$rows[]=array(
			array('data' => '', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
			array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
			array('data' => '', 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

		);

		$output .= createT(null, $rows);

		//transaksi
		$rows=null;
		$query = db_select('bendahara' . $kodeuk, 'b');
		$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
		$query->fields('b', array('tanggal', 'keperluan', 'jenis', 'jenispanjar', 'penerimanama'));
		$query->fields('bi', array('jumlah', 'keterangan'));
		$query->condition('b.kodekeg', $kodekeg, '=');
		$query->condition('bi.kodero', $data_rek->kodero, '=');
		$query->condition('bi.jumlah', 0, '<>');

		$query->condition('b.tanggal', $tglawal, '>=');
		$query->condition('b.tanggal', $tglakhir, '<=');

		$or = db_or();
		$or->condition('b.jenis', 'gaji', '=');
		$or->condition('b.jenis', 'ls', '=');
		$or->condition('b.jenis', 'tu-spj', '=');
		$or->condition('b.jenis', 'gu-spj', '=');
		$or->condition('b.jenis', 'ret-spj', '=');
		$or->condition('b.jenis', 'pindahbuku', '=');
		$query->condition($or);

		$query->orderBy('b.tanggal', 'ASC');
		//dpq($query);

		$total_ls = 0; $total_gu = 0; $total_tu = 0;

		# execute the query
		$no = 0;
		$res_spj = $query->execute();
		foreach ($res_spj as $data_spj) {
			$no++;

			$ls = 0; $gu = 0; $tu = 0;
			if ($data_spj->jenis == 'gu-spj')
				$gu = $data_spj->jumlah;
			else if (($data_spj->jenis == 'ls') or ($data_spj->jenis == 'gaji'))
				$ls += $data_spj->jumlah;
			else if ($data_spj->jenis == 'ret-spj') {

				if ($data_spj->jenispanjar == 'gu')
					$gu = -$data_spj->jumlah;
				else if ($data_spj->jenispanjar == 'ls')
					$ls = -$data_spj->jumlah;
				else
					$tu = -$data_spj->jumlah;

			} else if ($data_spj->jenis == 'pindahbuku') {

				if ($data_spj->jenispanjar == 'gu')
					$gu = $data_spj->jumlah;
				else if ($data_spj->jenispanjar == 'ls')
					$ls = $data_spj->jumlah;
				else
					$tu = $data_spj->jumlah;

			} else
				$tu = $data_spj->jumlah;

			$total_ls += $ls; $total_gu += $gu; $total_tu += $tu;

			$ketdetil = $data_spj->penerimanama;
			if ($data_spj->keterangan<>'') $ketdetil .= ' (' . $data_spj->keterangan . ')';

			$rows[] = array(
				array('data' => $no, 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
				array('data' => apbd_fd($data_spj->tanggal), 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => $data_spj->keperluan, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => $ketdetil, 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);

		}
		if ($no==0) {
			$rows[] = array(
				array('data' => '', 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
				array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => 'Tidak ada', 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => 'Tidak ada', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

				array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);
		}

		//SEBELUMNYA
		$ls_lalu = 0; $gu_lalu = 0; $tu_lalu = 0;
		read_sebelumnya($kodekeg, $data_rek->kodero, $tglawal, $ls_lalu, $gu_lalu, $tu_lalu);

		$rows[]=array(
			array('data' => 'Jumlah periode ini', 'colspan'=>4, 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;font-size:80%;'),

			array('data' => apbd_fn($total_ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
		);
		$rows[]=array(
			array('data' => 'Jumlah sampai dengan periode sebelumnya','colspan'=>4,  'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

			array('data' => apbd_fn($ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
		);

		$rows[]=array(
			array('data' => 'Jumlah sampai dengan periode ini', 'colspan'=>4, 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

			array('data' => apbd_fn($total_ls + $ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_gu + $gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_tu + $tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
		);
		$rows[]=array(
			array('data' => 'Jumlah Total Pengeluaran', 'colspan'=>6, 'width' => '430px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($total_ls+$total_gu+$total_tu + $ls_lalu + $gu_lalu + $tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-bottom:1px solid black;font-weight:bold;border-right:1px solid black;font-size:80%;'),
		);

		//space
		$rows[]=array(
			array('data' => '','colspan'=>8,  'width' => '600px','align'=>'center','style'=>'border:none;font-size:80%;'),
		);

		//render
		$output.=createT($header, $rows);
		$rows = null;

	}	//end rekening

	$output.=createT(null, $rows);
	//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		return $output;
}


function getLaporanbk6($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip){
	$styleheader='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;';
	$style='border-right:1px solid black;';

	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '25px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BUKU REKAPITULASI PENGELUARAN', 'width' => '450px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-6', 'width' => '25px','align'=>'center','style'=>'border:none;font-size:60%;'),
	);
	$rows[]=array(
		array('data' => 'PER RINCIAN OBYEK', 'width' => '500px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;'),
	);

	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'pimpinannama', 'pimpinanjabatan', 'pimpinanpangkat', 'pimpinannip', 'bendaharanama', 'bendaharanip', 'ppknama', 'ppknip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	# execute the query
	$results = $query->execute();
	foreach ($results as $data) {
		$namauk = $data->namauk;
		$pimpinannama = $data->pimpinannama;
		$pimpinanjabatan = $data->pimpinanjabatan;
		$pimpinanpangkat = $data->pimpinanpangkat;
		$pimpinannip = $data->pimpinannip;

		$bendaharanama = $data->bendaharanama;
		$bendaharanip = $data->bendaharanip;

		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;
	}

	//KEGIATAN
	$query = db_select('kegiatanskpd', 'k');
	$query->fields('k', array('kegiatan', 'kodesuk'));
	$query->condition('k.kodekeg', $kodekeg, '=');
	# execute the query
	$results = $query->execute();
	foreach ($results as $data) {
		$kegiatan = $data->kegiatan;
		$kodesuk = $data->kodesuk;
	}

	//BPEMBANTU
	//bila tidak ada pembantu, langsung bendahara
	$bpnama = $bendaharanama;
	$bpnip = $bendaharanip;
	$bpjabatan = 'BENDAHARA PENGELUARAN';

	//if (isUserPembantu())
	if (($kodesuk!='') and isUserPembantu()) {
		$query = db_select('subunitkerja', 'u');
		$query->fields('u', array('bpnama', 'bpnip', 'bpjabatan'));
		$query->condition('u.kodesuk', $kodesuk, '=');
		# execute the query
		$results = $query->execute();
		foreach ($results as $data) {
			$bpnama = $data->bpnama;
			$bpnip = $data->bpnip;
			$bpjabatan = 'BENDAHARA PENGELUARAN PEMBANTU';
		}
	}


	$rows[]=array(
		array('data' => 'SKPD', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => $namauk, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);
	$rows[]=array(
		array('data' => 'Kegiatan', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => $kegiatan, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);
	$rows[]=array(
		array('data' => 'Tahun Anggaran', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => apbd_tahun(), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);
	$rows[]=array(
		array('data' => 'Bulan/Tanggal', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

	);

	$output = createT(null, $rows);

	//Rekening
	$rows=null;
	$header=null;
	$header[]=array(
		array('data' => 'No.', 'width' => '20px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Tanggal', 'width' => '50px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Uraian', 'width' => '130px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Keterangan', 'width' => '100px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),

		array('data' => 'JUMLAH SPJ', 'width' => '200px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);
	$header[]=array(
		array('data' => 'LS', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'UP/GU', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'TU', 'width' => '70px','align'=>'center','style'=>'border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);


	$query = db_select('anggperkeg', 'a');
	$query->innerJoin('rincianobyek', 'ro', 'a.kodero=ro.kodero');
	$query->fields('a', array('anggaran'));
	$query->fields('ro', array('kodero', 'uraian'));
	$query->condition('a.kodekeg', $kodekeg, '=');
	$query->orderBy('ro.kodero', 'ASC');
	//dpq($query);

	# execute the query
	$res_rek = $query->execute();
	foreach ($res_rek as $data_rek) {
		if (is_ada_transaksi($kodekeg, $data_rek->kodero, $tglawal, $tglakhir)) {

			$rows[]=array(
				array('data' => 'Kode Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
				array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
				array('data' => $data_rek->kodero, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Nama Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
				array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
				array('data' => $data_rek->uraian, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

			);
			$rows[]=array(
				array('data' => 'Kredit APBD', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
				array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
				array('data' => apbd_fn($data_rek->anggaran), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

			);

			$output .= createT(null, $rows);

			//transaksi
			$rows=null;

			$query = db_select('bendahara' . $kodeuk, 'b');
			$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
			$query->fields('b', array('tanggal', 'keperluan', 'jenis', 'jenispanjar', 'penerimanama'));
			$query->fields('bi', array('jumlah', 'keterangan'));
			$query->condition('b.kodekeg', $kodekeg, '=');
			$query->condition('bi.kodero', $data_rek->kodero, '=');
			$query->condition('bi.jumlah', 0, '<>');

			$query->condition('b.tanggal', $tglawal, '>=');
			$query->condition('b.tanggal', $tglakhir, '<=');

			$or = db_or();
			$or->condition('b.jenis', 'gaji', '=');
			$or->condition('b.jenis', 'ls', '=');
			$or->condition('b.jenis', 'tu-spj', '=');
			$or->condition('b.jenis', 'gu-spj', '=');
			$or->condition('b.jenis', 'ret-spj', '=');
			$or->condition('b.jenis', 'pindahbuku', '=');
			$query->condition($or);

			$query->orderBy('b.tanggal', 'ASC');
			//dpq($query);

			$total_ls = 0; $total_gu = 0; $total_tu = 0;

			# execute the query
			$no = 0;
			$res_spj = $query->execute();
			foreach ($res_spj as $data_spj) {
				$no++;

				$ls = 0; $gu = 0; $tu = 0;
				if ($data_spj->jenis == 'gu-spj')
					$gu = $data_spj->jumlah;

				else if (($data_spj->jenis == 'ls') or ($data_spj->jenis == 'gaji'))
					$ls += $data_spj->jumlah;

				else if ($data_spj->jenis == 'ret-spj') {

					if ($data_spj->jenispanjar == 'gu')
						$gu = -$data_spj->jumlah;
					else if ($data_spj->jenispanjar == 'ls')
						$ls = -$data_spj->jumlah;
					else
						$tu = -$data_spj->jumlah;

				} else if ($data_spj->jenis == 'pindahbuku') {

					if ($data_spj->jenispanjar == 'gu')
						$gu = $data_spj->jumlah;
					else if ($data_spj->jenispanjar == 'ls')
						$ls = $data_spj->jumlah;
					else
						$tu = $data_spj->jumlah;


					} else
						$tu = $data_spj->jumlah;

				$total_ls += $ls; $total_gu += $gu; $total_tu += $tu;

				$ketdetil = $data_spj->penerimanama;
				if ($data_spj->keterangan<>'') $ketdetil .= ' (' . $data_spj->keterangan . ')';
				$rows[] = array(
					array('data' => $no, 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
					array('data' => apbd_fd($data_spj->tanggal), 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => $data_spj->keperluan, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => $ketdetil, 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

					array('data' => apbd_fn($ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => apbd_fn($gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => apbd_fn($tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				);

			}
			if ($no==0) {
				$rows[] = array(
					array('data' => '', 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
					array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => 'Tidak ada', 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => 'Tidak ada', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

					array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				);
			}

			//SEBELUMNYA
			$ls_lalu = 0; $gu_lalu = 0; $tu_lalu = 0;
			read_sebelumnya($kodekeg, $data_rek->kodero, $tglawal, $ls_lalu, $gu_lalu, $tu_lalu);

			$rows[]=array(
				array('data' => 'Jumlah periode ini', 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($total_ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Jumlah sampai dengan periode sebelumnya', 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Jumlah sampai dengan periode ini', 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($total_ls + $ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_gu + $gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_tu + $tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Jumlah Total Pengeluaran', 'width' => '300px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_ls+$total_gu+$total_tu + $ls_lalu + $gu_lalu + $tu_lalu), 'width' => '200px','align'=>'right','style'=>'border-top:1px solid black;border-bottom:1px solid black;font-weight:bold;border-right:1px solid black;font-size:80%;'),
			);

			//space
			$rows[]=array(
				array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;font-size:80%;'),
			);

			//render
			$output.=createT($header, $rows);
			$rows = null;

		}	//ada transaksi
	}	//end rekening

	$rows[] = array(
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => 'Jepara, ' . $tglcetak,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => 'Mengesahkan','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => 'PEJABAT PELAKSANA TEKNIS KEGIATAN','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => $bpjabatan,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => $pptknama,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;text-decoration:underline;'),
					array('data' => $bpnama,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;text-decoration:underline;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $pptknip,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => 'NIP. ' . $bpnip ,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);

	$output.=createT(null, $rows);
	//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		return $output;
}


function getLaporanbk6_tmpl($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip){
	$styleheader='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;';
	$style='border-right:1px solid black;';
	$header[]=array(
		array('data' => 'No.', 'width' => '20px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Tanggal', 'width' => '50px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Uraian', 'width' => '130px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Keterangan', 'width' => '100px','rowspan'=>2,'align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),

		array('data' => 'JUMLAH SPJ', 'colspan'=>3, 'width' => '200px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);
	$header[]=array(
		array('data' => 'LS', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'UP/GU', 'width' => '65px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'TU', 'width' => '70px','align'=>'center','style'=>'border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);


	$query = db_select('anggperkeg', 'a');
	$query->innerJoin('rincianobyek', 'ro', 'a.kodero=ro.kodero');
	$query->fields('a', array('anggaran'));
	$query->fields('ro', array('kodero', 'uraian'));
	$query->condition('a.kodekeg', $kodekeg, '=');
	$query->orderBy('ro.kodero', 'ASC');
	//dpq($query);

	# execute the query
	$res_rek = $query->execute();
	foreach ($res_rek as $data_rek) {
		if (is_ada_transaksi($kodekeg, $data_rek->kodero, $tglawal, $tglakhir)) {

			$rows[]=array(
				array('data' => 'Kode Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
				array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
				array('data' => $data_rek->kodero, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Nama Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
				array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
				array('data' => $data_rek->uraian, 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

			);
			$rows[]=array(
				array('data' => 'Kredit APBD', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
				array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
				array('data' => apbd_fn($data_rek->anggaran), 'width' => '380px','align'=>'left','style'=>'border:none;font-size:80%;'),

			);

			$output .= createT(null, $rows);

			//transaksi
			$rows=null;

			$query = db_select('bendahara' . $kodeuk, 'b');
			$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
			$query->fields('b', array('tanggal', 'keperluan', 'jenis', 'jenispanjar', 'penerimanama'));
			$query->fields('bi', array('jumlah', 'keterangan'));
			$query->condition('b.kodekeg', $kodekeg, '=');
			$query->condition('bi.kodero', $data_rek->kodero, '=');
			$query->condition('bi.jumlah', 0, '<>');

			$query->condition('b.tanggal', $tglawal, '>=');
			$query->condition('b.tanggal', $tglakhir, '<=');

			$or = db_or();
			$or->condition('b.jenis', 'gaji', '=');
			$or->condition('b.jenis', 'ls', '=');
			$or->condition('b.jenis', 'tu-spj', '=');
			$or->condition('b.jenis', 'gu-spj', '=');
			$or->condition('b.jenis', 'ret-spj', '=');
			$or->condition('b.jenis', 'pindahbuku', '=');
			$query->condition($or);

			$query->orderBy('b.tanggal', 'ASC');
			//dpq($query);

			$total_ls = 0; $total_gu = 0; $total_tu = 0;

			# execute the query
			$no = 0;
			$res_spj = $query->execute();
			foreach ($res_spj as $data_spj) {
				$no++;

				$ls = 0; $gu = 0; $tu = 0;
				if ($data_spj->jenis == 'gu-spj')
					$gu = $data_spj->jumlah;

				else if (($data_spj->jenis == 'ls') or ($data_spj->jenis == 'gaji'))
					$ls += $data_spj->jumlah;

				else if ($data_spj->jenis == 'ret-spj') {

					if ($data_spj->jenispanjar == 'gu')
						$gu = -$data_spj->jumlah;
					else if ($data_spj->jenispanjar == 'ls')
						$ls = -$data_spj->jumlah;
					else
						$tu = -$data_spj->jumlah;

				} else if ($data_spj->jenis == 'pindahbuku') {

					if ($data_spj->jenispanjar == 'gu')
						$gu = $data_spj->jumlah;
					else if ($data_spj->jenispanjar == 'ls')
						$ls = $data_spj->jumlah;
					else
						$tu = $data_spj->jumlah;


					} else
						$tu = $data_spj->jumlah;

				$total_ls += $ls; $total_gu += $gu; $total_tu += $tu;

				$ketdetil = $data_spj->penerimanama;
				if ($data_spj->keterangan<>'') $ketdetil .= ' (' . $data_spj->keterangan . ')';
				$rows[] = array(
					array('data' => $no, 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
					array('data' => apbd_fd($data_spj->tanggal), 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => $data_spj->keperluan, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => $ketdetil, 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

					array('data' => apbd_fn($ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => apbd_fn($gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => apbd_fn($tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				);

			}
			if ($no==0) {
				$rows[] = array(
					array('data' => '', 'width' => '20px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
					array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => 'Tidak ada', 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => 'Tidak ada', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),

					array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => '', 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
					array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				);
			}

			//SEBELUMNYA
			$ls_lalu = 0; $gu_lalu = 0; $tu_lalu = 0;
			read_sebelumnya($kodekeg, $data_rek->kodero, $tglawal, $ls_lalu, $gu_lalu, $tu_lalu);

			$rows[]=array(
				array('data' => 'Jumlah periode ini', 'colspan'=>4, 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($total_ls), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_gu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_tu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Jumlah sampai dengan periode sebelumnya', 'colspan'=>4, 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Jumlah sampai dengan periode ini', 'colspan'=>4, 'width' => '300px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),

				array('data' => apbd_fn($total_ls + $ls_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_gu + $gu_lalu), 'width' => '65px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_tu + $tu_lalu), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			);
			$rows[]=array(
				array('data' => 'Jumlah Total Pengeluaran', 'colspan'=>6, 'width' => '300px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;font-size:80%;'),
				array('data' => apbd_fn($total_ls+$total_gu+$total_tu + $ls_lalu + $gu_lalu + $tu_lalu), 'width' => '200px','align'=>'right','style'=>'border-top:1px solid black;border-bottom:1px solid black;font-weight:bold;border-right:1px solid black;font-size:80%;'),
			);

			//space
			$rows[]=array(
				array('data' => '', 'colspan'=>8, 'width' => '500px','align'=>'center','style'=>'border:none;font-size:80%;'),
			);

			//render
			$output.=createT($header, $rows);
			$rows = null;

		}	//ada transaksi
	}	//end rekening

	$output.=createT(null, $rows);
	//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		return $output;
}

function is_ada_transaksi($kodekeg, $kodero, $tglawal, $tglakhir) {

	$kodeuk = substr($kodekeg, 4,2);
	$jumlah = 0;

	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$query->condition('bi.jumlah', 0, '<>');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');

	$result = $query->execute();
	foreach ($result as $data) {
		$jumlah = $data->total;
	}

	return ($jumlah>0);
}

function read_sebelumnya($kodekeg, $kodero, $tglawal, &$ls, &$gu, &$tu) {
	$ls = 0; $gu = 0; $tu = 0;
	
	$kodeuk = substr($kodekeg, 4,2);
	
	//rea
	$kodeuk = substr($kodekeg, 4,2);
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->fields('b', array('jenis'));
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$query->condition('b.tanggal', $tglawal, '<');

	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '=');
	$or->condition('b.jenis', 'pindahbuku', '=');
	$query->condition($or);
	$query->groupBy('b.jenis');

	$result = $query->execute();
	foreach ($result as $data_spj) {

		if ($data_spj->jenis == 'gu-spj')
			$gu = $data_spj->total;
		else if (($data_spj->jenis == 'ls') or ($data_spj->jenis == 'gaji'))
			$ls += $data_spj->total;
		else if ($data_spj->jenis == 'tu-spj')
			$tu = $data_spj->total;
		else {
			if ($data_spj->jenispanjar == 'gu')
				$gu = $data_spj->total;
			else if ($data_spj->jenispanjar == 'ls')
				$ls = $data_spj->total;
			else
				$tu = $data_spj->total;

		}

	}

	//ret
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->fields('b', array('jenispanjar'));
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$query->condition('b.tanggal', $tglawal, '<');
	$query->condition('b.jenis', 'ret-spj', '=');

	$query->groupBy('b.jenispanjar');

	$result = $query->execute();
	foreach ($result as $data_spj) {

		if ($data_spj->jenispanjar == 'gu')
			$gu -= $data_spj->total;
		else if ($data_spj->jenispanjar == 'ls')
			$ls -= $data_spj->total;
		else
			$tu -= $data_spj->total;

	}
}
?>
