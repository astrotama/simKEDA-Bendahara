<?php

 
function anggaran_import_form($form, &$form_state) {


	
	$form['notes'] = array(
		'#type' => 'markup',	
		'#markup' => '<div class="import-notes">Perhatian!<ul><li>Pastikan bahwa anggaran per kegitaan dan rekeningnya sudah benar dan sudah dissahkan.</li><li>Proses ini akan meng-update anggaran di Penatausahaan, Bendahara dan Akuntansi sekaligus.</li><li>Untuk meng-update anggaran, klik tombol <code>Update Anggaran</code> dibawah dan tunggu prosesnya sampai selesai.</li></ul></div>',
	);

	//SIMPAN 
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Update Anggaran',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
} 

function anggaran_import_form_validate($form, &$form_state) {

}
	 
function anggaran_import_form_submit($form, &$form_state) {
	db_set_active('anggaran');
	
	$res_keg = db_query('SELECT kodekeg,kegiatan FROM `kegiatanskpd` WHERE inaktif=0 order by kodeuk, kegiatan');
	$arr_kegiatan = $res_keg->fetchAll();
	db_set_active();
	
	$operations = array();
	
	//Reset
	
	delete_kegiatan_all();
	$operations[] = array(
						'anggaran_import_form_batch_processing',  // The function to run on each row
						array('Delete'),  // The row in the csv
					);
	
	foreach ($arr_kegiatan as $kegiatan) {
		//drupal_set_message($kegiatan->kegiatan);
		
		
		transfer_kegiatan($kegiatan->kodekeg);
		
		$operations[] = array(
							'anggaran_import_form_batch_processing',  // The function to run on each row
							array($kegiatan),  // The row in the csv
						);
		
	} 
	
	
	//Pembiayaan
	transfer_pembiayaan();	
	$operations[] = array(
						'anggaran_import_form_batch_processing',  // The function to run on each row
						array(apbd_kodekeg_pembiayaan()),  // The row in the csv
					);
	//Rekening	
	
	transfer_rekening();
	$operations[] = array(
						'anggaran_import_form_batch_processing',  // The function to run on each row
						array('Rekening'),  // The row in the csv
					);
	
	
	
	// Once everything is gathered and ready to be processed... well... process it!
	$batch = array( 
		'title' => t('Meng-update data anggaran...'),
		'operations' => $operations,  // Runs all of the queued processes from the while loop above.
		'finished' => 'anggaran_import_form_finished', // Function to run when the import is successful
		'error_message' => t('The installation has encountered an error.'),
		'progress_message' => t('Kegiatan ke @current dari @total kegiatan'),
	);
	batch_set($batch);
			
	//drupal_set_message('Selesai');
	
	
}

