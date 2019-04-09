<?php

function kuitansi_a22_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');

	$bendid = arg(2);	
	$kodero = arg(3);	
	$print = arg(4);	
	//drupal_set_message($dokid);
	if($print=='a22') {			

		$tanggal = arg(5);	
		$penerimanama = arg(6);	
		$penerimanalamat = arg(7);	
		$jumlah = arg(8);
		
		$bendaharanama = arg(9); 
		$bendaharanip  = arg(10);
		$bendaharajabatan = arg(11);

		$ppknama = arg(12); 
		$ppknip  = arg(13);
		
		$output = printspp_a22($bendid, $kodero, $tanggal, $penerimanama, $penerimanalamat, $jumlah, $bendaharanama, $bendaharanip, $bendaharajabatan, $ppknama, $ppknip);
		apbd_ExportSPP_No_Footer($output, 'A2-2');


	} else {
	
		$output_form = drupal_get_form('kuitansi_a22_main_form');
		return drupal_render($output_form);// . $output;
	}		
		
}

function kuitansi_a22_main_form($form, &$form_state) {
   
	$referer = $_SERVER['HTTP_REFERER'];

		
	$bendid = arg(2);
	$kodero = arg(3);

	$kodeuk = apbd_getuseruk();
	
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->join('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->join('rincianobyek', 'ro', 'bi.kodero=ro.kodero');
	$query->join('unitkerja', 'uk', 'b.kodeuk=uk.kodeuk');
	$query->join('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	
	$query->fields('b', array('tanggal', 'keperluan', 'penerimanama'));
	$query->fields('bi', array('jumlah'));
	$query->fields('ro', array('uraian'));
	$query->fields('k', array('kegiatan', 'kodesuk'));
	$query->fields('uk', array('ppknama', 'ppknip', 'bendaharanama', 'bendaharanip'));
	
	//$query->fields('u', array('namasingkat'));
	$query->condition('b.bendid', $bendid, '=');
	$query->condition('bi.kodero', $kodero, '=');

	$results = $query->execute();
	foreach ($results as $data) {
		$kegiatan = $data->kegiatan;
		$rekening = $data->uraian;
		$keperluan = $data->keperluan;

		$jumlah = $data->jumlah;
		$terbilang = apbd_terbilang($data->jumlah);
		
		$tanggal = apbd_fd_long($data->tanggal);
		
		$ppknama = $data->ppknama;
		$ppknip = $data->ppknip;

		$bendaharanama = $data->bendaharanama;
		$bendaharanip = $data->bendaharanip;
		$bendaharajabatan = 'BENDAHARA PENGELUARAN';
		
		$penerimanama = $data->penerimanama;
		
		$kodesuk = $data->kodesuk;
	}
	
	//SUK
	if (isUserPembantu()) {
		$query = db_select('subunitkerja', 's');
		$query->fields('s', array('bpnama', 'bpnip', 'kabidnama', 'kabidnip'));
		$query->condition('s.kodesuk', $kodesuk, '=');

		$results = $query->execute();
		foreach ($results as $data) {			
			$bendaharanama = $data->bpnama;
			$bendaharanip = $data->bpnip;
			$bendaharajabatan = 'BENDAHARA PENGELUARAN PEMBANTU';
			$ppknama = $data->kabidnama;
			$ppknip = $data->kabidnip;
			
		}	
	}

	$form['bendid'] = array (
		'#type' => 'value',
		'#value' => $bendid,
	);
	$form['kodero'] = array (
		'#type' => 'value',
		'#value' => $kodero,
	);
	
	$form['kegiatan'] = array (
		'#title' =>  t('Kegiatan'),
		//'#type' => 'textfield',
		//'#default_value' => $keperluan,
		//'#type' => 'textfield',
		'#type' => 'item',
		'#markup' => '<p>' . $kegiatan . '</p>',
	);
	$form['rekening'] = array (
		'#title' =>  t('Rekening'),
		//'#type' => 'textfield',
		//'#default_value' => $keperluan,
		//'#type' => 'textfield',
		'#type' => 'item',
		'#markup' => '<p>' . $rekening . '</p>',
	);
	
	$form['keperluan'] = array (
		'#title' =>  t('Keperluan'),
		//'#type' => 'textfield',
		//'#default_value' => $keperluan,
		//'#type' => 'textfield',
		'#type' => 'item',
		'#markup' => '<p>' . $keperluan . '</p>',
	);

	$form['jumlah'] = array (
		'#title' =>  t('Jumlah'),
		//'#type' => 'item',
		'#type' => 'textfield',
		'#attributes' => array('style' => 'text-align: right'),	
		'#default_value' => $jumlah,
		//'#markup' => '<p align="right">' . $jumlah . '</p>',
	);

	$form['formpenerima'] = array (
		'#type' => 'fieldset',
		'#title'=> 'PENERIMA',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
		$form['formpenerima']['tanggal']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Tanggal'),
			//'#required' => TRUE,
			'#default_value'=> $tanggal, 
		);	
		$form['formpenerima']['penerimanama']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Nama'),
			//'#required' => TRUE,
			'#default_value'=> $penerimanama, 
		);	
		$form['formpenerima']['penerimanalamat']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Alamat'),
			//'#required' => TRUE,
			'#default_value'=> '', 
		);	
		
	$form['formbendahara'] = array (
		'#type' => 'fieldset',
		'#title'=> 'BENDAHARA PEMBANTU',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
		$form['formbendahara']['bendaharanama']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Nama'),
			//'#required' => TRUE,
			'#default_value'=> $bendaharanama, 
		);	
		$form['formbendahara']['bendaharanip']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Nama'),
			//'#required' => TRUE,
			'#default_value'=> $bendaharanip, 
		);	
		$form['formbendahara']['bendaharajabatan']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Jabatan'),
			//'#required' => TRUE,
			'#default_value'=> $bendaharajabatan, 
		);	

	$form['formppk'] = array (
		'#type' => 'fieldset',
		'#title'=> 'PIMPINAN PROGRAM KEGIATAN',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
		$form['formppk']['ppknama']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Nama'),
			//'#required' => TRUE,
			'#default_value'=> $ppknama, 
		);	
		$form['formppk']['ppknip']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('NIP'),
			//'#required' => TRUE,
			'#default_value'=> $ppknip, 
		);	
		
		$form['submit']= array(
		'#type' => 'submit',
		'#value' =>  '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Cetak',
		'#attributes' => array('class' => array('btn btn-info btn-sm')),
		//'#disabled' => TRUE,
		'#suffix' => "&nbsp;<a href='" . $referer . "' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Batal</a>",
		
	);
	return $form;
}

