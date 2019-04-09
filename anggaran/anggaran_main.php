<?php
function anggaran_main($arg=NULL, $name=NULL) {
	$limit = 10;
	
	if (arg(2)) {
		$kodeuk = arg(2);
				
	} else {
		$kodeuk = '81';
	}
	
	//DIFINING COLUMN
	$header = array (
		array('data' => 'No','width' => '10px', 'valign'=>'top'),
		array('data' => 'Kegiatan',  'field'=> 'kegiatan', 'valign'=>'top'), 
		array('data' => 'Triwulan 1', 'field'=> 'tw1', 'valign'=>'top'), 
		array('data' => 'Triwulan 2', 'field'=> 'tw2', 'valign'=>'top'),
		array('data' => 'Triwulan 3', 'field'=> 'tw3', 'valign'=>'top'),
		array('data' => 'Triwulan 4', 'field'=> 'tw3', 'valign'=>'top'),
		array('data' => 'Anggaran', 'field'=> 'tw3', 'valign'=>'top'),
		array('data' => ' ', 'field'=> ' ', 'valign'=>'top'),
	);
		
	$query = db_select('kegiatanskpd', 'k')->extend('PagerDefault')->extend('TableSort');
	# get the desired fields from the database
	$query->fields('k', array('kodekeg' ,'kegiatan', 'tw1','tw2', 'tw3', 'tw4', 'anggaran'));
	
	if ($kodeuk != '') $query->condition('k.kodeuk', $kodeuk, '=');
	$query->condition('k.inaktif',0, '=');
	$query->condition('k.anggaran',0, '>');
	
	$query->orderByHeader($header);
	$query->orderBy('k.kegiatan', 'ASC');
	$query->limit($limit);	
			
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
		$no++;  
		
		
		$editlink = l('Edit', 'anggaran/edit/' . $data->kodekeg , array ('html' => true));
		
		//$editlink =  apbd_button_hapus('operator/delete/' . $data->username);		
		
		$rows[] = array(
						array('data' => $no, 'width' => '10px', 'align' => 'right', 'valign'=>'top'),
						array('data' => $data->kegiatan, 'align' => 'left', 'valign'=>'top'),
						array('data' => apbd_fn($data->tw1), 'align' => 'right', 'valign'=>'top'),
						array('data' => apbd_fn($data->tw2), 'align' => 'right', 'valign'=>'top'),
						array('data' => apbd_fn($data->tw3), 'align' => 'right', 'valign'=>'top'),
						array('data' => apbd_fn($data->tw4), 'align' => 'right', 'valign'=>'top'),
						array('data' => apbd_fn($data->anggaran), 'align' => 'right', 'valign'=>'top'),
						array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
						
					);
	}

	//BUTTON
	//$btn = apbd_button_baru('contact/new');
	
	$output_form = drupal_get_form('anggaran_main_form');
	
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');

	return drupal_render($output_form) . $output;
	//return $output;
}


function anggaran_main_form($form, &$form_state) {
	
	$kodeuk = arg(2);
	if ($kodeuk=='') $kodeuk = '81';
	
	//UNIT KERJA
	//$options['ZZ'] = 'Semua-Laki';
	$res = db_query('select kodeuk, namauk from {unitkerja} order by kodedinas');
	foreach ($res as $data) {
		$options[$data->kodeuk] = $data->namauk;
	}
	$form['kodeuk']= array(
		'#type'         => 'select', 
		'#title'        => 'SKPD',
		'#options'		=> $options,
		//'#description'  => 'kodeuk', 
		//'#maxlength'    => 60, 
		//'#size'         => 20, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $kodeuk, 
	);	

	
    $form['submit'] = array (
        '#type' => 'submit',
		//'#suffix' => "&nbsp;<a href='/operators' class='btn_blue' style='color: white'>Batal</a>",
        '#value' => 'Tampilkan'
    );
    return $form;	
}

function anggaran_main_form_submit($form, &$form_state) {
    
	//MEMBACA VARIABLE HASIL INPUT USER
	$kodeuk = $form_state['values']['kodeuk'];

    drupal_goto('anggaran/filter/' . $kodeuk ); 
}


?>