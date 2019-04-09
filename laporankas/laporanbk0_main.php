<?php

function laporanbk0_main($arg=NULL, $nama=NULL) {
    
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
		if (strlen($kodesuk)==4) {
			$output = getlaporanbk0($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
			apbd_ExportPDF_L($output, $marginatas, 'BK0-' . $kodesuk . '-' . $tglawal . '-' . $tglakhir . '.PDF');
			//return $output;
			
		} else {
			$output = getlaporanbk_seksi($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak);
			//return $output;
			apbd_ExportPDF_L($output, $marginatas, 'BK0_' . $kodesuk . '-' . $tglawal . '-' . $tglakhir . '.PDF');
		}
		//apbd_ExportPDF_L($output, $marginatas, 'BK-1');
		//printlaporanbk0($output, 'BK-8', $marginatas);
	
	} else if (isset($exportpdf) && ($exportpdf=='xls'))  {	
		$output = getlaporanbk0_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir);
		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename=Laporan_BK0_" . $kodesuk . "-" . $tglawal . "-" . $tglakhir . ".xls" );
		header("Pragma: no-cache"); 
		header("Expires: 0");
		echo $output;		
	
	} else if (isset($exportpdf) && ($exportpdf=='tmpl'))  {
		
		//drupal_set_message('x');
		
		$output_form = drupal_get_form('laporanbk0_main_form');
		$output = getlaporanbk0_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir);
		return drupal_render($output_form). '<p align="center">. . . . .</p>' . $output;
		
	}else {
		$output_form = drupal_get_form('laporanbk0_main_form');
		return drupal_render($output_form);
		
	}	
	
}

