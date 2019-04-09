<?php

function laporan_menu_main($arg=NULL, $nama=NULL) {
	isset($_SESSION["laporan_kas_kodeuk"])? $kodeuk = $_SESSION["laporan_kas_kodeuk"]: $kodeuk = '81';
	
	if(arg(2) == 'resume'){
		
		$query = db_query('delete from bendahara' . $kodeuk);
			$query->execute();
			
		/*	
		$query = db_query("delete from bendaharaitem$kodeuk;");
			$query->execute();
		$query = db_query("delete from bendaharapajak$kodeuk;");
			$query->execute();
		*/
		
		$query = db_query('insert into bendahara' . $kodeuk . ' select * from bendahara where kodeuk=:kodeuk', array(':kodeuk'=>$kodeuk));
			$query->execute();
			
			
			/*
		$query = db_query("insert into bendaharaitem$kodeuk select bendaharaitem.* from bendaharaitem inner join bendahara$kodeuk on bendaharaitem.bendid=bendahara$kodeuk.bendid;");
			$query->execute();
		$query = db_query("insert into bendaharapajak$kodeuk select bendaharapajak.* from bendaharapajak inner join bendahara$kodeuk on bendaharapajak.bendid=bendahara$kodeuk.bendid;");
			$query->execute();
		*/
		
		drupal_set_message("berhasil");
	}

	//$btn = apbd_button_resume("/laporankas/$kodeuk/resume");
	$output_form = drupal_get_form('laporan_menu_main_form');
	return drupal_render($output_form);
	
}

function laporan_menu_main_form ($form, &$form_state) {
	
	if (isSuperuser()) {
		
		isset($_SESSION["laporan_kas_kodeuk"])? $kodeuk = $_SESSION["laporan_kas_kodeuk"]: $kodeuk = '81';
		
		$option_uk['ZZ'] = 'SELURUH OPD'; 
		$results = db_query('select kodeuk, namasingkat from {unitkerja} order by kodedinas');
		foreach($results as $data) {
		  $option_uk[$data->kodeuk] = $data->namasingkat; 
		}
		$form['formdata']['kodeuk'] = array(
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

			'#validated' => TRUE,
			'#ajax' => array(
				'event'=>'change',
				'callback' =>'_ajax_report',
				'wrapper' => 'rekening-wrapper',
			),
		);
		
		/*
		$form['submit']= array(
			'#type' => 'submit',
			'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Resume',
		);
		*/
			// Wrapper for rekdetil dropdown list
		$form['wrapperreport'] = array(
				'#prefix' => '<div id="rekening-wrapper">',
				'#suffix' => '</div>', 
		);			

		if (isset($form_state['values']['kodeuk'])) {
			// Pre-populate options for rekdetil dropdown list if rekening id is set
			$reportitem = _load_item($form_state['values']['kodeuk']);
		} else
			$reportitem = _load_item($kodeuk);

		// Detil dropdown list
		$form['wrapperreport']['item'] = array(
				'#title' => t('Item'),
				'#markup' => $reportitem,
		);	
			
		
		
	} else {
		
		$kodeuk = apbd_getuseruk();
		$form['formdata']['kodeuk'] = array(
			'#type' => 'hidden',
			'#default_value' => $kodeuk,
		);	
		/*
		$form['submit']= array(
			'#type' => 'submit',
			'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Resume',
		);
		*/
		if (isUserPembantu() or isUserSeksi()) {


		
			$kodesuk = apbd_getusersuk();
			$form['item']= array(
				'#type'     => 'item', 
				'#markup'	=> '<div class="list-group">' .
									'<a href="/laporanbk0/' . $kodeuk . '/'. $kodesuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-0 (Buku Kas Pembantu)</a>' .
									'<a href="/laporanbk2/' . $kodeuk . '/'. $kodesuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-2 (Buku Panjar)</a>' .
									'<a href="/laporanbk3/' . $kodeuk . '/'. $kodesuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-3 (Buku Pajak)</a>' .
									'<a href="/laporanbk5/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-5 (Kartu Kendali Kegiatan)</a>' .
									'<a href="/laporanbk6/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-6 (Rekap Pengeluaran per Kegiatan)</a>' .
									'<a href="/laporanbk8p/' . $kodeuk . '/'. $kodesuk . '/" class="list-group-item glyphicon glyphicon-th-large"> BK-8 (SPJ Bendahara Pembantu)</a>' .
									'<a href="/laporanberita/" class="list-group-item glyphicon glyphicon-th-large"> Berita Acara Pemeriksaan Kas</a>' .
							'</div>', 
			);
			
		} else {
			$form['item']= array(
				'#type'     => 'item', 
				'#markup'	=> '<div class="list-group">' .
									'<a href="/laporanbk0/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-0 (Buku Kas Pembantu)</a>' .
									'<a href="/laporanbk1std/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-1 (Buku Kas Umum Standard)</a>' .
									'<a href="/laporanbk1/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-1 (Buku Kas Umum Panjar)</a>' .
									'<a href="/laporanbk2/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-2 (Buku Panjar)</a>' .
									'<a href="/laporanbk3/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-3 (Buku Pajak)</a>' .
									'<a href="/laporanbk5/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-5 (Kartu Kendali Kegiatan)</a>' .
									'<a href="/laporanbk6/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-6 (Rekap Pengeluaran per Kegiatan)</a>' .
									'<a href="/laporanbk8/' . $kodeuk . '/7" class="list-group-item glyphicon glyphicon-th-large"> BK-7 (SPJ Administratif)</a>' .
									'<a href="/laporanbk8/' . $kodeuk . '/8" class="list-group-item glyphicon glyphicon-th-large"> BK-8 (SPJ Fungsional)</a>' .
									'<a href="/laporanbk8p/' . $kodeuk . '/" class="list-group-item glyphicon glyphicon-th-large"> BK-8 (SPJ Bendahara Pembantu)</a>' .
									
									'<a href="/laporanberita/" class="list-group-item glyphicon glyphicon-th-large"> Berita Acara Pemeriksaan Kas</a>' .
							'</div>', 
			);
			
		}		
	
	}		


	return $form;
}