function transfer_kegiatan($kodekeg) {

	db_set_active('anggaran');
	
	//baca data dari kegiatan dari anggaran
	$res_keg = db_query('SELECT * FROM {kegiatanskpd} WHERE kodekeg=:kodekeg', array(':kodekeg' => $kodekeg));
	$arr_kegiatan = $res_keg->fetchAll();

	//baca data dari rekening kegiatan dari anggaran
	$res_rek = db_query('SELECT * FROM {anggperkeg} WHERE kodekeg=:kodekeg', array(':kodekeg' => $kodekeg));
	$arr_rek = $res_rek->fetchAll();
	
	//Transfer Penatausahaan
	db_set_active();
	foreach ($arr_kegiatan as $data_keg) {
		
		//drupal_set_message($data_keg->kegiatan);
		
		
		//delete first
		$num = db_delete('kegiatanskpd')
		  ->condition('kodekeg', $kodekeg)
		  ->execute();	
		
		$num = db_delete('anggperkeg')
		  ->condition('kodekeg', $kodekeg)
		  ->execute();	
		
		
		$isblud = 0;
		if (($data_keg->kodekeg=='201803351008') or ($data_keg->kodekeg=='201803351009')) {		//KDH
			$kodeuk = '02';
			$kodesuk = $kodeuk . '01';
			$kodepa = $kodesuk . '01';
			
		} else {
			if ($data_keg->isppkd=='1')
				$kodeuk = '00';
			else
				$kodeuk = $data_keg->kodeuk;
			
			if ($data_keg->kegiatan=='Pelayanan Kesehatan Puskesmas')
				$isblud = 1;
			else
				$isblud = 0;
			
			if ($data_keg->kodesuk=='')
				$kodesuk = $kodeuk . '01';
			else
				$kodesuk = $data_keg->kodesuk;

			if ($data_keg->kodepa=='')
				$kodepa = $kodeuk . '01';
			else
				$kodepa = $data_keg->kodepa;
		}		
		
		
		//ReInput 
		$num = db_insert('kegiatanskpd') // Table name no longer needs {}
			->fields(array(
				'kodekeg' => $data_keg->kodekeg, 
				'jenis' => $data_keg->jenis, 
				'tahun' => $data_keg->tahun, 
				'kodepro' => $data_keg->kodepro, 

				'kodeuk' => $kodeuk, 
				'kodesuk' => $kodesuk,
				'kodepa' => $kodepa,				

				'kegiatan' => $data_keg->kegiatan, 
				'isgaji' => $data_keg->isgaji,
				'isppkd' => $data_keg->isppkd,
				'total' => $data_keg->total, 
				'sumberdana1' => $data_keg->sumberdana1, 
				'programsasaran' => $data_keg->programsasaran, 
				'programtarget' => $data_keg->programtarget, 
				'masukansasaran' => $data_keg->masukansasaran, 
				'masukantarget' => $data_keg->masukantarget, 
				'keluaransasaran' => $data_keg->keluaransasaran, 
				'keluarantarget' => $data_keg->keluarantarget, 
				'hasilsasaran' => $data_keg->hasilsasaran, 
				'hasiltarget' => $data_keg->hasiltarget,  
				'tw1' => $data_keg->tw1, 
				'tw2' => $data_keg->tw2, 
				'tw3' => $data_keg->tw3, 
				'tw4' => $data_keg->tw4, 
				'anggaran'=> $data_keg->total, 
				
				'isblud' => $isblud,
				
				'sumberdana2' => $data_keg->sumberdana2,
				'waktupelaksanaan' => $data_keg->waktupelaksanaan,
				'latarbelakang' => $data_keg->latarbelakang,
				'kelompoksasaran' => $data_keg->kelompoksasaran,
				  
			))
			->execute();	
		
		
		
		//Rekening	
		foreach ($arr_rek as $data_rek) {
			
			//drupal_set_message('Rekening : ' . $data_rek->kodero);
			
			
			$num = db_insert('anggperkeg') // Table name no longer needs {}
				->fields(array(
					'kodekeg' => $data_rek->kodekeg,
					'kodero' => $data_rek->kodero,
					'uraian' => $data_rek->uraian,
					'jumlah' => $data_rek->jumlah,
					'anggaran' => $data_rek->jumlah,				 
					'jumlahsebelum' => $data_rek->jumlahsebelum,
				))
				->execute();	
			//if ($num) drupal_set_message('Input Item OK');	
			
		}
		
	}

	

}