function laporanbk0_main_form ($form, &$form_state) {
	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = apbd_getuseruk();
	
	//$kodesuk = arg(2);
	//if ($kodesuk=='') $kodesuk = apbd_getusersuk();
	
	$tglawal = arg(3);
	$tglakhir = arg(4);
	
	//drupal_set_message($tglawal);

	
	if ($tglawal == '') {
		//$tglawal_form =  apbd_date_create_dateone_form();		//mktime(0, 0, 0, date('m'), 1, apbd_tahun());
		//$tglakhir_form =  apbd_date_create_currdate_form();		//mktime(0, 0, 0, date('m'), date('d'), apbd_tahun());

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

	
	if (isUserSeksi()) {
		$form['kodesuk']= array(
			'#type'     => 'hidden', 
			'#default_value'	=> apbd_getusersuk(), 
		);
	
	} elseif (isUserPembantu()) { 

		$kodesuk = arg(2);
		if ($kodesuk=='') $kodesuk = apbd_getusersuk();
	
		$res = db_query('select kodepa, namapa from {PelakuAktivitas} where kodesuk=:kodesuk order by kodepa', array(':kodesuk'=>$kodesuk));
		foreach ($res as $datasek) {
			$opt_suk[$datasek->kodepa] = $datasek->namapa;
		}	
		
		$form['kodesuk']= array(
			'#type'     => 'select', 
			'#title' =>  t('Bidang/Bagian/Seksi'),
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',
			'#options' =>  $opt_suk,
			'#default_value'	=> $kodesuk, 
		);	
		
	} else {
		
		$kodesuk = arg(2);
		if ($kodesuk=='') $kodesuk = apbd_getusersuk();

		$results = db_query('select kodesuk, namasuk from {subunitkerja} where kodeuk=:kodeuk order by kodesuk', array(':kodeuk'=>$kodeuk));
		foreach ($results as $data) {
			$opt_suk[$data->kodesuk] = $data->namasuk;

			$res = db_query('select kodepa, namapa from {PelakuAktivitas} where kodesuk=:kodesuk order by kodepa', array(':kodesuk'=>$data->kodesuk));
			foreach ($res as $datasek) {
				$opt_suk[$datasek->kodepa] = '- ' . $datasek->namapa;
			}	
		}
		
		$form['kodesuk']= array(
			'#type'     => 'select', 
			'#title' =>  t('Bidang/Bagian/Seksi'),
			'#options' =>  $opt_suk,
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',
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

function laporanbk0_main_form_validate($form, &$form_state) {
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);
	
	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);		
	
	//drupal_set_message('a ' . $tglawalx);
	//drupal_set_message('k ' . $tglakhirx);
	
	if ($tglakhirx < $tglawalx) form_set_error('tglakhir', 'Tanggal laporan harus diisi dengan benar, dimana tanggal akhir tidak boleh lebih kecil daripada tanggal awal');

	$tglawaly = strtotime($tglawal);
	$tglakhiry = strtotime($tglakhir);
	$datediff =round (($tglakhiry - $tglawaly)/ (60 * 60 * 24));
	if ($datediff > 93) form_set_error('tglakhir', 'Periode maksimal laporan adalah 3 bulan, atur tanggal awal dan tanggal akhir sehingga periode laporannya tidak lebih dari dua bulan.');
	
}

function laporanbk0_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	
	$tglawal = dateapi_convert_timestamp_to_datetime($form_state['values']['tglawal']);
	$_SESSION["laporan_kas_tgl_awal"] = apbd_date_convert_db2form($tglawal);
	
	//drupal_set_message('x : ' . apbd_date_convert_db2form($tglawal));
	
	$tglakhir = dateapi_convert_timestamp_to_datetime($form_state['values']['tglakhir']);
	$_SESSION["laporan_kas_tgl_akhir"] = apbd_date_convert_db2form($tglakhir);		
	
	$tglcetak = $form_state['values']['tglcetak'];
	$marginatas = $form_state['values']['marginatas'];
	
	if($form_state['clicked_button']['#value'] == $form_state['values']['submit']) 
	$uri = '/laporanbk0/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/pdf/' . $tglcetak . '/' . $marginatas;
	elseif($form_state['clicked_button']['#value'] == $form_state['values']['submittmpl']) 
		$uri = '/laporanbk0/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/tmpl/' . $tglcetak . '/' . $marginatas;
	else
		$uri = '/laporanbk0/' . $kodeuk . '/' . $kodesuk . '/'  . $tglawal . '/' . $tglakhir . '/xls/' . $tglcetak . '/' . $marginatas;
	drupal_goto($uri);
	
}


function getlaporanbk0($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak){
	set_time_limit(0);
	//ini_set('memory_limit','640M');

	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'bendaharanama', 'bendaharanip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	//dpq($query);			
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {
		$namauk = $data->namauk;
		
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
	
	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '75px','align'=>'center','style'=>'border:none;'),
		array('data' => 'PEMERINTAH KABUPATEN JEPARA', 'width' => '725px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-0|BIDANG', 'width' => '75px','align'=>'right','style'=>'border:none;font-size:60%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '300px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => 'BUKU KAS UMUM BENDAHARA PEMBANTU', 'width' => '275px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => '', 'width' => '300px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	$rows[]=array(
		array('data' =>  $namauk, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  $namasuk, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  'Tanggal ' . apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	
	//Content	
	$header=null;
	$rows=null;
	if (arg(5) == 'xls'){
	$header[]=array(
		array('data' => 'No.',  'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Kegiatan/Rekening', 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan','width' => '220px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Ref','width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS PANJAR Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'KAS PANJAR Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	}else{
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Kegiatan/Rekening', 'rowspan'=>2, 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '220px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Ref', 'rowspan'=>2,'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS PANJAR', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	
	$header[]=array(
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	}
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'kodesuk', 'tanggal', 'jenis', 'total', 'keperluan', 'penerimanama'));
	$query->fields('k', array('kegiatan'));

	$or = db_or();
	//$or->condition('b.jenis', 'ls', '=');
	//$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '='); 
	$or->condition('b.jenis', 'pjr-in', '=');
	$or->condition('b.jenis', 'pjr-out', '='); 
	$or->condition('b.jenis', 'ret-spj', '=');
	$query->condition($or);
	
	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	//$query->condition('k.kodesuk', $kodesuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.bendid');
	//$query->orderBy('b.kodesuk');
	
	//dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {

			case 'ls':
			case 'gaji': {
					$panjartambah = $data->total;
					$panjarkurang = $data->total;	
					$belanjatambah = $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah = $data->total;
					$panjarkurang = $data->total;
				}
				break;

			case 'pjr-in': {
					$panjartambah = $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang = $data->total;
				}
				break;

			case 'ret-spj': {
					$belanjakurang = $data->total;
				}
				break;

		}
	
		//Keterangan PANJAR
		$keterangan = '';
		if ($data->jenis=='pjr-in') {
			/*
			$query = db_select('subunitkerja', 's');
			$query->fields('s', array('namasuk'));
			$query->condition('s.kodeuk', $kodeuk, '=');	
			$query->condition('s.kodesuk', $data->kodesuk, '=');	
			
			//dpq($query);
			
			$res_suk  = $query->execute();			
			foreach ($res_suk as $data_suk) {
				$keterangan = $data_suk->namasuk;
			}
			*/
			$keterangan = 'PENERIMAAN UANG PANJAR';
			
		} else if (($data->jenis=='pjr-in') or ($data->jenis=='pjr-out')) {
			$keterangan = 'PENGELUARAN UANG PANJAR';			

		} else {

			$keterangan = $data->kegiatan;
			
		}
			
		
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan, 'width' => '225px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan,'width' => '220px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->bendid,'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if (($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) {
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
					array('data' => $uraian, 'width' => '215px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '220px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '575px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjartambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	//PERIODE INI
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_panjar_ini = $total_panjartambah - $total_panjarkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_belanja_lalu = 0; $total_panjar_lalu = 0;
	read_kas_sebelumnya($kodeuk, $kodesuk, $tglawal, $total_panjar_lalu, $total_belanja_lalu);
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Panjar pada tanggal ' . apbd_fd_long($tglakhir), 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '150px','align'=>'right','style'=>'border-bottom:1px solid black;border-right:1px solid black;border-top:1px solid black;font-size:75%;'),
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
					array('data' => 'BENDAHARA PENGELUARAN','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'BENDAHARA PENGELUARAN PEMBANTU','width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => $bendaharanama,'width' => '435px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
					array('data' => $bpnama,'width' => '440px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $bendaharanip,'width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'NIP. ' . $bpnip,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'Mengetahui,','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => 'PIMPINAN PROGRAM KEGIATAN','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => $kabidnama,'width' => '875px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => 'NIP. ' . $kabidnip,'width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);

	$output.=createT($header, $rows);
	
		return $output;
}


function getlaporanbk0_tmpl($kodeuk, $kodesuk, $tglawal, $tglakhir){
	set_time_limit(0);
	//ini_set('memory_limit','640M');
	
	//Content	
	// $header=null;
	// $rows=null;
	$header=array();
	/*
	if (arg(5) == 'xls'){
		$header[] = array(
			array('data' => 'No.',  'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => 'Tanggal', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'Kegiatan/Rekening', 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'Keterangan','width' => '220px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'Ref','width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'KAS PANJAR Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
			array('data' => 'KAS PANJAR Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
			array('data' => 'PENGELUARAN Bertambah', 'colspan' => '2', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
			array('data' => 'PENGELUARAN Berkurang', 'colspan' => '2', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		);
	
	}else{
	*/	
		$header[] = array(
			array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'Kegiatan/Rekening', 'rowspan'=>2, 'colspan' => 2, 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'Keterangan', 'rowspan'=>2,'width' => '220px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'Ref', 'rowspan'=>2,'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'KAS PANJAR', 'colspan' => 2, 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
			array('data' => 'PENGELUARAN', 'colspan' => 2, 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		);
		
		
		$header[] = array(
			array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
			array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
			array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
			array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
			
		);
	//}
	
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'kodesuk', 'tanggal', 'jenis', 'total', 'keperluan', 'penerimanama'));
	$query->fields('k', array('kegiatan'));

	$or = db_or();
	//$or->condition('b.jenis', 'ls', '=');
	//$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '='); 
	$or->condition('b.jenis', 'pjr-in', '=');
	$or->condition('b.jenis', 'pjr-out', '='); 
	$or->condition('b.jenis', 'ret-spj', '=');
	$query->condition($or);
	
	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	//$query->condition('k.kodesuk', $kodesuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.bendid');
	//$query->orderBy('b.kodesuk');
	
	//dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {

			case 'ls':
			case 'gaji': {
					$panjartambah = $data->total;
					$panjarkurang = $data->total;	
					$belanjatambah = $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah = $data->total;
					$panjarkurang = $data->total;
				}
				break;

			case 'pjr-in': {
					$panjartambah = $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang = $data->total;
				}
				break;

			case 'ret-spj': {
					$belanjakurang = $data->total;
				}
				break;

		}
	
		//Keterangan PANJAR
		$keterangan = '';
		if ($data->jenis=='pjr-in') {
			/*
			$query = db_select('subunitkerja', 's');
			$query->fields('s', array('namasuk'));
			$query->condition('s.kodeuk', $kodeuk, '=');	
			$query->condition('s.kodesuk', $data->kodesuk, '=');	
			
			//dpq($query);
			
			$res_suk  = $query->execute();			
			foreach ($res_suk as $data_suk) {
				$keterangan = $data_suk->namasuk;
			}
			*/
			$keterangan = 'PENERIMAAN UANG PANJAR';
			
		} else if (($data->jenis=='pjr-in') or ($data->jenis=='pjr-out')) {
			$keterangan = 'PENGELUARAN UANG PANJAR';			

		} else {

			$keterangan = $data->kegiatan;
			
		}
			
		
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan, 'colspan' => 2,  'width' => '225px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan,'width' => '220px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->bendid,'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if (($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) {
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
					array('data' => $uraian, 'width' => '215px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '220px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		
	}

	$rows[]=array(
		array('data' => '', 'colspan'=>6, 'width' => '575px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjartambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);

	//PERIODE INI
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_panjar_ini = $total_panjartambah - $total_panjarkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'colspan'=>6,'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_panjar_ini), 'colspan'=>2,'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'colspan'=>2,'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_belanja_lalu = 0; $total_panjar_lalu = 0;
	read_kas_sebelumnya($kodeuk, $kodesuk, $tglawal, $total_panjar_lalu, $total_belanja_lalu);
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'colspan'=>6,'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu), 'colspan'=>2,'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'colspan'=>2,'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'colspan'=>6,'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'colspan'=>2,'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'colspan'=>2,'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Panjar pada tanggal ' . apbd_fd_long($tglakhir), 'colspan'=>6,'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'colspan'=>2,'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '','colspan'=>2, 'width' => '150px','align'=>'right','style'=>'border-bottom:1px solid black;border-right:1px solid black;border-top:1px solid black;font-size:75%;'),
	);	
	$output .= createT($header,$rows,null);
	//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
	return $output;
}


function getlaporanbk_seksi($kodeuk, $kodepa, $tglawal, $tglakhir, $tglcetak){
	set_time_limit(0);
	//ini_set('memory_limit','640M');

	$kodesuk = substr($kodepa,0,4);
	
	
	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'bendaharanama', 'bendaharanip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	//dpq($query);			
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {
		$namauk = $data->namauk;
		
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
		
		$bendaharanama = $data->bpnama;
		$bendaharanip = $data->bpnip;

	}		
	
	//PELAKU AKTIVITAS
	$query = db_select('PelakuAktivitas', 'u');
	$query->fields('u', array('namapa', 'bpnama', 'bpnip', 'pimpinannama', 'pimpinannip'));
	$query->condition('u.kodesuk', $kodesuk, '=');
	$query->condition('u.kodepa', $kodepa, '=');
	//dpq($query);			
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {
		$namapa = $data->namapa;
		
		$bpnama = $data->bpnama;
		$bpnip = $data->bpnip;
		$pptknama = $data->pimpinannama;
		$pptknip = $data->pimpinannip;

	}	
	
	
	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '75px','align'=>'center','style'=>'border:none;'),
		array('data' => 'PEMERINTAH KABUPATEN JEPARA', 'width' => '725px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-0|PPTK', 'width' => '75px','align'=>'right','style'=>'border:none;font-size:60%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '300px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => 'BUKU KAS UMUM BENDAHARA SEKSI/PPTK', 'width' => '275px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => '', 'width' => '300px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	$rows[]=array(
		array('data' =>  $namauk, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  $namasuk, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  $namapa, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  'Tanggal ' . apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	
	//Content	
	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Kegiatan/Rekening', 'rowspan'=>2, 'width' => '225px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '220px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Ref', 'rowspan'=>2,'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS PANJAR', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	
	$header[]=array(
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	
	//content
	//SPJ
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'kodesuk', 'tanggal', 'jenis', 'total', 'keperluan', 'penerimanama'));
	$query->fields('k', array('kegiatan'));

	$or = db_or();
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '='); 
	//$or->condition('b.jenis', 'seksi-in', '=');
	//$or->condition('b.jenis', 'seksi-out', '='); 
	$or->condition('b.jenis', 'ret-spj', '=');
	$query->condition($or);
	
	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('k.kodepa', $kodepa, '=');

	//PANJAR
	$query_panjar = db_select('bendahara' . $kodeuk, 'b');
	$query_panjar->fields('b', array('bendid', 'kodesuk', 'tanggal', 'jenis', 'total', 'keperluan', 'penerimanama', 'kodepa'));
	
	/*
	$or = db_or();
	$or->condition('b.jenis', 'seksi-in', '=');
	$or->condition('b.jenis', 'seksi-out', '='); 
	$query_panjar->condition($or);
	*/
	$query_panjar->condition('b.panjarseksi', '1', '=');	
	
	$query_panjar->condition('b.tanggal', $tglawal, '>=');	
	$query_panjar->condition('b.tanggal', $tglakhir, '<=');	
	$query_panjar->condition('b.kodeuk', $kodeuk, '=');
	$query_panjar->condition('b.kodesuk', $kodesuk, '=');
	$query_panjar->condition('b.kodepa', $kodepa, '=');

	
	$query->union($query_panjar);
	
	$query_panjar->orderBy('tanggal');
	$query_panjar->orderBy('bendid');
	
	dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {

			case 'ls':
			case 'gaji': {
					$panjartambah = $data->total;
					$panjarkurang = $data->total;	
					$belanjatambah = $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah = $data->total;
					$panjarkurang = $data->total;
				}
				break;

			case 'pjr-in':
			case 'seksi-in': {
					$panjartambah = $data->total;
				}
				break;
			
			case 'pjr-out':
			case 'seksi-out': {
					$panjarkurang = $data->total;
				}
				break;

			case 'ret-spj': {
					$belanjakurang = $data->total;
				}
				break;

		}
	
		//Keterangan PANJAR
		$keterangan = '';
		if  ($data->jenis=='seksi-in') {
			$keterangan = 'PENERIMAAN UANG PANJAR PPTK';
			
		} else if  ($data->jenis=='seksi-out') {
			$keterangan = 'PENGEMBALIAN UANG PANJAR PPTK';			

		} else if  ($data->jenis=='pjr-in') {
			$keterangan = 'PENERIMAAN UANG PANJAR PPTK';			

		} else if  ($data->jenis=='pjr-out') {
			$keterangan = 'PENGEMBALIAN UANG PANJAR PPTK';			

		} else {

			$keterangan = $data->kegiatan;
			
		}
			
		
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan, 'width' => '225px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan,'width' => '220px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->bendid,'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if (($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) {
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
					array('data' => $uraian, 'width' => '215px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '220px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '','width' => '50px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '575px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjartambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	//PERIODE INI
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_panjar_ini = $total_panjartambah - $total_panjarkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_belanja_lalu = 0; $total_panjar_lalu = 0;
	read_kas_sebelumnya_seksi($kodeuk, $kodesuk, $kodepa, $tglawal, $total_panjar_lalu, $total_belanja_lalu);
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Panjar pada tanggal ' . apbd_fd_long($tglakhir), 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '150px','align'=>'right','style'=>'border-bottom:1px solid black;border-right:1px solid black;border-top:1px solid black;font-size:75%;'),
	);
	
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'Jepara, ' . $tglcetak,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	); 
	$rows[] = array(
					array('data' => 'Mengetahui,','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => '','width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'BENDAHARA PENGELUARAN BIDANG','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'PEJABAT PELAKSANA TEKNIS KEGIATAN','width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => $bendaharanama,'width' => '435px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
					array('data' => $pptknama,'width' => '440px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $bendaharanip,'width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'NIP. ' . $pptknip,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);


	$output.=createT($header, $rows);
	
		return $output;
}

function getlaporanbk01($kodeuk, $kodesuk, $tglawal, $tglakhir, $tglcetak){
	set_time_limit(0);
	//ini_set('memory_limit','640M');

	//SKPD
	$query = db_select('unitkerja', 'u');
	$query->fields('u', array('namauk', 'namasingkat', 'bendaharanama', 'bendaharanip'));
	$query->condition('u.kodeuk', $kodeuk, '=');
	//dpq($query);			
	# execute the query	
	$results = $query->execute();
	foreach ($results as $data) {
		$namauk = $data->namauk;
		
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
	
	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '25px','align'=>'center','style'=>'border:none;'),
		array('data' => 'PEMERINTAH KABUPATEN JEPARA', 'width' => '825px','align'=>'center','style'=>'border:none;'),
		array('data' => 'BK-0', 'width' => '25px','align'=>'center','style'=>'border:none;font-size:60%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '300px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => 'BUKU KAS UMUM BENDAHARA PEMBANTU', 'width' => '275px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => '', 'width' => '300px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	$rows[]=array(
		array('data' =>  $namauk, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  $namasuk, 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$rows[]=array(
		array('data' =>  'Tanggal ' . apbd_fd_long($tglawal) . ' s/d. ' . apbd_fd_long($tglakhir), 'width' => '875px','align'=>'center','style'=>'border:none;font-size:80%;'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	
	//Content	
	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '230px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '265px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS/PANJAR', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	
	$header[]=array(
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'kodesuk', 'tanggal', 'jenis', 'total', 'keperluan', 'penerimanama', 'kodesuk'));
	$query->fields('k', array('kegiatan'));

	$or = db_or();
	$or->condition('b.jenis', 'ls', '=');
	$or->condition('b.jenis', 'gaji', '=');
	$or->condition('b.jenis', 'tu-spj', '=');
	$or->condition('b.jenis', 'gu-spj', '='); 
	$or->condition('b.jenis', 'pjr-in', '=');
	$or->condition('b.jenis', 'pjr-out', '='); 
	$or->condition('b.jenis', 'ret-spj', '=');
	$query->condition($or);
	
	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	//$query->condition('k.kodesuk', $kodesuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.kodekeg');
	$query->orderBy('b.kodesuk');
	
	//dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {

			case 'ls':
			case 'gaji': {
					$panjartambah = $data->total;
					$panjarkurang = $data->total;	
					$belanjatambah = $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah = $data->total;
					$panjarkurang = $data->total;
				}
				break;

			case 'pjr-in': {
					$panjartambah = $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang = $data->total;
				}
				break;

			case 'ret-spj': {
					$belanjakurang = $data->total;
				}
				break;

		}
	
		//Keterangan PANJAR
		$keterangan = '';
		if (($data->jenis=='pjr-in') or ($data->jenis=='pjr-out')) {
			$query = db_select('subunitkerja', 's');
			$query->fields('s', array('namasuk'));
			$query->condition('s.kodeuk', $kodeuk, '=');	
			$query->condition('s.kodesuk', $data->kodesuk, '=');	
			
			//dpq($query);
			
			$res_suk  = $query->execute();			
			foreach ($res_suk as $data_suk) {
				$keterangan = $data_suk->namasuk;
			}
			
		} else {

			$keterangan = $data->kegiatan;
			
		}
			
		
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan, 'width' => '230px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan,'width' => '265px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if (($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) {
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
					array('data' => $uraian, 'width' => '220px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '265px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '575px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjartambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	//PERIODE INI
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_panjar_ini = $total_panjartambah - $total_panjarkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_belanja_lalu = 0; $total_panjar_lalu = 0;
	read_kas_sebelumnya($kodeuk, $kodesuk, $tglawal, $total_panjar_lalu, $total_belanja_lalu);
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Panjar pada tanggal ' . apbd_fd_long($tglakhir), 'width' => '575px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
		array('data' => '', 'width' => '150px','align'=>'right','style'=>'border-bottom:1px solid black;border-right:1px solid black;border-top:1px solid black;font-size:75%;'),
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
					array('data' => 'BENDAHARA PENGELUARAN','width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'BENDAHARA PENGELUARAN PEMBANTU','width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => $bendaharanama,'width' => '435px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
					array('data' => $bpnama,'width' => '440px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $bendaharanip,'width' => '435px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
					array('data' => 'NIP. ' . $bpnip,'width' => '440px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
	);
	$rows[] = array(
					array('data' => 'Mengetahui,','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => 'PIMPINAN PROGRAM KEGIATAN','width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '875px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => $kabidnama,'width' => '875px', 'align'=>'center','style'=>'border:none;text-decoration:underline;font-size:75%;'),
				);
	$rows[] = array(
					array('data' => 'NIP. ' . $kabidnip,'width' => '875px', 'align'=>'center','style'=>'border:none;font-size:75%;'),
				);

	$output.=createT($header, $rows);
	
		return $output;
}


function read_kas_sebelumnya($kodeuk, $kodesuk, $tglawal, &$panjar, &$belanja){

	//init
	$belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
	
	//belanja tambah
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->addExpression('SUM(b.total)', 'total');
	$query->fields('b', array('jenis'));

	//NON LS
	$query->condition('b.jenis', 'ls', '<>');	
	$query->condition('b.jenis', 'gaji', '<>');	
	
	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->groupBy('b.jenis');
		
	$results = $query->execute();
	
	foreach ($results as $data) {
		switch ($data->jenis) {
			case 'ls':
			case 'gaji': {
					$panjartambah += $data->total;
					$panjarkurang += $data->total;	
					$belanjatambah += $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah += $data->total;
					$panjarkurang += $data->total;

				}
				break;

			case 'pjr-in': {
					$panjartambah += $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang += $data->total;
				}
				break;
				
		}	
	}
	
	//belanja kurang
	$query = db_select('bendahara' . $kodeuk, 'b');
	//$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('b.jenis', 'ret-spj', '=');
		
	$results = $query->execute();
	
	foreach ($results as $data) {
		$belanjakurang = $data->total;
	}
		
	$belanja = $belanjatambah - $belanjakurang;
	$panjar = $panjartambah - $panjarkurang;
}

function read_kas_sebelumnya_seksi($kodeuk, $kodesuk, $kodepa, $tglawal, &$panjar, &$belanja){

	//init
	$belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
	
	//belanja tambah
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->addExpression('SUM(b.total)', 'total');
	$query->fields('b', array('jenis'));

	//NON LS
	$query->condition('b.jenis', 'ls', '<>');	
	$query->condition('b.jenis', 'gaji', '<>');	
	
	//NON PANJAR, query dipisah
	$query->condition('b.jenis', 'seksi-in', '<>');	
	$query->condition('b.jenis', 'seksi-out', '<>');	

	//NON PANJAR BIDANG
	$query->condition('b.jenis', 'pjr-in', '<>');	
	$query->condition('b.jenis', 'pjr-out', '<>');	
	
	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.kodesuk', $kodesuk, '=');
	$query->condition('k.kodepa', $kodepa, '=');
	$query->groupBy('b.jenis');
		
	$results = $query->execute();
	
	foreach ($results as $data) {
		switch ($data->jenis) {
			case 'ls':
			case 'gaji': {
					$panjartambah += $data->total;
					$panjarkurang += $data->total;	
					$belanjatambah += $data->total;
				}
				break;

			case 'tu-spj':
			case 'gu-spj': {
					$belanjatambah += $data->total;
					$panjarkurang += $data->total;

				}
				break;

			case 'seksi-in': {
					$panjartambah += $data->total;
				}
				break;

			case 'seksi-out': {
					$panjarkurang += $data->total;
				}
				break;
				
		}	
	}
	
	//PANJAR PPTK
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');
	$query->fields('b', array('jenis'));
	
	$or = db_or();
	$or->condition('b.jenis', 'seksi-in', '=');	
	$or->condition('b.jenis', 'seksi-out', '=');	
	$query->condition($or);
	
	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('b.kodepa', $kodepa, '=');
	$query->groupBy('b.jenis');
		
	$results = $query->execute();
	
	foreach ($results as $data) {
		switch ($data->jenis) {

			case 'seksi-in': {
					$panjartambah += $data->total;
				}
				break;

			case 'seksi-out': {
					$panjarkurang += $data->total;
				}
				break;
				
		}	
	}	
	
	//belanja kurang
	$query = db_select('bendahara' . $kodeuk, 'b');
	//$query->innerJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->addExpression('SUM(b.total)', 'total');

	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('b.kodesuk', $kodesuk, '=');
	$query->condition('b.jenis', 'ret-spj', '=');
		
	$results = $query->execute();
	
	foreach ($results as $data) {
		$belanjakurang = $data->total;
	}
		
	$belanja = $belanjatambah - $belanjakurang;
	$panjar = $panjartambah - $panjarkurang;
}


?>
