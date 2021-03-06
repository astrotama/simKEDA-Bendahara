<?php
function spj_edit_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$bendid = arg(2);	
	if(arg(3)=='pdf'){		
	
	} else {
	
		//$btn = l('Cetak', '');
		//$btn .= "&nbsp;" . l('Excel', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary')));
		
		//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('pager');
		$output_form = drupal_get_form('spj_edit_main_form');
		return drupal_render($output_form);// . $output;
	}		
	
}

function spj_edit_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	//$current_url = url(current_path(), array('absolute' => TRUE));
	/*$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjgajilastpage"] = $referer;
	else
		$referer = $_SESSION["spjgajilastpage"];*/
	
	//db_set_active('penatausahaan');
	$bendid = arg(2);
	$kodeuk = apbd_getuseruk();
	$query = db_select('bendahara' . $kodeuk, 'd');
	$query->innerJoin('unitkerja', 'u', 'd.kodeuk=u.kodeuk');

	# get the desired fields from the database
	$query->fields('d', array('bendid','keperluan', 'kodeuk', 'spjno', 'tanggal'));
	$query->fields('u', array('namasingkat'));	
	$query->condition('d.bendid', $bendid, '=');
	
	# execute the query
	$results = $query->execute();
		
	$rows = array();
	$no = 0;
	foreach ($results as $data) {
		
		$bendid = $data->bendid;
		$spjtgl = strtotime($data->tanggal);		

		$spjno=$data->spjno;
		$kodeuk = $data->kodeuk;
		$namasingkat = $data->namasingkat;
		$keperluan = $data->keperluan;
		
		
	}
	
	drupal_set_title($keperluan);
	
	$form['bendid'] = array(
		'#type' => 'value',
		'#value' => $bendid,
	);	
	$form['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);

	$form['spjno'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. SPJ'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		//'#required' => TRUE,
		'#default_value' => $spjno,
	);
	$form['spjtgl'] = array(
		'#type' => 'date',
		'#title' =>  t('Tanggal SPJ'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $spjtgl,
		'#default_value'=> array(
			'year' => format_date($spjtgl, 'custom', 'Y'),
			'month' => format_date($spjtgl, 'custom', 'n'), 
			'day' => format_date($spjtgl, 'custom', 'j'), 
		  ), 
		
	);

	$form['keperluan'] = array(
		'#type' => 'textfield',
		'#title' =>  t('Keperluan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $keperluan,
	);

	//REKENING	
	$form['formdokumen'] = array (
		'#type' => 'fieldset',
		//'#title'=> 'PAJAK<em class="text-info pull-right">' . apbd_fn($pajak) . '</em>',
		'#title'=> 'REKENING',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
		$form['formdokumen']['tablerekening']= array(
			'#prefix' => '<div class="table-responsive"><table class="table"><tr><th width="10px">NO</th><th width="90px">KODE</th><th>URAIAN</th><th width="150px">JUMLAH</th><th width="150px">KETERANGAN</th></tr>',
			 '#suffix' => '</table></div>',
		);	
		
		$i = 0;
		$query = db_query('SELECT d.kodero,ro.uraian,d.jumlah, d.keterangan FROM `bendaharaitem`  as d inner join `rincianobyek` as ro on d.kodero=ro.kodero WHERE bendid= :bendid', array(':bendid'=>$bendid));
		foreach ($query as $data) {

			$i++; 
			$kode = $data->kodero;
			$uraian = $data->uraian;
			$jumlah = $data->jumlah;
			$keterangan = $data->keterangan;
			
			$form['formdokumen']['tablerekening']['kodero' . $i]= array(
					'#type' => 'value',
					'#value' => $kode,
			); 
			
			$form['formdokumen']['tablerekening']['nomor' . $i]= array(
					'#prefix' => '<tr><td>',
					'#markup' => $i,
					//'#size' => 10,
					'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['kode' . $i]= array(
					'#prefix' => '<td>',
					'#markup' => $kode,
					//'#size' => 10,
					'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['uraian' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> $uraian, 
				'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['jumlah' . $i]= array(
				'#type'         => 'textfield', 
				'#prefix' => '<td>',
				'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
				'#default_value'=> $jumlah, 
				'#suffix' => '</td>',
			);	
			$form['formdokumen']['tablerekening']['keterangan' . $i]= array(
				'#type'  => 'textfield', 
				'#prefix' => '<td>',
				'#size' => 25,
				'#default_value'=> $keterangan, 
				'#suffix' => '</td></tr>',
			);	
		}
		$form['formdokumen']['jumlahrekening']= array(
			'#type' => 'value',
			'#value' => $i,
		);	

	//PAJAK	
	$form['formpajak'] = array (
		'#type' => 'fieldset',
		//'#title'=> 'PAJAK<em class="text-info pull-right">' . apbd_fn($pajak) . '</em>',
		'#title'=> 'PAJAK',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
		$form['formpajak']['tablepajak']= array(
			'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="90px">KODE</th><th>URAIAN</th><th width="150px">JUMLAH</th><th width="150px">KETERANGAN</th></tr>',
			 '#suffix' => '</table>',
		);	 
		$i = 0;
		$query = db_select('bendaharapajak'. $kodeuk, 'bp');
		$query->join('ltpajak', 'p', 'bp.kodepajak=p.kodepajak');
		$query->fields('p', array('kodepajak', 'uraian'));
		$query->fields('bp', array('jumlah', 'keterangan'));
		$query->condition('bp.bendid', $bendid, '=');
		$query->orderBy('bp.kodepajak', 'ASC');
		$results = $query->execute();
		foreach ($results as $data) {

			$i++; 
			$kode = $data->kodepajak;
			$uraian = $data->uraian;
			$jumlah = $data->jumlah;
			$keterangan = $data->keterangan;
			$form['formpajak']['tablepajak']['kodepajak' . $i]= array(
					'#type' => 'value',
					'#value' => $kode,
			); 
			
			$form['formpajak']['tablepajak']['nomor' . $i]= array(
					'#prefix' => '<tr><td>',
					'#markup' => $i,
					//'#size' => 10,
					'#suffix' => '</td>',
			); 
			$form['formpajak']['tablepajak']['kode' . $i]= array(
					'#prefix' => '<td>',
					'#markup' => $kode,
					'#size' => 10,
					'#suffix' => '</td>',
			); 
			$form['formpajak']['tablepajak']['uraianpajak' . $i]= array(
				//'#type'         => 'textfield', 
				'#prefix' => '<td>',
				'#markup'=> $uraian, 
				'#suffix' => '</td>',
			); 
			$form['formpajak']['tablepajak']['jumlahpajak' . $i]= array(
				'#type'         => 'textfield', 
				'#default_value'=> $jumlah, 
				'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
				'#size' => 25,
				'#prefix' => '<td>',
				'#suffix' => '</td>',
			);	
			$form['formpajak']['tablepajak']['keteranganpajak' . $i]= array(
				'#type'         => 'textfield', 
				'#default_value'=> $keterangan, 
				'#size' => 25,
				'#prefix' => '<td>',
				'#suffix' => '</td></tr>',
			);	
			
		}
		
		if ($i==0) {
			$query = db_select('ltpajak', 'p');
			$query->fields('p', array('kodepajak', 'uraian'));
			$results = $query->execute();
			foreach ($results as $data) {
				
				$i++; 
				$kode = $data->kodepajak;
				$uraian = $data->uraian;
				$jumlah = 0;
				$keterangan = '';
				$form['formpajak']['tablepajak']['kodepajak' . $i]= array(
						'#type' => 'value',
						'#value' => $kode,
				); 
				
				$form['formpajak']['tablepajak']['nomor' . $i]= array(
						'#prefix' => '<tr><td>',
						'#markup' => $i,
						//'#size' => 10,
						'#suffix' => '</td>',
				); 
				$form['formpajak']['tablepajak']['kode' . $i]= array(
						'#prefix' => '<td>',
						'#markup' => $kode,
						'#size' => 10,
						'#suffix' => '</td>',
				); 
				$form['formpajak']['tablepajak']['uraianpajak' . $i]= array(
					//'#type'         => 'textfield', 
					'#prefix' => '<td>',
					'#markup'=> $uraian, 
					'#suffix' => '</td>',
				); 
				$form['formpajak']['tablepajak']['jumlahpajak' . $i]= array(
					'#type'         => 'textfield', 
					'#default_value'=> $jumlah, 
					'#attributes' => array('style' => 'text-align: right'),		//array('id' => 'righttf'),
					'#size' => 25,
					'#prefix' => '<td>',
					'#suffix' => '</td>',
				);	
				$form['formpajak']['tablepajak']['keteranganpajak' . $i]= array(
					'#type'         => 'textfield', 
					'#default_value'=> '', 
					'#size' => 25,
					'#prefix' => '<td>',
					'#suffix' => '</td></tr>',
				);					
			}	
		}
		
		$form['jumlahrekpajak']= array(
			'#type' => 'value',
			'#value' => $i,
		);   		
		
	//SIMPAN
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-file" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	
	return $form;
}

function spj_edit_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor SPJ harap diisi dengan benar');
		
}
	
function spj_edit_main_form_submit($form, &$form_state) {
	$bendid = $form_state['values']['bendid'];
	$spjno = $form_state['values']['spjno'];
	$spjtgl = $form_state['values']['spjtgl'];
	$tanggal = $spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	$keperluan = $form_state['values']['keperluan'];
	
	$jumlahrekening = $form_state['values']['jumlahrekening'];
	$jumlahrekpajak = $form_state['values']['jumlahrekpajak'];
	
	$kodeuk = apbd_getuseruk();
	
	//rekening
	$total = 0;	
	for($n=1;$n<=$jumlahrekening;$n++){
		$kodero = $form_state['values']['kodero' . $n];
		$keterangan = $form_state['values']['keterangan' . $n];
		$jumlah = $form_state['values']['jumlah' . $n];
		
		$total += $jumlah;
		
		$query = db_update('bendaharaitem' . $kodeuk) // Table name no longer needs {}
		->fields(array(
			'jumlah' => $jumlah,
			'keterangan' => $keterangan,
		))
		->condition('bendid', $bendid, '=')
		->condition('kodero', $kodero, '=')
		->execute();
		
	}

	//pajak
	$pajak = 0;	
	for($n=1; $n<=$jumlahrekpajak; $n++){
		$kodepajak = $form_state['values']['kodepajak' . $n];
		$keterangan = $form_state['values']['keteranganpajak' . $n];
		$jumlahpajak = $form_state['values']['jumlahpajak' . $n];
		
		$pajak += $jumlahpajak;
		
		$query = db_update('bendaharapajak' . $kodeuk) // Table name no longer needs {}
		->fields(array(
			'jumlah' => $jumlah,
			'keterangan' => $keterangan,
		))
		->condition('bendid', $bendid, '=')
		->condition('kodepajak', $kodepajak, '=')
		->execute();
		
	}
	
	$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
	->fields(array(
		  'spjno' => $spjno,
		  'tanggal' => $tanggal,
		  'keperluan' => $keperluan,
		  'total' => $total,
		  'pajak' => $pajak,
	))
	->condition('bendid', $bendid, '=')
	->execute();

	drupal_goto('spjarsip');
}



?>