function transfer_pembiayaan() {

	db_set_active('anggaran');
	
	//baca data dari kegiatan dari anggaran
	$res_keg = db_query('SELECT SUM(jumlah) AS jumlahpembiayaan FROM {anggperda} WHERE left(kodero,2)=:kodekeluar', array(':kodekeluar' => '62'));
	foreach ($res_keg as $data) {
		$jumlahpembiayaan = $data->jumlahpembiayaan;
	}

	//drupal_set_message($jumlahpembiayaan);
	
	//baca data dari rekening kegiatan dari anggaran
	$res_rek = db_query('SELECT * FROM {anggperda} WHERE left(kodero,2)=:kodekeluar', array(':kodekeluar' => '62'));
	$arr_rek = $res_rek->fetchAll();
	
	//Transfer Penatausahaan
	db_set_active();

	//delete first
	$num = db_delete('kegiatanskpd')
	  ->condition('kodekeg', apbd_kodekeg_pembiayaan())
	  ->execute();	
	
	$num = db_delete('anggperkeg')
	  ->condition('kodekeg', apbd_kodekeg_pembiayaan())
	  ->execute();	
	
	//ReInput 
	$num = db_insert('kegiatanskpd') // Table name no longer needs {}
		->fields(array(
			'kodekeg' => apbd_kodekeg_pembiayaan(), 
			'jenis' => '1', 
			'tahun' => apbd_tahun(), 
			'kodepro' => '000', 
			'kodeuk' => '00', 
			'kegiatan' => 'Kegiatan Pembiayaan', 
			'kodesuk' => '0001', 
			'isgaji' => '0',
			'isppkd' => '1',
			'total' => $jumlahpembiayaan, 
			'sumberdana1' => 'APBD', 
			'programsasaran' => '-', 
			'programtarget' => '-', 
			'masukansasaran' => '-', 
			'masukantarget' => '-', 
			'keluaransasaran' => '-', 
			'keluarantarget' => '-', 
			'hasilsasaran' => '-', 
			'hasiltarget' => '-',  
			'tw1' => 0, 
			'tw2' => 0, 
			'tw3' => 0, 
			'tw4' => 0, 
			'anggaran'=> $jumlahpembiayaan, 
			
			'sumberdana2' => '-',
			'waktupelaksanaan' => '-',
			'latarbelakang' => '-',
			'kelompoksasaran' => '-',
			  
		))
		->execute();	
	
	//Rekening	
	foreach ($arr_rek as $data_rek) {
		
		//drupal_set_message('Rekening : ' . $data_rek->kodero);
		
		
		$num = db_insert('anggperkeg') // Table name no longer needs {}
			->fields(array(
				'kodekeg' => apbd_kodekeg_pembiayaan(),
				'kodero' => $data_rek->kodero,
				'uraian' => $data_rek->uraian,
				'jumlah' => $data_rek->jumlah,
				'anggaran' => $data_rek->jumlah,				 
				'jumlahsebelum' => $data_rek->jumlahsebelum,
			))
			->execute();	
		//if ($num) drupal_set_message('Input Item OK');	
		
	}
 
	
}

function transfer_rekening() {

	db_set_active('anggaran');
	
	//baca data dari rekening kegiatan dari anggaran
	$res_rek = db_query('SELECT * FROM {jenis}');
	$arr_jenis = $res_rek->fetchAll();

	$res_rek = db_query('SELECT * FROM {obyek}');
	$arr_obyek = $res_rek->fetchAll();

	$res_rek = db_query('SELECT * FROM {rincianobyek}');
	$arr_rincianobyek = $res_rek->fetchAll();
	
	//Transfer Penatausahaan
	db_set_active();

	//delete first
	$num = db_delete('jenis')->execute();	
	$num = db_delete('obyek')->execute();	
	$num = db_delete('rincianobyek')->execute();	
	
	
	//ReInput 
	foreach ($arr_jenis as $data) {
		$num = db_insert('jenis') // Table name no longer needs {}
			->fields(array(
				'kodej' =>  $data->kodej, 
				'kodek' => $data->kodek, 
				'uraian' => $data->uraian, 
			))
			->execute();	
	}
	foreach ($arr_obyek as $data) {
		$num = db_insert('obyek') // Table name no longer needs {}
			->fields(array(
				'kodej' =>  $data->kodej, 
				'kodeo' => $data->kodeo, 
				'uraian' => $data->uraian, 
			))
			->execute();	
	}	
	foreach ($arr_rincianobyek as $data) {
		$num = db_insert('rincianobyek') // Table name no longer needs {}
			->fields(array(
				'kodero' =>  $data->kodero, 
				'kodeo' => $data->kodeo, 
				'uraian' => $data->uraian, 
			))
			->execute();	
	}	
	
	
}



function anggaran_import_form_batch_processing($data) {
	drupal_set_message('Hai');
	
}

function anggaran_import_form_finished() {
  drupal_set_message(t('Update anggaran selesai...'));
}

function delete_kegiatan_all() {
	$num = db_delete('kegiatanskpd')->execute();	
	$num = db_delete('anggperkeg')->execute();	
	  
}
?>
