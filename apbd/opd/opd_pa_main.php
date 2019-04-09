<?php
function opd_pa_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('opd_pa_main_form');
	return drupal_render($output_form);// . $output;
	
}

function opd_pa_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	//$referer = $_SERVER['HTTP_REFERER'];
	
	/*
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjgajilastpage"] = $referer;
	else
		$referer = $_SESSION["spjgajilastpage"];*/
	
	//db_set_active('penatausahaan');
	$kodesuk = arg(2);
	$kodeuk =  substr($kodesuk, 0,2);

	$form['kodesuk'] = array(
		'#type' => 'value',
		'#value' => $kodesuk,
	);	

	//PAJAK	
	$form['formbidang']= array(
		'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="60px">KODE</th><th>NAMA</th><th>PIMPINAN</th><th>NIP PIMPINAN</th><th>JABATAN PIMPINAN</th><th> </th></tr>',
		 '#suffix' => '</table>',
	);	 
	
	$i = 0;
	$query = db_query('SELECT kodesuk,kodepa,namapa,bpnama,bpnip, pimpinannama,pimpinannip,pimpinanjabatan FROM PelakuAktivitas WHERE kodesuk=:kodesuk', array(':kodesuk' => $kodesuk));
	foreach ($query as $data) {
		
		$i++;
		
		$form['formbidang']['e_kodepa' . $i]= array(
				'#type' => 'value',
				'#value' => $data->kodepa,
		); 
		
		$form['formbidang']['nomor' . $i]= array(
				'#prefix' => '<tr><td>',
				'#markup' => $i,
				//'#size' => 10,
				'#suffix' => '</td>',
		); 
		$form['formbidang']['kodepa' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> substr($data->kodepa,-2), 
				'#size' => 2,
				'#maxlength' => 2,
				'#suffix' => '</td>',
		); 
		$form['formbidang']['namapa' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> $data->namapa, 
			'#suffix' => '</td>',
		); 
		/*
		$form['formbidang']['bpnama' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> $data->bpnama, 
			'#suffix' => '</td>',
		); 
		$form['formbidang']['bpnip' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> $data->bpnip, 
			'#suffix' => '</td>',
		); 
		*/
		$form['formbidang']['pimpinannama' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> $data->pimpinannama, 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['pimpinannip' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> $data->pimpinannip, 
				'#suffix' => '</td>',
		); 

		$form['formbidang']['pimpinanjabatan' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> $data->pimpinanjabatan, 
				'#suffix' => '</td></tr>',
		); 
	}	

	for ($x = 1; $x <= 3; $x++)  {
		
		$i++;
		
		$form['formbidang']['e_kodepa' . $i]= array(
				'#type' => 'value',
				'#value' => 'new',
		); 
		
		$form['formbidang']['nomor' . $i]= array(
				'#prefix' => '<tr><td>',
				'#markup' => $i,
				//'#size' => 10,
				'#suffix' => '</td>',
		); 
		$form['formbidang']['kodepa' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#size' => 2,
				'#maxlength' => 2,
				'#suffix' => '</td>',
		); 
		
		/*
		$form['formbidang']['bpnama' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> '', 
			'#suffix' => '</td>',
		); 
		$form['formbidang']['bpnip' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> '', 
			'#suffix' => '</td>',
		); 
		*/
		
		$form['formbidang']['namapa' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> '', 
			'#suffix' => '</td>',
		); 

		$form['formbidang']['pimpinannama' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['pimpinannip' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#suffix' => '</td>',
		); 
		
		$form['formbidang']['pimpinanjabatan' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#suffix' => '</td>',
		);

	}		

	$form['formbidang']['jumlahbidang']= array(
		'#type' => 'value',
		'#value' => $i,
	);	
	
	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-save" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
		'#suffix' => "&nbsp;<a href='/opd/bidang/" . $kodeuk . "' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	return $form;
}

function opd_pa_main_form_validate($form, &$form_state) {


}
	
function opd_pa_main_form_submit($form, &$form_state) {
$kodesuk = $form_state['values']['kodesuk'];
$kodeuk =  substr($kodesuk, 0,2);
$jumlahbidang = $form_state['values']['jumlahbidang'];

for($n=1; $n<=$jumlahbidang; $n++){
	$e_kodepa = $form_state['values']['e_kodepa' . $n];

	$kodepa = $form_state['values']['kodepa' . $n];
	$namapa = $form_state['values']['namapa' . $n];

	//$bpnama = $form_state['values']['bpnama' . $n];
	//$bpnip = $form_state['values']['bpnip' . $n];

	$pimpinannama = $form_state['values']['pimpinannama' . $n];
	$pimpinannip = $form_state['values']['pimpinannip' . $n];

	$pimpinanjabatan = $form_state['values']['pimpinanjabatan' . $n];
	
	if ($kodepa=='') {
		
		if ($e_kodepa != 'new') {
			$num_deleted = db_delete('PelakuAktivitas')
				  ->condition('kodesuk', $kodesuk)
				  ->condition('kodepa', $e_kodepa)
				  ->execute();				
		}
		 
	} else {
		
		$kodepa = $kodesuk . $kodepa;
		if ($e_kodepa=='new') {						//old
			$query = db_insert('PelakuAktivitas') // Table name no longer needs {}
					->fields(array(
					  'kodesuk' => $kodesuk,
					  'kodepa' => $kodepa,
					  'namapa' => $namapa,				  
					  'pimpinannama' => $pimpinannama,
					  'pimpinannip' => $pimpinannip,				  
					  //'bpnama' => $bpnama,
					  //'bpnip' => $bpnip,				  
					  'pimpinanjabatan' => $pimpinanjabatan,	  
			))
			->execute();
				
			
		} else {									//new
			$query = db_update('PelakuAktivitas')  	// Table name no longer needs {}
			->fields(array(
				'kodepa' => $kodepa,
				'namapa' => $namapa,
				//'bpnama' => $bpnama,
				//'bpnip' => $bpnip,				  				
				'pimpinannama' => $pimpinannama,
				'pimpinannip' => $pimpinannip,				  
				'pimpinanjabatan' => $pimpinanjabatan,	  
			))
			->condition('kodesuk', $kodesuk, '=')
			->condition('kodepa', $e_kodepa, '=')
			->execute();
					
		}	
	}

}
	
drupal_goto('/opd/bidang/' . $kodeuk);
	
}



?>
