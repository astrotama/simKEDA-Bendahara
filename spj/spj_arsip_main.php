<?php
function spj_arsip_main($arg=NULL, $nama=NULL) {
	$qlike='';
	$limit = 20;
    
	if ($arg) {
		switch($arg) {
			case 'filter':
			
				//drupal_set_message('filter');
				//drupal_set_message(arg(5));

				$kodeuk = arg(2);
				$kodesuk = arg(3);
				$bulan = arg(4);
				$jenis = arg(5);
				$kodekeg = arg(6);
				$tanggal = arg(7);
				$katakunci = arg(8);
				
				if ($kodekeg=='') $kodekeg = 'ZZ';
				
				break;
				
			case 'excel':
				break;

			default:
				//drupal_access_denied();
				break;
		}
		
	} else { 
		if (isUserSKPD()) {
			$kodeuk = apbd_getuseruk();
			
			$kodesuk = 'ZZ';
		} else  if (isUserPembantu()) {
			$kodeuk = apbd_getuseruk();
			$kodesuk = apbd_getusersuk();;
		} else {
			//isset($_SESSION["spj_arsip_kodeuk"])?$kodeuk = $_SESSION["spj_arsip_kodeuk"]:$kodeuk = 'ZZ';
			$kodeuk = '81';
			$kodesuk = 'ZZ';
		}
		//drupal_set_message($kodeuk);
		$bulan = '0';
		$jenis = 'ZZ';
		$kodekeg = 'ZZ';
		$tanggal = '0';
		$katakunci = '';
		
	}
	
	//drupal_set_message('UK ' . $kodeuk);
	//drupal_set_message('SUK ' . apbd_getusersuk());
	
	$output_form = drupal_get_form('spj_arsip_main_form');
	if (isSuperuser()) 
		$header = array (
			array('data' => 'No','width' => '10px', 'valign'=>'top'),
			array('data' => 'Nomor', 'width' => '100px','field'=> 'spjno', 'valign'=>'top'),
			array('data' => 'Tanggal', 'width' => '90px', 'field'=> 'tanggal', 'valign'=>'top'),
			array('data' => 'Jenis', 'field'=> 'jenis', 'valign'=>'top'),
			array('data' => 'No Ref', 'width' => '100px', 'field'=> 'noref', 'valign'=>'top'),
			array('data' => 'SKPD', 'field'=> 'namasingkat', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			array('data' => 'Keperluan', 'field'=> 'keperluan', 'valign'=>'top'),
			array('data' => 'Jumlah', 'width' => '100px', 'field'=> 'total', 'valign'=>'top'),
			array('data' => 'Pajak', 'width' => '100px', 'field'=> 'pajak', 'valign'=>'top'),
			array('data' => '', 'width' => '60px', 'valign'=>'top'),
			
		);
	else
		$header = array (
			array('data' => 'No','width' => '10px', 'valign'=>'top'),
			array('data' => 'Nomor', 'width' => '100px','field'=> 'spjno', 'valign'=>'top'),
			array('data' => 'Tanggal', 'width' => '90px', 'field'=> 'tanggal', 'valign'=>'top'),
			array('data' => 'Jenis', 'field'=> 'jenis', 'valign'=>'top'),
			array('data' => 'No Ref', 'width' => '100px', 'field'=> 'noref', 'valign'=>'top'),
			array('data' => 'Kegiatan', 'field'=> 'kegiatan', 'valign'=>'top'),
			array('data' => 'Keperluan', 'field'=> 'keperluan', 'valign'=>'top'),
			array('data' => 'Jumlah', 'width' => '100px', 'field'=> 'total', 'valign'=>'top'),
			array('data' => 'Pajak', 'width' => '100px', 'field'=> 'pajak', 'valign'=>'top'),
			array('data' => '', 'width' => '60px', 'valign'=>'top'),
			
		);
		

	$query = db_select('bendahara' . $kodeuk, 'd')->extend('PagerDefault')->extend('TableSort');
	$query->innerJoin('unitkerja', 'u', 'd.kodeuk=u.kodeuk');
	$query->leftJoin('kegiatanskpd', 'k', 'd.kodekeg=k.kodekeg');

	# get the desired fields from the database
	$query->fields('d', array('dokid', 'tanggal', 'spjno', 'noref', 'keperluan', 'total', 'pajak', 'kodeuk', 'jenis' , 'jenispanjar' , 'bendid', 'kodesuk', 'sudahproses'));
	$query->fields('u', array('namasingkat'));
	$query->fields('k', array('kegiatan'));
	$query->condition('d.jenis', 'seksi-in', '!=');
	$query->condition('d.jenis', 'seksi-out', '!=');
	
	if ($bulan !='0') {	
		if ($bulan=='12') {
			if ($tanggal=='0') {
				$query->condition('tanggal', apbd_tahun() . '-12-01', '>=');
				$query->condition('tanggal', apbd_tahun()+1 . '-01-01', '<');
			} else {
				$query->condition('tanggal', apbd_tahun() . '-12-' . sprintf('%02d', $tanggal), '=');
			}	
			
		} else {
			if ($tanggal=='0') {
				$query->condition('tanggal', apbd_tahun() . '-' . sprintf('%02d', $bulan) . '-01', '>=');
				$query->condition('tanggal', apbd_tahun() . '-' . sprintf('%02d', $bulan+1) . '-01', '<');
			} else {
				$query->condition('tanggal', apbd_tahun() . '-' . sprintf('%02d', $bulan) . '-' . sprintf('%02d', $tanggal), '=');
			}
			
		}
		
	}
	//drupal_set_message($kodeuk);
	if ($kodeuk !='ZZ') $query->condition('d.kodeuk', $kodeuk, '=');
	if ($kodesuk !='ZZ') $query->condition('d.kodesuk', $kodesuk, '=');
	if ($kodekeg !='ZZ') $query->condition('d.kodekeg', $kodekeg, '=');
	
	if (isUserSeksi()) $query->condition('k.kodepa', apbd_getusersuk(), '=');
	
	if ($jenis !='ZZ') {
		if ($jenis =='kas') {
			$or = db_or();
			$or->condition('d.jenis', 'up', '=');
			$or->condition('d.jenis', 'tu', '=');
			$or->condition('d.jenis', 'gu-kas', '=');
			$query->condition($or);
		
		} else {
			$query->condition('d.jenis', $jenis, '=');
		
		}
	
	} else {
		$query->condition('d.jenis', 'pjr-in', '<>');
		$query->condition('d.jenis', 'pjr-out', '<>');
	}
	
	/*
	if (isUserPembantu()) {
		$kodesuk = apbd_getusersuk();
		$query->condition('d.kodesuk', $kodesuk, '=');
		$query->condition('d.kaspembantukeluar', 0, '>');
		$query->condition('d.kasbendaharamasuk', 0, '=');
	}
	*/

	if ($katakunci!='') {
		$or = db_or();
		$or->condition('d.keperluan', '%' . db_like($katakunci) . '%', 'LIKE');
		$or->condition('d.noref', '%' . db_like($katakunci) . '%', 'LIKE');
		$or->condition('k.kegiatan', '%' . db_like($katakunci) . '%', 'LIKE');
		$query->condition($or);
	}
	
	$query->orderByHeader($header);
	$query->orderBy('d.tanggal', 'DESC');
	$query->limit($limit);

	//dpq($query);
	//drupal_set_message(apbd_getusersuk());
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
		
		$total = $data->total;
		
		if ($data->jenis=='gaji') {
			$editlink = apbd_button_jurnal('spjgaji/edit/' . $data->bendid);
			$edithapus =  apbd_button_hapus('spjgaji/delete/' . $data->bendid);
			$keperluan = l($data->keperluan,  'http://simkedajepara.web.id/sp2d/default.aspx?dokid=E_SP2D_' . $data->dokid . '.PDF', array ('html' => true));
		
		} else if ($data->jenis=='ls') {
			$editlink = apbd_button_jurnal('spjls/edit/' . $data->bendid);
			$edithapus =  apbd_button_hapus('spjls/delete/' . $data->bendid);
			$keperluan = l($data->keperluan,  'http://simkedajepara.web.id/sp2d/default.aspx?dokid=E_SP2D_' . $data->dokid . '.PDF', array ('html' => true));
		
		} else if (($data->jenis=='up') or ($data->jenis=='tu') or ($data->jenis=='gu-kas')) {
			$editlink = apbd_button_jurnal('spjup/edit/' . $data->bendid);
			$edithapus =  apbd_button_hapus('spjup/delete/' . $data->bendid);
			$keperluan = l($data->keperluan,  'http://simkedajepara.web.id/sp2d/default.aspx?dokid=E_SP2D_' . $data->dokid . '.PDF', array ('html' => true));

		} else if (($data->jenis=='tu-spj') or ($data->jenis=='gu-spj')) {
			$editlink = apbd_button_jurnal('spjgu/edit/' . $data->bendid);
			$edithapus =  apbd_button_hapus('spjgu/delete/' . $data->bendid);
			if ($data->dokid=='')
				$keperluan = $data->keperluan;
			else
				$keperluan = l($data->keperluan,  'http://simkedajepara.web.id/sp2d/default.aspx?dokid=E_SP2D_' . $data->dokid . '.PDF', array ('html' => true));
			
		} else if ($data->jenis=='ret-kas') {
			$editlink = apbd_button_jurnal('pengembaliankas/edit/' . $data->bendid);
			$edithapus =  apbd_button_hapus('pengembalian/delete/' . $data->bendid);
			$keperluan = $data->keperluan;

		} else if ($data->jenis=='ret-spj') {
			$editlink = apbd_button_jurnal('pengembalianspj/edit/' . $data->bendid);
			$edithapus =  apbd_button_hapus('pengembalian/delete/' . $data->bendid);
			$keperluan = $data->keperluan;
			
		} else if ($data->jenis=='pindahbuku') {
			$editlink = apbd_button_jurnal('pindahbuku/edit/' . $data->bendid);
			$edithapus =  apbd_button_hapus('pengembalian/delete/' . $data->bendid);
			$keperluan = $data->keperluan;
			
			$res_item = db_query('SELECT sum(jumlah) as total FROM bendaharaitem' . $kodeuk . ' WHERE jumlah>0 and bendid= :bendid', array(':bendid' => $data->bendid));
			foreach ($res_item as $data_item) {
				$total = $data_item->total;
			}
			

		} else {
			$editlink = apbd_button_jurnal('');
			$edithapus = '';
			$keperluan = '';
		}
		
		if ($data->sudahproses==0) $editlink .= $edithapus;
		
		if (isSuperuser())
			$rows[] = array(
						array('data' => $no, 'align' => 'right', 'valign'=>'top'),
						array('data' => $data->spjno,'align' => 'left', 'valign'=>'top'),
						array('data' => apbd_fd($data->tanggal),'align' => 'left', 'valign'=>'top'),
						array('data' => strtoupper($data->jenis),'align' => 'left', 'valign'=>'top'),
						array('data' => $data->noref,'align' => 'left', 'valign'=>'top'),
						array('data' => $data->namasingkat,'align' => 'left', 'valign'=>'top'),
						array('data' => $data->kegiatan,'align' => 'left', 'valign'=>'top'),
						array('data' => $keperluan,'align' => 'left', 'valign'=>'top'),
						array('data' => apbd_fn($total),'align' => 'right', 'valign'=>'top'),
						array('data' => apbd_fn($data->pajak),'align' => 'right', 'valign'=>'top'),
						$editlink,						
					);
		else 
			$rows[] = array(
						array('data' => $no, 'align' => 'right', 'valign'=>'top'),
						array('data' => $data->spjno,'align' => 'left', 'valign'=>'top'),
						array('data' => apbd_fd($data->tanggal),'align' => 'left', 'valign'=>'top'),
						array('data' => strtoupper($data->jenis),'align' => 'left', 'valign'=>'top'),
						array('data' => $data->noref,'align' => 'left', 'valign'=>'top'),
						array('data' => $data->kegiatan,'align' => 'left', 'valign'=>'top'),
						array('data' => $keperluan,'align' => 'left', 'valign'=>'top'),
						array('data' => apbd_fn($total),'align' => 'right', 'valign'=>'top'),
						array('data' => apbd_fn($data->pajak),'align' => 'right', 'valign'=>'top'),
						$editlink,						
					);
	}
	
	//drupal_set_message($kodeuk);
	if (isUserPembantu()) {
		$btn = '<div class="btn-group">' .
				'<button type="button" class="btn btn-primary glyphicon glyphicon-print dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
				' Cetak <span class="caret"></span>' .
				'</button>' .
					'<ul class="dropdown-menu">' .
						'<li><a href="/laporanbk0/' . $kodeuk . '/'. $kodesuk . '">BK-0 (Buku Kas Umum Pembantu)</a></li>' .
						'<li><a href="/laporanbk2/' . $kodeuk . '/'. $kodesuk . '">BK-2 (Buku Panjar)</a></li>' .
						'<li><a href="/laporanbk3/' . $kodeuk . '/'. $kodesuk . '">BK-3 (Buku Pajak)</a></li>' .
						'<li><a href="/laporanbk5/' . $kodeuk . '">BK-5 (Kartu Kendali Kegiatan)</a></li>' .
						'<li><a href="/laporanbk6/' . $kodeuk . '">BK-6 (Rekap Pengeluaran per Kegiatan)</a></li>' .
						'<li><a href="/laporanbk8p/' . $kodeuk . '/'. $kodesuk . '">BK-8 (SPJ Bendahara Pembantu)</a></li>' .
					'</ul>' .
				'</div>';
				
	} else {		
		$btn = '<div class="btn-group">' .
				'<button type="button" class="btn btn-primary glyphicon glyphicon-print dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
				' Cetak <span class="caret"></span>' .
				'</button>' .
					'<ul class="dropdown-menu">' .
						'<li><a href="/laporanbk1/' . $kodeuk . '">BK-1 (Buku Kas Umum)</a></li>' .
						'<li><a href="/laporanbk2/' . $kodeuk . '">BK-2 (Buku Panjar)</a></li>' .
						'<li><a href="/laporanbk3/' . $kodeuk . '">BK-3 (Buku Pajak)</a></li>' .
						'<li><a href="/laporanbk5/' . $kodeuk . '">BK-5 (Kartu Kendali Kegiatan)</a></li>' .
						'<li><a href="/laporanbk6/' . $kodeuk . '">BK-6 (Rekap Pengeluaran per Kegiatan)</a></li>' .
						'<li><a href="/laporanbk8/' . $kodeuk . '/7">BK-7 (SPJ Administratif)</a></li>' .
						'<li role="separator" class="divider"></li>' .
						'<li><a href="/laporanbk8/' . $kodeuk . '/8">BK-8 (SPJ Fungsional)</a></li>' .
					'</ul>' .
				'</div>';
	}
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$output .= theme('pager');
	return drupal_render($output_form) /*. $btn*/ . $output . $btn;
	
	
}

