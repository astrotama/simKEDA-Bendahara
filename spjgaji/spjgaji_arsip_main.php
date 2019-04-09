<?php
function spjgaji_arsip_main($arg=NULL, $nama=NULL) {
	$qlike='';
	$limit = 10;
    
	if ($arg) {
		switch($arg) {
			case 'show':
				$qlike = " and lower(k.kegiatan) like lower('%%%s%%')";    
				break;
			case 'filter':
			
				//drupal_set_message('filter');
				//drupal_set_message(arg(5));
				
				$kodeuk = arg(2);
				$bulan = arg(3);
				$spjok = arg(4);
				$jenisgaji = arg(5);

				break;
				
			case 'excel':
				break;

			default:
				//drupal_access_denied();
				break;
		}
		
	} else {
		if (isUserSKPD()) 
			$kodeuk = apbd_getuseruk();
		else {
			isset($_SESSION["spjgaji_arsip_kodeuk"])?$kodeuk = $_SESSION["spjgaji_arsip_kodeuk"]:$kodeuk = 'ZZ';
			
		}
		//$bulan = date('m');
		
		
	}
	
	//drupal_set_message($keyword);
	//drupal_set_message($jenisdokumen);
	
	//drupal_set_message(apbd_getkodejurnal('90'));
	
	$output_form = drupal_get_form('spjgaji_arsip_main_form');
	if (isSuperuser())
		$header = array (
			array('data' => 'No','width' => '10px', 'valign'=>'top'),
			array('data' => '', 'width' => '10px', 'valign'=>'top'),
			array('data' => 'spj', 'field'=> 'spjno', 'valign'=>'top'),
			array('data' => 'Kodeuk', 'field'=> 'sppno', 'valign'=>'top'),
			array('data' => 'Tanggal', 'width' => '90px', 'field'=> 'spjtgl', 'valign'=>'top'),
			array('data' => 'Keperluan', 'field'=> 'keperluan', 'valign'=>'top'),
			array('data' => '', 'width' => '60px', 'valign'=>'top'),
			
		);
	else 
		$header = array (
			array('data' => 'No','width' => '10px', 'valign'=>'top'),
			array('data' => '', 'width' => '10px', 'valign'=>'top'),
			array('data' => 'spj', 'field'=> 'spjno', 'valign'=>'top'),
			array('data' => 'Tanggal', 'width' => '90px', 'field'=> 'spjtgl', 'valign'=>'top'),
			array('data' => 'Keperluan', 'field'=> 'keperluan', 'valign'=>'top'),
			array('data' => '', 'width' => '60px', 'valign'=>'top'),
			
		);
		

	$query = db_select('bendahara' . $kodeuk, 'd')->extend('PagerDefault')->extend('TableSort');
	$query->innerJoin('unitkerja', 'u', 'd.kodeuk=u.kodeuk');

	# get the desired fields from the database
	$query->fields('d', array('dokid', 'tanggal', 'keperluan', 'kodeuk','bendid'));
	$query->fields('u', array('namasingkat'));
	
	//GAJI
	//$query->condition('d.jenisdokumen', 3, '=');
	
	if ($kodeuk !='ZZ') $query->condition('d.kodeuk', $kodeuk, '=');
	$query->orderByHeader($header);
	$query->orderBy('u.namasingkat', 'ASC');
	$query->limit($limit);
	
	/*
	if (isAdministrator()) {
	dpq($query);
		
	}
	*/
	
	# execute the query
	$results = $query->execute();
		
	# build the table fields
	$no=0;

	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		$no = $page * $limit;
	} else {
		$no = 0;
	} 

	$rows = array();
	foreach ($results as $data) {
		$editlink='';
		if (isSuperuser())
			$rows[] = array(
						array('data' => $no, 'align' => 'right', 'valign'=>'top'),
						array('data' => '','align' => 'right', 'valign'=>'top'),
						array('data' => $data->bendid,'align' => 'left', 'valign'=>'top'),
						array('data' => $data->namasingkat,'align' => 'center', 'valign'=>'top'),
						array('data' => $data->tanggal,'align' => 'left', 'valign'=>'top'),
						array('data' => $data->keperluan,'align' => 'left', 'valign'=>'top'),
						$editlink,
						//"<a href=\'?q=jurnal/edit/'>" . 'Register' . '</a>',
						
					);
		else 
			$rows[] = array(
						array('data' => $no, 'align' => 'right', 'valign'=>'top'),
						array('data' => '','align' => 'right', 'valign'=>'top'),
						array('data' => $data->bendid,'align' => 'left', 'valign'=>'top'),
						array('data' => $data->tanggal,'align' => 'left', 'valign'=>'top'),
						array('data' => $data->keperluan,'align' => 'left', 'valign'=>'top'),
						$editlink,
						//"<a href=\'?q=jurnal/edit/'>" . 'Register' . '</a>',
						
					);			
	}
	
	
	//BUTTON
	$btn = apbd_button_print('');
	$btn .= "&nbsp;" . apbd_button_excel('');	
	
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');
	return drupal_render($output_form) . $btn . $output . $btn;
	
	
}


