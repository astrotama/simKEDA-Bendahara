<?php
function spjup_newpick_main($arg=NULL, $nama=NULL) {
	$qlike='';
	$limit = 10;
	
	$kodeuk = apbd_getuseruk();
    
	if ($arg) {
		switch($arg) {
			case 'filter':
			
				//drupal_set_message('filter');
				//drupal_set_message(arg(5));
				
				$bulan = arg(3);
				$spjsudah = arg(4);

				break;
				
			case 'excel':
				break;

			default:
				//drupal_access_denied();
				break;
		}
		
	} else {
		$kodeuk = apbd_getuseruk();
        //$r = (1 == $v) ? 'Yes' : 'No';
		//$bulan = date('m');
		//isset($_SESSION["spjup_newpick_bulan"])?$bulan = $_SESSION["spjup_newpick_bulan"]:$bulan = '1';
		//isset($_SESSION["spjup_newpick_spjsudah"])?$spjsudah = $_SESSION["spjup_newpick_bulan"]:$spjsudah = 'ZZ';
		$bulan = '0';
		$spjsudah = '0';		
		
	}
	db_set_active('penatausahaan');

	//drupal_set_message($spjsudah);
	//drupal_set_message($keyword);
	//drupal_set_message($jenisdokumen);
	
	//drupal_set_message(apbd_getkodejurnal('90'));
	
	$header = array (
		array('data' => 'No','width' => '5px', 'valign'=>'top'),
		array('data' => '', 'width' => '5px', 'valign'=>'top'),
		array('data' => 'SP2D', 'valign'=>'top'),
		array('data' => 'Tanggal', 'width' => '100px', 'valign'=>'top'),
		array('data' => 'Jenis', 'width' => '25px', 'valign'=>'top'),
		array('data' => 'Bulan', 'valign'=>'top'),
		array('data' => 'Keperluan', 'valign'=>'top'),
		array('data' => 'Jumlah', 'width' => '90px',  'valign'=>'top'),
		array('data' => '', 'width' => '90px', 'valign'=>'top'),
		
	);

	//DB PENATAUSAHAAN
	$limit = 10;
	
	$query = db_select('dokumen', 'd');
	$query->innerJoin('unitkerja', 'u', 'd.kodeuk=u.kodeuk');

	# get the desired fields from the database
	$query->fields('d', array('dokid', 'bulan', 'keperluan', 'kodeuk', 'spmno', 'sppno', 'sp2dno', 'sp2dtgl', 'jumlah', 'potongan', 'netto', 'spjsudah', 'jenisgaji', 'spjsudah', 'jenisdokumen'));
	$query->fields('u', array('namasingkat'));
	
	//UP TU
	$or = db_or();
	$or->condition('d.jenisdokumen', 0, '=');		//UP
	$or->condition('d.jenisdokumen', 1, '=');		//GU
	$or->condition('d.jenisdokumen', 2, '=');		//TU
	$query->condition($or);

	if ($spjsudah !='ZZ') $query->condition('d.spjsudah', $spjsudah, '=');
	$query->condition('d.kodeuk', $kodeuk, '=');
	$query->condition('d.sp2dok', 1, '=');
	
	if ($bulan !='0') $query->condition('d.bulan', $bulan, '=');	
	//$query->limit(10);
	//$query->condition('d.spjsudah', '0', '=');
	$query->orderBy('d.sp2dtgl', 'ASC');
	
	
	if (isAdministrator()) {
		dpq($query);
		
	}
	
	
	
	# execute the query
	$results = $query->execute();
		
	# build the table fields


	$rows = array();
	$no = 0;
	foreach ($results as $data) {
		$no++;  
		
		
		
		
		
		//$editlink ='<a href="barangjasaspp/newrek/' . $data->dokid.'">SPJ</a>';//createlink('SPJ', 'barangjasaspp/newrek/' . $data->dokid);
		
		if($data->spjsudah=='1') {
			$spjsudah = apbd_icon_sudah();
			$editlink = '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true">Bukukan</span>';
			
		} else {
			$spjsudah = apbd_icon_belum();
			$editlink = createlink('<span class="glyphicon glyphicon-chevron-right" aria-hidden="true">Bukukan</span>','barupost/' . $data->dokid);
		}
		
		$keperluan = createlink($data->keperluan,  'http://simkedajepara.web.id/sp2d/default.aspx?dokid=E_SP2D_' . $data->dokid . '.PDF');
		
		if($data->jenisdokumen=='0') 
			$jenisdokumen = 'UP';
		
		else if($data->jenisdokumen=='1') 
			$jenisdokumen = 'GU';
		
		else 
			$jenisdokumen = 'TU';
		
		$rows[] = array(
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $spjsudah,'align' => 'center', 'valign'=>'top'),
					array('data' => $data->sp2dno,'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fd($data->sp2dtgl),'align' => 'center', 'valign'=>'top'),
					array('data' => $jenisdokumen,'align' => 'center', 'valign'=>'top'),
					array('data' => apbd_getbulan($data->bulan), 'align' => 'left', 'valign'=>'top'),
					array('data' => $keperluan,'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->jumlah),'align' => 'right', 'valign'=>'top'),
					$editlink,
				);			
	}
	
	
	
	db_set_active();
    $table=createTable($header,$rows);
	$output_form = drupal_get_form('spjup_newpick_main_form');
	return drupal_render($output_form) . $table;
	
}


function spjup_newpick_main_form_submit($form, &$form_state) {
	$bulan = $form_state['values']['bulan'];
	$spjsudah = $form_state['values']['spjsudah'];
	
	/*
	if($form_state['clicked_button']['#value'] == $form_state['values']['submit2']) {
		drupal_set_message($form_state['values']['submit2']);
	}
	else{
		drupal_set_message($form_state['clicked_button']['#value']);
	}
	*/

	$_SESSION["spjup_newpick_bulan"] = $bulan;
	$_SESSION["spjup_newpick_spjsudah"] = $spjsudah;

	$uri = 'spjup/baru/filter/' . $bulan . '/' . $spjsudah;
	drupal_goto($uri);
	
}


function spjup_newpick_main_form($form, &$form_state) {
	$kodeuk = apbd_getuseruk();
	if(arg(2)!=null){
		
		$bulan = arg(3);
		$spjsudah = arg(4);

	} else {

		//$bulan = date('m');
		//isset($_SESSION["spjls_newpick_bulan"])?$bulan = $_SESSION["spjls_newpick_bulan"]:$bulan = date('m');;
		//isset($_SESSION["spjls_newpick_spjsudah"])?$spjsudah = $_SESSION["spjls_newpick_bulan"]:$spjsudah = 'ZZ';		
		$bulan = arg(3);
		$spjsudah = arg(4);
		
	}
 
	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=>  'PILIHAN DATA' . '<em><small class="text-info pull-right">' . get_label_data($bulan, $spjsudah) . '</small></em>',		//'#attributes' => array('class' => array('container-inline')),
		//'#title'=>  '<p>PILIHAN DATA</p>' . '<em><small class="text-info pull-right">klik disini utk menampilkan/menyembunyikan pilihan data</small></em>',
		//'#attributes' => array('class' => array('container-inline')),
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);		
	
	//SKPD
	$form['formdata']['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);			
	
	//BULAN
	$option_bulan =array('Setahun', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
	$form['formdata']['bulan'] = array(
		'#type' => 'select',
		'#title' =>  t('Bulan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		'#options' => $option_bulan,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' =>$bulan,
	);


	$opt_sp2d['ZZ'] ='SEMUA';
	$opt_sp2d['0'] = 'BELUM DIBUKUKAN';
	$opt_sp2d['1'] = 'SUDAH DIBUKUKAN';	
	//$opt_sp2d['2'] = 'SUDAH VALIDASI';	
	$form['formdata']['spjsudah'] = array(
		'#type' => 'select',
		'#title' =>  t('Dibukukan'),
		'#options' => $opt_sp2d,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $spjsudah,
	);	 

	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span> Tampilkan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	return $form;
}


function get_label_data($bulan, $status) {
if ($bulan=='0')
	$label = 'Setahun';
else 
	$label = 'Bulan ' . apbd_get_namabulan($bulan);


if ($status=='ZZ')
	$label .= '/Semua SP2D';
else if ($status=='0')	
	$label .= '/Belum dibukukan';
else if ($status=='1')	
	$label .= '/Sudah dibukukan';

$label .= ' (Klik disini untuk mengganti pilihan data)';
return $label;
}


?>
