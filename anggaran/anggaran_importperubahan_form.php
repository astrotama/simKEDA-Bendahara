<?php

 
function anggaran_importperubahan_form($form, &$form_state) {

	db_set_active('anggaran');
	
	$res = db_query('SELECT revisi FROM {setupapp}');
	foreach ($res as $data) {
		$revisi = $data->revisi;
	}
	db_set_active();
	
	drupal_set_title('Transfer Anggaran Revisi #' . $revisi);

	$form['revisi']= array(
		'#type'         => 'value', 
		'#title'        => 'Kegiatan',  
		//'#required'     => !$disabled, 
		'#value' => $revisi,
	);	
	$form['notes'] = array(
		'#type' => 'markup',	
		'#markup' => '<div class="import-notes">Perhatian!<ul><li>Pastikan bahwa anggaran per kegitaan dan rekeningnya sudah benar dan sudah dissahkan.</li><li>Proses ini akan meng-update anggaran di Penatausahaan, Bendahara dan Akuntansi sekaligus.</li><li>Untuk meng-update anggaran, klik tombol <code>Update Anggaran</code> dibawah dan tunggu prosesnya sampai selesai.</li></ul></div>',
	);	

	//SIMPAN 
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => 'Update Anggaran',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
} 

function anggaran_importperubahan_form_validate($form, &$form_state) {

}
	 
function anggaran_importperubahan_form_submit($form, &$form_state) {
	$revisi = $form_state['values']['revisi'];
	
	transfer_perubahan($revisi);
	
}

function transfer_perubahan($revisi) {
	
	$periode = $revisi + 1;
	
	db_set_active('anggaran');
	
	$res_keg = db_query('SELECT kodekeg,kegiatan FROM {kegiatanperubahan} WHERE inaktif=0 and periode=:periode order by kodeuk, kegiatan', array(':periode'=>$periode));
	$arr_kegiatan = $res_keg->fetchAll();
	db_set_active();
	
	$operations = array();
	
	foreach ($arr_kegiatan as $kegiatan) {
		//drupal_set_message($kegiatan->kegiatan);
		transfer_kegiatan($kegiatan->kodekeg);
 
		$operations[] = array(
							'anggaran_importperubahan_form_batch_processing',  // The function to run on each row
							array($kegiatan),  // The row in the csv
						);
		
	}


	// Once everything is gathered and ready to be processed... well... process it!
	$batch = array( 
		'title' => t('Meng-update data anggaran...'),
		'operations' => $operations,  // Runs all of the queued processes from the while loop above.
		'finished' => 'anggaran_importperubahan_form_finished', // Function to run when the import is successful
		'error_message' => t('The installation has encountered an error.'),
		'progress_message' => t('Kegiatan ke @current dari @total kegiatan'),
	);
	batch_set($batch);
			
	//drupal_set_message('Selesai');	
}



function transfer_penetapan() {
	db_set_active('anggaran');
	
	$res_keg = db_query('SELECT kodekeg,kegiatan FROM `kegiatanskpd` WHERE inaktif=0 and total>0 order by kodeuk, kegiatan');
	$arr_kegiatan = $res_keg->fetchAll();
	db_set_active();
	
	$operations = array();
	
	//Reset
	delete_kegiatan_all();
	$operations[] = array(
						'anggaran_importperubahan_form_batch_processing',  // The function to run on each row
						array('Delete'),  // The row in the csv
					);
	
	foreach ($arr_kegiatan as $kegiatan) {
		//drupal_set_message($kegiatan->kegiatan);
		transfer_kegiatan($kegiatan->kodekeg);
 
		$operations[] = array(
							'anggaran_importperubahan_form_batch_processing',  // The function to run on each row
							array($kegiatan),  // The row in the csv
						);
		
	}

	//Pembiayaan
	transfer_pembiayaan();	
	$operations[] = array(
						'anggaran_importperubahan_form_batch_processing',  // The function to run on each row
						array(apbd_kodekeg_pembiayaan()),  // The row in the csv
					);
	//Rekening	
	transfer_rekening();
	$operations[] = array(
						'anggaran_importperubahan_form_batch_processing',  // The function to run on each row
						array('Rekening'),  // The row in the csv
					);
	

	// Once everything is gathered and ready to be processed... well... process it!
	$batch = array( 
		'title' => t('Meng-update data anggaran...'),
		'operations' => $operations,  // Runs all of the queued processes from the while loop above.
		'finished' => 'anggaran_importperubahan_form_finished', // Function to run when the import is successful
		'error_message' => t('The installation has encountered an error.'),
		'progress_message' => t('Kegiatan ke @current dari @total kegiatan'),
	);
	batch_set($batch);
			
	//drupal_set_message('Selesai');	
}

