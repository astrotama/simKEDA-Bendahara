<?php

function laporanbk1b_main($arg=NULL, $nama=NULL) {
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
		$batch=arg(9);
		
		
	} else {
		$kodeuk = '81';
		$jenis = 1;		//variable_get('apbdtahun', 0);
		
		$tglawal =  '2017-1-1';
		$tglakhir =  '2017-1-1';
		
	}
	
	if (isset($exportpdf) && ($exportpdf=='pdf'))  {
		//drupal_set_message($batch;
		if ($batch == '0') {
			$_SESSION["bk8-batch"] = '';
			$output = getLaporanbk1($kodeuk, $jenis, $tglawal, $tglakhir, $tglcetak);
			apbd_ExportPDF_L($output, $marginatas, 'BK-1');

		} else if ($batch == '1') {
			$_SESSION["bk8-batch"] = $batch . '-';
			$output = getLaporanbk1_pertama($kodeuk, $jenis, $tglawal, $tglakhir, $tglcetak);
			apbd_ExportPDF_L($output, $marginatas, 'BK-1');
			
		} else if ($last == 'last') {
			$_SESSION["bk8-batch"] = $batch . '-';
			getLaporanbk1_terakhir($batch, $kodeuk, $jenis, $tglawal, $tglakhir, $tglcetak, $output1, $output2);
			apbd_ExportPDF_L($output, $marginatas, 'BK-1');
			
		}else{
			$output = getLaporanbk1_lembar_kegiatan($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak,$batch);
			//apbd_ExportPDF('L', 'F4', $output, 'BK-1');
			apbd_ExportPDF_L($output, $marginatas, 'BK-1');
			//printlaporanbk1($output, 'BK-1', $marginatas);
		}	
		
	
	}else if (isset($exportpdf) && ($exportpdf=='cron'))  {	
		apbd_bk1_paging();
	
	}
	else {
		$output_form = drupal_get_form('laporanbk1b_main_form');
		return drupal_render($output_form);
		
	}	
	
}

