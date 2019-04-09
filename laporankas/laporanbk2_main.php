<?php

function laporanbk2_main($arg=NULL, $nama=NULL) {
    
	if ($arg) {
		
		
		$kodeuk = arg(1);
		$kodesuk = arg(2);
		$tglawal = arg(3);
		$tglakhir = arg(4);
		
		$tglcetak = arg(6);
		$marginatas = arg(7);
		
		$exportpdf = arg(5);
		
		
	} 
	
	if (isset($exportpdf) && ($exportpdf=='pdf'))  {	
		$output = getlaporanbk2($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
		apbd_ExportPDF_P($output, $marginatas, 'BK2_' . $kodeuk . $kodesuk . '-' . $tglawal . '-' . $tglakhir . '.PDF');
	
	} else if (isset($exportpdf) && ($exportpdf=='xls'))  {	
		$output = getlaporanbk2_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename=Laporan_BK2-" . $kodeuk . $kodesuk . "-" . $tglawal . "-" . $tglakhir . ".xls" );
		header("Pragma: no-cache"); 
		header("Expires: 0");
		echo $output;		
	
	}else if (isset($exportpdf) && ($exportpdf=='tmpl'))  {	
		$output = getlaporanbk2_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
		$output_form = drupal_get_form('laporanbk2_main_form');
		return drupal_render($output_form). '<p align="center">. . . . .</p>' . $output;	
	
	} else {
		$output_form = drupal_get_form('laporanbk2_main_form');
		return drupal_render($output_form);
		
	}	
	
}

function laporanbk2_main_form ($form, &$form_state) {
	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = apbd_getuseruk();
	
	$kodesuk = arg(2);
	$tglawal = arg(3);
	$tglakhir = arg(4);
	if ($kodesuk=='') $kodesuk = 'ZZ';

	
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
		$opt_suk['ZZ'] = '- SEMUA BIDANG -';
		$query = db_select('subunitkerja', 's');
		$query->fields('s', array('kodesuk', 'namasuk'));
		$query->condition('s.kodeuk', $kodeuk, '=');
		$results = $query->execute();
		foreach ($results as $data) {
			$opt_suk[$data->kodesuk] = $data->namasuk;
		}
		
		$form['kodesuk']= array(
			'#type'     => 'select', 
			'#title' =>  t('Bidang/Bagian'),
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',
			'#options' =>  $opt_suk,
			'#default_value'	=> $kodesuk, 
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
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',
		'#date_label_position' => 'within',
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

function laporanbk2_main_form_validate($form, &$form_state) {
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

function laporanbk2_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	
	$tglawal = dateapi_convert_timestamp_to_datetime($form_state['values']['tglawal']);
	$_SESSION["laporan_kas_tgl_awal"] = apbd_date_convert_db2form($tglawal);
	
	$tglakhir = dateapi_convert_timestamp_to_datetime($form_state['values']['tglakhir']);
	$_SESSION["laporan_kas_tgl_akhir"] = apbd_date_convert_db2form($tglakhir);	
	
	$tglcetak = $form_state['values']['tglcetak'];
	$marginatas = $form_state['values']['marginatas'];
	
	if($form_state['clicked_button']['#value'] == $form_state['values']['submit'])
		$uri = 'laporanbk2/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/pdf/' . $tglcetak . '/' . $marginatas;
	elseif($form_state['clicked_button']['#value'] == $form_state['values']['submittmpl'])
		$uri = 'laporanbk2/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/tmpl/' . $tglcetak . '/' . $marginatas;
	else
		$uri = 'laporanbk2/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/xls/' . $tglcetak . '/' . $marginatas;
	
	drupal_goto($uri);
	
}

function read_kas_sebelumnya($kodeuk, $kodesuk, $tglawal, &$masuk, &$keluar){

	//init
	$masuk = 0; $keluar = 0;
	
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');
	$query->fields('b', array('jenis'));

	if ($kodesuk=='ZZ')
		$query->condition('b.kodesuk', '0000', '<>');	
	else
		$query->condition('b.kodesuk', $kodesuk, '=');
	
	$or = db_or();
	$or->condition('b.jenis', 'pjr-in', '=');	
	$or->condition('b.jenis', 'pjr-out', '=');	
	$or->condition('b.jenis', 'tu-spj', '=');	
	$or->condition('b.jenis', 'gu-spj', '=');	
	$query->condition($or);
	
	
	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->groupBy('b.jenis');
	$query->groupBy('b.kodesuk');
	
	//dpq($query);
	
	$results = $query->execute();
	
	foreach ($results as $data) {
		switch ($data->jenis) {
			case 'tu-spj':
			case 'gu-spj':
			case 'pjr-out':
				$keluar += $data->total;
				break;

			case 'pjr-in':
				$masuk += $data->total;
				break;

		}	
	}
}

function getLaporanbk2($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak){

	set_time_limit (1024);
	//set_time_limit(0);
	ini_set('memory_limit','940M');
	
	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'pimpinannama', 'pimpinanjabatan', 'pimpinanpangkat', 'pimpinannip', 'bendaharanama', 'bendaharanip', 'ppknama', 'ppknip', 'ppkjabatan'));
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
		$bendaharajabatan = 'BENDAHARA PENGELUARAN';

		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;
		
	}	
	
	if ($kodesuk!='ZZ') {
		$query = db_select('subunitkerja', 'u');
		$query->fields('u', array('bpnama', 'bpnip', 'namasuk'));
		$query->condition('u.kodeuk', $kodeuk, '=');
		$query->condition('u.kodesuk', $kodesuk, '=');
		//dpq($query);			
		# execute the query	
		$results = $query->execute();
		foreach ($results as $data) {
			
			$bendaharanama = $data->bpnama;
			$bendaharanip = $data->bpnip;
			$bendaharajabatan = 'BENDAHARA PENGELUARAN PEMBANTU';
			
			$namasuk = $data->namasuk;
			
		}	
	}
	
	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '25px','align'=>'center','style'=>'border:none;'),	
		array('data' => 'BUKU PANJAR', 'width' => '460px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-2', 'width' => '25px','align'=>'center','style'=>'border:none;font-size:60%;'),
	);
	
	$rows[]=array(
		array('data' =>  $namauk, 'width' => '510px','align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	if ($kodesuk != 'ZZ') {
		$rows[]=array(
			array('data' =>  $namasuk, 'width' => '510px','align'=>'center','style'=>'border:none;font-size:75%;'),
		);
	}
	$rows[]=array(
		array('data' =>  'Tanggal ' . apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '510px','align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));

	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No.', 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'width' => '120px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'width' => '100px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'S a l d o', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	$awal_masuk = 0; $awal_keluar =0; $saldo =0;
	read_kas_sebelumnya($kodeuk, $kodesuk, $tglawal, $awal_masuk, $awal_keluar);
	$saldo = $awal_masuk - $awal_keluar;
	$total_masuk = 0; $total_keluar = 0;
	
	$rows[]=array(
		array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => 'Saldo sebelumnya', 'width' => '120px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($awal_masuk), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($awal_keluar), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($saldo) , 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//Content
	$n = 0;
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('subunitkerja', 's', 'b.kodesuk=s.kodesuk');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'tanggal', 'total', 'keperluan','kodesuk'));
	$query->fields('k', array('kegiatan'));
	$query->fields('s', array('namasuk'));

	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	
	if ($kodesuk=='ZZ')
		$query->condition('b.kodesuk', '0000', '<>');	
	else
		$query->condition('b.kodesuk', $kodesuk, '=');

	$or = db_or();
	$or->condition('b.jenis', 'pjr-in', '=');	
	$or->condition('b.jenis', 'pjr-out', '=');	
	$or->condition('b.jenis', 'tu-spj', '=');	
	$or->condition('b.jenis', 'gu-spj', '=');	
	$query->condition($or);
	
	$query->orderBy('b.tanggal');
	$query->orderBy('b.kodekeg');
	$query->orderBy('b.kodesuk');	
	
	//dpq($query);
	
	$results = $query->execute();
	
	foreach ($results as $data) {
		$n++;
		
		$masuk = 0; $keluar = 0;
		switch ($data->jenis) {
			case 'tu-spj':
			case 'gu-spj': {
					$keluar = $data->total;
					$keterangan = $data->kegiatan;
				} 
				break;

			case 'pjr-in': {
					$masuk = $data->total;
					$keterangan = $data->namasuk;
				}
				break;

			case 'pjr-out': {
					$keluar = $data->total;
					$keterangan = $data->namasuk;
				}
				break;
				
		}		

		$saldo = $saldo	+ $masuk - $keluar;
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan, 'width' => '120px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan, 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($masuk), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($keluar), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($saldo), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		);
		
		$total_masuk += $masuk; $total_keluar += $keluar; 
	}
	
	$rows[]=array(
		array('data' => 'TOTAL', 'width' => '300px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-left:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		
		array('data' => apbd_fn($total_masuk), 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		array('data' => apbd_fn($total_keluar), 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		array('data' => apbd_fn($saldo), 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-bottom:2px solid black;font-weight:bold;border-right:1px solid black;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '510px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'Jepara, ' .  $tglcetak,'width' => '260px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'Mengesahkan','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => '','width' => '260px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'PEJABAT PENATAUSAHAAN KEUANGAN','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => $bendaharajabatan,'width' => '260px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '510px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '510px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => $ppknama,'width' => '250px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
					array('data' => $bendaharanama,'width' => '260px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $ppknip,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'NIP.' . $bendaharanip,'width' => '250px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	
	$output.=createT($header, $rows);
		return $output;
}


function getLaporanbk2_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak){

	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'pimpinannama', 'pimpinanjabatan', 'pimpinanpangkat', 'pimpinannip', 'bendaharanama', 'bendaharanip', 'ppknama', 'ppknip', 'ppkjabatan'));
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
		$bendaharajabatan = 'BENDAHARA PENGELUARAN';

		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;
		
	}	
	
	if ($kodesuk!='ZZ') {
		$query = db_select('subunitkerja', 'u');
		$query->fields('u', array('bpnama', 'bpnip', 'namasuk'));
		$query->condition('u.kodeuk', $kodeuk, '=');
		$query->condition('u.kodesuk', $kodesuk, '=');
		//dpq($query);			
		# execute the query	
		$results = $query->execute();
		foreach ($results as $data) {
			
			$bendaharanama = $data->bpnama;
			$bendaharanip = $data->bpnip;
			$bendaharajabatan = 'BENDAHARA PENGELUARAN PEMBANTU';
			
			$namasuk = $data->namasuk;
			
		}	
	}

	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No.', 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'width' => '120px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'width' => '100px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'S a l d o', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	$awal_masuk = 0; $awal_keluar =0; $saldo =0;
	read_kas_sebelumnya($kodeuk, $kodesuk, $tglawal, $awal_masuk, $awal_keluar);
	$saldo = $awal_masuk - $awal_keluar;
	$total_masuk = 0; $total_keluar = 0;
	
	$rows[]=array(
		array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => 'Saldo sebelumnya', 'width' => '120px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($awal_masuk), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($awal_keluar), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($saldo) , 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//Content
	$n = 0;
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('subunitkerja', 's', 'b.kodesuk=s.kodesuk');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'tanggal', 'total', 'keperluan','kodesuk'));
	$query->fields('k', array('kegiatan'));
	$query->fields('s', array('namasuk'));

	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	
	if ($kodesuk=='ZZ')
		$query->condition('b.kodesuk', '0000', '<>');	
	else
		$query->condition('b.kodesuk', $kodesuk, '=');

	$or = db_or();
	$or->condition('b.jenis', 'pjr-in', '=');	
	$or->condition('b.jenis', 'pjr-out', '=');	
	$or->condition('b.jenis', 'tu-spj', '=');	
	$or->condition('b.jenis', 'gu-spj', '=');	
	$query->condition($or);
	
	$query->orderBy('b.tanggal');
	$query->orderBy('b.kodekeg');
	$query->orderBy('b.kodesuk');	
	
	//dpq($query);
	
	$results = $query->execute();
	
	foreach ($results as $data) {
		$n++;
		
		$masuk = 0; $keluar = 0;
		switch ($data->jenis) {
			case 'tu-spj':
			case 'gu-spj': {
					$keluar = $data->total;
					$keterangan = $data->kegiatan;
				} 
				break;

			case 'pjr-in': {
					$masuk = $data->total;
					$keterangan = $data->namasuk;
				}
				break;

			case 'pjr-out': {
					$keluar = $data->total;
					$keterangan = $data->namasuk;
				}
				break;
				
		}		

		$saldo = $saldo	+ $masuk - $keluar;
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan, 'width' => '120px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan, 'width' => '100px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($masuk), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($keluar), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($saldo), 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		);
		
		$total_masuk += $masuk; $total_keluar += $keluar; 
	}

	$rows[]=array(
		array('data' => 'TOTAL', 'colspan'=>4, 'width' => '300px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-left:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		
		array('data' => apbd_fn($total_masuk), 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		array('data' => apbd_fn($total_keluar), 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		array('data' => apbd_fn($saldo), 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-bottom:2px solid black;font-weight:bold;border-right:1px solid black;font-size:75%;'),
	);	
	$output.=createT($header, $rows);
		return $output;
}


?>
