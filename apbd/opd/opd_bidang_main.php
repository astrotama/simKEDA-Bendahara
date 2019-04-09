<?php
function opd_bidang_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$output_form = drupal_get_form('opd_bidang_main_form');
	return drupal_render($output_form);// . $output;
	
}

function opd_bidang_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjgajilastpage"] = $referer;
	else
		$referer = $_SESSION["spjgajilastpage"];*/
	
	//db_set_active('penatausahaan');
	$kodeuk = arg(2);

	$form['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);
	

	//PAJAK	
	$form['formbidang']= array(
		'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="60px">KODE</th><th>BIDANG</th><th>KABID(PPK)</th><th>NIP PPK</th><th>BENDAHARA</th><th>NIP BENDAHARA</th><th> </th></tr>',
		 '#suffix' => '</table>',
	);	 
	
	$i = 0;
	$query = db_query('SELECT kodesuk,namasuk,bpnama,bpnip,kabidnama,kabidnip FROM `subunitkerja` WHERE kodeuk=:kodeuk', array(':kodeuk' => $kodeuk));
	foreach ($query as $data) {
		
		$i++;
		
		$form['formbidang']['e_kodesuk' . $i]= array(
				'#type' => 'value',
				'#value' => $data->kodesuk,
		); 
		
		$form['formbidang']['nomor' . $i]= array(
				'#prefix' => '<tr><td>',
				'#markup' => $i,
				//'#size' => 10,
				'#suffix' => '</td>',
		); 
		$form['formbidang']['kodesuk' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> substr($data->kodesuk,-2), 
				'#size' => 2,
				'#maxlength' => 2,
				'#suffix' => '</td>',
		); 
		$form['formbidang']['namasuk' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> $data->namasuk, 
			'#suffix' => '</td>',
		); 

		$form['formbidang']['kabidnama' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> $data->kabidnama, 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['kabidnip' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> $data->kabidnip, 
				'#suffix' => '</td>',
		); 

		$form['formbidang']['bpnama' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> $data->bpnama, 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['bpnip' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> $data->bpnip, 
				'#suffix' => '</td>',
		);
		$form['formbidang']['sub' . $i]= array(
				'#prefix' 	=> '<td>',
				'#markup'=> '<a href="/opd/pa/'. $kodeuk . '0' . $i . '" class="btn btn-info btn-sm">Seksi</a>', 
				'#suffix' => '</td></tr>',
		);
	}	

	for ($x = 1; $x <= 3; $x++)  {
		
		$i++;
		
		$form['formbidang']['e_kodesuk' . $i]= array(
				'#type' => 'value',
				'#value' => 'new',
		); 
		
		$form['formbidang']['nomor' . $i]= array(
				'#prefix' => '<tr><td>',
				'#markup' => $i,
				//'#size' => 10,
				'#suffix' => '</td>',
		); 
		$form['formbidang']['kodesuk' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#size' => 2,
				'#maxlength' => 2,
				'#suffix' => '</td>',
		); 

		$form['formbidang']['namasuk' . $i]= array(
			'#type'         => 'textfield', 
			'#prefix' => '<td>',
			'#default_value'=> '', 
			'#suffix' => '</td>',
		); 

		$form['formbidang']['kabidnama' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['kabidnip' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#suffix' => '</td>',
		); 
		
		$form['formbidang']['bpnama' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#suffix' => '</td>',
		); 
		$form['formbidang']['bpnip' . $i]= array(
				'#type'		=> 'textfield', 
				'#prefix' 	=> '<td>',
				'#default_value'=> '', 
				'#suffix' => '</td></tr>',
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
		'#suffix' => "&nbsp;<a href='/' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	return $form;
}

function opd_bidang_main_form_validate($form, &$form_state) {

}
	
function opd_bidang_main_form_submit($form, &$form_state) {
$kodeuk = $form_state['values']['kodeuk'];
$jumlahbidang = $form_state['values']['jumlahbidang'];

for($n=1; $n<=$jumlahbidang; $n++){
	$e_kodesuk = $form_state['values']['e_kodesuk' . $n];

	$kodesuk = $form_state['values']['kodesuk' . $n];
	$namasuk = $form_state['values']['namasuk' . $n];

	$bpnama = $form_state['values']['bpnama' . $n];
	$bpnip = $form_state['values']['bpnip' . $n];

	$kabidnama = $form_state['values']['kabidnama' . $n];
	$kabidnip = $form_state['values']['kabidnip' . $n];
	
	if ($kodesuk=='') {
		
		if ($e_kodesuk != 'new') {
			$num_deleted = db_delete('subunitkerja')
				  ->condition('kodeuk', $kodeuk)
				  ->condition('kodesuk', $e_kodesuk)
				  ->execute();				
		}
		 
	} else {
		
		$kodesuk = $kodeuk . $kodesuk;
		if ($e_kodesuk=='new') {						//old
			$query = db_insert('subunitkerja') // Table name no longer needs {}
					->fields(array(
					  'kodeuk' => $kodeuk,
					  'kodesuk' => $kodesuk,
					  'namasuk' => $namasuk,				  
					  'bpnama' => $bpnama,
					  'bpnip' => $bpnip,				  
					  'kabidnama' => $kabidnama,
					  'kabidnip' => $kabidnip,				  
			))
			->execute();
				
			
		} else {									//new
			$query = db_update('subunitkerja') 		// Table name no longer needs {}
			->fields(array(
				'kodesuk' => $kodesuk,
				'namasuk' => $namasuk,
				'bpnama' => $bpnama,
				'bpnip' => $bpnip,				  
				'kabidnama' => $kabidnama,
				'kabidnip' => $kabidnip,				  
			))
			->condition('kodeuk', $kodeuk, '=')
			->condition('kodesuk', $e_kodesuk, '=')
			->execute();
					
		}	
	}

}
	
drupal_goto('');
	
}



?>
