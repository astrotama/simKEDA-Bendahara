<?php

function laporanrekap_main($arg=NULL, $nama=NULL) {
    
	if ($arg) {
		
		
		$kodeuk = arg(1);
		$tglawal = arg(2);
		$tglakhir = arg(3);
		
		$exportpdf = arg(4);
		$marginatas = arg(5);
		
		
		
	} else {
		$kodeuk = '81';
		
		$tglawal =  '2017-1-1';
		$tglakhir =  '2017-1-1';
		
	}
	
	//drupal_set_message($exportpdf);
	
	if (isset($exportpdf) && ($exportpdf=='pdf'))  {	
		$output = getlaporanbk3($kodeuk, $kodesuk, $kodepajak, $tglawal, $tglakhir, $tglcetak);
		//apbd_ExportPDF('P', 'F4', $output, 'BK-1');
		apbd_ExportPDF_P($output, $marginatas, 'BK-3');
		//printlaporanbk3($output, 'BK-8', $marginatas);
	
	} else {
		$output_form = drupal_get_form('laporanrekap_main_form');
		return drupal_render($output_form);
		
	}	
	
}

function laporanrekap_main_form ($form, &$form_state) {
	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = apbd_getuseruk();

	$kodesuk = arg(2);
	if ($kodesuk=='') {
		if (isUserSKPD())
			$kodesuk = 'ZZ';
		else
			$kodesuk = apbd_getusersuk();
	}
	$tglawal = arg(2);
	$tglakhir = arg(3);
	
	$exportpdf = arg(4);
	$marginatas = arg(5);

	if ($tglawal	=='') {
		//$kodeuk = apbd_getuseruk();
		$tglawal_form =  apbd_date_create_dateone_form();		//mktime(0, 0, 0, date('m'), 1, apbd_tahun());
		$tglakhir_form =  apbd_date_create_currdate_form();		//mktime(0, 0, 0, date('m'), date('d'), apbd_tahun());
	} else {
		
		$tglawal_form =  apbd_date_convert_db2form($tglawal);	//mktime(0, 0, 0, $arr_awal[1], $arr_awal[2], $arr_awal[0]);
		$tglakhir_form =  apbd_date_convert_db2form($tglakhir);	
	}
	$form['kodeuk']= array(
		'#type' 	=> 'value', 
		'#value'	=> $kodeuk, 
	);
	
	if (isUserPembantu()) {
		$form['kodesuk']= array(
			'#type' 	=> 'value', 
			'#value'	=> $kodesuk, 
		);
		
	} else {
		$opt_suk['ZZ'] = '- KESELURUHAN -';
		$query = db_select('subunitkerja', 's');
		$query->fields('s', array('kodesuk', 'namasuk'));
		$query->condition('s.kodeuk', $kodeuk, '=');
		//dpq($query);
		$results = $query->execute();
		foreach ($results as $data) {
			$opt_suk[$data->kodesuk] = $data->namasuk;
		}
		
		$form['kodesuk']= array(
			'#type'     => 'select', 
			'#title' =>  t('Bidang/Bagian'),
			'#options' =>  $opt_suk,
			'#default_value'	=> $kodesuk, 
		);

	}
	$opt_pajak['ZZ'] = '- KESELURUHAN -';
	$query = db_select('ltpajak', 's');
	$query->fields('s', array('kodepajak', 'uraian'));
	$results = $query->execute();
	foreach ($results as $data) {
		$opt_pajak[$data->kodepajak] = $data->uraian;
	}
	
	$form['kodepajak']= array(
		'#type'     => 'select', 
		'#title' =>  t('Jenis Pajak'),
		'#options' =>  $opt_pajak,
		'#default_value'	=> $kodepajak, 
	);

	$form['tglawal'] = array(
		'#type' => 'date',
		'#title' =>  t('Periode laporan, mulai tanggal'),
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
		'#type' => 'date',
		'#title' =>  t('Sampai dengan tanggal'),
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

	$form['tglcetak']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Tanggal Cetak', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		//'#size'         => 20, 
		//'#required'     => !$disabled, 
		'#disabled'     => false, 
		'#default_value'=> date('j F Y'), 
	);
	
	$form['marginatas']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Margin Atas', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		//'#size'         => 20, 
		//'#required'     => !$disabled, 
		'#disabled'     => false, 
		'#default_value'=> '15', 
	);
	
	$form['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Cetak',
		'#attributes' => array('class' => array('btn btn-primary btn-sm pull-right')),
	);	
	
	return $form;
}