function kuitansi_a22_main_form_submit($form, &$form_state) {
	$bendid = $form_state['values']['bendid'];
	$kodero = $form_state['values']['kodero'];
	$tanggal = $form_state['values']['tanggal'];
	$penerimanama = $form_state['values']['penerimanama'];
	$penerimanalamat = $form_state['values']['penerimanalamat'];
	$jumlah = $form_state['values']['jumlah'];

	$bendaharanama = $form_state['values']['bendaharanama'];
	$bendaharanip = $form_state['values']['bendaharanip'];
	$bendaharajabatan = $form_state['values']['bendaharajabatan'];

	$ppknama = $form_state['values']['ppknama'];
	$ppknip = $form_state['values']['ppknip'];
	
	if ($tanggal=='') $tanggal = 'x';
	if ($penerimanama=='') $penerimanama = 'x';
	if ($penerimanalamat=='') $penerimanalamat = 'x';
	
	drupal_goto('kuitansi/edita2/' . $bendid . '/' . $kodero . '/a22/' . $tanggal . '/'. $penerimanama . '/'. $penerimanalamat . '/' . 
		$jumlah . '/' . $bendaharanama . '/' . $bendaharanip . '/' . $bendaharajabatan . '/' . $ppknama . '/' . $ppknip );
	
}

