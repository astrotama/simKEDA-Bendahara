<?php

function laporanberita_main($arg=NULL, $nama=NULL) {
    //ini_set('memory_limit', '2048M');
	if ($arg) {
		
		
		$exportpdf = arg(1);
		
		
		
		
	}
	
	if (isset($exportpdf) && ($exportpdf=='pdf'))  {	
		$output = getlaporanbk1(arg(2),arg(4));
		$marginatas=arg(3);
		//apbd_ExportPDF('L', 'F4', $output, 'BK-1');
		apbd_ExportBerita_P($output, $marginatas, 'BK-1');
		//printlaporanbk1($output, 'BK-8', $marginatas);
	
	} else {
		$output_form = drupal_get_form('laporanberita_main_form');
		return drupal_render($output_form);
		
	}	
	
}

function laporanberita_main_form ($form, &$form_state) {
	
	$kodeuk=81;
	$results=db_query("select * from beritaacara where kodeuk=:kodeuk",array(':kodeuk'=>$kodeuk));
	foreach($results as $data){
		$kertas=$data->kertas;
		$logam=$data->logam;
		$belumcair=$data->belumcair;
		$saldo=$data->saldo;
		$surat=$data->surat;
		$panjar=$data->panjar;
		$saldobku=$data->saldobku;
		$selisih=$data->selisih;
		$keterangan=$data->keterangan;
		
	}
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
	$form['kertas']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Uang Kertas', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $kertas,
	);
	$form['logam']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Uang Logam', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $logam,
	);
	$form['belum']= array(
		'#type'         => 'textfield', 
		'#title'        => 'SP2D dan alat pembayaran lainya yang belum cair', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $belumcair,
	);
	$form['saldo']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Saldo Bank', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $saldo,
	);
	$form['surat']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Surat/barang/benda berharga yang diizinkan', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $surat,
	);
	$form['uang']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Uang Panjar', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $panjar,
	);
	$form['saldobku']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Saldo Uang Menurut Buku Kas Umum', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $saldobku,
	);
	$form['selisih']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Perbedaan', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> $selisih,
	);
	$form['penjelasan']= array(
		'#type'         => 'textarea', 
		'#title'        => 'Penjelasan', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		//'#maxlength'    => 50, 
		'#default_value'=> $keterangan,
	);
	$form['tanggal']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Tanggal Cetak', 
		//'#attributes'	=> array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 50, 
		'#default_value'=> '',
	);
	$form['cetak']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Cetak',
		'#attributes' => array('class' => array('btn btn-primary btn-sm pull-right')),
	);	
	$form['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-save" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-primary btn-sm pull-right')),
	);	
	
	return $form;
}

function laporanberita_main_form_validate($form, &$form_state) {
	

	
}

function laporanberita_main_form_submit($form, &$form_state) {
	$marginatas = $form_state['values']['marginatas'];
	$tanggal = $form_state['values']['tanggal'];
	$kertas = $form_state['values']['kertas'];
	$logam = $form_state['values']['logam'];
	$belum = $form_state['values']['belum'];
	$saldo = $form_state['values']['saldo'];
	$saldo = $form_state['values']['saldo'];
	$surat = $form_state['values']['surat'];
	$uang = $form_state['values']['uang'];
	$saldobku = $form_state['values']['saldobku'];
	$selisih = $form_state['values']['selisih'];
	$penjelasan = $form_state['values']['penjelasan'];
	if(isSuperUser()){
		$kodeuk=81;
	}
	else{
		$kodeuk=apbd_getuseruk();
	}
	
	
	$res=db_query("select count($kertas) as tot from beritaacara where kodeuk=:kodeuk",array(':kodeuk'=>$kodeuk));
	$total=0;
	foreach($res as $data){
		$total=$data->tot;
	}
	
	if($form_state['clicked_button']['#value'] == $form_state['values']['cetak']) {
		drupal_goto('laporanberita/pdf/' . $kodeuk . '/'.$marginatas.'/'.$tanggal);
		
	}else if($form_state['clicked_button']['#value'] == $form_state['values']['submit']) {
		
		if($total>0){
			$res=db_update('beritaacara')
			->fields(array(
			  'kodeuk' =>$kodeuk,
			  'kertas' => $kertas,
			  'logam' => $logam,
			  'belumcair' => $belum,
			  'saldo'=>$saldo,
			  'surat'=>$surat,
			  'panjar'=>$uang,
			  'saldobku'=>$saldobku,
			  'selisih'=>$selisih,
			  'keterangan'=>$penjelasan,
			))
			->condition('kodeuk', $kodeuk, '=')
			->execute();
		} else{
			$res=db_insert('beritaacara')
			->fields(array(
			  'kodeuk' =>$kodeuk,
			  'kertas' => $kertas,
			  'logam' => $logam,
			  'belumcair' => $belum,
			  'saldo'=>$saldo,
			  'surat'=>$surat,
			  'panjar'=>$uang,
			  'saldobku'=>$saldobku,
			  'selisih'=>$selisih,
			  'keterangan'=>$penjelasan,
			))
			->execute();
		}
		
		drupal_set_message($total);
		drupal_set_message("Data Tersimpan");
	}
	
	
}