function laporan_menu_main_form_submit($form, &$form_state){
	$kodeuk = $form_state['values']['kodeuk'];
	
		$query = db_query('delete from bendahara' . $kodeuk);
			$query->execute();
			
		/*	
		$query = db_query("delete from bendaharaitem$kodeuk;");
			$query->execute();
		$query = db_query("delete from bendaharapajak$kodeuk;");
			$query->execute();
		*/
		
		//		$query = db_query('insert into bendahara' . $kodeuk . ' select * from bendahara where kodeuk=:kodeuk', array(':kodeuk'=>$kodeuk));
		$query = db_query("insert into bendahara" . $kodeuk . " select * from bendahara where kodeuk='" . $kodeuk . "'");
			$query->execute();
}
function _ajax_report($form, $form_state) {
	// Return the dropdown list including the wrapper
	return $form['wrapperreport'];
}

function _load_item($kodeuk) {

	$_SESSION["laporan_kas_kodeuk"] = $kodeuk;

	$reportitem	= '<div class="list-group">' .
						'<a href="/laporanbk0/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-0 (Buku Kas Pembantu)</a>' .
						'<a href="/laporanbk1std/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-1 (Buku Kas Umum Standard)</a>' .						
						'<a href="/laporanbk1/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-1 (Buku Kas Umum)</a>' .
						'<a href="/laporanbk2/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-2 (Buku Panjar)</a>' .
						'<a href="/laporanbk3/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-3 (Buku Pajak)</a>' .
						'<a href="/laporanbk5/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-5 (Kartu Kendali Kegiatan)</a>' .
						'<a href="/laporanbk6/' . $kodeuk . '" class="list-group-item glyphicon glyphicon-th-large"> BK-6 (Rekap Pengeluaran per Kegiatan)</a>' .
						'<a href="/laporanbk8/' . $kodeuk . '/7" class="list-group-item glyphicon glyphicon-th-large"> BK-7 (SPJ Administratif)</a>' .
						'<a href="/laporanbk8/' . $kodeuk . '/8" class="list-group-item glyphicon glyphicon-th-large"> BK-8 (SPJ Fungsional)</a>' .
						'<a href="/laporanbk8p/' . $kodeuk . '/" class="list-group-item glyphicon glyphicon-th-large"> BK-8 (SPJ Bendahara Pembantu)</a>' .
						'<a href="/laporanpajak/' . $kodeuk . '/" class="list-group-item glyphicon glyphicon-th-large"> Laporan Pajak</a>' .
						'<a href="/laporan_daftar/" class="list-group-item glyphicon glyphicon-th-large"> Laporan Daftar</a>' .
						'<a href="/laporan_rekapitulasi/" class="list-group-item glyphicon glyphicon-th-large"> Laporan Rekapitulasi</a>' .
				'</div>'; 

	return $reportitem;
}

?>
