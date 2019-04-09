<?php
function panjarseksi_arsip_main($arg=NULL, $nama=NULL) {
	$qlike='';
	$limit = 10;
    
	if ($arg) {
		switch($arg) {
			case 'filter':
			
				//drupal_set_message('filter');
				//drupal_set_message(arg(5));
				
				$kodesuk = arg(2);
				$kodepa = arg(3);
				$jenis = arg(4);
				$jenispanjar = arg(5);
				$bulan = arg(6);

				break;
				
			case 'excel':
				break;

			default:
				//drupal_access_denied();
				break;
		}
		
	} else { 
		if (isUserSKPD() or isUserPembantu()) {
			$kodesuk = apbd_getusersuk();
			
		} else {
			//isset($_SESSION["panjarseksi_arsip_kodesuk"])?$kodesuk = $_SESSION["panjarseksi_arsip_kodesuk"]:$kodesuk = 'ZZ';
			$kodesuk = '8101';
		}
		$kodepa = 'ZZ';
		$bulan = '0';
		$jenis = 'ZZ';
		$jenispanjar = '00';
		
	}
	
	$kodeuk = apbd_getuseruk();
	
	//drupal_set_message('UK ' . $kodesuk);
	//drupal_set_message('SUK ' . apbd_getusersuk());
	
	$output_form = drupal_get_form('panjarseksi_arsip_main_form');
	$header = array (
		array('data' => 'No','width' => '10px', 'valign'=>'top'),
		array('data' => '','width' => '5px', 'valign'=>'top'),
		array('data' => 'Nomor', 'field'=> 'spjno', 'valign'=>'top'),
		array('data' => 'Tanggal', 'width' => '90px', 'field'=> 'tanggal', 'valign'=>'top'),
		array('data' => 'Jenis', 'field'=> 'tanggal', 'valign'=>'top'),
		array('data' => 'PPTK', 'field'=> 'namasuk', 'valign'=>'top'),
		array('data' => 'No Ref', 'width' => '100px', 'field'=> 'tanggal', 'valign'=>'top'),
		array('data' => 'Keperluan', 'field'=> 'keperluan', 'valign'=>'top'),
		array('data' => 'Jumlah', 'width' => '100px', 'field'=> 'total', 'valign'=>'top'),
		array('data' => '', 'width' => '60px', 'valign'=>'top'),
		
	);
		

	$query = db_select('bendahara' . $kodeuk, 'd')->extend('PagerDefault')->extend('TableSort');
	$query->leftJoin('PelakuAktivitas', 'suk', 'suk.kodepa=d.kodepa');

	# get the desired fields from the database
	$query->fields('d', array('dokid', 'tanggal', 'spjno', 'noref', 'keperluan', 'total', 'pajak', 'kodesuk', 'jenis' , 'jenispanjar' , 'bendid', 'kodepa'));
	$query->fields('suk', array('namapa'));
	
	if ($bulan !='0') {	
		if ($bulan=='12') {
			$query->condition('tanggal', apbd_tahun() . '-12-01', '>=');
			$query->condition('tanggal', apbd_tahun()+1 . '-01-01', '<');
		} else {
			$query->condition('tanggal', apbd_tahun() . '-' . sprintf('%02d', $bulan) . '-01', '>=');
			$query->condition('tanggal', apbd_tahun() . '-' . sprintf('%02d', $bulan+1) . '-01', '<');
		}
	}
	
	$query->condition('d.kodeuk', $kodeuk, '=');
	$query->condition('d.kodesuk', $kodesuk, '=');
	if ($kodepa !='ZZ') $query->condition('d.kodepa', $kodepa, '=');
	if ($jenis !='ZZ') 
		$query->condition('d.jenis', $jenis, '=');
	else {
		$or = db_or();
		$or->condition('d.jenis', 'seksi-in', '=');
		$or->condition('d.jenis', 'seksi-out', '=');
		$query->condition($or);		
	}
	if ($jenispanjar !='00') $query->condition('d.jenispanjar', $jenispanjar, '=');

	
	$query->orderByHeader($header);
	$query->orderBy('d.tanggal', 'DESC');
	$query->orderBy('d.bendid', 'ASC');
	$query->limit($limit);
	

	//dpq($query);
		
	
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
		
		$keperluan = $data->keperluan;
		
		$editlink = apbd_button_jurnal('panjarseksi/edit/' . $data->bendid);
		$editlink .=  apbd_button_hapus('panjar/delete/' . $data->bendid);		
		
		if ($data->jenis=='seksi-in')
			$icon = apbd_icon_plus();
		else
			$icon = apbd_icon_minus();
		
		$rows[] = array(
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $icon, 'align' => 'right', 'valign'=>'top'),
					array('data' => $data->spjno,'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fd($data->tanggal),'align' => 'left', 'valign'=>'top'),
					array('data' => strtoupper($data->jenispanjar),'align' => 'left', 'valign'=>'top'),
					array('data' => $data->namapa,'align' => 'left', 'valign'=>'top'),
					array('data' => $data->noref,'align' => 'left', 'valign'=>'top'),
					array('data' => $data->keperluan,'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->total),'align' => 'right', 'valign'=>'top'),
					$editlink,						
				);
	}
	
	
	//BUTTON
	
	$btn = apbd_button_print('/laporanbk2/' . $kodesuk );
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');
	return drupal_render($output_form) . $btn . $output . $btn;
	
	
}