function transfer_kegiatan($kodekeg) {

	db_set_active('anggaran');
	
	//baca data dari kegiatan dari anggaran
	$res_keg = db_query('SELECT * FROM {kegiatanperubahan} WHERE kodekeg=:kodekeg', array(':kodekeg' => $kodekeg));
	$arr_kegiatan = $res_keg->fetchAll();

	//baca data dari rekening kegiatan dari anggaran
	$res_rek = db_query('SELECT * FROM {anggperkegperubahan} WHERE kodekeg=:kodekeg', array(':kodekeg' => $kodekeg));
	$arr_rek = $res_rek->fetchAll();
	
	//Transfer Penatausahaan
	db_set_active();
	foreach ($arr_kegiatan as $data_keg) {
		
		drupal_set_message('Kegiatan : ' . $data_keg->kegiatan);
		
		//delete first
		$num = db_delete('kegiatanskpd')
		  ->condition('kodekeg', $kodekeg)
		  ->execute();	
		
		$num = db_delete('anggperkeg')
		  ->condition('kodekeg', $kodekeg)
		  ->execute();	

		//ReInput 
		$num = db_insert('kegiatanskpd') // Table name no longer needs {}
			->fields(array(
				'kodekeg' => $data_keg->kodekeg, 
				'jenis' => $data_keg->jenis, 
				'tahun' => $data_keg->tahun, 
				'kodepro' => $data_keg->kodepro, 
				'kodeuk' => $data_keg->kodeuk, 
				'kegiatan' => $data_keg->kegiatan, 
				'kodesuk' => $data_keg->kodesuk, 
				'isgaji' => $data_keg->isgaji,
				'inaktif' => $data_keg->inaktif,
				
				'sumberdana1' => $data_keg->sumberdana1, 
				
				'programsasaran' => $data_keg->programsasaran, 
				'programtarget' => $data_keg->programtarget, 
				'masukansasaran' => $data_keg->masukansasaran, 
				'masukantarget' => $data_keg->masukantarget, 
				'keluaransasaran' => $data_keg->keluaransasaran, 
				'keluarantarget' => $data_keg->keluarantarget, 
				'hasilsasaran' => $data_keg->hasilsasaran, 
				'hasiltarget' => $data_keg->hasiltarget,  

				'tw1penetapan' => $data_keg->tw1, 
				'tw2penetapan' => $data_keg->tw2, 
				'tw3penetapan' => $data_keg->tw3, 
				'tw4penetapan' => $data_keg->tw4, 

				'tw1' => $data_keg->tw1p, 
				'tw2' => $data_keg->tw2p, 
				'tw3' => $data_keg->tw3p, 
				'tw4' => $data_keg->tw4p, 

				'totalpenetapan' => $data_keg->total, 
				'total' => $data_keg->totalp, 
				'anggaran'=> $data_keg->totalp, 
				
				'waktupelaksanaan' => $data_keg->waktupelaksanaan,
				'latarbelakang' => $data_keg->latarbelakang,
				'kelompoksasaran' => $data_keg->kelompoksasaran,
				  
			))
			->execute();		  
		  
		//Rekening	
		foreach ($arr_rek as $data_rek) {
			
			drupal_set_message('Rekening : ' . $data_rek->kodero . ' - ' . $data_rek->jumlahp);
			$num = db_insert('anggperkeg') // Table name no longer needs {}
				->fields(array(
					'kodekeg' => $data_rek->kodekeg,
					'kodero' => $data_rek->kodero,
					'uraian' => $data_rek->uraian,
					'jumlahpenetapan' => $data_rek->jumlah,
					'jumlah' => $data_rek->jumlahp,
					'anggaran' => $data_rek->jumlahp,				 
					'jumlahsebelum' => $data_rek->jumlahsebelum,
				))
				->execute();	
			
			
		}
	}
 
	//Transfer Bendahara
	db_set_active('bendahara');
	foreach ($arr_kegiatan as $data_keg) {
		
		drupal_set_message('Kegiatan : ' . $data_keg->kegiatan);
		
		//delete first
		$num = db_delete('kegiatanskpd')
		  ->condition('kodekeg', $kodekeg)
		  ->execute();	
		
		$num = db_delete('anggperkeg')
		  ->condition('kodekeg', $kodekeg)
		  ->execute();	

		//ReInput 
		$num = db_insert('kegiatanskpd') // Table name no longer needs {}
			->fields(array(
				'kodekeg' => $data_keg->kodekeg, 
				'jenis' => $data_keg->jenis, 
				'tahun' => $data_keg->tahun, 
				'kodepro' => $data_keg->kodepro, 
				'kodeuk' => $data_keg->kodeuk, 
				'kegiatan' => $data_keg->kegiatan, 
				'kodesuk' => $data_keg->kodesuk, 
				'isgaji' => $data_keg->isgaji,
				'inaktif' => $data_keg->inaktif,
				
				'sumberdana1' => $data_keg->sumberdana1, 
				
				'programsasaran' => $data_keg->programsasaran, 
				'programtarget' => $data_keg->programtarget, 
				'masukansasaran' => $data_keg->masukansasaran, 
				'masukantarget' => $data_keg->masukantarget, 
				'keluaransasaran' => $data_keg->keluaransasaran, 
				'keluarantarget' => $data_keg->keluarantarget, 
				'hasilsasaran' => $data_keg->hasilsasaran, 
				'hasiltarget' => $data_keg->hasiltarget,  	

				'tw1penetapan' => $data_keg->tw1, 
				'tw2penetapan' => $data_keg->tw2, 
				'tw3penetapan' => $data_keg->tw3, 
				'tw4penetapan' => $data_keg->tw4, 

				'tw1' => $data_keg->tw1p, 
				'tw2' => $data_keg->tw2p, 
				'tw3' => $data_keg->tw3p, 
				'tw4' => $data_keg->tw4p, 

				'totalpenetapan' => $data_keg->total, 
				'total' => $data_keg->totalp, 
				'anggaran'=> $data_keg->totalp, 
				
				'waktupelaksanaan' => $data_keg->waktupelaksanaan,
				'latarbelakang' => $data_keg->latarbelakang,
				'kelompoksasaran' => $data_keg->kelompoksasaran,
				  
			))
			->execute();		  
		  
		//Rekening	
		foreach ($arr_rek as $data_rek) {
			
			drupal_set_message('Rekening : ' . $data_rek->kodero . ' - ' . $data_rek->jumlahp);
			$num = db_insert('anggperkeg') // Table name no longer needs {}
				->fields(array(
					'kodekeg' => $data_rek->kodekeg,
					'kodero' => $data_rek->kodero,
					'uraian' => $data_rek->uraian,
					'jumlahpenetapan' => $data_rek->jumlah,
					'jumlah' => $data_rek->jumlahp,
					'anggaran' => $data_rek->jumlahp,				 
					'jumlahsebelum' => $data_rek->jumlahsebelum,
				))
				->execute();	
			
			
		}
	}
	
	db_set_active();

}


