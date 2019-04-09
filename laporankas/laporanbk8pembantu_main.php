<?php
function laporanbk8pembantu_main($arg=NULL, $nama=NULL) {
    $h = '<style>label{font-weight: bold; display: block; width: 140px; float: left;}</style>';
    //drupal_set_html_head($h);
	//drupal_add_css('apbd.css');
	//drupal_add_css('files/css/tablenew.css');
	//drupal_add_js('files/js/kegiatancam.js');
	$qlike='';
	$limit = 10;
    
	if ($arg) {
		
		
		$kodeuk = arg(2);
		$kodesuk = arg(3);
		$tglawal = arg(4);
		$tglakhir = arg(5);
		
		$exportpdf = arg(6);

		$tglcetak = arg(7);
		$marginatas = arg(8);
		
		
		
		
	}
	
	if (isset($exportpdf) && ($exportpdf=='pdf'))  {	
		$output = getLaporanbk8($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
		//apbd_ExportPDF('L', 'F4', $output, 'BK-8');
		printLaporanbk8($output, 'BK8_Pembantu_' . $kodeuk . $kodesuk . '-' . $tglawal . '-' . $tglakhir . '.PDF', $marginatas);
	
	}  else if (isset($exportpdf) && ($exportpdf=='xls'))  {	
		$output = getLaporanbk8($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename=Laporan_BK8_Pembantu_" . $kodeuk . $kodesuk . "-" . $tglawal . "-" . $tglakhir . ".xls" );
		header("Pragma: no-cache"); 
		header("Expires: 0");
		echo $output;		
	
	} else if (isset($exportpdf) && ($exportpdf=='tmpl'))  {	
		$output = getLaporanbk8_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
		$output_form = drupal_get_form('laporanbk8pembantu_main_form');
		return drupal_render($output_form). '<p align="center">. . . . .</p>' . $output;
	
	} else {
		$output_form = drupal_get_form('laporanbk8pembantu_main_form');
		return drupal_render($output_form);
		
	}	
	
}

function laporanbk8pembantu_main_form ($form, &$form_state) {
	$kodeuk = arg(2);
	if ($kodeuk=='') $kodeuk = apbd_getuseruk();
	
	$kodesuk = arg(3);
	if ($kodesuk=='') $kodesuk = apbd_getusersuk();
	
	$tglawal = arg(4);
	$tglakhir = arg(5);

	
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

	if (isUserPembantu())
		$form['kodesuk']= array(
			'#type'     => 'hidden', 
			'#default_value'	=> apbd_getusersuk(), 
		);
		
	else {
		$query = db_select('subunitkerja', 's');
		$query->fields('s', array('kodesuk', 'namasuk'));
		$query->condition('s.kodeuk', $kodeuk, '=');
		$results = $query->execute();
		foreach ($results as $data) {
			$opt_suk[$data->kodesuk] = $data->namasuk;
		}
		
		$form['kodesuk']= array(
			'#type'     => 'select', 
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',
			'#title' =>  t('Bidang/Bagian'),
			'#options' =>  $opt_suk,
			'#default_value'	=> $kodesuk, 
			'#validated' => TRUE,
			'#ajax' => array(
				'event'=>'change',
				'callback' =>'_ajax_bagian',
				'wrapper' => 'bagian-wrapper',
			),			
		);
	}
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
	
	$lastpage = 5;
	
	// Wrapper for rekdetil dropdown list
	$form['wrapperbagian'] = array(
		'#prefix' => '<div id="bagian-wrapper">',
		'#suffix' => '</div>',
	);
	
	// Options for rekdetil dropdown list
	//$arr_page = array('- Pilih -');
	if (isset($form_state['values']['kodesuk'])) {
		// Pre-populate options for rekdetil dropdown list if rekening id is set
		$arr_page = _load_bagian($kodeuk, $form_state['values']['kodesuk']);
	} else
		$arr_page = _load_bagian($kodeuk, $kodesuk);
	
	
	$form['wrapperbagian']['lembar']= array(
		'#type'         => 'select', 
		'#title'        => 'Bagian ke-', 
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',
		'#options'  => $arr_page, 
		'#description'  => 'Untuk OPD besar dengan banyak kegiatan dan mengalami kesulitan dalam mencetak BK-8, disarankan untuk mencetak BK-8 per bagian.',
		'#default_value'=> '1', 
	);
	$form['wrapperbagian']['lastpage']= array(
		'#type' 	=> 'value', 
		'#value'	=> $lastpage, 
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


function _ajax_bagian($form, $form_state) {
	// Return the dropdown list including the wrapper
	return $form['wrapperbagian'];
}

function _load_bagian($kodeuk, $kodesuk) {
	
	//drupal_set_message('i . ' . $kodeuk);
	//drupal_set_message('i . ' . $kodesuk);
	 
	$arr_page = array();
	$arr_page[0] = 'Keseluruhan';
	$res_page = db_query('select distinct batch from {kegiatanbk8p} where kodeuk=:kodeuk and kodesuk=:kodesuk order by batch', array(':kodeuk' => $kodeuk, ':kodesuk' => $kodesuk));
	foreach ($res_page as $data) {
		$arr_page[$data->batch] = 'Bagian ke-' . $data->batch;
	}	
	return $arr_page;
}


function laporanbk8pembantu_main_form_validate($form, &$form_state) {
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);
	
	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);		
	if ($tglakhirx < $tglawalx) form_set_error('tglakhir', 'Tanggal laporan harus diisi dengan benar, dimana tanggal akhir tidak boleh lebih kecil daripada tanggal awal');

	
}

function laporanbk8pembantu_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);
	
	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);		
	
	$tglcetak = $form_state['values']['tglcetak'];
	$marginatas = $form_state['values']['marginatas'];
	
	if($form_state['clicked_button']['#value'] == $form_state['values']['submit'])
		$uri = 'laporanbk8p/foo/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/pdf/' . $tglcetak . '/' . $marginatas;
	elseif($form_state['clicked_button']['#value'] == $form_state['values']['submittmpl'])
		$uri = 'laporanbk8p/foo/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/tmpl/' . $tglcetak . '/' . $marginatas;
	else
		$uri = 'laporanbk8p/foo/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/xls/' . $tglcetak . '/' . $marginatas;
	
	drupal_goto($uri);
	
}