function panjarseksi_arsip_main_form_submit($form, &$form_state) {
	$kodesuk = $form_state['values']['kodesuk'];
	$kodepa = $form_state['values']['kodepa'];
	$jenis = $form_state['values']['jenis'];
	$jenispanjar = $form_state['values']['jenispanjar'];
	$bulan = $form_state['values']['bulan'];
	
	$uri = 'panjarseksiarsip/filter/' . $kodesuk . '/' . $kodepa . '/' . $jenis . '/' . $jenispanjar . '/' . $bulan ;
	drupal_goto($uri);
	
}


function panjarseksi_arsip_main_form($form, &$form_state) {
	
	if(arg(2)!=null){
		
		$kodesuk = arg(2);
		$kodepa = arg(3);
		$jenis = arg(4);
		$jenispanjar = arg(5);
		$bulan = arg(6);
		

	} else {
		$kodesuk = apbd_getusersuk();
		//$bulan = date('m');		
		$bulan = '0';
		$jenis = 'ZZ';
		$kodepa = 'ZZ';
		$jenispanjar = '00';
		
	}
 
	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=>  'PILIHAN DATA' . '<em><small class="text-info pull-right"></small></em>',	
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);		
	
	//SKPD
	$kodesuk = apbd_getusersuk();
	$form['formdata']['kodesuk'] = array(
		'#type' => 'value',
		'#value' => $kodesuk,
	);			
	
	$option_suk = array();
	# execute the query
	$results = db_query('select kodepa,namapa from {PelakuAktivitas} where kodesuk=:kodesuk order by kodepa', array(':kodesuk'=>$kodesuk));
	
	$option_suk['ZZ'] = '- SELURUH PPTK -';
	foreach ($results as $data) {
		$option_suk[$data->kodepa] = $data->namapa;
	}
	$form['formdata']['kodepa'] = array(
		'#type' => 'select',
		'#title' =>  t('Bidang/Bagian'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#prefix' => '<div id="skpd-replace">',
		//'#suffix' => '</div>',
		// When the form is rebuilt during ajax processing, the $selected variable
		// will now have the new value and so the options will change.
		'#options' => $option_suk,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodesuk,
		'#default_value' => $kodepa,
	);		
 
	//JENIS PANJAR
	$opt_spj['ZZ'] = 'SEMUA';
	$opt_spj['seksi-in'] = 'PENGELUARAN PANJAR UNTUK PPTK';
	$opt_spj['seksi-out'] = 'PENERIMAAN PANJAR DARI PPTK';	
	$form['formdata']['jenis'] = array(
		'#type' => 'select',
		'#title' =>  t('Jenis Panjar'),
		'#options' => $opt_spj,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodesuk,
		'#default_value' => $jenis,
	);	 
	
	//PANJAR
	$opt_panjar = array();
	$opt_panjar['00'] = 'Semua';
	$opt_panjar['gu'] = 'Ganti Uang';
	$opt_panjar['tu'] = 'Tambahan Uang';
	$form['formdata']['jenispanjar'] = array(
		'#type' => 'radios',
		'#title' =>  t('Jenis Kas (GU/TU)'),
		'#options' => $opt_panjar,
		'#default_value' => $jenispanjar,
	);
	
	//BULAN
	$option_bulan =array('Setahun', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
	$form['formdata']['bulan'] = array(
		'#type' => 'select',
		'#title' =>  t('Bulan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		'#options' => $option_bulan,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodesuk,
		'#default_value' =>$bulan,
	);
	

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