function printspp_a22($bendid, $kodero, $tanggal, $nama, $alamat, $jumlah, $bendaharanama, $bendaharanip, $bendaharajabatan, $ppknama, $ppknip) {
	
	if ($tanggal=='x') $tanggal = '';
	if ($nama=='x') $nama = '';
	if ($alamat=='x') $alamat = '';
	
	//READ UP
	$kodeuk = apbd_getuseruk();
	$query = db_select('bendahara' . $kodeuk, 'b');
	$query->join('unitkerja', 'u', 'b.kodeuk=u.kodeuk');
	$query->join('bendaharaitem' . $kodeuk, 'bi', 'b.bendid=bi.bendid');
	$query->join('rincianobyek', 'ro', 'bi.kodero=ro.kodero');
	$query->join('kegiatanskpd', 'k', 'b.kodekeg=k.kodekeg');
	
	$query->fields('b', array('keperluan'));
	$query->fields('ro', array('uraian'));
	$query->fields('k', array('kegiatan', 'kodepro', 'kodekeg'));
	$query->fields('u', array('ppknama', 'ppknip', 'kodedinas'));
	
	//$query->fields('u', array('namasingkat'));
	$query->condition('b.bendid', $bendid, '=');
	$query->condition('bi.kodero', $kodero, '=');

	dpq ($query);
	
	$results = $query->execute();
	foreach ($results as $data) {
		$kegiatan = $data->kodedinas . '.' . $data->kodepro . '.' . substr($data->kodekeg, -3) . ' - ' . $data->kegiatan;
		$rekening = $kodero . ' - ' . $data->uraian;
		$keperluan = $data->keperluan;
		
	}

	$terbilang = apbd_terbilang($jumlah);
	
	$header=array();
	$rows[]=array(
		array('data' => '', 'width' => '300px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '80px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'A2-2', 'width' => '110px','align'=>'right','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '510px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => 'PEMERINTAH KABUPATEN JEPARA', 'width' => '510px','align'=>'center','style'=>'border:none;font-size:150%;font-weight:bold'),
	);
	$rows[]=array(
		array('data' => 'Nama Kegiatan', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => $kegiatan, 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => 'Rekening', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => $rekening, 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => 'Tahun anggaran', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => apbd_tahun(), 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '510px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => 'TANDA BUKTI PENGELUARAN', 'width' => '510px','align'=>'center','style'=>'border:none;font-size:150%;font-weight:bold;text-decoration:underline;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '375px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => 'Sudah terima dari', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => 'PEMERINTAH KABUPATEN JEPARA', 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => 'Uang sejumlah', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => $terbilang, 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => 'Untuk', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => $keperluan, 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '400px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '510px','align'=>'left','style'=>'border:none;'),
	);
	
	$rows[]=array(
		array('data' => '<div style="vertical-align:middle;">TERBILANG Rp </div>', 'width' => '115px','rowspan'=>'3','align'=>'left','style'=>'border-top:2px solid black;border-bottom:2px solid black;font-size:150%;vertical-align: middle;'),
		array('data' => '#' . apbd_fn($jumlah), 'width' => '135px','rowspan'=>'3','align'=>'right','style'=>'border-top:2px solid black;border-bottom:2px solid black;font-size:175%;vertical-align: middle;'),
		array('data' => '', 'width' => '25px','rowspan'=>'3','align'=>'left','style'=>'border:none'),
		array('data' => 'Jepara, ' . $tanggal, 'width' => '225px','align'=>'left','style'=>'border:none;'),
	);
	
	$rows[]=array(
		array('data' => 'Yang berhak menerima', 'width' => '260px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => 'Tanda tangan', 'width' => '90px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '135px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '275px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Nama', 'width' => '90px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => $nama, 'width' => '135px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '275px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Alamat', 'width' => '90px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'center','style'=>'border:none;'),
		array('data' => $alamat, 'width' => '135px','align'=>'left','style'=>'border-bottom:0.5px dashed grey;'),
	);
	$rows[] = array(
					array('data' => 'Setuju dibayarkan,','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					
	);
	$rows[] = array(
					array('data' => 'PIMPINAN PROGRAM KEGIATAN','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					array('data' => $bendaharajabatan,'width' => '255px', 'align'=>'center','style'=>'border:none;'),
					
	);
	$rows[] = array(
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					
	);
	/*
	$rows[] = array(
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					
	);
	*/
	$rows[] = array(
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					
	);
	$rows[] = array(
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '255px', 'align'=>'center','style'=>'border:none;'),
					
	);
	$rows[] = array(
					array('data' => $ppknama,'width' => '255px', 'align'=>'center','style'=>'border:none;font-weight:bold;text-decoration:underline;'),
					array('data' => $bendaharanama,'width' => '255px', 'align'=>'center','style'=>'border:none;font-weight:bold;text-decoration:underline;'),
					
	);
	$rows[] = array(
					array('data' => 'NIP. ' . $ppknip,'width' => '255px', 'align'=>'center','style'=>'border:none;'),
					array('data' => 'NIP. ' . $bendaharanip,'width' => '255px', 'align'=>'center','style'=>'border:none;'),
					
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
		return $output;
	}



?>
