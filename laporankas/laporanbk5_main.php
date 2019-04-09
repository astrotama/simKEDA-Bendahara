<?php
function laporanbk5_main($arg=NULL, $nama=NULL) {

	if ($arg) {

		$kodeuk = arg(1);
		$kodekeg = arg(2);
		$tglawal = arg(3);
		$tglakhir = arg(4);

		$exportpdf = arg(5);
		$marginatas = arg(6);
		$tglcetak = arg(7);
		$pptknama = arg(8);
		$pptknip = arg(9);

	} 

	if (isset($exportpdf) && ($exportpdf=='pdf'))  {

		$output = getLaporanbk5($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		apbd_ExportPDF_L($output, $marginatas, 'BK5_' . $kodeuk . '-' . $kodekeg . '-' . $tglawal . '-' . $tglakhir . '.PDF');


	} else if (isset($exportpdf) && ($exportpdf=='xls'))  {
		$output = getLaporanbk5_tmpl($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename=Laporan_BK5-" . $kodeuk . "-" . $kodekeg . "-" . $tglawal . "-" . $tglakhir . ".xls" );
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $output;

	}else if (isset($exportpdf) && ($exportpdf=='tmpl'))  {
		$output = getLaporanbk5_tmpl($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip);
		$output_form = drupal_get_form('laporanbk5_main_form');
		return drupal_render($output_form). '<p align="center">. . . . .</p>' . $output;

	} else {
		$output_form = drupal_get_form('laporanbk5_main_form');
		return drupal_render($output_form);

	}



}

function laporanbk5_main_form () {
	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = apbd_getuseruk();

	
	$kodekeg = arg(2);
	$tglawal = arg(3);
	$tglakhir = arg(4);

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
	
	$opt_kegiatan = array();
	$opt_kegiatan[''] = '- Pilih Kegiatan -';

	$query = db_select('kegiatanskpd', 'd');
	# get the desired fields from the database
	$query->fields('d', array('kodekeg',  'kegiatan'));
	$query->condition('d.kodeuk', $kodeuk, '=');
	$query->condition('d.inaktif', 0, '=');
	$query->condition('d.anggaran', 0, '>');
	if (isUserPembantu()) $query->condition('d.kodesuk', apbd_getusersuk(), '=');
	$query->orderBy('d.kegiatan', 'ASC');
	$results = $query->execute();
	foreach ($results as $data) {
		$opt_kegiatan[$data->kodekeg] = $data->kegiatan;
	}


	$form['kodekeg'] = array(
		'#type' => 'select',
		'#title' =>  t('Kegiatan'),
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',
		'#options' => $opt_kegiatan,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $kodekeg,
	);

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
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		'#date_label_position' => 'within',
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
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '',
		'#maxlength'    => 50,
		//'#size'         => 20,
		//'#required'     => !$disabled,
		'#disabled'     => false,
		'#default_value'=> '',
	);
	$form['pptknip']= array(
		'#type'         => 'textfield',
		'#title'        => 'NIP PPTK',
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '',
		'#maxlength'    => 50,
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

function laporanbk5_main_form_validate($form, &$form_state) {
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

function laporanbk5_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$_SESSION["kodekas_uk"] = $kodeuk;
	
	$kodekeg = $form_state['values']['kodekeg'];

	$tglawal = dateapi_convert_timestamp_to_datetime($form_state['values']['tglawal']);
	$_SESSION["laporan_kas_tgl_awal"] = apbd_date_convert_db2form($tglawal);
	
	$tglakhir = dateapi_convert_timestamp_to_datetime($form_state['values']['tglakhir']);
	$_SESSION["laporan_kas_tgl_akhir"] = apbd_date_convert_db2form($tglakhir);

	$tglcetak = $form_state['values']['tglcetak'];
	$marginatas = $form_state['values']['marginatas'];

	$pptknama = $form_state['values']['pptknama'];
	$pptknip = $form_state['values']['pptknip'];

	if($form_state['clicked_button']['#value'] == $form_state['values']['submit'])
	$uri = 'laporanbk5/' . $kodeuk . '/' . $kodekeg . '/'  . $tglawal . '/' . $tglakhir . '/pdf/' . $marginatas . '/' . $tglcetak . '/' . $pptknama . '/' . $pptknip;
elseif($form_state['clicked_button']['#value'] == $form_state['values']['submittmpl'])
	$uri = 'laporanbk5/' . $kodeuk . '/' . $kodekeg . '/'  . $tglawal . '/' . $tglakhir . '/tmpl/' . $marginatas . '/' . $tglcetak . '/' . $pptknama . '/' . $pptknip;
else
	$uri = 'laporanbk5/' . $kodeuk . '/' . $kodekeg . '/'  . $tglawal . '/' . $tglakhir . '/xls/' . $marginatas . '/' . $tglcetak . '/' . $pptknama . '/' . $pptknip;

	drupal_goto($uri);

}


function getLaporanbk5($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip){
	$styleheader='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;';
	$style='border-right:1px solid black;';

	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '25px','align'=>'center','style'=>'border:none;'),
		array('data' => 'KARTU KENDALI KEGIATAN', 'width' => '825px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-5', 'width' => '25px','align'=>'center','style'=>'border:none;font-size:60%;'),
	);
	$rows[]=array(
		array('data' => 'Tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;font-weight:bold;'),
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
	if ((isUserPembantu()) and ($kodesuk!='')) {
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
		array('data' => $namauk, 'width' => '755px','align'=>'left','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' => 'Nama Kegiatan', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => $kegiatan, 'width' => '755px','align'=>'left','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' => 'Nama PPTK', 'width' => '100px','align'=>'left','style'=>'border:none;font-size:80%;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;font-size:80%;'),
		array('data' => $pptknama, 'width' => '755px','align'=>'left','style'=>'border:none;font-size:80%;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$header=null;
	$rows=null;
	if (arg(5) == 'xls'){
	$header[]=array(
		array('data' => 'No', 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Kode Rekening', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Uraian', 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Pagu Anggaran', 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Sebelumnya', 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Kegiatan UP/GU', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Kegiatan TU', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Kegiatan LS', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'Total Realisasi', 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Sisa Pagu Anggaran', 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);

	}else{
	$header[]=array(
		array('data' => 'No', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Kode Rekening', 'rowspan'=>2, 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Pagu Anggaran', 'rowspan'=>2,'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Sebelumnya', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Kegiatan', 'width' => '240px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Total Realisasi', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Sisa Pagu Anggaran', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);

	$header[]=array(
		array('data' => 'UP/GU', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'TU', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'LS', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
	);
	}
	$n = 0;
	$lalu_total = 0; $ls_total = 0; $gu_total = 0; $tu_total = 0; $rea_total = 0;

	//contents
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
		$n++;

		//lalu
		$lalu = read_sebelumnya($kodekeg, $data_rek->kodero, $tglawal);

		//transaksi
		$ls = 0; $gu = 0; $tu = 0;
		read_sekarang($kodekeg, $data_rek->kodero, $tglawal, $tglakhir, $ls, $gu, $tu);

		$anggaran_total += $data_rek->anggaran;

		$lalu_total += $lalu;

		$ls_total += $ls;
		$gu_total += $gu;
		$tu_total += $tu;

		$realisai = $ls + $gu + $tu + $lalu;
		$rea_total += $realisai;

		//LALU

		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => apbd_format_rek_rincianobyek($data_rek->kodero), 'width' => '60px','align'=>'center','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => $data_rek->uraian, 'width' => '225px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($data_rek->anggaran), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($lalu), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($gu), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($tu), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($ls), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($realisai), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($data_rek->anggaran - $realisai), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),

		);
	}
	$rows[]=array(
			array('data' => 'TOTAL', 'width' => '315px','align'=>'center','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($anggaran_total),'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($lalu_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($gu_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($tu_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($ls_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($rea_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($anggaran_total - $rea_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),

	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => 'Mengesahkan,','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => 'Jepara, ' . $tglcetak,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => 'PEJABAT PELAKSANA KEGIATAN','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => $bpjabatan,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => $pptknama,'width' => '435px', 'align'=>'center','style'=>'border:none;font-size:80%;text-decoration:underline;'),
					array('data' => $bpnama,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:80%;text-decoration:underline;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $pptknip,'width' => '435px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
					array('data' => 'NIP. ' . $bpnip,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:80%;'),
	);


	$output.=createT($header, $rows);
		return $output;
}

function getLaporanbk5_tmpl($kodeuk, $kodekeg, $tglawal, $tglakhir, $tglcetak, $pptknama, $pptknip){
	$styleheader='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;';
	$style='border-right:1px solid black;';


	$header[]=array(
		array('data' => 'No', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
		array('data' => 'Kode Rekening', 'rowspan'=>2, 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Pagu Anggaran', 'rowspan'=>2,'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Sebelumnya', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Realisasi Kegiatan', 'colspan'=>3, 'width' => '240px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Total Realisasi', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
		array('data' => 'Sisa Pagu Anggaran', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:80%;'),
	);

	$header[]=array(
		array('data' => 'UP/GU', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'TU', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:80%;'),
		array('data' => 'LS', 'width' => '80px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:80%;'),
	);

	$n = 0;
	$lalu_total = 0; $ls_total = 0; $gu_total = 0; $tu_total = 0; $rea_total = 0;

	//contents
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
		$n++;

		//lalu
		$lalu = read_sebelumnya($kodekeg, $data_rek->kodero, $tglawal);

		//transaksi
		$ls = 0; $gu = 0; $tu = 0;
		read_sekarang($kodekeg, $data_rek->kodero, $tglawal, $tglakhir, $ls, $gu, $tu);

		$anggaran_total += $data_rek->anggaran;

		$lalu_total += $lalu;

		$ls_total += $ls;
		$gu_total += $gu;
		$tu_total += $tu;

		$realisai = $ls + $gu + $tu + $lalu;
		$rea_total += $realisai;

        //$uraian = <a href= "/laporanbk6/".arg(1)."/".arg(2)."/".arg(3)."/".arg(4)."/".arg(5)."/".arg(6)."/".arg(7)."/".arg(8)."/".arg(9)

		$uraian = l($data_rek->uraian, 'laporanbk6/'.arg(1).'/'.arg(2).'/'.$data_rek->kodero.'/'.arg(3).'/'.arg(4).'/'.arg(5).'/'.arg(6).'/'.arg(7).'/'.arg(8).'/'.arg(9) , array ('html' => true, 'attributes'=> array('target' => '_blank')));

		//LALU

		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => apbd_format_rek_rincianobyek($data_rek->kodero), 'width' => '60px','align'=>'center','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => $uraian, 'width' => '225px','align'=>'left','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($data_rek->anggaran), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($lalu), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($gu), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($tu), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($ls), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($realisai), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),
			array('data' => apbd_fn($data_rek->anggaran - $realisai), 'width' => '80px','align'=>'right','style'=>'border-right:1px solid black;font-size:80%;'),

		);
	}


	$rows[]=array(
			array('data' => 'TOTAL', 'colspan'=>3, 'width' => '315px','align'=>'center','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($anggaran_total),'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($lalu_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($gu_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($tu_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($ls_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($rea_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),
			array('data' => apbd_fn($anggaran_total - $rea_total), 'width' => '80px','align'=>'right','style'=>'border:1px solid black;font-weight:bold;font-size:80%;'),

	);

	$output.=createT($header, $rows);
		return $output;
}


function read_sekarang($kodekeg, $kodero, $tglawal, $tglakhir, &$ls, &$gu, &$tu) {
	$ls = 0; $gu = 0; $tu = 0;
	
	$kodeuk = substr($kodekeg, 4,2);
	if ($kodeuk=='81') {
		$kodeuk = $_SESSION["kodekas_uk"];
	}	
	//drupal_set_message($kodeuk);
	
	//realisasi
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->fields('b', array('jenis'));
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');

	$or = db_or();
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'ls', '=');
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '=');
	$query->condition($or);
	$query->groupBy('b.jenis');

	$result = $query->execute();
	foreach ($result as $data_spj) {

		if ($data_spj->jenis == 'gu-spj')
			$gu += $data_spj->total;
		else if (($data_spj->jenis == 'ls') or ($data_spj->jenis == 'gaji'))
			$ls += $data_spj->total;
		else
			$tu += $data_spj->total;

	}

	//pindahbuku
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->fields('b', array('jenispanjar'));
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	$query->condition('b.jenis', 'pindahbuku', '=');
	//$query->condition('bi.jumlah', '0', '>=');

	$query->groupBy('b.jenispanjar');
	$result = $query->execute();
	foreach ($result as $data_spj) {

		if ($data_spj->jenispanjar == 'gu')
			$gu += $data_spj->total;
		else if ($data_spj->jenispanjar == 'ls')
			$ls += $data_spj->total;
		else
			$tu += $data_spj->total;

	}


	//pindahbuku -
	/*
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->fields('b', array('jenispanjar'));
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
	$query->condition('b.jenis', 'pindahbuku', '=');
	$query->condition('bi.jumlah', '0', '<');

	$query->groupBy('b.jenispanjar');
	$result = $query->execute();
	foreach ($result as $data_spj) {

		if ($data_spj->jenispanjar == 'gu')
			$gu += $data_spj->total;
		else if ($data_spj->jenispanjar == 'ls')
			$ls += $data_spj->total;
		else
			$tu += $data_spj->total;

	}
	*/

	//ret
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->fields('b', array('jenispanjar'));
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$query->condition('b.tanggal', $tglawal, '>=');
	$query->condition('b.tanggal', $tglakhir, '<=');
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


function read_sebelumnya($kodekeg, $kodero, $tglawal) {
	$val = 0;

	//rea
	$kodeuk = substr($kodekeg, 4,2); 
	if ($kodeuk=='81') {
		$kodeuk = $_SESSION["kodekas_uk"];
	}	
	//drupal_set_message($kodeuk);

	
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
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

	//dpq($query);

	$result = $query->execute();
	foreach ($result as $data_spj) {

		$val = $data_spj->total;

	}

	//ret
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->addExpression('SUM(bi.jumlah)', 'total');
	$query->condition('b.kodekeg', $kodekeg, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$query->condition('b.tanggal', $tglawal, '<');
	$query->condition('b.jenis', 'ret-spj', '=');

	$result = $query->execute();
	foreach ($result as $data_spj) {

		$val -= $data_spj->total;

	}

	return $val;
}


?>
