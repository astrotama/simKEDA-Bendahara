<?php

function spjls_delete_form() {
    //drupal_add_js("$(document).ready(function(){ updateAnchorClass('.container-inline')});", 'inline');
    
    $bendid = arg(2);
	
    if (isset($bendid)) {
		$kodeuk = apbd_getuseruk();
		$query = db_select('bendahara' . $kodeuk, 'd');
		$query->fields('d', array('bendid','dokid','keperluan', 'kodeuk', 'noref', 'spjno', 'tanggal', 'total'));
		$query->condition('d.bendid', $bendid, '=');
		
		//dpq($query);	
		
		$ada = false;		
		# execute the query	
		$results = $query->execute();
		foreach ($results as $data) {
			$bendid = $data->bendid;
			$dokid = $data->dokid;
			
			$spjtgl = $data->tanggal;		
			$spjno=$data->spjno;
			
			$noref = $data->noref;			
			$keperluan = $data->keperluan . ', sebesar ' . apbd_fn($data->total); 
			
			$ada = true;		
		}	
		
	 
        if ($ada) {
            
			//drupal_set_message('x');		
			$form['formdata'] = array (
				'#type' => 'fieldset',
				'#title'=> 'Konfirmasi Penghapusan SPJ LS Barang Jasa',
				'#collapsible' => TRUE,
				'#collapsed' => FALSE,        
			);
			
			
			$form['formdata']['bendid'] = array('#type' => 'value', '#value' => $bendid);
			$form['formdata']['dokid'] = array('#type' => 'value', '#value' => $dokid);
			$form['formdata']['nomor'] = array (
						'#type' => 'item',
						'#title' =>  t('Nomor/Tanggal'),
						'#markup' => '<p>' . $spjno . ', ' . apbd_fd($spjtgl)  . '/<p>',
					);
			$form['formdata']['noref'] = array (
						'#type' => 'item',
						'#title' =>  t('No. SP2D'),
						'#markup' => '<p>' . $noref  . '</p>',
					);
			$form['formdata']['keterangan'] = array (
						'#type' => 'item',
						'#title' =>  t('Uraian'),
						'#markup' => '<p>' . $keperluan . '</p>',
					);
			
			//FORM NAVIGATION	
			$current_url = url(current_path(), array('absolute' => TRUE));
			$referer = $_SERVER['HTTP_REFERER'];
			if ($current_url != $referer)
				$_SESSION["arsipspjlastpage"] = $referer;
			else
				$referer = $_SESSION["arsipspjlastpage"];
			
			return confirm_form($form,
								'Anda yakin menghapus SPJ Nomor/Tanggal : ' .  $spjno . ', ' . apbd_fd($spjtgl),
								$referer,
								'PERHATIAN : SPJ yang dihapus tidak bisa dikembalikan lagi.',
								//'<button type="button" class="btn btn-danger">Hapus</button>',
								//'<em class="btn btn-danger">Hapus</em>',
								//'<input class="btn btn-danger" type="button" value="Hapus">',
								'Hapus',
								'Batal');
        }
    }
}
function spjls_delete_form_validate($form, &$form_state) {
}

function spjls_delete_form_submit($form, &$form_state) {
    if ($form_state['values']['confirm']) {
        $bendid = $form_state['values']['bendid'];
		$dokid = $form_state['values']['dokid'];
		
		$kodeuk = apbd_getuseruk();
		 
		//rekening
		$num = db_delete('bendaharapajak' . $kodeuk)
		  ->condition('bendid', $bendid)
		  ->execute();
		
		//pajak
		$num = db_delete('bendaharaitem' . $kodeuk)
		  ->condition('bendid', $bendid)
		  ->execute();
		
		//bendahara
		$num = db_delete('bendahara' . $kodeuk)
		  ->condition('bendid', $bendid)
		  ->execute();
		
		//dokumen
		if ($num) {
			//update sp2d
			db_set_active('penatausahaan');

			$query = db_update('dokumen')
			->fields(
					array(
						'spjsudah' => 0,
					)
				);
			$query->condition('dokid', $dokid, '=');
			$res = $query->execute();
			db_set_active();
			
		}
        if ($num) {
			
            drupal_set_message('Penghapusan berhasil dilakukan');
			
			$referer = $_SESSION["arsipspjlastpage"];
            drupal_goto($referer);
        }
        
    }
}
?>