function getData($kodeuk,$bulan,$jenisdokumen,$keyword){
	
}

function spjgaji_arsip_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$uri = 'spjgajiarsip/filter/' . $kodeuk ;
	drupal_goto($uri);
	
}


function spjgaji_arsip_main_form($form, &$form_state) {
	
	/*
	$kodeuk = 'ZZ';
	//$bulan = date('m');
	$bulan = '1';
	$spjok = 'ZZ';
	*/
	
	if(arg(2)!=null){
		
		$kodeuk = arg(2);
		

	} else {
		if (isUserSKPD()) 
			$kodeuk = apbd_getuseruk();
		else {
			isset($_SESSION["spjgaji_arsip_kodeuk"])?$kodeuk = $_SESSION["spjgaji_arsip_kodeuk"]:$kodeuk = 'ZZ';
		}
		//$bulan = date('m');
		
		
	}
 
	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=>  'PILIHAN DATA' . '<em><small class="text-info pull-right"></small></em>',		//'#attributes' => array('class' => array('container-inline')),
		//'#title'=>  '<p>PILIHAN DATA</p>' . '<em><small class="text-info pull-right">klik disini utk menampilkan/menyembunyikan pilihan data</small></em>',
		//'#attributes' => array('class' => array('container-inline')),
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);		
	
	//SKPD
		if (isUserSKPD()) {
		$kodeuk = apbd_getuseruk();
		$form['formdata']['kodeuk'] = array(
			'#type' => 'value',
			'#value' => $kodeuk,
		);			
	} else {	
	$query = db_select('unitkerja', 'p');
	# get the desired fields from the database
	$query->fields('p', array('namasingkat','kodeuk','kodedinas'))
			->orderBy('kodedinas', 'ASC');
	# execute the query
	$results = $query->execute();
	# build the table fields
	$option_skpd['ZZ'] = 'SELURUH SKPD'; 
	if($results){
		foreach($results as $data) {
		  $option_skpd[$data->kodeuk] = $data->namasingkat; 
		}
	}		
	$form['formdata']['kodeuk'] = array(
		'#type' => 'select',
		'#title' =>  t('SKPD'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#prefix' => '<div id="skpd-replace">',
		//'#suffix' => '</div>',
		// When the form is rebuilt during ajax processing, the $selected variable
		// will now have the new value and so the options will change.
		'#options' => $option_skpd,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $kodeuk,
	);
	}
	
	

	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span> Tampilkan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	return $form;
}

function get_label_data($bulan, $jenisgaji, $status) {
if ($bulan=='0')
	$label = 'Setahun';
else 
	$label = 'Bulan ' . apbd_get_namabulan($bulan);

if ($jenisgaji=='ZZ')
	$label .= '/Semua gaji';
else if ($jenisgaji=='0')	
	$label .= '/Gaji reguler';
else if ($jenisgaji=='1')	
	$label .= '/Kekurangan gaji';
else if ($jenisgaji=='2')
	$label .= '/Gaji susulan';
else if ($jenisgaji=='3')	
	$label .= '/Gaji terusan';
else if ($jenisgaji=='4')	
	$label .= '/Tamsil';

if ($status=='ZZ')
	$label .= '/Semua spj';
else if ($status=='0')	
	$label .= '/Belum verifikasi';
else if ($status=='1')	
	$label .= '/Sudah verifikasi';

$label .= ' (Klik disini untuk mengganti pilihan data)';
return $label;
}

?>