function laporanrekap_main_form_validate($form, &$form_state) {
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);
	
	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);		
	if ($tglakhirx < $tglawalx) form_set_error('tglakhir', 'Tanggal laporan harus diisi dengan benar, dimana tanggal akhir tidak boleh lebih kecil daripada tanggal awal');

	
}

function laporanrekap_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	$kodepajak = $form_state['values']['kodepajak'];
	
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);
	
	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);		
	
	$tglcetak = $form_state['values']['tglcetak'];
	$marginatas = $form_state['values']['marginatas'];
	
	$uri = 'laporanbk3/' . $kodeuk . '/' . $kodesuk . '/' . $kodepajak . '/'  . $tglawalx . '/' . $tglakhirx . '/pdf/' . $tglcetak . '/' . $marginatas;
	
	drupal_goto($uri);
	
}

function read_kas_sebelumnya($kodeuk, $kodesuk, $kodepajak, $tglawal, &$masuk, &$keluar){

	//init
	$masuk = 0; $keluar = 0;
	
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->addExpression('SUM(bp.jumlah)', 'total');

	if ($kodesuk!='ZZ') $query->condition('b.kodesuk', $kodesuk, '=');
	if ($kodepajak!='ZZ') $query->condition('bp.kodepajak', $kodepajak, '=');
	
	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$results = $query->execute();
	
	foreach ($results as $data) {
		$masuk = $data->total;
	}
	$keluar = $masuk;
}

