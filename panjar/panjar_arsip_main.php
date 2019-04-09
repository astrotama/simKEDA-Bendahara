<?php
function panjar_arsip_main($arg=NULL, $nama=NULL) {
	$qlike='';
	$limit = 10;
    
	if ($arg) {
		switch($arg) {
			case 'filter':
			
				//drupal_set_message('filter');
				//drupal_set_message(arg(5));
				
				$kodeuk = arg(2);
				$kodesuk = arg(3);
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
			$kodeuk = apbd_getuseruk();
			
		} else {
			//isset($_SESSION["panjar_arsip_kodeuk"])?$kodeuk = $_SESSION["panjar_arsip_kodeuk"]:$kodeuk = 'ZZ';
			$kodeuk = '81';
		}
		$kodesuk = 'ZZ';
		$bulan = '0';
		$jenis = 'ZZ';
		$jenispanjar = '00';
		
	}
	
	//drupal_set_message('UK ' . $kodeuk);
	//drupal_set_message('SUK ' . apbd_getusersuk());
	
	$output_form = drupal_get_form('panjar_arsip_main_form');
	$header = array (
		array('data' => 'No','width' => '10px', 'valign'=>'top'),
		array('data' => '','width' => '5px', 'valign'=>'top'),
		array('data' => 'Nomor', 'field'=> 'spjno', 'valign'=>'top'),
		array('data' => 'Tanggal', 'width' => '90px', 'field'=> 'tanggal', 'valign'=>'top'),
		array('data' => 'Jenis', 'field'=> 'tanggal', 'valign'=>'top'),
		array('data' => 'Bidang/Seksi', 'field'=> 'namasuk', 'valign'=>'top'),
		array('data' => 'No Ref', 'width' => '100px', 'field'=> 'tanggal', 'valign'=>'top'),
		array('data' => 'Keperluan', 'field'=> 'keperluan', 'valign'=>'top'),
		array('data' => 'Jumlah', 'width' => '100px', 'field'=> 'total', 'valign'=>'top'),
		array('data' => '', 'width' => '60px', 'valign'=>'top'),
		
	);
		

	$query = db_select('bendahara' . $kodeuk, 'd')->extend('PagerDefault')->extend('TableSort');
	$query->leftJoin('subunitkerja', 'suk', 'suk.kodesuk=d.kodesuk');

	# get the desired fields from the database
	$query->fields('d', array('dokid', 'tanggal', 'spjno', 'noref', 'keperluan', 'total', 'pajak', 'kodeuk', 'jenis' , 'jenispanjar' , 'bendid', 'kodesuk', 'kodepa', 'panjarseksi'));
	$query->fields('suk', array('namasuk'));
	
	if ($bulan !='0') {	
		if ($bulan=='12') {
			$query->condition('tanggal', apbd_tahun() . '-12-01', '>=');
			$query->condition('tanggal', apbd_tahun()+1 . '-01-01', '<');
		} else {
			$query->condition('tanggal', apbd_tahun() . '-' . sprintf('%02d', $bulan) . '-01', '>=');
			$query->condition('tanggal', apbd_tahun() . '-' . sprintf('%02d', $bulan+1) . '-01', '<');
		}
	}
	
	if ($kodeuk !='ZZ') $query->condition('d.kodeuk', $kodeuk, '=');
	if ($kodesuk !='ZZ') $query->condition('d.kodesuk', $kodesuk, '=');
	if ($jenis !='ZZ') 
		$query->condition('d.jenis', $jenis, '=');
	else {
		$or = db_or();
		$or->condition('d.jenis', 'pjr-in', '=');
		$or->condition('d.jenis', 'pjr-out', '=');
		$query->condition($or);		
	}
	if ($jenispanjar !='00') $query->condition('d.jenispanjar', $jenispanjar, '=');

	
	$query->orderByHeader($header);
	$query->orderBy('d.tanggal', 'DESC');
	$query->orderBy('d.bendid', 'ASC');
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
		$no++;
		
		$keperluan = $data->keperluan;
		
		$editlink = apbd_button_jurnal('panjar/edit/' . $data->bendid);
		$editlink .=  apbd_button_hapus('panjar/delete/' . $data->bendid);

		
		if ($data->jenis=='pjr-in')
			$icon = apbd_icon_plus();
		else
			$icon = apbd_icon_minus();
		
		if ($data->panjarseksi)
			$bidang = getnamaseksi($data->kodepa);
		else 
			$bidang = $data->namasuk;
		
		$rows[] = array(
					array('data' => $no, 'align' => 'right', 'valign'=>'top'),
					array('data' => $icon, 'align' => 'right', 'valign'=>'top'),
					array('data' => $data->spjno,'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fd($data->tanggal),'align' => 'left', 'valign'=>'top'),
					array('data' => strtoupper($data->jenispanjar),'align' => 'left', 'valign'=>'top'),
					array('data' => $bidang,'align' => 'left', 'valign'=>'top'),
					array('data' => $data->noref,'align' => 'left', 'valign'=>'top'),
					array('data' => $data->keperluan,'align' => 'left', 'valign'=>'top'),
					array('data' => apbd_fn($data->total),'align' => 'right', 'valign'=>'top'),
					$editlink,						
				);
	}
	
	
	//BUTTON
	/*
	$btn = l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK1', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	$btn .= "&nbsp;" . l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK2', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	$btn .= "&nbsp;" . l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK3', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	$btn .= "&nbsp;" . l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK4', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	$btn .= "&nbsp;" . l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK5', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	$btn .= "&nbsp;" . l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK6', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	$btn .= "&nbsp;" . l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK7', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	$btn .= "&nbsp;" . l('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> BK8', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
	*/
	
	/*
	$btn = '<div class="btn-group">' .
			'<button type="button" class="btn btn-primary glyphicon glyphicon-print dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
			' Cetak <span class="caret"></span>' .
			'</button>' .
				'<ul class="dropdown-menu">' .
					'<li><a href="laporanbk1"><s>BK-1</s></a></li>' .
					'<li><a href="laporanbk2"><s>BK-2</s></a></li>' .
					'<li><a href="laporanbk3"><s>BK-3</s></a></li>' .
					'<li><a href="laporanbk4"><s>BK-4</s></a></li>' .
					'<li><a href="laporanbk5"><s>BK-5</s></a></li>' .
					'<li><a href="laporanbk6/' . $kodeuk . '">BK-6</a></li>' .
					'<li><a href="laporanbk8/' . $kodeuk . '/1/1">BK-7</a></li>' .
					'<li role="separator" class="divider"></li>' .
					'<li><a href="laporanbk8/' . $kodeuk . '/1/1">BK-8</a></li>' .
				'</ul>' .
			'</div>';
	*/
	
	$btn = apbd_button_print('/laporanbk2/' . $kodeuk );
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');
	return drupal_render($output_form) . $btn . $output . $btn;
	
	
}