function transfer_pembiayaan() {

	db_set_active('anggaran');
	
	//baca data dari kegiatan dari anggaran
	$res_keg = db_query('SELECT SUM(jumlah) AS jumlahpembiayaan FROM {anggperda} WHERE left(kodero,3)=:kodekeluar', array(':kodekeluar' => '602'));
	foreach ($res_keg as $data) {
		$jumlahpembiayaan = $data->jumlahpembiayaan;
	}

	//drupal_set_message($jumlahpembiayaan);
	
	//baca data dari rekening kegiatan dari anggaran
	$res_rek = db_query('SELECT * FROM {anggperda} WHERE left(kodero,3)=:kodekeluar', array(':kodekeluar' => '602'));
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
			'jenis' => '2', 
			'tahun' => apbd_tahun(), 
			'kodepro' => '000', 
			'kodeuk' => '01', 
			'kegiatan' => 'Kegiatan Pembiayaan', 
			'kodesuk' => '0101', 
			'isgaji' => '0',
			'total' => $jumlahpembiayaan, 
			'sumberdana1' => 'APBY', 
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
 
	//Transfer Bendahara
	db_set_active('bendahara');
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
			'jenis' => '2', 
			'tahun' => apbd_tahun(), 
			'kodepro' => '000', 
			'kodeuk' => '01', 
			'kegiatan' => 'Kegiatan Pembiayaan', 
			'kodesuk' => '0101', 
			'isgaji' => '0',
			'total' => $jumlahpembiayaan, 
			'sumberdana1' => 'APBY', 
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
	db_set_active();
 
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

	//Transfer Bendahara
	db_set_active('bendahara');

	//delete first
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
	db_set_active();
 
}



function anggaran_importperubahan_form_batch_processing($data) {
	drupal_set_message('Hai');
	
}

function anggaran_importperubahan_form_finished() {
  drupal_set_message(t('Update anggaran selesai...'));
}


?>