function laporanbk1b_main_form ($form, &$form_state) {
	
	$kodeuk = arg(1);
	if ($kodeuk=='') $kodeuk = apbd_getuseruk();

	$jenis = arg(2);
	$tglawal = arg(3);
	$tglakhir = arg(4);
	$detil = arg(5);

	
	if ($tglawal	=='') {
		//$kodeuk = apbd_getuseruk();
		$jenis = '1';
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
	$form['jenis']= array(
		'#type'     => 'value', 
		'#value'	=> $jenis, 
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
	$form['detil']= array(
		'#type'         => 'checkbox', 
		'#title'        => 'Detil (Tampilkan Rekening)', 
		'#default_value'=> $detil, 
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
	$arr_page = array();
	$lastpage = 0;
	$arr_page[0] = 'Keseluruhan';
	$res_page = db_query('select distinct batch from {kegiatanbk1} where kodeuk=:kodeuk order by batch', array(':kodeuk' => $kodeuk));
	foreach ($res_page as $data) {
		$arr_page[$data->batch] = 'Bagian ke-' . $data->batch;
		$lastpage = $data->batch;
	}
	
	if ($lastpage > 1) {
		$form['lembar']= array(
			'#type'         => 'select', 
			'#title'        => 'Bagian ke-', 
			'#options'  => $arr_page, 
			'#description'  => 'Untuk OPD besar dengan lebih dari 15 kegiatan dan mengalami kesulitan dalam mencetak BK-1, disarankan untuk mencetak BK-1 per bagian.',
			'#default_value'=> '1', 
		);	
	
	} else {
		$form['lembar']= array(
			'#type'         => 'value', 
			'#value'=> '0', 
		);	
	}
	$form['lastpage']= array(
		'#type' 	=> 'value', 
		'#value'	=> $lastpage, 
	);
	$form['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Cetak',
		'#attributes' => array('class' => array('btn btn-primary btn-sm pull-right')),
	);	
	
	return $form;
}

function laporanbk1b_main_form_validate($form, &$form_state) {
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);
	
	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);		
	if ($tglakhirx < $tglawalx) form_set_error('tglakhir', 'Tanggal laporan harus diisi dengan benar, dimana tanggal akhir tidak boleh lebih kecil daripada tanggal awal');

	
}

function laporanbk1b_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$jenis = $form_state['values']['jenis'];
	
	$tglawal = $form_state['values']['tglawal'];
	$tglawalx = apbd_date_convert_form2db($tglawal);
	$detil = $form_state['values']['detil'];
	
	$tglakhir = $form_state['values']['tglakhir'];
	$tglakhirx = apbd_date_convert_form2db($tglakhir);		
	
	$tglcetak = $form_state['values']['tglcetak'];
	$marginatas = $form_state['values']['marginatas'];
	
	$batch = $form_state['values']['lembar'];
	$lastpage = $form_state['values']['lastpage'];
	$lastpage = $form_state['values']['lastpage'];
	if ($batch==$lastpage) 
		$last = 'last';
	else
		$last = 'no';
	$uri = 'laporanbk1b/' . $kodeuk . '/' . $jenis . '/'  . $tglawalx . '/' . $tglakhirx . '/'. $detil . '/pdf/' . $tglcetak . '/' . $marginatas. '/' . $batch. '/' . $lastpage;
	
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
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '210px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '135px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PANJAR', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	
	$header[]=array(
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'tanggal', 'total', 'keperluan', 'penerimanama', 'kodesuk'));
	$query->fields('k', array('kegiatan'));

	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.kodekeg');
	$query->orderBy('b.kodesuk');
	
	dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_kastambah = 0; $total_kaskurang = 0; $total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {
			case 'up':
			case 'tu':
			case 'gu-kas': {
					$kastambah = $data->total;
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
					if ($data->kodesuk=='0000') {
						$kaskurang = $data->total;
						
					}	else {
						$panjarkurang = $data->total;
					}
				}
				break;

			case 'pjr-in': {
					$kaskurang = $data->total;
					$panjartambah = $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang = $data->total;
					$kastambah = $data->total;
				}
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
			array('data' => $data->keperluan, 'width' => '210px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan,'width' => '135px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kastambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kaskurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if  ((($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) and ($detil)) {
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
					array('data' => $uraian, 'width' => '200px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '135px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_kastambah += $kastambah; $total_kaskurang += $kaskurang; 
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '425px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kastambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_kaskurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjartambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	//PERIODE INI
	$total_kas_ini = $total_kastambah - $total_kaskurang;
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_panjar_ini = $total_panjartambah - $total_panjarkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_kas_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_kas_lalu = 0; $total_belanja_lalu = 0; $total_panjar_lalu = 0;
	read_kas_sebelumnya($kodeuk, $jenis, $tglawal, $total_kas_lalu, $total_belanja_lalu, $total_panjar_lalu);
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjar_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Kas pada tanggal ' . apbd_fd_long($tglakhir), 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini + $total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
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

function getLaporanbk1_pertama($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak){
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
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '210px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '135px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PANJAR', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	
	$header[]=array(
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->innerJoin('kegiatanbk1', 'bk', 'k.kodekeg=bk.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'tanggal', 'total', 'keperluan', 'penerimanama', 'kodesuk'));
	$query->fields('k', array('kegiatan'));

	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('bk.batch', 1, '=');
	$query->condition('bk.kodeuk', $kodeuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.kodekeg');
	$query->orderBy('b.kodesuk');
	
	dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_kastambah = 0; $total_kaskurang = 0; $total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {
			case 'up':
			case 'tu':
			case 'gu-kas': {
					$kastambah = $data->total;
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
					if ($data->kodesuk=='0000') {
						$kaskurang = $data->total;
						
					}	else {
						$panjarkurang = $data->total;
					}
				}
				break;

			case 'pjr-in': {
					$kaskurang = $data->total;
					$panjartambah = $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang = $data->total;
					$kastambah = $data->total;
				}
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
			array('data' => $data->keperluan, 'width' => '210px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan,'width' => '135px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kastambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kaskurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if  ((($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) and ($detil)) {
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
					array('data' => $uraian, 'width' => '200px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '135px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_kastambah += $kastambah; $total_kaskurang += $kaskurang; 
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '875px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		
	);
	
	//PERIODE INI
	
	$output.=createT($header, $rows);
	
		return $output;
	
}

function getLaporanbk1_lembar_kegiatan($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak,$batch){
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
	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '210px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '135px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PANJAR', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	
	$header[]=array(
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	$nomor=0;
	$result=db_query("select count(b.bendid) as nomor from bendahara b inner Join kegiatanbk1 as bk on b.kodekeg=bk.kodekeg where b.tanggal>=:tglawal and b.tanggal<=:tglakhir and  b.kodeuk=:kodeuk and bk.batch<:batch",array(':tglawal'=>$tglawal,':tglakhir'=>$tglakhir,':kodeuk'=>$kodeuk,':batch'=>$batch));
	foreach($result as $dat){
		$nomor=$dat->nomor;
	}
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->innerJoin('kegiatanbk1', 'bk', 'k.kodekeg=bk.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'tanggal', 'total', 'keperluan', 'penerimanama', 'kodesuk'));
	$query->fields('k', array('kegiatan'));

	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('bk.batch', $batch, '=');
	$query->condition('bk.kodeuk', $kodeuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.kodekeg');
	$query->orderBy('b.kodesuk');
	
	dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_kastambah = 0; $total_kaskurang = 0; $total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {
			case 'up':
			case 'tu':
			case 'gu-kas': {
					$kastambah = $data->total;
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
					if ($data->kodesuk=='0000') {
						$kaskurang = $data->total;
						
					}	else {
						$panjarkurang = $data->total;
					}
				}
				break;

			case 'pjr-in': {
					$kaskurang = $data->total;
					$panjartambah = $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang = $data->total;
					$kastambah = $data->total;
				}
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
			
		$nomor++;
		$rows[]=array(
			array('data' => $nomor, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan, 'width' => '210px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan,'width' => '135px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kastambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kaskurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if  ((($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) and ($detil)) {
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
					array('data' => $uraian, 'width' => '200px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '135px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_kastambah += $kastambah; $total_kaskurang += $kaskurang; 
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '875px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		
	);
	
	//PERIODE INI
	
	$output.=createT($header, $rows);
	
		return $output;
	
}

function getLaporanbk1_terakhir($kodeuk, $jenis, $tglawal, $tglakhir, $detil, $tglcetak,$batch){
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
	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No.', 'rowspan'=>2, 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
		array('data' => 'Tanggal', 'rowspan'=>2, 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Uraian', 'rowspan'=>2, 'width' => '210px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'Keterangan', 'rowspan'=>2,'width' => '135px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'KAS', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PENGELUARAN', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
		array('data' => 'PANJAR', 'width' => '150px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;font-size:75%;'),
	);
	
	
	$header[]=array(
		array('data' => 'Masuk', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Keluar', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Bertambah', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => 'Berkurang', 'width' => '75px','align'=>'center','style'=>'border-right:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		
	);
	$nomor=0;
	$result=db_query("select count(b.bendid) as nomor from bendahara b inner Join kegiatanbk1 as bk on b.kodekeg=bk.kodekeg where b.tanggal>=:tglawal and b.tanggal<=:tglakhir and  b.kodeuk=:kodeuk and bk.batch<:batch",array(':tglawal'=>$tglawal,':tglakhir'=>$tglakhir,':kodeuk'=>$kodeuk,':batch'=>$batch));
	foreach($result as $dat){
		$nomor=$dat->nomor;
	}
	//content
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->leftJoin('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	$query->innerJoin('kegiatanbk1', 'bk', 'k.kodekeg=bk.kodekeg');
	$query->fields('b', array('bendid', 'jenis', 'tanggal', 'total', 'keperluan', 'penerimanama', 'kodesuk'));
	$query->fields('k', array('kegiatan'));

	$query->condition('b.tanggal', $tglawal, '>=');	
	$query->condition('b.tanggal', $tglakhir, '<=');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->condition('bk.batch', $batch, '=');
	$query->condition('bk.kodeuk', $kodeuk, '=');
	$query->orderBy('b.tanggal');
	$query->orderBy('b.kodekeg');
	$query->orderBy('b.kodesuk');
	
	dpq ($query);
	
	$results = $query->execute();
	
	$n = 0;
	$total_kastambah = 0; $total_kaskurang = 0; $total_belanjatambah = 0; $total_belanjakurang = 0; 
	$total_panjartambah = 0; $total_panjarkurang = 0;
	
	foreach ($results as $data) {
		$n++;
		
		$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
		switch ($data->jenis) {
			case 'up':
			case 'tu':
			case 'gu-kas': {
					$kastambah = $data->total;
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
					if ($data->kodesuk=='0000') {
						$kaskurang = $data->total;
						
					}	else {
						$panjarkurang = $data->total;
					}
				}
				break;

			case 'pjr-in': {
					$kaskurang = $data->total;
					$panjartambah = $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang = $data->total;
					$kastambah = $data->total;
				}
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
			
		$nomor++;
		$rows[]=array(
			array('data' => $nomor, 'width' => '30px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fd($data->tanggal), 'width' => '50px','align'=>'center','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $data->keperluan, 'width' => '210px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => $keterangan,'width' => '135px','align'=>'left','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kastambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($kaskurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjartambah), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
			array('data' => apbd_fn($panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-top:0.5px solid grey;border-right:1px solid black;font-size:75%;'),
			
		);
		
		//REKENING
		if  ((($data->jenis=='ls') or ($data->jenis=='gaji') or ($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) and ($detil)) {
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
					array('data' => $uraian, 'width' => '200px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => $data_rek->keterangan,'width' => '135px','align'=>'left','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_tambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => apbd_fn($rek_belanja_kurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-style: italic;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;font-size:75%;'),
					array('data' => '', 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
					
				);
			}	
		}

		//TOTAL
		$total_kastambah += $kastambah; $total_kaskurang += $kaskurang; 
		$total_belanjatambah += $belanjatambah; $total_belanjakurang += $belanjakurang; 
		$total_panjartambah += $panjartambah; $total_panjarkurang += $panjarkurang;		
		
	}
	
	$rows[]=array(
		array('data' => '', 'width' => '425px','align'=>'center','style'=>'border-top:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kastambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_kaskurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjatambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanjakurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjartambah), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjarkurang), 'width' => '75px','align'=>'right','style'=>'border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;font-size:75%;'),
	);
	
	//PERIODE INI
	$total_kas_ini = $total_kastambah - $total_kaskurang;
	$total_belanja_ini = $total_belanjatambah - $total_belanjakurang;
	$total_panjar_ini = $total_panjartambah - $total_panjarkurang;
	$rows[]=array(
		array('data' => 'Jumlah pada tanggal ' . apbd_fd_long($tglawal) . ' s/d ' . apbd_fd_long($tglakhir) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		array('data' => apbd_fn($total_kas_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	
	//PERIODE LALU
	$total_kas_lalu = 0; $total_belanja_lalu = 0; $total_panjar_lalu = 0;
	read_kas_sebelumnya($kodeuk, $jenis, $tglawal, $total_kas_lalu, $total_belanja_lalu, $total_panjar_lalu);
	$rows[]=array(
		array('data' => 'Jumlah sebelum tanggal ' . apbd_fd_long($tglawal) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjar_lalu), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Jumlah kumulatif s/d tanggal ' . apbd_fd_long($tglakhir) , 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),

		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_belanja_lalu + $total_belanja_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		array('data' => apbd_fn($total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
	);
	$rows[]=array(
		array('data' => 'Sisa Kas pada tanggal ' . apbd_fd_long($tglakhir), 'width' => '425px','align'=>'right','style'=>'border-right:1px solid black;font-size:75%;'),
		
		
		array('data' => apbd_fn($total_kas_lalu + $total_kas_ini + $total_panjar_lalu + $total_panjar_ini), 'width' => '150px','align'=>'right','style'=>'border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;font-size:75%;'),
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

	//$output.=createT($header, $rows);
	
	//PERIODE INI
	
	$output.=createT($header, $rows);
	
	return $output;
	
}


function read_kas_sebelumnya($kodeuk, $jenis, $tglawal, &$kas, &$belanja, &$panjar){

	//init
	$kastambah = 0; $kaskurang = 0; $belanjatambah = 0; $belanjakurang = 0; $panjartambah = 0; $panjarkurang = 0;
	
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->addExpression('SUM(b.total)', 'total');
	$query->fields('b', array('jenis','kodesuk'));

	$query->condition('b.tanggal', $tglawal, '<');	
	$query->condition('b.kodeuk', $kodeuk, '=');
	$query->groupBy('b.jenis');
	$query->groupBy('b.kodesuk');
		
	$results = $query->execute();
	
	foreach ($results as $data) {
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
					if ($data->kodesuk=='0000') {
						$kaskurang += $data->total;
						
					}	else {
						$panjarkurang += $data->total;

					}
				}
				break;

			case 'pjr-in': {
					$kaskurang += $data->total;
					$panjartambah += $data->total;
				}
				break;

			case 'pjr-out': {
					$panjarkurang += $data->total;
					$kastambah += $data->total;
				}
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
	$panjar = $panjartambah - $panjarkurang;
}
function apbd_bk1_paging() {
	$res_uk = db_query('select kodeuk from {unitkerja}');
	foreach ($res_uk as $datauk) {
		drupal_set_message($datauk->kodeuk);

		$num = db_delete('kegiatanbk1')
		  ->condition('kodeuk', $datauk->kodeuk)
		  ->execute();
		
		$batch = 1;
		$i  = 0;
		
		$res_keg = db_query('select kodekeg from {kegiatanskpd} where inaktif=0 and anggaran>0 and kodeuk=:kodeuk order by kodepro,kodekeg', array(':kodeuk' => $datauk->kodeuk));
		foreach ($res_keg as $datakeg) {
			drupal_set_message($datakeg->kodekeg);
			
			$lastkeg = $datakeg->kodekeg;
			
			$i++;

			$query = db_insert('kegiatanbk1') // Table name no longer needs {}
				->fields(array(
					  'kodekeg' => $datakeg->kodekeg,
					  'batch' => $batch,
					  'kodeuk' => $datauk->kodeuk,					  
				))
				->execute();	
			
			if ($batch==1) 
				$batas = 9; 
			else 
				$batas = 10;
			
			if ($i == $batas) {
				$batch++ ;
				$i = 0;
			}	
				
		}	//kodekeg	
		
		//last kegiatan
		/*
		$query = db_update('kegiatanbk1') // Table name no longer needs {}
		->fields(array(
			'batch' => $spjno,
			'tanggal' => $tanggal,
			'noref' => $noref,
			'kodesuk' => $kodesuk,
			'keperluan' => $keperluan,
			'jenispanjar' => $jenispanjar,			
			'total' => $total,

			  
		))
		->condition('bendid', $bendid, '=')
		->execute();	
		*/
		//$res_x = db_query('update {kegiatanbk1} set batch=batch+1 where kodekeg=:kodekeg', array(':kodekeg' => $lastkeg));	
		
	}	//kodeuk
}

?>
