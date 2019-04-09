<?php
function laporanbk1_std_main($arg=NULL, $nama=NULL) {
    //ini_set('memory_limit', '2048M');
	if ($arg) {
		
		
		$kodeuk = arg(1);
		if ($kodeuk=='') $kodeuk = apbd_getuseruk();
		
		$jenis = arg(2);
		$tglawal = arg(3);
		$tglakhir = arg(4);
		$detil = arg(5);
		
		$exportpdf = arg(6);

		$tglcetak = arg(7);
		$marginatas = arg(8);
		
		
		
	} 
	
	if (isset($exportpdf) && ($exportpdf=='pdf'))  {	
		$output = getlaporanbk1($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak);
		apbd_ExportPDF_L($output, $marginatas, 'BK1_' . $kodeuk . '-' . $tglawal . '-' . $tglakhir . '.PDF');
	
	} else if (isset($exportpdf) && ($exportpdf=='xls'))  {	
		$output = getlaporanbk1_tmpl($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak);
		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename= Laporan_BK1-" . $kodeuk . "-" . $tglawal . "-" . $tglakhir . ".xls" );
		header("Pragma: no-cache"); 
		header("Expires: 0");
		echo $output;		
	
	} else if (isset($exportpdf) && ($exportpdf=='tmpl'))  {
		$output_form = drupal_get_form('laporanbk1_std_main_form');
		$output = getlaporanbk1_tmpl($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak);
		return drupal_render($output_form). '<p align="center">. . . . .</p>' . $output;
	}else {
		$output_form = drupal_get_form('laporanbk1_std_main_form');
		return drupal_render($output_form);
		
	}	
	
}