function panjar_arsip_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	$jenis = $form_state['values']['jenis'];
	$jenispanjar = $form_state['values']['jenispanjar'];
	$bulan = $form_state['values']['bulan'];
	
	$uri = 'panjararsip/filter/' . $kodeuk . '/' . $kodesuk . '/' . $jenis . '/' . $jenispanjar . '/' . $bulan ;
	drupal_goto($uri);
	
}


function panjar_arsip_main_form($form, &$form_state) {
	
	if(arg(2)!=null){
		
		$kodeuk = arg(2);
		$kodesuk = arg(3);
		$jenis = arg(4);
		$jenispanjar = arg(5);
		$bulan = arg(6);
		

	} else {
		if (isUserSKPD()) 
			$kodeuk = apbd_getuseruk();
		else {
			//isset($_SESSION["panjar_arsip_kodeuk"])?$kodeuk = $_SESSION["panjar_arsip_kodeuk"]:$kodeuk = 'ZZ';
			$kodeuk = '81';
		}
		//$bulan = date('m');		
		$bulan = '0';
		$jenis = 'ZZ';
		$jenispanjar = '00';
		
	}
 
	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=>  'PILIHAN DATA' . '<em><small class="text-info pull-right"></small></em>',	
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);		
	
	//SKPD
	$kodeuk = apbd_getuseruk();
	$form['formdata']['kodeuk'] = array(
		'#type' => 'value',
		'#value' => $kodeuk,
	);			
	
	$query = db_select('subunitkerja', 'p');
	# get the desired fields from the database
	$query->fields('p', array('namasuk','kodesuk'));
	$query->condition('p.kodeuk', $kodeuk, '=');
	# execute the query
	$option_suk['ZZ'] = 'SELURUH BIDANG/BAGIAN'; 
	$results = $query->execute();
	# build the table fields
	foreach($results as $data) {
	  $option_suk[$data->kodesuk] = $data->namasuk; 
	}
	$form['formdata']['kodesuk'] = array(
		'#type' => 'select',
		'#title' =>  t('Bidang/Bagian'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#prefix' => '<div id="skpd-replace">',
		//'#suffix' => '</div>',
		// When the form is rebuilt during ajax processing, the $selected variable
		// will now have the new value and so the options will change.
		'#options' => $option_suk,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $kodesuk,
	);		
 
	//JENIS PANJAR
	$opt_spj['ZZ'] = 'SEMUA';
	$opt_spj['pjr-in'] = 'PENGELUARAN PANJAR UNTUK BIDANG';
	$opt_spj['pjr-out'] = 'PENERIMAAN PANJAR DARI BIDANG';	
	$form['formdata']['jenis'] = array(
		'#type' => 'select',
		'#title' =>  t('Jenis Panjar'),
		'#options' => $opt_spj,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
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
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
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

function getnamaseksi($kodepa) {
	$x = $kodepa;
	$res = db_query('select namapa from {PelakuAktivitas} where kodepa=:kodepa', array(':kodepa'=>$kodepa));
	foreach ($res as $data) {
		$x = $data->namapa;
	}
	
	return $x;
}

?>