function getLaporanbk8($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak){
	$bulan = 1;

	set_time_limit(0);
	//ini_set('memory_limit','940M');
	
	$styleheader='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;';
	$style='border-right:1px solid black;';

	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'bendaharanama', 'bendaharanip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	//dpq($query);			
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

	}	


	//BIDANG
	$query = db_select('subunitkerja', 'u');
	$query->fields('u', array('namasuk', 'bpnama', 'bpnip', 'kabidnama', 'kabidnip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	$query->condition('u.kodesuk', $kodesuk, '=');
	//dpq($query);			
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {
		$namasuk = $data->namasuk;
		
		$bpnama = $data->bpnama;
		$bpnip = $data->bpnip;
		$kabidnama = $data->kabidnama;
		$kabidnip = $data->kabidnip;

	}	
	
	//HEADER TOP
	$header=array();
	$rows[]=array(
		array('data' => 'KABUPATEN JEPARA', 'width' => '875px','align'=>'center'),
	);
	$rows[]=array(
		array('data' => 'LAPORAN PERTANGGUNGJAWABAN BENDAHARA PENGELUARAN PEMBANTU', 'width' => '875px','align'=>'center'),
	);


	
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center'),
	);
	$rows[]=array(
		array('data' => 'SKPD', 'width' => '155px','align'=>'left','style'=>'font-size:70%;border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:70%;'),
		array('data' => $namauk, 'width' => '700px','align'=>'left','style'=>'border:none;font-size:70%;'),
	);
	$rows[]=array(
		array('data' => 'Bidang/Bagian', 'width' => '155px','align'=>'left','style'=>'font-size:70%;border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:70%;'),
		array('data' => $namasuk, 'width' => '700px','align'=>'left','style'=>'border:none;font-size:70%;'),
	);
	$rows[]=array(
		array('data' => 'PPK', 'width' => '155px','align'=>'left','style'=>'border:none;font-size:70%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:70%;'),
		array('data' => $kabidnama, 'width' => '700px','align'=>'left','style'=>'border:none;font-size:70%;'),
	);
	$rows[]=array(
		array('data' => 'Bendahara Pembantu', 'width' => '155px','align'=>'left','style'=>'border:none;font-size:70%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:70%;'),
		array('data' => $bpnama, 'width' => '700px','align'=>'left','style'=>'border:none;font-size:70%;'),
	);
	$rows[]=array(
		array('data' => 'Tahun Anggaran', 'width' => '155px','align'=>'left','style'=>'border:none;font-size:70%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:70%;'),
		array('data' => apbd_tahun(), 'width' => '700px','align'=>'left','style'=>'border:none;font-size:70%;'),
	);
	$rows[]=array(
		array('data' => 'Bulan/Periode', 'width' => '155px','align'=>'left','style'=>'border:none;font-size:70%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:70%;'),
		array('data' => apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '700px','align'=>'left','style'=>'border:none;font-size:70%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '155px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '700px','align'=>'left','style'=>'border:none;'),
	);
	
	
	// PENGELUARAN ...........
	$rows[]=array(
		array('data' => 'PENGELUARAN', 'width' => '155px','align'=>'left','style'=>'border:none;font-size:70%;'),
		array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '700px','align'=>'left','style'=>'border:none;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$rows=null;
	$header=null;
	if (arg(5) == 'xls'){
	$header[]=array(
		array('data' => 'Kode',  'width' => '40px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
		array('data' => 'Uraian',  'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Jumlah Anggaran',  'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji s.d Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-UP/GU Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-TU Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Jumlah SPJ (LS+UP/GU/TU)', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Sisa Pagu Anggaran', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
	);
	
	}else{
	$header[]=array(
		array('data' => 'Kode',  'width' => '40px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
		array('data' => 'Uraian',  'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Jumlah Anggaran',  'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji s.d Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-UP/GU Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-TU Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'Jumlah SPJ (LS+UP/GU/TU)', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Sisa Pagu Anggaran', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
	);
	}
	//init total
	$total_anggaran = 0;
	$total_lslalu =0; $total_lsini = 0; $total_lstotal = 0;
	$total_gulalu =0; $total_guini = 0; $total_gutotal = 0;
	$total_tulalu =0; $total_tuini = 0; $total_tutotal = 0;

	$total_jumlahspj = 0;
	$total_sisa = 0;
	
	//var spj
	$lslalu =0; $lsini = 0; $lstotal = 0;
	$gulalu =0; $guini = 0; $gutotal = 0;
	$tulalu =0; $tuini = 0; $tutotal = 0;
	
	//kegiatan
	$query = db_select('kegiatanskpd', 'k');
	$query->fields('k', array('kodekeg', 'kodepro', 'kegiatan', 'anggaran'));
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	if ($kodeuk=='81') $query->condition('k.isppkd', 0, '=');
	$query->orderBy('k.kodepro', 'ASC');
	$query->orderBy('k.kodekeg', 'ASC');
	//dpq($query);	
		
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {

		$anggaran = $data->anggaran;

		//read spj kegiatan
		read_spj_kegiatan($kodesuk, $data->kodekeg, $tglawal, $tglakhir, $lslalu, $lsini, $lstotal, $gulalu, $guini, $gutotal, $tulalu, $tuini, $tutotal);
		
		//SUB TOTAL			KOLOM JUMLAH SPJ
		$jumlahspj = $lstotal + $gutotal + $tutotal;
		$sisa = $anggaran - $jumlahspj;
		
		//TOTAL				PENGELUARAN
		$total_anggaran += $anggaran;
		$total_lslalu += $lslalu; $total_lsini += $lsini; $total_lstotal += $lstotal;
		$total_gulalu += $gulalu; $total_guini += $guini; $total_gutotal += $gutotal;
		$total_tulalu += $tulalu; $total_tuini += $tuini; $total_tutotal += $tutotal;

		$total_jumlahspj += $jumlahspj;
		$total_sisa += $sisa;
		
		//Render Kegiatan	
		$rows[]=array(
			array('data' => $data->kodepro . '.' . substr($data->kodekeg, -3), 'width' => '40px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => $data->kegiatan, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;font-weight:bold;'),
			array('data' => apbd_fn($data->anggaran), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),

			array('data' => apbd_fn($lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($sisa), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			
		);	
		
		//Hanya yang ada SPJ yang ditampilkan
		if ($jumlahspj>0) {
			//Rekening
			$query = db_select('anggperkeg', 'a');
			$query->innerJoin('rincianobyek', 'ro', 'a.kodero=ro.kodero');
			$query->fields('a', array('anggaran'));
			$query->fields('ro', array('kodero', 'uraian'));
			$query->condition('a.kodekeg', $data->kodekeg, '=');
			$query->orderBy('ro.kodero', 'ASC');
			//dpq($query);	
				
			# execute the query	
			$res_rek = $query->execute();
			foreach ($res_rek as $data_rek) {
				
				$anggaran = $data_rek->anggaran;

				//read spj rekening
				read_spj_rekening($kodesuk, $data->kodekeg, $data_rek->kodero, $tglawal, $tglakhir, $lslalu, $lsini, $lstotal, $gulalu, $guini, $gutotal, $tulalu, $tuini, $tutotal);
				
				//sub total
				$jumlahspj = $lstotal + $gutotal + $tutotal;
				$sisa = $anggaran - $jumlahspj;
				
				//Render Rekening
				$uraian = str_replace('Belanja ','',$data_rek->uraian);
				$rows[]=array(
					array('data' => $data_rek->kodero, 'width' => '40px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => $uraian, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($data_rek->anggaran), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					
					array('data' => apbd_fn($lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					
					array('data' => apbd_fn($gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					
					array('data' => apbd_fn($tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($sisa), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					
				);	
					
			}	//end of rekening
		}	
	}	//	end of kegiatan
	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;border-left:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => 'JUMLAH BELANJA', 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),

			array('data' => apbd_fn($total_anggaran), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_sisa), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
	);
	
	//FOOTER PENGELUARAN
	//1. PAJAK					//Footer Pengeluaran Pajak
	//I. Read Data
	//1. LS GAJI
	
	$lslalu_pph21  = 0; $lsini_pph21 = 0; 
	$lslalu_pph22  = 0; $lsini_pph22 = 0; 
	$lslalu_pph23  = 0; $lsini_pph23 = 0; 
	$lslalu_pph4  = 0; $lsini_pph4 = 0; 
	$lslalu_ppn  = 0; $lsini_ppn = 0; 
	$lslalu_pd  = 0; $lsini_pd = 0; 
	
	read_pajak_ls($kodeuk, $kodesuk, $tglawal, $tglakhir, $lslalu_pph21 ,$lsini_pph21,	$lslalu_pph22 ,$lsini_pph22,
		$lslalu_pph23, $lsini_pph23, $lslalu_pph4 ,$lsini_pph4, $lslalu_ppn, $lsini_ppn,
		$lslalu_pd, $lsini_pd);

	$lstotal_pph21 = $lslalu_pph21 + $lsini_pph21;
	$lstotal_pph22 = $lslalu_pph22 + $lsini_pph22;
	$lstotal_pph23 = $lslalu_pph23 + $lsini_pph23;
	$lstotal_pph4 = $lslalu_pph4 + $lsini_pph4;
	$lstotal_ppn = $lslalu_ppn + $lsini_ppn;
	$lstotal_pd = $lslalu_pd + $lsini_pd;
	
	//2. GU							//Footer Pengeluaran Pajak
	$gulalu_pph21 = 0; $guini_pph21 = 0;
	$gulalu_pph22 = 0; $guini_pph22 = 0;
	$gulalu_pph23 = 0; $guini_pph23 = 0;
	$gulalu_pph4 = 0; $guini_pph4 = 0;
	$gulalu_ppn = 0; $guini_ppn = 0;
	$gulalu_pd = 0; $guini_pd = 0;

	read_pajak_gu($kodeuk, $kodesuk, $tglawal, $tglakhir, $gulalu_pph21 ,$guini_pph21,	$gulalu_pph22 ,$guini_pph22,
		$gulalu_pph23, $guini_pph23, $gulalu_pph4 ,$guini_pph4, $gulalu_ppn, $guini_ppn,
		$gulalu_pd, $guini_pd);
	
	$gutotal_pph21 = $gulalu_pph21 + $guini_pph21;
	$gutotal_pph22 = $gulalu_pph22 + $guini_pph22;
	$gutotal_pph23 = $gulalu_pph23 + $guini_pph23;
	$gutotal_pph4 = $gulalu_pph4 + $guini_pph4;
	$gutotal_ppn = $gulalu_ppn + $guini_ppn;
	$gutotal_pd = $gulalu_pd + $guini_pd;
	
	//3. TU							//Footer Pengeluaran Pajak
	$tulalu_pph21 = 0; $tuini_pph21 = 0; 
	$tulalu_pph22 = 0; $tuini_pph22 = 0; 
	$tulalu_pph23 = 0; $tuini_pph23 = 0; 
	$tulalu_pph4 = 0; $tuini_pph4 = 0; 
	$tulalu_ppn = 0; $tuini_ppn = 0; 
	$tulalu_pd = 0; $tuini_pd = 0;

	read_pajak_tu($kodeuk, $kodesuk, $tglawal, $tglakhir, $tulalu_pph21 ,$tuini_pph21,	$tulalu_pph22 ,$tuini_pph22,
		$tulalu_pph23, $tuini_pph23, $tulalu_pph4 ,$tuini_pph4, $tulalu_ppn, $tuini_ppn,
		$tulalu_pd, $tuini_pd);

	$tutotal_pph21 = $tulalu_pph21 + $tuini_pph21;
	$tutotal_pph22 = $tulalu_pph22 + $tuini_pph22;
	$tutotal_pph23 = $tulalu_pph23 + $tuini_pph23;
	$tutotal_pph4 = $tulalu_pph4 + $tuini_pph4;
	$tutotal_ppn = $tulalu_ppn + $tuini_ppn;
	$tutotal_pd = $tulalu_pd + $tuini_pd;

	//TOTAL PAJAK
	$pajak_ls_lalu = $lslalu_pph21+$lslalu_pph22+$lslalu_pph23+$lslalu_pph4+$lslalu_ppn+$lslalu_pd; 
	$pajak_ls_ini = $lsini_pph21+$lsini_pph22+$lsini_pph23+$lsini_pph4+$lsini_ppn+$lsini_pd;
	$pajak_ls_total = $pajak_ls_ini+$pajak_ls_lalu;

	$pajak_gu_lalu = $gulalu_pph21+$gulalu_pph22+$gulalu_pph23+$gulalu_pph4+$gulalu_ppn+$gulalu_pd;
	$pajak_gu_ini = $guini_pph21+$guini_pph22+$guini_pph23+$guini_pph4+$guini_ppn+$guini_pd;
	$pajak_gu_total = $pajak_gu_ini+$pajak_gu_lalu;

	$pajak_tu_lalu = $tulalu_pph21+$tulalu_pph22+$tulalu_pph23+$tulalu_pph4+$tulalu_ppn+$tulalu_pd; 
	$pajak_tu_ini = $tuini_pph21+$tuini_pph22+$tuini_pph23+$tuini_pph4+$tuini_ppn+$tuini_pd;
	$pajak_tu_total = $pajak_tu_ini+$pajak_tu_lalu;
	
	//Read Pengembalian
	$retur_gu_lalu = 0; $retur_tu_lalu = 0; 
	$retur_gu_ini = 0; $retur_tu_ini = 0; 
	read_pengembalian($kodeuk, $kodesuk, $tglawal, $tglakhir, $retur_gu_lalu, $retur_gu_ini, $retur_tu_lalu, $retur_tu_ini);
	$retur_gu_total = $retur_gu_lalu + $retur_gu_ini;
	$retur_tu_total = $retur_tu_lalu + $retur_tu_ini;
	
	//TOTAL PENGELUARAN
	$total_keluar_ls_lalu = $total_lslalu + $pajak_ls_lalu;
	$total_keluar_ls_ini = $total_lsini + $pajak_ls_ini;
	$total_keluar_ls_total = $total_lstotal + $pajak_ls_total;

	$total_keluar_gu_lalu = $total_gulalu + $pajak_gu_lalu + $retur_gu_lalu;
	$total_keluar_gu_ini = $total_guini + $pajak_gu_ini + $retur_gu_ini;
	$total_keluar_gu_total = $total_gutotal + $pajak_gu_total + $retur_gu_total;

	$total_keluar_tu_lalu = $total_tulalu + $pajak_tu_lalu + $retur_tu_lalu;
	$total_keluar_tu_ini = $total_tuini + $pajak_tu_ini + $retur_tu_ini;
	$total_keluar_tu_total = $total_tutotal + $pajak_tu_total  + $retur_tu_total;
	
	//II. RENDER PAJAK
	//a. Total Pajak				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'POTONGAN PAJAK', 'width' => '130px','align'=>'left','style'=>'border-bottom:1px solid black;font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($pajak_ls_total+$pajak_gu_total+$pajak_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
	);		
	//b. PPN						//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_ppn),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_ppn+$gutotal_ppn+$tutotal_ppn), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//c. PPh 21			//Footer Pengeluaran Pajak	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 21', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph21),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph21+$gutotal_pph21+$tutotal_pph21), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);
	//d. PPh 22				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 22', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph22),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph22+$gutotal_pph22+$tutotal_pph22), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//e. PPh 23			//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 23', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph23),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph23+$gutotal_pph23+$tutotal_pph23), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//f. PPh ps 4		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh Final', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph4),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph4+$gutotal_pph4+$tutotal_pph4), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//g. Pajak Daerah		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- Pajak Daerah', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pd),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pd+$gutotal_pd+$tutotal_pd), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);			
	
	//3. PENGEMBALIAN
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'PENGEMBALIAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0','width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($retur_gu_lalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($retur_tu_lalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($retur_gu_total + $retur_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	
	//4. TOTAL PENGELUARAN
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => 'JUMLAH PENGELUARAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_keluar_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_keluar_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_keluar_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($total_keluar_ls_total+$total_keluar_gu_total+$total_keluar_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
	);			
	
	// PENERIMAAN
	$rows[]=array(
		array('data' => '', 'width' => '155px','align'=>'left','style'=>'font-size:70%;border:none;'),
		array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '700px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => 'PENERIMAAN', 'width' => '155px','align'=>'left','style'=>'font-size:70%;border:none;'),
		array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '700px','align'=>'left','style'=>'border:none;'),
	);
	
	$output.=createT($header, $rows);
	
	$rows=null;
	$header=null;
	$header[]=array(
		array('data' => 'Kode', 'rowspan'=>2, 'width' => '40px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;font-size:70%;'),
		array('data' => '', 'rowspan'=>2, 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji', 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-UP/GU', 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-TU', 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Jumlah SPJ s.d. Bulan ini', 'rowspan'=>2, 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Keterangan', 'rowspan'=>2, 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
	);
	
	
	//Content
	
	$header[]=array(
		array('data' => 'sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		
		array('data' => 'sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		
		array('data' => 'sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		
	);
	
	//SP2D
	//a. READ DATA
	
	//init
	$lslalu = 0; $lsini = 0; $lstotal = 0;
	$gulalu = 0; $guini = 0; $gutotal = 0;
	$tulalu = 0; $tuini = 0; $tutotal = 0;

	read_sp2d_penerimaan($kodeuk, $kodesuk, $tglawal, $tglakhir, $lslalu, $lsini, $lstotal, $gulalu, $guini, $gutotal, 
		$tulalu, $tuini, $tutotal);	
	
	//sub total
	$jumlahspj = $lstotal + $gutotal + $tutotal;
	
	//b. Render Data Penerimaan
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'SP2D/PANJAR', 'width' => '130px','align'=>'left','style'=>'border-bottom:1px solid black;font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
	);		

	
	//PAJAK		=> SAMA
		
	//TOTAL PENERIMAAN
	$total_masuk_ls_lalu = $lslalu + $pajak_ls_lalu; 
	$total_masuk_ls_ini = $lsini + $pajak_ls_ini; 
	$total_masuk_ls_total = $lstotal + $pajak_ls_total;

	$total_masuk_gu_lalu = $gulalu + $pajak_gu_lalu; 
	$total_masuk_gu_ini = $guini + $pajak_gu_ini; 
	$total_masuk_gu_total = $gutotal + $pajak_gu_total;

	$total_masuk_tu_lalu = $tulalu + $pajak_tu_lalu; 
	$total_masuk_tu_ini = $tuini + $pajak_tu_ini; 
	$total_masuk_tu_total = $tutotal + $pajak_tu_total;
	
	//II. RENDER PAJAK
	//a. Total Pajak				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'POTONGAN PAJAK', 'width' => '130px','align'=>'left','style'=>'border-bottom:1px solid black;font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($pajak_ls_total+$pajak_gu_total+$pajak_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
	);		
	//b. PPN						//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_ppn),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_ppn+$gutotal_ppn+$tutotal_ppn), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//c. PPh 21			//Footer Pengeluaran Pajak	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 21', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph21),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph21+$gutotal_pph21+$tutotal_pph21), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);
	//d. PPh 22				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 22', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph22),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph22+$gutotal_pph22+$tutotal_pph22), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//e. PPh 23			//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 23', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph23),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph23+$gutotal_pph23+$tutotal_pph23), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//f. PPh ps 4		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh Final', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph4),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph4+$gutotal_pph4+$tutotal_pph4), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//g. Pajak Daerah		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- Pajak Daerah', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pd),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pd+$gutotal_pd+$tutotal_pd), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);			
	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'PENGEMBALIAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0','width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => 'JUMLAH PENERIMAAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($total_masuk_ls_total+$total_masuk_gu_total+$total_masuk_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
	);			
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => 'SALDO PANJAR', 'width' => '130px','align'=>'left','style'=>'font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),
			array('data' => '', 'width' => '70px','align'=>'left','style'=>'border-right:1px solid black;font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),
			
			array('data' => apbd_fn($total_masuk_ls_lalu-$total_keluar_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_ini-$total_keluar_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_total-$total_keluar_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_gu_lalu-$total_keluar_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_ini-$total_keluar_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_total-$total_keluar_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_tu_lalu-$total_keluar_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_ini-$total_keluar_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_total-$total_keluar_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),

			array('data' => apbd_fn(($total_masuk_ls_total+$total_masuk_gu_total+$total_masuk_tu_total) - ($total_keluar_ls_total+$total_keluar_gu_total+$total_keluar_tu_total)), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),
			
	);
	$output.=createT($header, $rows);
	$header=null;
	$rows=null;
	//ttd
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);

	$rows[] = array(
					array('data' => 'Mengesahkan,','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => 'Jepara, ' . $tglcetak , 'width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => 'BENDAHARA PENGELUARAN','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => 'BENDAHARA PENGELUARAN PEMBANTU','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => $bendaharanama,'width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;font-weight:bold;text-decoration:underline'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => $bpnama,'width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;font-weight:bold;text-decoration:underline'),
	);

	$rows[] = array(
					array('data' => 'NIP. ' . $bendaharanip,'width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => 'NIP. ' . $bpnip,'width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => 'Mengetahui,','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => 'KEPALA BIDANG/BAGIAN','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	); 
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => $kabidnama,'width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;font-weight:bold;text-decoration:underline'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);
	
	$rows[] = array(
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => 'NIP. ' . $kabidnip,'width' => '295px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
					array('data' => '','width' => '290px', 'align'=>'center','style'=>'border:none;font-size:70%;'),
	);	
	$output .= theme('table', array('header' => $header, 'rows' => $rows ));
	//$output.=createT($header, $rows);
	//$output .=theme_box('', apbd_theme_table($header, $rows));
	//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
		return $output;
}

function getLaporanbk8_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak){
	$bulan = 1;

	set_time_limit(0);
	//ini_set('memory_limit','940M');
	
	$styleheader='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;';
	$style='border-right:1px solid black;';

	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'bendaharanama', 'bendaharanip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	//dpq($query);			
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

	}	


	//BIDANG
	$query = db_select('subunitkerja', 'u');
	$query->fields('u', array('namasuk', 'bpnama', 'bpnip', 'kabidnama', 'kabidnip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	$query->condition('u.kodesuk', $kodesuk, '=');
	//dpq($query);			
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {
		$namasuk = $data->namasuk;
		
		$bpnama = $data->bpnama;
		$bpnip = $data->bpnip;
		$kabidnama = $data->kabidnama;
		$kabidnip = $data->kabidnip;

	}	
	if (arg(5) == 'xls'){
	$header[]=array(
		array('data' => 'Kode',  'width' => '40px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
		array('data' => 'Uraian',  'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Jumlah Anggaran',  'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji s.d Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-UP/GU Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-TU Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Jumlah SPJ (LS+UP/GU/TU)', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Sisa Pagu Anggaran', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
	);
	
	}else{
	$header[]=array(
		array('data' => 'Kode',  'width' => '40px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
		array('data' => 'Uraian',  'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Jumlah Anggaran',  'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji s.d Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-UP/GU Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-UP/GU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-TU Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'SPJ-TU sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;'),
		array('data' => 'Jumlah SPJ (LS+UP/GU/TU)', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Sisa Pagu Anggaran', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
	);
	}
	//init total
	$total_anggaran = 0;
	$total_lslalu =0; $total_lsini = 0; $total_lstotal = 0;
	$total_gulalu =0; $total_guini = 0; $total_gutotal = 0;
	$total_tulalu =0; $total_tuini = 0; $total_tutotal = 0;

	$total_jumlahspj = 0;
	$total_sisa = 0;
	
	//var spj
	$lslalu =0; $lsini = 0; $lstotal = 0;
	$gulalu =0; $guini = 0; $gutotal = 0;
	$tulalu =0; $tuini = 0; $tutotal = 0;
	
	//kegiatan
	$query = db_select('kegiatanskpd', 'k');
	$query->fields('k', array('kodekeg', 'kodepro', 'kegiatan', 'anggaran'));
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	if ($kodeuk=='81') $query->condition('k.isppkd', 0, '=');
	$query->orderBy('k.kodepro', 'ASC');
	$query->orderBy('k.kodekeg', 'ASC');
	//dpq($query);	
		
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {

		$anggaran = $data->anggaran;

		//read spj kegiatan
		read_spj_kegiatan($kodesuk, $data->kodekeg, $tglawal, $tglakhir, $lslalu, $lsini, $lstotal, $gulalu, $guini, $gutotal, $tulalu, $tuini, $tutotal);
		
		//SUB TOTAL			KOLOM JUMLAH SPJ
		$jumlahspj = $lstotal + $gutotal + $tutotal;
		$sisa = $anggaran - $jumlahspj;
		
		//TOTAL				PENGELUARAN
		$total_anggaran += $anggaran;
		$total_lslalu += $lslalu; $total_lsini += $lsini; $total_lstotal += $lstotal;
		$total_gulalu += $gulalu; $total_guini += $guini; $total_gutotal += $gutotal;
		$total_tulalu += $tulalu; $total_tuini += $tuini; $total_tutotal += $tutotal;

		$total_jumlahspj += $jumlahspj;
		$total_sisa += $sisa;
		
		//Render Kegiatan	
		$rows[]=array(
			array('data' => $data->kodepro . '.' . substr($data->kodekeg, -3), 'width' => '40px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => $data->kegiatan, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;font-weight:bold;'),
			array('data' => apbd_fn($data->anggaran), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),

			array('data' => apbd_fn($lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			array('data' => apbd_fn($sisa), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-top:0.5px solid black;font-size:70%;'),
			
		);	
		
		//Hanya yang ada SPJ yang ditampilkan
		if ($jumlahspj>0) {
			//Rekening
			$query = db_select('anggperkeg', 'a');
			$query->innerJoin('rincianobyek', 'ro', 'a.kodero=ro.kodero');
			$query->fields('a', array('anggaran'));
			$query->fields('ro', array('kodero', 'uraian'));
			$query->condition('a.kodekeg', $data->kodekeg, '=');
			$query->orderBy('ro.kodero', 'ASC');
			//dpq($query);	
				
			# execute the query	
			$res_rek = $query->execute();
			foreach ($res_rek as $data_rek) {
				
				$anggaran = $data_rek->anggaran;

				//read spj rekening
				read_spj_rekening($kodesuk, $data->kodekeg, $data_rek->kodero, $tglawal, $tglakhir, $lslalu, $lsini, $lstotal, $gulalu, $guini, $gutotal, $tulalu, $tuini, $tutotal);
				
				//sub total
				$jumlahspj = $lstotal + $gutotal + $tutotal;
				$sisa = $anggaran - $jumlahspj;
				
				//Render Rekening
				$uraian = str_replace('Belanja ','',$data_rek->uraian);
				$rows[]=array(
					array('data' => $data_rek->kodero, 'width' => '40px','align'=>'left','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => $uraian, 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($data_rek->anggaran), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					
					array('data' => apbd_fn($lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					
					array('data' => apbd_fn($gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					
					array('data' => apbd_fn($tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					array('data' => apbd_fn($sisa), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
					
				);	
					
			}	//end of rekening
		}	
	}	//	end of kegiatan
	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;border-left:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => 'JUMLAH BELANJA', 'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),

			array('data' => apbd_fn($total_anggaran), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_sisa), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
	);
	
	//FOOTER PENGELUARAN
	//1. PAJAK					//Footer Pengeluaran Pajak
	//I. Read Data
	//1. LS GAJI
	
	$lslalu_pph21  = 0; $lsini_pph21 = 0; 
	$lslalu_pph22  = 0; $lsini_pph22 = 0; 
	$lslalu_pph23  = 0; $lsini_pph23 = 0; 
	$lslalu_pph4  = 0; $lsini_pph4 = 0; 
	$lslalu_ppn  = 0; $lsini_ppn = 0; 
	$lslalu_pd  = 0; $lsini_pd = 0; 
	
	read_pajak_ls($kodeuk, $kodesuk, $tglawal, $tglakhir, $lslalu_pph21 ,$lsini_pph21,	$lslalu_pph22 ,$lsini_pph22,
		$lslalu_pph23, $lsini_pph23, $lslalu_pph4 ,$lsini_pph4, $lslalu_ppn, $lsini_ppn,
		$lslalu_pd, $lsini_pd);

	$lstotal_pph21 = $lslalu_pph21 + $lsini_pph21;
	$lstotal_pph22 = $lslalu_pph22 + $lsini_pph22;
	$lstotal_pph23 = $lslalu_pph23 + $lsini_pph23;
	$lstotal_pph4 = $lslalu_pph4 + $lsini_pph4;
	$lstotal_ppn = $lslalu_ppn + $lsini_ppn;
	$lstotal_pd = $lslalu_pd + $lsini_pd;
	
	//2. GU							//Footer Pengeluaran Pajak
	$gulalu_pph21 = 0; $guini_pph21 = 0;
	$gulalu_pph22 = 0; $guini_pph22 = 0;
	$gulalu_pph23 = 0; $guini_pph23 = 0;
	$gulalu_pph4 = 0; $guini_pph4 = 0;
	$gulalu_ppn = 0; $guini_ppn = 0;
	$gulalu_pd = 0; $guini_pd = 0;

	read_pajak_gu($kodeuk, $kodesuk, $tglawal, $tglakhir, $gulalu_pph21 ,$guini_pph21,	$gulalu_pph22 ,$guini_pph22,
		$gulalu_pph23, $guini_pph23, $gulalu_pph4 ,$guini_pph4, $gulalu_ppn, $guini_ppn,
		$gulalu_pd, $guini_pd);
	
	$gutotal_pph21 = $gulalu_pph21 + $guini_pph21;
	$gutotal_pph22 = $gulalu_pph22 + $guini_pph22;
	$gutotal_pph23 = $gulalu_pph23 + $guini_pph23;
	$gutotal_pph4 = $gulalu_pph4 + $guini_pph4;
	$gutotal_ppn = $gulalu_ppn + $guini_ppn;
	$gutotal_pd = $gulalu_pd + $guini_pd;
	
	//3. TU							//Footer Pengeluaran Pajak
	$tulalu_pph21 = 0; $tuini_pph21 = 0; 
	$tulalu_pph22 = 0; $tuini_pph22 = 0; 
	$tulalu_pph23 = 0; $tuini_pph23 = 0; 
	$tulalu_pph4 = 0; $tuini_pph4 = 0; 
	$tulalu_ppn = 0; $tuini_ppn = 0; 
	$tulalu_pd = 0; $tuini_pd = 0;

	read_pajak_tu($kodeuk, $kodesuk, $tglawal, $tglakhir, $tulalu_pph21 ,$tuini_pph21,	$tulalu_pph22 ,$tuini_pph22,
		$tulalu_pph23, $tuini_pph23, $tulalu_pph4 ,$tuini_pph4, $tulalu_ppn, $tuini_ppn,
		$tulalu_pd, $tuini_pd);

	$tutotal_pph21 = $tulalu_pph21 + $tuini_pph21;
	$tutotal_pph22 = $tulalu_pph22 + $tuini_pph22;
	$tutotal_pph23 = $tulalu_pph23 + $tuini_pph23;
	$tutotal_pph4 = $tulalu_pph4 + $tuini_pph4;
	$tutotal_ppn = $tulalu_ppn + $tuini_ppn;
	$tutotal_pd = $tulalu_pd + $tuini_pd;

	//TOTAL PAJAK
	$pajak_ls_lalu = $lslalu_pph21+$lslalu_pph22+$lslalu_pph23+$lslalu_pph4+$lslalu_ppn+$lslalu_pd; 
	$pajak_ls_ini = $lsini_pph21+$lsini_pph22+$lsini_pph23+$lsini_pph4+$lsini_ppn+$lsini_pd;
	$pajak_ls_total = $pajak_ls_ini+$pajak_ls_lalu;

	$pajak_gu_lalu = $gulalu_pph21+$gulalu_pph22+$gulalu_pph23+$gulalu_pph4+$gulalu_ppn+$gulalu_pd;
	$pajak_gu_ini = $guini_pph21+$guini_pph22+$guini_pph23+$guini_pph4+$guini_ppn+$guini_pd;
	$pajak_gu_total = $pajak_gu_ini+$pajak_gu_lalu;

	$pajak_tu_lalu = $tulalu_pph21+$tulalu_pph22+$tulalu_pph23+$tulalu_pph4+$tulalu_ppn+$tulalu_pd; 
	$pajak_tu_ini = $tuini_pph21+$tuini_pph22+$tuini_pph23+$tuini_pph4+$tuini_ppn+$tuini_pd;
	$pajak_tu_total = $pajak_tu_ini+$pajak_tu_lalu;
	
	//Read Pengembalian
	$retur_gu_lalu = 0; $retur_tu_lalu = 0; 
	$retur_gu_ini = 0; $retur_tu_ini = 0; 
	read_pengembalian($kodeuk, $kodesuk, $tglawal, $tglakhir, $retur_gu_lalu, $retur_gu_ini, $retur_tu_lalu, $retur_tu_ini);
	$retur_gu_total = $retur_gu_lalu + $retur_gu_ini;
	$retur_tu_total = $retur_tu_lalu + $retur_tu_ini;
	
	//TOTAL PENGELUARAN
	$total_keluar_ls_lalu = $total_lslalu + $pajak_ls_lalu;
	$total_keluar_ls_ini = $total_lsini + $pajak_ls_ini;
	$total_keluar_ls_total = $total_lstotal + $pajak_ls_total;

	$total_keluar_gu_lalu = $total_gulalu + $pajak_gu_lalu + $retur_gu_lalu;
	$total_keluar_gu_ini = $total_guini + $pajak_gu_ini + $retur_gu_ini;
	$total_keluar_gu_total = $total_gutotal + $pajak_gu_total + $retur_gu_total;

	$total_keluar_tu_lalu = $total_tulalu + $pajak_tu_lalu + $retur_tu_lalu;
	$total_keluar_tu_ini = $total_tuini + $pajak_tu_ini + $retur_tu_ini;
	$total_keluar_tu_total = $total_tutotal + $pajak_tu_total  + $retur_tu_total;
	
	//II. RENDER PAJAK
	//a. Total Pajak				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'POTONGAN PAJAK', 'width' => '130px','align'=>'left','style'=>'border-bottom:1px solid black;font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($pajak_ls_total+$pajak_gu_total+$pajak_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
	);		
	//b. PPN						//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_ppn),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_ppn+$gutotal_ppn+$tutotal_ppn), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//c. PPh 21			//Footer Pengeluaran Pajak	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 21', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph21),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph21+$gutotal_pph21+$tutotal_pph21), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);
	//d. PPh 22				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 22', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph22),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph22+$gutotal_pph22+$tutotal_pph22), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//e. PPh 23			//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 23', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph23),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph23+$gutotal_pph23+$tutotal_pph23), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//f. PPh ps 4		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh Final', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph4),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph4+$gutotal_pph4+$tutotal_pph4), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//g. Pajak Daerah		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- Pajak Daerah', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pd),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pd+$gutotal_pd+$tutotal_pd), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);			
	
	//3. PENGEMBALIAN
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'PENGEMBALIAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0','width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($retur_gu_lalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($retur_tu_lalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($retur_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($retur_gu_total + $retur_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	
	//4. TOTAL PENGELUARAN
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => 'JUMLAH PENGELUARAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_keluar_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_keluar_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_keluar_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_keluar_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($total_keluar_ls_total+$total_keluar_gu_total+$total_keluar_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
	);			
	
	// PENERIMAAN
	$rows[]=array(
		array('data' => '', 'width' => '155px','align'=>'left','style'=>'font-size:70%;border:none;'),
		array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '700px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => 'PENERIMAAN', 'width' => '155px','align'=>'left','style'=>'font-size:70%;border:none;'),
		array('data' => '', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '700px','align'=>'left','style'=>'border:none;'),
	);
	
	$output.=createT($header, $rows);
	
	$rows=null;
	$header=null;
	$header[]=array(
		array('data' => 'Kode', 'rowspan'=>2, 'width' => '40px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;font-size:70%;'),
		array('data' => '', 'rowspan'=>2, 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-LS Gaji & Non Gaji', 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-UP/GU', 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'SPJ-TU', 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Jumlah SPJ s.d. Bulan ini', 'rowspan'=>2, 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
		array('data' => 'Keterangan', 'rowspan'=>2, 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:70%;'),
	);
	
	
	//Content
	
	$header[]=array(
		array('data' => 'sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		
		array('data' => 'sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		
		array('data' => 'sd Bln Lalu', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Bulan Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'sd Bln Ini', 'width' => '55px','align'=>'center','style'=>'font-size:70%;border-right:1px solid black;border-bottom:1px solid black;'),
		
	);
	
	//SP2D
	//a. READ DATA
	
	//init
	$lslalu = 0; $lsini = 0; $lstotal = 0;
	$gulalu = 0; $guini = 0; $gutotal = 0;
	$tulalu = 0; $tuini = 0; $tutotal = 0;

	read_sp2d_penerimaan($kodeuk, $kodesuk, $tglawal, $tglakhir, $lslalu, $lsini, $lstotal, $gulalu, $guini, $gutotal, 
		$tulalu, $tuini, $tutotal);	
	
	//sub total
	$jumlahspj = $lstotal + $gutotal + $tutotal;
	
	//b. Render Data Penerimaan
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'SP2D/PANJAR', 'width' => '130px','align'=>'left','style'=>'border-bottom:1px solid black;font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($jumlahspj), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
	);		

	
	//PAJAK		=> SAMA
		
	//TOTAL PENERIMAAN
	$total_masuk_ls_lalu = $lslalu + $pajak_ls_lalu; 
	$total_masuk_ls_ini = $lsini + $pajak_ls_ini; 
	$total_masuk_ls_total = $lstotal + $pajak_ls_total;

	$total_masuk_gu_lalu = $gulalu + $pajak_gu_lalu; 
	$total_masuk_gu_ini = $guini + $pajak_gu_ini; 
	$total_masuk_gu_total = $gutotal + $pajak_gu_total;

	$total_masuk_tu_lalu = $tulalu + $pajak_tu_lalu; 
	$total_masuk_tu_ini = $tuini + $pajak_tu_ini; 
	$total_masuk_tu_total = $tutotal + $pajak_tu_total;
	
	//II. RENDER PAJAK
	//a. Total Pajak				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'POTONGAN PAJAK', 'width' => '130px','align'=>'left','style'=>'border-bottom:1px solid black;font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($pajak_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($pajak_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($pajak_ls_total+$pajak_gu_total+$pajak_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:70%;'),
			
	);		
	//b. PPN						//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_ppn),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_ppn), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_ppn+$gutotal_ppn+$tutotal_ppn), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//c. PPh 21			//Footer Pengeluaran Pajak	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 21', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph21),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph21), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph21+$gutotal_pph21+$tutotal_pph21), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);
	//d. PPh 22				//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 22', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph22),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph22), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph22+$gutotal_pph22+$tutotal_pph22), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//e. PPh 23			//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh 23', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph23),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph23), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph23+$gutotal_pph23+$tutotal_pph23), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);	
	//f. PPh ps 4		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- PPh Final', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pph4),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pph4), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pph4+$gutotal_pph4+$tutotal_pph4), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	//g. Pajak Daerah		//Footer Pengeluaran Pajak
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => '- Pajak Daerah', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($lslalu_pd),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lsini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($lstotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($gulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($guini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($gutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($tulalu_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tuini_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($tutotal_pd), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($lstotal_pd+$gutotal_pd+$tutotal_pd), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);			
	
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-left:1px solid black;border-right:1px solid black;font-size:70%;'),
			array('data' => 'PENGEMBALIAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0','width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => '0', 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
			array('data' => '0', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;'),
			
	);		
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => 'JUMLAH PENERIMAAN', 'width' => '130px','align'=>'left','style'=>'font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),

			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),

			array('data' => apbd_fn($total_masuk_ls_total+$total_masuk_gu_total+$total_masuk_tu_total), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
	);			
	$rows[]=array(
			array('data' => '', 'width' => '40px','align'=>'right','style'=>'border-bottom:1px solid black;border-top:1px solid black;border-left:1px solid black;font-size:70%;'),
			array('data' => 'SALDO PANJAR', 'width' => '130px','align'=>'left','style'=>'font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),
			array('data' => '', 'width' => '70px','align'=>'left','style'=>'border-right:1px solid black;font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),
			
			array('data' => apbd_fn($total_masuk_ls_lalu-$total_keluar_ls_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_ini-$total_keluar_ls_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_ls_total-$total_keluar_ls_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_gu_lalu-$total_keluar_gu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_ini-$total_keluar_gu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_gu_total-$total_keluar_gu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			
			array('data' => apbd_fn($total_masuk_tu_lalu-$total_keluar_tu_lalu),'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_ini-$total_keluar_tu_ini), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => apbd_fn($total_masuk_tu_total-$total_keluar_tu_total), 'width' => '55px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),

			array('data' => apbd_fn(($total_masuk_ls_total+$total_masuk_gu_total+$total_masuk_tu_total) - ($total_keluar_ls_total+$total_keluar_gu_total+$total_keluar_tu_total)), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:70%;'),
			array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:70%;border-bottom:1px solid black;border-top:1px solid black;'),
			
	);
	$output.=createT($header, $rows);
		return $output;
}



function fooX(&$abc) {
	$abc = $abc * 10;
}
 
function read_spj_kegiatan($kodesuk, $kodekeg, $tglawal, $tglakhir, &$lslalu, &$lsini, &$lstotal, &$gulalu, &$guini, &$gutotal, &$tulalu, &$tuini, &$tutotal) {

	$kodeuk = substr($kodekeg, 4,2);
	//init
	$lslalu =0; $lsini = 0; $lstotal = 0;
	$gulalu =0; $guini = 0; $gutotal = 0;
	$tulalu =0; $tuini = 0; $tutotal = 0;
	
	//LALU
	//1. LS
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lslalu = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'ls', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lslalu -= $data_spj->total;
	}
	
	//2. GU
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'gu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$gulalu = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'gu', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$gulalu -= $data_spj->total;
	}
	

	//3. TU
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'tu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tulalu = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'tu', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tulalu -= $data_spj->total;
	}	
	
	//INI
	//1. LS
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lsini = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');

	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'ls', '=');	
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lsini -= $data_spj->total;
	}	
	$lstotal = $lslalu + $lsini;
	
	
	//2. GU
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'gu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$guini = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');

	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'gu', '=');	
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$guini -= $data_spj->total;
	}	
	$gutotal = $gulalu + $guini;

	//3. TU
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'tu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tuini = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');

	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'tu', '=');	
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tuini -= $data_spj->total;
	}		
	$tutotal = $tulalu + $tuini;	
}

function read_spj_rekening($kodesuk, $kodekeg, $kodero, $tglawal, $tglakhir, &$lslalu, &$lsini, &$lstotal, &$gulalu, &$guini, &$gutotal, &$tulalu, &$tuini, &$tutotal) {
	
	$kodeuk = substr($kodekeg, 4,2);
	
	//init
	$lslalu =0; $lsini = 0; $lstotal = 0;
	$gulalu =0; $guini = 0; $gutotal = 0;
	$tulalu =0; $tuini = 0; $tutotal = 0;
	
	//LALU
	//1. LS GAHI			//Rekening
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lslalu = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'ls', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lslalu -= $data_spj->total;
	}
	
	
	//2. GU				//Rekening
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'gu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$gulalu = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'gu', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$gulalu -= $data_spj->total;
	}	

	//3. TU			//Rekening
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');

	$query->condition('b.jenis', 'tu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tulalu = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'tu', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tulalu -= $data_spj->total;
	}		

	
	//INI
	//1. LS GAHI			//Rekening
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lsini = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'ls', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lsini -= $data_spj->total;
	}
	$lstotal = $lslalu + $lsini;
	
	
	//2. GU				//Rekening
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'gu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$guini = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'gu', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$guini -= $data_spj->total;
	}	
	$gutotal = $gulalu + $guini;

	//3. TU			//Rekening
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');

	$query->condition('b.jenis', 'tu-spj', '=');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tuini = $data_spj->total;
	}
	//ret
	$query = db_select('bendaharaitem' . $kodeuk, 'bi');
	$query->innerJoin('bendahara' . $kodeuk, 'b', 'bi.bendid=b.bendid');
	$query->fields('bi', array('kodero'));
	$query->addExpression('SUM(bi.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	$query->condition('b.jenispanjar', 'tu', '=');
	
	$query->condition('b.kodekeg', $kodekeg, '=');
	//$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('bi.kodero', $kodero, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tuini -= $data_spj->total;
	}	
	$tutotal = $tulalu + $tuini;	

}


function read_pajak_ls($kodeuk, $kodesuk, $tglawal, $tglakhir, &$lslalu_pph21 ,&$lsini_pph21,	&$lslalu_pph22 ,&$lsini_pph22,
	&$lslalu_pph23, &$lsini_pph23, &$lslalu_pph4 ,&$lsini_pph4, &$lslalu_ppn, &$lsini_ppn,
	&$lslalu_pd, &$lsini_pd) {

	$lslalu_pph21  = 0; $lsini_pph21 = 0; 
	$lslalu_pph22  = 0; $lsini_pph22 = 0; 
	$lslalu_pph23  = 0; $lsini_pph23 = 0; 
	$lslalu_pph4  = 0; $lsini_pph4 = 0; 
	$lslalu_ppn  = 0; $lsini_ppn = 0; 
	$lslalu_pd  = 0; $lsini_pd = 0; 

	//LALU
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bp', array('kodepajak'));
	
	$query->addExpression('SUM(bp.jumlah)', 'total');
 
 	$query->condition('b.tanggal', $tglawal, '<');

	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('bp.kodepajak');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->kodepajak=='01')
			$lslalu_pph21 = $data_spj->total; 
		else if ($data_spj->kodepajak=='02')
			$lslalu_pph22 = $data_spj->total;	
		else if ($data_spj->kodepajak=='03')	
			$lslalu_pph23 = $data_spj->total;
		else if ($data_spj->kodepajak=='04')
			$lslalu_pph4 = $data_spj->total;
		else if ($data_spj->kodepajak=='09')
			$lslalu_ppn = $data_spj->total;
		else 
			$lslalu_pd = $data_spj->total;
			
	}	
	
	//INI
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bp', array('kodepajak'));
	
	$query->addExpression('SUM(bp.jumlah)', 'total');
 
 	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');

	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('bp.kodepajak');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->kodepajak=='01')
			$lsini_pph21 = $data_spj->total; 
		else if ($data_spj->kodepajak=='02')
			$lsini_pph22 = $data_spj->total;	
		else if ($data_spj->kodepajak=='03')	
			$lsini_pph23 = $data_spj->total;
		else if ($data_spj->kodepajak=='04')
			$lsini_pph4 = $data_spj->total;
		else if ($data_spj->kodepajak=='09')
			$lsini_ppn = $data_spj->total;
		else 
			$lsini_pd = $data_spj->total;
			
	}	
}

function read_pajak_gu($kodeuk, $kodesuk, $tglawal, $tglakhir, &$gulalu_pph21 ,&$guini_pph21,	&$gulalu_pph22 ,&$guini_pph22,
	&$gulalu_pph23, &$guini_pph23, &$gulalu_pph4 ,&$guini_pph4, &$gulalu_ppn, &$guini_ppn,
	&$gulalu_pd, &$guini_pd) {
	
	//INIT
	$gulalu_pph21 = 0; $guini_pph21 = 0;
	$gulalu_pph22 = 0; $guini_pph22 = 0;
	$gulalu_pph23 = 0; $guini_pph23 = 0;
	$gulalu_pph4 = 0; $guini_pph4 = 0;
	$gulalu_ppn = 0; $guini_ppn = 0;
	$gulalu_pd = 0; $guini_pd = 0;
	
	//LALU
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bp', array('kodepajak'));
	
	$query->addExpression('SUM(bp.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'gu-spj', '=');
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('bp.kodepajak');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->kodepajak=='01')
			$gulalu_pph21 = $data_spj->total;
		else if ($data_spj->kodepajak=='02')
			$gulalu_pph22 = $data_spj->total;	
		else if ($data_spj->kodepajak=='03')	
			$gulalu_pph23 = $data_spj->total;
		else if ($data_spj->kodepajak=='04')
			$gulalu_pph4 = $data_spj->total;
		else if ($data_spj->kodepajak=='09')
			$gulalu_ppn = $data_spj->total;
		else 
			$gulalu_pd = $data_spj->total;
			
	}
	
	//INI
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bp', array('kodepajak'));
	
	$query->addExpression('SUM(bp.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'gu-spj', '=');
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('bp.kodepajak');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->kodepajak=='01')
			$guini_pph21 = $data_spj->total;
		else if ($data_spj->kodepajak=='02')
			$guini_pph22 = $data_spj->total;	
		else if ($data_spj->kodepajak=='03')	
			$guini_pph23 = $data_spj->total;
		else if ($data_spj->kodepajak=='04')
			$guini_pph4 = $data_spj->total;
		else if ($data_spj->kodepajak=='09')
			$guini_ppn = $data_spj->total;
		else 
			$guini_pd = $data_spj->total;
			
	}
}

function read_pajak_tu($kodeuk, $kodesuk, $tglawal, $tglakhir, &$tulalu_pph21 ,&$tuini_pph21,	&$tulalu_pph22 ,&$tuini_pph22,
	&$tulalu_pph23, &$tuini_pph23, &$tulalu_pph4 ,&$tuini_pph4, &$tulalu_ppn, &$tuini_ppn,
	&$tulalu_pd, &$tuini_pd) {
	
	//init
	$tulalu_pph21 = 0; $tuini_pph21 = 0;
	$tulalu_pph22 = 0; $tuini_pph22 = 0;
	$tulalu_pph23 = 0; $tuini_pph23 = 0;
	$tulalu_pph4 = 0; $tuini_pph4 = 0;
	$tulalu_ppn = 0; $tuini_ppn = 0;
	$tulalu_pd = 0; $tuini_pd = 0;
	
	//LALU
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bp', array('kodepajak'));
	
	$query->addExpression('SUM(bp.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'tu-spj', '=');
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('bp.kodepajak');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->kodepajak=='01')
			$tulalu_pph21 = $data_spj->total;
		else if ($data_spj->kodepajak=='02')
			$tulalu_pph22 = $data_spj->total;	
		else if ($data_spj->kodepajak=='03')	
			$tulalu_pph23 = $data_spj->total;
		else if ($data_spj->kodepajak=='04')
			$tulalu_pph4 = $data_spj->total;
		else if ($data_spj->kodepajak=='09')
			$tulalu_ppn = $data_spj->total;
		else 
			$tulalu_pd = $data_spj->total;
			
	}		
	
	//INI
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('bp', array('kodepajak'));
	
	$query->addExpression('SUM(bp.jumlah)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'tu-spj', '=');
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('bp.kodepajak');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->kodepajak=='01')
			$tuini_pph21 = $data_spj->total;
		else if ($data_spj->kodepajak=='02')
			$tuini_pph22 = $data_spj->total;	
		else if ($data_spj->kodepajak=='03')	
			$tuini_pph23 = $data_spj->total;
		else if ($data_spj->kodepajak=='04')
			$tuini_pph4 = $data_spj->total;
		else if ($data_spj->kodepajak=='09')
			$tuini_ppn = $data_spj->total;
		else 
			$tuini_pd = $data_spj->total;
			
	}	
}

function read_sp2d_penerimaan($kodeuk, $kodesuk, $tglawal, $tglakhir, &$lslalu, &$lsini, &$lstotal, &$gulalu, &$guini, &$gutotal, 
		&$tulalu, &$tuini, &$tutotal) {

	//init
	$lslalu =0; $lsini = 0; $lstotal = 0;
	$gulalu =0; $guini = 0; $gutotal = 0;
	$tulalu =0; $tuini = 0; $tutotal = 0;
	
	//LALU
	//1. LS
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->addExpression('SUM(b.total)', 'total');
	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('b.tanggal', $tglawal, '<');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lslalu = $data_spj->total;
	}
	
	
	//2. GU							//Read Data Penerimaan Content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.jenis', 'pjr-in', '=');
	$query->condition('b.jenispanjar', 'gu', '=');
	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$gulalu = $data_spj->total;
	}

	//3. TU							//Read Data Penerimaan Content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.jenis', 'pjr-in', '=');
	$query->condition('b.jenispanjar', 'tu', '=');
	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tulalu = $data_spj->total;
	}
	
	//INI	
	//1. LS
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');

	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$query->condition($or);
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$lsini = $data_spj->total;
	}
	$lstotal = $lslalu + $lsini;
	
	
	//2. GU							//Read Data Penerimaan Content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.jenis', 'pjr-in', '=');
	$query->condition('b.jenispanjar', 'gu', '=');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$guini = $data_spj->total;
	}
	$gutotal = $gulalu + $guini;

	//3. TU							//Read Data Penerimaan Content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.jenis', 'pjr-in', '=');
	$query->condition('b.jenispanjar', 'tu', '=');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');

	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		$tuini = $data_spj->total;
	}
	$tutotal = $tulalu + $tuini;	
}

function read_pengembalian($kodeuk, $kodesuk, $tglawal, $tglakhir, &$gulalu_ret, &$guini_ret, &$tulalu_ret, &$tuini_ret) {
	
	//INIT
	$gulalu_ret = 0; $guini_ret = 0;
	$tulalu_ret = 0; $tuini_ret = 0;

	
	//LALU
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->fields('b', array('jenispanjar'));
	$query->addExpression('SUM(b.total)', 'total');
	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'pjr-out', '=');
	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$query->groupBy('b.jenispanjar');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->jenispanjar=='gu')
			$gulalu_ret = $data_spj->total;
		else 
			$tulalu_ret = $data_spj->total;
			
	}
	//ret
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('jenispanjar'));
	$query->addExpression('SUM(b.total)', 'total');
	$query->condition('b.tanggal', $tglawal, '<');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('b.jenispanjar');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->jenispanjar=='gu')
			$gulalu_ret += $data_spj->total;
		else 
			$tulalu_ret += $data_spj->total;
			
	}
	
	//INI
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->fields('b', array('jenispanjar'));
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'pjr-out', '=');
	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$query->groupBy('b.jenispanjar');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->jenispanjar=='gu')
			$guini_ret = $data_spj->total;
		else 
			$tuini_ret = $data_spj->total;			
	}
	//ret
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('jenispanjar'));	
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	
	$query->condition('b.jenis', 'ret-spj', '=');
	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('b.jenispanjar');
	
	$res_spj = $query->execute();
	foreach ($res_spj as $data_spj) {
		if ($data_spj->jenispanjar=='gu')
			$guini_ret += $data_spj->total;
		else 
			$tuini_ret += $data_spj->total;
			
	}
	
}