function laporanbk1_std_main_form($form, &$form_state) {
	
	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = apbd_getuseruk();

	$jenis = arg(2);
	$tglawal = arg(3);
	$tglakhir = arg(4);
	$detil = arg(5);

	
	if ($tglawal == '') {
		$jenis = '1';

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
	$form['jenis']= array(
		'#type'     => 'value', 
		'#value'	=> $jenis, 
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
	$form['batasdetil']= array(
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',	
		'#type' => 'item',
		'#markup' => '</br>',
	);	
	$form['detil']= array(
		'#type'         => 'checkbox', 
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',
		'#title'        => 'Detil (Tampilkan Rekening)', 
		'#default_value'=> $detil, 
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

function laporanbk1_std_main_form_validate($form, &$form_state) {
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

function laporanbk1_std_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$jenis = $form_state['values']['jenis'];
	
	$detil = $form_state['values']['detil'];
	
	$tglawal = dateapi_convert_timestamp_to_datetime($form_state['values']['tglawal']);
	$_SESSION["laporan_kas_tgl_awal"] = apbd_date_convert_db2form($tglawal);
	
	$tglakhir = dateapi_convert_timestamp_to_datetime($form_state['values']['tglakhir']);
	$_SESSION["laporan_kas_tgl_akhir"] = apbd_date_convert_db2form($tglakhir);	
	
	$tglcetak = $form_state['values']['tglcetak'];
	$marginatas = $form_state['values']['marginatas'];
	
	if($form_state['clicked_button']['#value'] == $form_state['values']['submit']) 
		$uri = 'laporanbk1std/' . $kodeuk . '/' . $jenis . '/'  . $tglawal . '/' . $tglakhir . '/'. $detil . '/pdf/' . $tglcetak . '/' . $marginatas;
	elseif($form_state['clicked_button']['#value'] == $form_state['values']['submittmpl'])
		$uri = 'laporanbk1std/' . $kodeuk . '/' . $jenis . '/'  . $tglawal . '/' . $tglakhir . '/'. $detil . '/tmpl/' . $tglcetak . '/' . $marginatas;
	else
		$uri = 'laporanbk1std/' . $kodeuk . '/' . $jenis . '/'  . $tglawal . '/' . $tglakhir . '/'. $detil . '/xls/' . $tglcetak . '/' . $marginatas;
	
	drupal_goto($uri);
	
}


function getLaporanbk1($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak){
	set_time_limit(0);
	ini_set('memory_limit','940M');
	//max_execution_time = 600;
	//max_input_time = 600;
	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'pimpinannama', 'pimpinanjabatan', 'pimpinanpangkat', 'pimpinannip', 'bendaharanama', 'bendaharanip', 'ppknama', 'ppknip'));
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

		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;
		
	}	
	
	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '25px','align'=>'center','style'=>'border:none;'),	
		array('data' => 'PEMERINTAH KABUPATEN JEPARA', 'width' => '825px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-1', 'width' => '25px','align'=>'center','style'=>'border:none;font-size:60%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '325px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => 'B U K U', 'width' => '75px','align'=>'right','style'=>'border:none;font-weight:bold;'),
		array('data' => 'K A S', 'width' => '75px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => 'U M U M', 'width' => '75px','align'=>'left','style'=>'border:none;font-weight:bold;'),
		array('data' => '', 'width' => '325px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	$rows[]=array(
		array('data' =>  $namauk, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  'Tanggal ' . apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	
	//Content	
	$header=null;
	$rows=null;
	if (arg(6) == 'xls'){
	$header[]=array(
		array('data' => 'No.','width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Kegiatan/Rekening',  'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Ref', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Kas Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Kas Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Pengeluaran Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Pengeluaran Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Pajak Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Pajak Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	}else{
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Kegiatan/Rekening', 'rowspan'=>2, 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Ref', 'rowspan'=>2,'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PAJAK', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	$header[]=array(
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	}
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'noref', 'tanggal', 'total', 'pajak', 'keperluan', 'penerimanama', 'kodesuk'));
	$query->fields('k', array('kegiatan'));

	$or = db_or();
	$or->condition('b.jenis', 'up', '=');
	$or->condition('b.jenis', 'tu', '=');
	$or->condition('b.jenis', 'gu-kas', '=');
	$or->condition('b.jenis', 'ls', '=');
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '='); 
	$or->condition('b.jenis', 'ret-spj', '=');
	$or->condition('b.jenis', 'ret-kas', '=');
	$query->condition($or);
	
	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	//$query->condition('b.kodeuk', $kodeuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.bendid');
	
	//dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_kastambah = 0; $total_kaskurang = 0; $total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_pajaktambah = 0; $total_pajakkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; 
		
		
		$pajaktambah = read_pajak($kodeuk, $data->bendid); 
		$pajakkurang = $pajaktambah;
		
		$noref  = '';
		switch ($data->jenis) {
			case 'up':
			case 'tu':
			case 'gu-kas': {
					$kastambah = $data->total;
					$noref  = ' ' . $data->noref;
				}
				break;

			case 'ls':
			case 'gaji': {
					$kastambah = $data->total;
					$kaskurang = $data->total;	
					$belanjatambah = $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah = $data->total;
					$kaskurang = $data->total;
				}
				break;

			case 'pjr-in': 
				break;

			case 'pjr-out': 
				break;

			case 'ret-spj': {
					$belanjakurang = $data->total;
				}
				break;

			case 'ret-kas': {
					$kaskurang = $data->total;
				}
				break;
				
		}
		
		$kastambah += $pajaktambah;
		$kaskurang += $pajakkurang;
		
		$keterangan = $data->kegiatan;
			
		
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan, 'width' => '165px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan,'width' => '130px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->bendid . $noref,'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kastambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kaskurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($pajaktambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($pajakkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if  ((($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj') or ($data->jenis=='spj-ret')) and ($detil)) {
			
			//REKENING
			$query = db_select('bendaharaitem' . $kodeuk, 'bi');
			$query->join('rincianobyek', 'ro', 'bi.kodero=ro.kodero');
			$query->fields('bi', array('jumlah', 'keterangan'));
			$query->fields('ro', array('uraian'));

			$query->condition('bi.bendid', $data->bendid, '=');
			$query->orderBy('ro.kodero');
			
			$res_rek = $query->execute();
			foreach ($res_rek as $data_rek) {
				$rek_belanja_tambah = 0; $rek_belanja_kurang = 0;
				if ($data->jenis=='spj-ret')
					$rek_belanja_kurang = $data_rek->jumlah;
				else
					$rek_belanja_tambah = $data_rek->jumlah;
				
				$uraian = str_replace('Belanja ','',$data_rek->uraian);
				$rows[]=array(
					
					array('data' => '', 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '50px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '-', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => $uraian, 'width' => '155px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
			}	
		
			//PAJAK
			
			$query = db_select('bendaharapajak' . $kodeuk, 'bp');
			$query->join('ltpajak', 'p', 'bp.kodepajak=p.kodepajak');
			$query->fields('bp', array('jumlah'));
			$query->fields('p', array('uraian'));

			$query->condition('bp.bendid', $data->bendid, '=');
			$query->orderBy('bp.kodepajak');
			
			$res_rek = $query->execute();
			foreach ($res_rek as $data_rek) {
				$pajak = $data_rek->jumlah;
				
				$rows[]=array(
					
					array('data' => '', 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '50px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '-', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => $data_rek->uraian . ' (Pungut)', 'width' => '155px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
				$rows[]=array(
					
					array('data' => '', 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '50px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '-', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => $data_rek->uraian . ' (Setor)', 'width' => '155px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);				
			}
						
		
		}

		//TOTAL
		$total_kastambah += $kastambah; $total_kaskurang += $kaskurang; 
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		$total_pajaktambah += $pajaktambah; $total_pajakkurang += $pajakkurang;		
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '425px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kastambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_kaskurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajaktambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajakkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	//PERIODE INI
	$total_kas_ini = $total_kastambah - $total_kaskurang;
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_pajak_ini = $total_pajaktambah - $total_pajakkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_kas_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajak_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_kas_lalu = 0; $total_belanja_lalu = 0; $total_pajak_lalu = 0;
	read_kas_sebelumnya($kodeuk, $jenis, $tglawal, $total_kas_lalu, $total_belanja_lalu, $total_pajak_lalu);
	$total_pajak_lalu = 0;
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajak_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajak_lalu + $total_pajak_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Kas pada tanggal ' . apbd_fd_long($tglakhir), 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini + $total_pajak_lalu + $total_pajak_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '150px','align'=>'right','style'=>'border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
	);
	
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'Jepara, ' . $tglcetak,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	); 
	$rows[] = array(
					array('data' => 'Mengesahkan,','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => '','width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'PEJABAT PENATAUSAHAAN KEUANGAN','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'BENDAHARA PENGELUARAN','width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => $ppknama,'width' => '435px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
					array('data' => $bendaharanama,'width' => '440px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $ppknip,'width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'NIP. ' . $bendaharanip,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'Mengetahui,','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => $pimpinanjabatan,'width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => $pimpinannama,'width' => '875px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => 'NIP. ' . $pimpinannip,'width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);

	$output.=createT($header, $rows);
	
		return $output;
}

function getLaporanbk1_tmpl($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak){
	set_time_limit(0);
	ini_set('memory_limit','940M');
	//max_execution_time = 600;
	//max_input_time = 600;
	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'pimpinannama', 'pimpinanjabatan', 'pimpinanpangkat', 'pimpinannip', 'bendaharanama', 'bendaharanip', 'ppknama', 'ppknip'));
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

		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;
		
	}
	
	//Content	
	$header=null;
	$rows=null;

	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Kegiatan/Rekening', 'rowspan'=>2, 'width' => '165px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '130px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Ref', 'rowspan'=>2,'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS', 'colspan'=>2, 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN',  'colspan'=>2,'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PAJAK',  'colspan'=>2,'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	$header[]=array(
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'noref', 'tanggal', 'total', 'pajak', 'keperluan', 'penerimanama', 'kodesuk'));
	$query->fields('k', array('kegiatan'));

	$or = db_or();
	$or->condition('b.jenis', 'up', '=');
	$or->condition('b.jenis', 'tu', '=');
	$or->condition('b.jenis', 'gu-kas', '=');
	$or->condition('b.jenis', 'ls', '=');
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '='); 
	$or->condition('b.jenis', 'ret-spj', '=');
	$or->condition('b.jenis', 'ret-kas', '=');
	$query->condition($or);
	
	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	//$query->condition('b.kodeuk', $kodeuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.bendid');
	
	//dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_kastambah = 0; $total_kaskurang = 0; $total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_pajaktambah = 0; $total_pajakkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; 
		$noref  = '';
		
		$pajaktambah = read_pajak($kodeuk, $data->bendid); 
		$pajakkurang = $pajaktambah;
		
		switch ($data->jenis) {
			case 'up':
			case 'tu':
			case 'gu-kas': {
					$kastambah = $data->total;
					$noref  = ' ' . $data->noref;
				}
				break;

			case 'ls':
			case 'gaji': {
					$kastambah = $data->total;
					$kaskurang = $data->total;	
					$belanjatambah = $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah = $data->total;
					$kaskurang = $data->total;
				}
				break;

			case 'pjr-in': 
				break;

			case 'pjr-out': 
				break;

			case 'ret-spj': {
					$belanjakurang = $data->total;
				}
				break;

			case 'ret-kas': {
					$kaskurang = $data->total;
				}
				break;
				
		}
		
		$kastambah += $pajaktambah;
		$kaskurang += $pajakkurang;
		
		$keterangan = $data->kegiatan;
			
		
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan, 'width' => '165px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan,'width' => '130px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->bendid . $noref,'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kastambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kaskurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($pajaktambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($pajakkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		
		//REKENING
		if  ((($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj') or ($data->jenis=='spj-ret')) and ($detil)) {
			
			//REKENING
			$query = db_select('bendaharaitem' . $kodeuk, 'bi');
			$query->join('rincianobyek', 'ro', 'bi.kodero=ro.kodero');
			$query->fields('bi', array('jumlah', 'keterangan'));
			$query->fields('ro', array('uraian'));

			$query->condition('bi.bendid', $data->bendid, '=');
			$query->orderBy('ro.kodero');
			
			$res_rek = $query->execute();
			foreach ($res_rek as $data_rek) {
				$rek_belanja_tambah = 0; $rek_belanja_kurang = 0;
				if ($data->jenis=='spj-ret')
					$rek_belanja_kurang = $data_rek->jumlah;
				else
					$rek_belanja_tambah = $data_rek->jumlah;
				
				$uraian = '-) ' . str_replace('Belanja ','',$data_rek->uraian);
				$rows[]=array(
					
					array('data' => '', 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '50px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => $uraian, 'width' => '165px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
			}	
		
			//PAJAK
			
			$query = db_select('bendaharapajak' . $kodeuk, 'bp');
			$query->join('ltpajak', 'p', 'bp.kodepajak=p.kodepajak');
			$query->fields('bp', array('jumlah'));
			$query->fields('p', array('uraian'));

			$query->condition('bp.bendid', $data->bendid, '=');
			$query->orderBy('bp.kodepajak');
			
			$res_rek = $query->execute();
			foreach ($res_rek as $data_rek) {
				$pajak = $data_rek->jumlah;
				
				$rows[]=array(
					
					array('data' => '', 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '50px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '-) ' . $data_rek->uraian . ' (Pungut)', 'width' => '165px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
				$rows[]=array(
					
					array('data' => '', 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '50px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '-) ' . $data_rek->uraian . ' (Setor)', 'width' => '165px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '130px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '0', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($data_rek->jumlah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);				
			}
						
		
		}
		
		
		//TOTAL
		$total_kastambah += $kastambah; $total_kaskurang += $kaskurang; 
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		$total_pajaktambah += $pajaktambah; $total_pajakkurang += $pajakkurang;		
		
	}

	$rows[]=array(
		array('data' => '', 'colspan'=>5, 'width' => '425px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kastambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_kaskurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajaktambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajakkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	//PERIODE INI
	$total_kas_ini = $total_kastambah - $total_kaskurang;
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_pajak_ini = $total_pajaktambah - $total_pajakkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'colspan'=>5, 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_kas_ini), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajak_ini), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_kas_lalu = 0; $total_belanja_lalu = 0; $total_pajak_lalu = 0;
	read_kas_sebelumnya($kodeuk, $jenis, $tglawal, $total_kas_lalu, $total_belanja_lalu, $total_pajak_lalu);
	$total_pajak_lalu = 0;
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'colspan'=>5, 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajak_lalu),'colspan'=>2,  'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'colspan'=>5, 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_pajak_lalu + $total_pajak_ini), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Kas pada tanggal ' . apbd_fd_long($tglakhir), 'colspan'=>5, 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini + $total_pajak_lalu + $total_pajak_ini), 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '','colspan'=>2,  'width' => '150px','align'=>'right','style'=>'border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '', 'colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
	);	
	$output.=createT($header, $rows);
	
		return $output;
}


//($kodeuk, $jenis, $tglawal, $total_kas_lalu, $total_belanja_lalu, $total_pajak_lalu);
function read_kas_sebelumnya($kodeuk, $jenis, $tglawal, &$kas, &$belanja, &$pajak){

	//init
	$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; $pjk = 0;;
	
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');
	$query->addExpression('SUM(b.pajak)', 'pajak');
	$query->fields('b', array('jenis'));

	//NON LS
	$query->condition('b.jenis', 'ls', '<>');	
	$query->condition('b.jenis', 'gaji', '<>');
	
	$query->condition('b.tanggal', $tglawal, '<');	
	//$query->condition('b.kodeuk', $kodeuk, '=');
	$query->groupBy('b.jenis');
		
	$results = $query->execute();
	
	foreach ($results as $data) {
		
		$pjk += $data->pajak;
		switch ($data->jenis) {
			case 'up':
			case 'tu':
			case 'gu-kas': {
					$kastambah += $data->total;
				}
				break;

			case 'ls':
			case 'gaji': {
					$kastambah += $data->total;
					$kaskurang += $data->total;	
					$belanjatambah += $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah += $data->total;
					$kaskurang += $data->total;
				}
				break;

			case 'pjr-in': 
				break;

			case 'pjr-out': 
				break;

			case 'ret-spj': {
					$belanjakurang += $data->total;
				}
				break;

			case 'ret-kas': {
					$kaskurang += $data->total;
				}
				break;
				
		}	
	}
	
	$kas = $kastambah - $kaskurang;
	$belanja = $belanjatambah - $belanjakurang;
	$pajak = $pjk;
}

function read_pajak($kodeuk, $bendid) {
	$pajak = 0;
	$res = db_query('select sum(jumlah) total from {bendaharapajak' . $kodeuk . '} where bendid=:bendid', array(':bendid'=>$bendid));
	foreach ($res as $data) {
		$pajak = $data->total;
	}
	
	return $pajak;
}

?>