function getLaporanbk1($kodeuk,$tanggal){
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
	$results=db_query("select * from beritaacara where kodeuk=:kodeuk",array(':kodeuk'=>$kodeuk));
	foreach($results as $data){
		$kertas=$data->kertas;
		$logam=$data->logam;
		$belumcair=$data->belumcair;
		$saldo=$data->saldo;
		$surat=$data->surat;
		$panjar=$data->panjar;
		$saldobku=$data->saldobku;
		$selisih=$data->selisih;
		$keterangan=$data->keterangan;
		
	}
	$header=array();
	$rows[]=array(
		array('data' => 'PEMERINTAH KABUPATEN JEPARA', 'width' => '535px','align'=>'center','style'=>'border:none;font style:bold'),
		
	);
	$rows[]=array(
		array('data' => 'BERITA ACARA PEMERIKSAAN KAS', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'Pada hari ini ............... tanggal '.$tanggal . ' yang bertanda tangan di bawah ini:', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '100px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Nama Lengkap', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'left','style'=>'border:none;'),
		array('data' => $pimpinannama, 'width' => '325px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '100px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Jabatan', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'left','style'=>'border:none;'),
		array('data' => $pimpinanjabatan, 'width' => '325px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '70px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Sesuai dengan Peraturan Daerah Nomor  ...............  Tahun 2017 kami melakukan pemeriksaan setempat pada :', 'width' => '465px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '100px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Nama Lengkap', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'left','style'=>'border:none;'),
		array('data' => $bendaharanama, 'width' => '325px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '100px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Jabatan', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '10px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Bendahara Pengeluaran', 'width' => '325px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'berdasarkan Keputusan Bupati Nomor  ...............  Tanggal  ............... 2017 ditugaskan mengurus uang berdasarkan hasil pemeriksaan kas serta bukti-bukti yang berada dalam pengurusan itu, kami menemui kenyataan sebagai berikut:', 'width' => '535px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'Jumlah uang yang kami hitung dihadapan pejabat tersebut adalah :', 'width' => '535px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => 'a.', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Uang kertas', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($kertas), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => 'b.', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Uang logam', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($logam), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => 'c.', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'SP2D dan alat pembayaran lainya yang belum dicairkan', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($belumcair), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => 'd.', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Saldo Bank', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($saldo), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => 'e.', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Surat/barang/benda berharga yang diizinkan', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($surat), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => 'f.', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Uang Panjar', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($panjar), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'JUMLAH', 'width' => '190px','align'=>'right','style'=>'border:none;'),
		array('data' => '', 'width' => '10px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($kertas+$logam+$belumcair+$saldo+$surat+$panjar), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => '-', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Saldo uang menurut Buku Kas Umum', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp.', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn($saldobku), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Daerah, Register dan lain sebagainya berjumlah', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => '', 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => '-', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Perbedaan positif/negatif antara saldo kas dan Saldo Buku', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Rp', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => apbd_fn(($saldobku)-($kertas+$logam+$belumcair+$saldo+$surat+$panjar)), 'width' => '200px','align'=>'right','style'=>'border:none;'),
		
	);
	
	$rows[]=array(
		array('data' => '', 'width' => '35px','align'=>'left','style'=>'border:none;'),
		array('data' => '-', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		array('data' => 'Penjelasan perbedaan positif/negatif', 'width' => '200px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'left','style'=>'border:none;'),
		//array('data' => 'Rp', 'width' => '30px','align'=>'left','style'=>'border:none;'),
		array('data' => $keterangan, 'width' => '230px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '535px','align'=>'left','style'=>'border:none;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Jepara, '.$tanggal, 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'Yang diperiksa', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Yang memeriksa', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'Bendahara Pengeluaran', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Pengguna Anggaran / Kuasa Pengguna Anggaran', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => '', 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => $bendaharanama, 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => $pimpinannama, 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'NIP. '.$bendaharanip, 'width' => '265px','align'=>'center','style'=>'border:none;'),
		array('data' => 'NIP. '.$pimpinannip, 'width' => '265px','align'=>'center','style'=>'border:none;'),
		
	);
	$output.=createT($header, $rows);
	
		return $output;
}



?>