function printLaporanbk8($htmlContent, $pdfFiel, $marginatas) {
    require_once('files/tcpdf/config/lang/eng.php');
    require_once('files/tcpdf/tcpdf.php');

	class MYPDF extends TCPDF {  
	   // Page footer
		public function Footer() {
			// Position at 15 mm from bottom
			//$this->SetY(-10);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
		  //$this->Cell(0, 10, $this->getAliasNumPage().' dari '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');     
		  $this->Cell(0,0,$this->PageNo(),'T',0,'R');
		}      
	} 
	
    $pdf = new MYPDF('L', PDF_UNIT, 'F4', true, 'UTF-8', false);
    set_time_limit(0);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SIPPD');
    $pdf->SetTitle('PDF Gen');
    $pdf->SetSubject('PDF Gen');
    $pdf->SetKeywords('APBD');
    $pdf->setPrintHeader(false);
    $pdf->setFooterFont(array('helvetica','', 9));
    $pdf->setFooterMargin(10);
	$pdf->setRightMargin(1);

	//$pdf->setHeaderMargin(20);
	//$pdf->SetMargins(10,20);

	$pdf->setHeaderMargin(20);
	$pdf->SetMargins(10, $marginatas);
	
	//$pdf->SetMargins(15,15);
    $pdf->SetAutoPageBreak(true, 11);
    $pdf->setLanguageArray($l);
    //$pdf->SetFont('helvetica','', 20);
    $pdf->AddPage();
    $pdf->writeHTML($htmlContent, true, 0, true, 0);
	
	//$ypos = $pdf->GetY();
	//$pdf->Write(0, $ypos, '', 0, 'L', true, 0, false, false, 0);
	//$ypos = $ypos-10;
	//$pdf->Write(0, $ypos, '', 0, 'L', true, 0, false, false, 0);
	
	//$pdf->SetY($ypos, true, false);
	
	//$pdf->writeHTML($htmlContent, true, 0, true, 0);
    $pdf->Output($pdfFiel, 'I');
	
}
?>