function spj_arsip_main_form_submit($form, &$form_state) {
	$kodeuk = $form_state['values']['kodeuk'];
	$kodesuk = $form_state['values']['kodesuk'];
	$bulan = $form_state['values']['bulan'];
	$jenis = $form_state['values']['jenis'];
	$kodekeg = $form_state['values']['kodekeg'];
	$tanggal = $form_state['values']['tanggal'];
	$katakunci = $form_state['values']['katakunci'];
	
	$uri = 'spjarsip/filter/' . $kodeuk . '/' . $kodesuk . '/' . $bulan . '/' . $jenis . '/' . $kodekeg . '/' . $tanggal . '/' . $katakunci;
	drupal_goto($uri);
	
}


function spj_arsip_main_form($form, &$form_state) {
	
	if(arg(2)!=null){
		
		$kodeuk = arg(2);
		$kodesuk = arg(3);
		$bulan = arg(4);
		$jenis = arg(5);
		$kodekeg = arg(6);
		$tanggal = arg(7);
		$katakunci = arg(8);
		$kodepa = 'ZZ';
		
		if ($kodekeg=='') $kodekeg = 'ZZ';


		if (isUserSeksi()) {
			$kodepa = apbd_getusersuk();
		}
		
	} else {
		if (isUserSKPD()) {
			$kodeuk = apbd_getuseruk();
			$kodesuk = 'ZZ';
			$kodepa = 'ZZ';
			
		} elseif (isUserPembantu()) {
			$kodeuk = apbd_getuseruk();
			$kodesuk = apbd_getusersuk();
			$kodepa = 'ZZ';

		} elseif (isUserSeksi()) {
			$kodeuk = apbd_getuseruk();
			$kodepa = apbd_getusersuk();
			$kodesuk = substr($kodepa, 0, 4);
			
		} else {
			//isset($_SESSION["spj_arsip_kodeuk"])?$kodeuk = $_SESSION["spj_arsip_kodeuk"]:$kodeuk = 'ZZ';
			$kodeuk = 'ZZ';
			$kodesuk = 'ZZ';
			$kodepa = 'ZZ';
		}
		
		
		
		//$bulan = date('m');		
		$bulan = '0';
		$tanggal = '0';
		$jenis = 'ZZ';
		$kodekeg = 'ZZ';
		$katakunci = '';
		
	}
	
	
	$form['formdata'] = array (
		'#type' => 'fieldset',
		'#title'=>  'PILIHAN DATA' . '<em><small class="text-info pull-right"></small></em>',	
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,        
	);		
	
	if (isSuperuser()) {
		
		$option_uk['ZZ'] = '- SELURUH SKPD -';
		
		$results = db_query('select kodeuk, namasingkat from {unitkerja} order by kodedinas');
		foreach($results as $data) {
		  $option_uk[$data->kodeuk] = $data->namasingkat; 
		}
		$form['formdata']['kodeuk'] = array(
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',		
			'#type' => 'select',
			'#title' =>  t('OPD'),
			// The entire enclosing div created here gets replaced when dropdown_first
			// is changed.
			//'#prefix' => '<div id="skpd-replace">',
			//'#suffix' => '</div>',
			// When the form is rebuilt during ajax processing, the $selected variable
			// will now have the new value and so the options will change.
			'#options' => $option_uk,
			//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
			'#default_value' => $kodeuk,
		);		

		$form['formdata']['kodesuk'] = array(
			'#type' => 'value',
			'#value' => 'ZZ',
		);			

		$form['formdata']['kodekeg'] = array(
			'#type' => 'value',
			'#value' => 'ZZ',
		);	 
		
		
	} else if (isUserSKPD()) {
		//SKPD
		$form['formdata']['kodeuk'] = array(
			'#type' => 'value',
			'#value' => $kodeuk,
		);			
		
		$option_suk['ZZ'] = '- SELURUH BIDANG/BAGIAN -'; 
		$results = db_query('select kodesuk, namasuk from {subunitkerja} where kodeuk=:kodeuk order by kodesuk', array(':kodeuk' => $kodeuk));
		foreach($results as $data) {
		  $option_suk[$data->kodesuk] = $data->namasuk; 
		}
		$form['formdata']['kodesuk'] = array(
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',		
		
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

		$query = db_query('SELECT kodekeg,kegiatan FROM `kegiatanskpd` WHERE anggaran>0 and inaktif=0 and kodeuk=:kodeuk ORDER BY kegiatan', array(':kodeuk'=>$kodeuk));
		$opt_kegiatan['ZZ'] = 'SEMUA';
		foreach ($query as $data) {
			$opt_kegiatan[$data->kodekeg] = $data->kegiatan;	
		}
		
		$form['formdata']['kodekeg'] = array(
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',				
			'#type' => 'select',
			'#title' =>  t('Kegiatan'),
			'#options' => $opt_kegiatan,
			//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
			'#default_value' => $kodekeg,
		);	 
		
	
	} else {
		//SKPD
		$form['formdata']['kodeuk'] = array(
			'#type' => 'value',
			'#value' => $kodeuk,
		);			
		$form['formdata']['kodesuk'] = array(
			'#type' => 'value',
			'#value' => $kodesuk,
		);		

		$opt_kegiatan['ZZ'] = 'SEMUA';
		if (isUserPembantu())
			$query = db_query('SELECT kodekeg,kegiatan FROM `kegiatanskpd` WHERE anggaran>0 and inaktif=0 and kodeuk=:kodeuk and kodesuk=:kodesuk ORDER BY kegiatan', array(':kodeuk'=>$kodeuk, ':kodesuk'=> $kodesuk));
		elseif (isUserSeksi())
			$query = db_query('SELECT kodekeg,kegiatan FROM `kegiatanskpd` WHERE anggaran>0 and inaktif=0 and kodeuk=:kodeuk and kodesuk=:kodesuk and kodepa=:kodepa ORDER BY kegiatan', array(':kodeuk'=>$kodeuk, ':kodesuk'=> $kodesuk, ':kodepa'=> $kodepa));
		foreach ($query as $data) {
			$opt_kegiatan[$data->kodekeg] = $data->kegiatan;	
		}
		
		$form['formdata']['kodekeg'] = array(
			'#prefix' => '<div class="col-md-12">',
			'#suffix' => '</div>',				
			'#type' => 'select',
			'#title' =>  t('Kegiatan'),
			'#options' => $opt_kegiatan,
			//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
			'#default_value' => $kodekeg,
		);	 		
	}

	//BULAN
	$option_bulan =array('Setahun', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
	$form['formdata']['bulan'] = array(
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',				
		'#type' => 'select',
		'#title' =>  t('Bulan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		'#options' => $option_bulan,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' =>$bulan,
	);
	$option_tanggal =array('Sebulan', '1','2','3','4','5','6','7','8','9','10', '11','12','13','14','15','16','17','18','19','20', '21','22','23','24','25','26','27','28','29','30','31');
	$form['formdata']['tanggal'] = array(
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',				
		'#type' => 'select',
		'#title' =>  t('Tanggal'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		'#options' => $option_tanggal,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' =>$tanggal,
	);
	
	//JENIS GAJI
	$opt_spj['ZZ'] = 'SEMUA';
	$opt_spj['kas'] = 'PENERIMAAN KAS';	
	$opt_spj['gaji'] = 'GAJI';
	$opt_spj['ls'] = 'BARANG JASA';	
	$opt_spj['gu-spj'] = 'SPJ GU (GANTI UANG)';	
	$opt_spj['tu-spj'] = 'SPJ TU (TAMBAHAN UANG)';	
	$opt_spj['ret-spj'] = 'PENGEMBALIAN BELANJA';	
	$opt_spj['ret-kas'] = 'PENGEMBALIAN KAS (SISA TU/GU)';	
	$opt_spj['pindahbuku'] = 'PINDAH BUKU';
	$form['formdata']['jenis'] = array(
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',					
		'#type' => 'select',
		'#title' =>  t('Jenis SPJ'),
		'#options' => $opt_spj,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $jenis,
	);	 


	
	//KATA KUNCI
	$form['formdata']['katakunci'] = array(
		'#prefix' => '<div class="col-md-6">',
		'#suffix' => '</div>',				
	
		'#type' => 'textfield',
		'#title' =>  t('Kata Kunci'),
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $katakunci,
	);	 
	
	$form['formdata']['submit']= array(
		'#prefix' => '<div class="col-md-12">',
		'#suffix' => '</div>',				
	
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