function getlaporanbk3($kodeuk, $kodesuk, $kodepajak, $tglawal, $tglakhir, $tglcetak){

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

		$bendaharanama = $data->bendaharanama;
		$bendaharanip = $data->bendaharanip;
		$bendaharajabatan = 'BENDAHARA PENGELUARAN';

		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;
		
	}	
	
	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '25px','align'=>'center','style'=>'border:none;'),	
		array('data' => 'BUKU PAJAK', 'width' => '460px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-3', 'width' => '25px','align'=>'center','style'=>'border:none;font-size:60%;'),
	);	
	$rows[]=array(
		array('data' => '', 'width' => '510px','align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'OPD', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
		array('data' => $namauk, 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
		
	);
	if ($kodesuk=='ZZ') {
		$rows[]=array(
			array('data' => 'Kepala OPD', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
			array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
			array('data' => $pimpinannama, 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
			
		);
		$rows[]=array(
			array('data' => 'Bendahara', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
			array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
			array('data' => $bendaharanama, 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
			
		);
		
	} else {
		//BIDANG/BAGIAN
		$query = db_select('subunitkerja', 'u');
		$query->fields('u', array('namasuk', 'bpnama', 'bpnip', 'kabidnama', 'kabidnip'));
		$query->condition('u.kodeuk', $kodeuk, '=');
		$query->condition('u.kodesuk', $kodesuk, '=');
		dpq($query);			
		# execute the query	
		$results = $query->execute();
		foreach ($results as $data) {
			$namasuk = $data->namasuk;
			
			$bpnama = $data->bpnama;
			
			$bendaharanama = $data->bpnama;
			$bendaharanip = $data->bpnip;

			$kabidnama = $data->kabidnama;
			
			$ppknama = $data->kabidnama;
			$ppknip = $data->kabidnip;
			
		}	

		$rows[]=array(
			array('data' => 'Bidang/Bagian', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
			array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
			array('data' => $namasuk, 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
			
		);
		$rows[]=array(
			array('data' => 'Kpl Bidang/Bagian', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
			array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
			array('data' => $kabidnama, 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
			
		);
		$rows[]=array(
			array('data' => 'Bendahara', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
			array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
			array('data' => $bpnama, 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
			
		);		
		
	}
	if ($kodepajak!='ZZ') {
		$query = db_select('ltpajak', 'p');
		$query->fields('p', array('uraian'));
		$query->condition('p.kodepajak', $kodepajak, '=');
		$results = $query->execute();
		foreach ($results as $data) {			
			$uraian = $data->uraian;
			
		}	
		$rows[]=array(
			array('data' => 'Jenis Pajak', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
			array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
			array('data' => $uraian, 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
			
		);		
	}
	$rows[]=array(
		array('data' => 'Bulan/Tanggal', 'width' => '75px','align'=>'left','style'=>'border:none;font-size:75%;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;font-size:75%;'),
		array('data' => apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '405px','align'=>'left','style'=>'border:none;font-size:75%;'),
		
	);		
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));

	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No.', 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'width' => '190px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Ket', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Pungut', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Setor', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'S a l d o', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	$awal_masuk = 0; $awal_keluar =0; $saldo =0;
	read_kas_sebelumnya($kodeuk, $kodesuk, $kodepajak, $tglawal, $awal_masuk, $awal_keluar);
	$saldo = $awal_masuk - $awal_keluar;
	$total_masuk = 0; $total_keluar = 0;
	
	$rows[]=array(
		array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => 'Saldo sebelumnya', 'width' => '190px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '60px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($awal_masuk), 'width' => '60px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($awal_keluar), 'width' => '60px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($saldo) , 'width' => '60px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//Content
	$n = 0;
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('bendaharapajak'. $kodeuk, 'bp', 'b.bendid=bp.bendid');
	$query->innerJoin('ltpajak', 'p', 'bp.kodepajak=p.kodepajak');
	$query->fields('b', array('bendid', 'tanggal', 'keperluan'));
	$query->fields('bp', array('jumlah'));
	$query->fields('p', array('uraian'));

	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('bp.jumlah', '0', '>');
	
	if ($kodesuk!='ZZ') $query->condition('b.kodesuk', $kodesuk, '=');
	if ($kodepajak!='ZZ') $query->condition('bp.kodepajak', $kodepajak, '=');	

	$query->orderBy('b.tanggal');
	$query->orderBy('b.bendid');
	
	$results = $query->execute();
	
	foreach ($results as $data) {
		$n++;
		
		$keluar = $data->jumlah;
		$masuk = $data->jumlah;

		$saldo = $saldo	+ $masuk - $keluar;
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan, 'width' => '190px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => $data->uraian, 'width' => '60px','align'=>'left','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($masuk), 'width' => '60px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($keluar), 'width' => '60px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($saldo), 'width' => '60px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		);
		
		$total_masuk += $masuk; $total_keluar += $keluar; 
	}
	
	$rows[]=array(
		array('data' => 'TOTAL', 'width' => '330px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-left:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		
		array('data' => apbd_fn($total_masuk), 'width' => '60px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		array('data' => apbd_fn($total_keluar), 'width' => '60px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;font-size:75%;'),
		array('data' => apbd_fn($saldo), 'width' => '60px','align'=>'right','style'=>'border-top:1px solid black;border-bottom:2px solid black;font-weight:bold;border-right:1px solid black;font-size:75%;'),
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
	
	if ($kodesuk=='ZZ') {
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
		
	} else {
		$rows[] = array(
						array('data' => 'KEPALA BIDANG/BAGIAN','width' => '250px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
						array('data' => 'BENDAHARA PENGELUARAN PEMBANTU','width' => '260px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
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
	}
	$output.=createT($header, $rows);
		return $output;
}


?>
