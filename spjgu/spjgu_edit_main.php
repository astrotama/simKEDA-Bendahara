<?php
function spjgu_edit_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$bendid = arg(2);	
	if(arg(3)=='pdf'){		
	
	} else {
	
		//$btn = l('Cetak', '');
		//$btn .= "&nbsp;" . l('Excel', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary')));
		
		//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('pager');
		$output_form = drupal_get_form('spjgu_edit_main_form');
		return drupal_render($output_form);// . $output;
	}		
	
}

function spjgu_edit_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	$current_url = url(current_path(), array('absolute' => TRUE));
	$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjlastpage"] = $referer;
	else
		$referer = $_SESSION["spjlastpage"];
	
	//db_set_active('penatausahaan');
	$bendid = arg(2);
	$reset = arg(3);
	//drupal_set_message($bendid);
	$kodeuk = substr($bendid, 0, 2);		//apbd_getuseruk();
	$query = db_select('bendahara' . $kodeuk, 'd');

	# get the desired fields from the database
	$query->fields('d', array('bendid','keperluan', 'kodeuk', 'noref', 'kodekeg', 'spjno', 'tanggal', 'penerimanama', 'penerimanpwp', 'nokontrak', 'jenis', 'sudahproses', 'dispensasi', 'dokid'));
	$query->condition('d.bendid', $bendid, '=');
	
	# execute the query
	$results = $query->execute();
		
	$rows = array();
	$no = 0;
	foreach ($results as $data) {
		
		$bendid = $data->bendid;
		$kodekeg = $data->kodekeg;
		
		//$spjtgl = strtotime($data->tanggal);		
		
		$spjtgl = dateapi_convert_timestamp_to_datetime($data->tanggal);
		
		$spjno=$data->spjno;
		
		$noref = $data->noref;
		
		$kodeuk = $data->kodeuk;
		$keperluan = $data->keperluan;

		$penerimanama = $data->penerimanama;
		$penerimanpwp = $data->penerimanpwp;
		$nokontrak = $data->nokontrak;
		$jenis = $data->jenis;
		
		
		$sudahproses = $data->sudahproses;
		$dokid = $data->dokid;
		
		if ($data->dispensasi=='1') $sudahproses = 0;
		
		
	}
	
	if ($sudahproses=='1') {
		db_set_active('penatausahaan');
		
		//drupal_set_message($dokid);
		
		$results = db_query('select sppno, spptgl, keperluan from dokumen where dokid=:dokid', array(':dokid'=>$dokid));
			
		foreach ($results as $data) {
			$dok_tata_usaha = 'No. ' . $data->sppno . ' tgl. ' . apbd_fd($data->spptgl);
		}	
		
		db_set_active();
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
	$form['sudahproses'] = array(
		'#type' => 'value',
		'#value' => $sudahproses,
	);
	
	if ($sudahproses) {
		$form['desc'] = array(
			'#markup' => '<blockquote class="blockquote-reverse"><p style="color:red;font-size:smaller">SPJ sudah dibuatkan SPP ' . $dok_tata_usaha . ', nominal dan tanggal tidak bisa diubah lagi</p>
  <footer>Admin</footer></blockquote>',
		);
	}
	
	$form['spjno'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. SPJ'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		//'#required' => TRUE,
		'#default_value' => $spjno,
	);
	/*$form['spjtgl'] = array(
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
		
	);*/
	
	$form['tanggaltitle'] = array(
	'#markup' => 'tanggal',
	);
	$form['spjtgl']= array(
		 '#type' => 'date_select', // types 'date_select, date_text' and 'date_timezone' are also supported. See .inc file.
		 '#default_value' => $spjtgl, 
				
		 //'#default_value'=> array(
		//	'year' => format_date($TANGGAL, 'custom', 'Y'),
		//	'month' => format_date($TANGGAL, 'custom', 'n'), 
		//	'day' => format_date($TANGGAL, 'custom', 'j'), 
		 // ), 
		 
		 '#date_format' => 'd-m-Y',
		 '#date_label_position' => 'within', // See other available attributes and what they do in date_api_elements.inc
		 '#date_timezone' => 'America/Chicago', // Optional, if your date has a timezone other than the site timezone.
		 //'#date_increment' => 15, // Optional, used by the date_select and date_popup elements to increment minutes and seconds.
		 '#date_year_range' => '-30:+1', // Optional, used to set the year range (back 3 years and forward 3 years is the default).
		 //'#description' => 'Tanggal',
	);

	$opt_jenis['gu-spj'] = 'GU (GANTI UANG)';
	$opt_jenis['tu-spj'] = 'TU (TAMBAHAN UANG)';
	$form['jenis'] = array(
		'#type' => 'select',
		'#title' =>  t('Jenis SPJ'),
		'#options' => $opt_jenis,
		//'#default_value' => isset($form_state['values']['skpd']) ? $form_state['values']['skpd'] : $kodeuk,
		'#default_value' => $jenis,
	);	
	$form['nokontrak'] = array(
		'#type' => 'textfield',
		'#title' =>  t('No. Nota/Invoice'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $nokontrak,
	);
	$form['keperluan'] = array(
		'#type' => 'textfield',
		'#title' =>  t('Keperluan'),
		// The entire enclosing div created here gets replaced when dropdown_first
		// is changed.
		//'#disabled' => true,
		'#default_value' => $keperluan,
	);

	//PENERIMA
	$form['formpenerima'] = array (
		'#type' => 'fieldset',
		'#title'=> 'PENERIMA',
		'#collapsible' => TRUE,
		'#collapsed' => FALSE,        
	);	
		$form['formpenerima']['penerimanama']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('Nama'),
			//'#required' => TRUE,
			'#default_value'=> $penerimanama, 
		);				
		$form['formpenerima']['penerimanpwp']= array(
			'#type'         => 'textfield', 
			'#title' =>  t('NPWP'),
			//'#required' => TRUE,
			'#default_value'=> $penerimanpwp, 
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
			'#prefix' => '<div class="table-responsive"><table class="table"><tr><th width="10px">NO</th><th width="75px">KODE</th><th>URAIAN</th><th width="110px">ANGGARAN</th><th width="110px">CAIR</th><th width="120px">JUMLAH</th><th width="200px">KETERANGAN</th><th width="5px">A1</th><th width="5px">A2</th></tr>',
			 '#suffix' => '</table></div>',
		);	
		
		$i = 0;
		//$query = db_query('SELECT d.kodero,ro.uraian,d.jumlah, d.keterangan FROM `bendaharaitem`  as d inner join `rincianobyek` as ro on d.kodero=ro.kodero WHERE bendid= :bendid', array(':bendid'=>$bendid));
		$query = db_query('SELECT d.kodero,ro.uraian,d.jumlah, d.keterangan, a.anggaran FROM bendaharaitem' . $kodeuk . '  as d inner join rincianobyek as ro on d.kodero=ro.kodero inner join anggperkeg as a on d.kodero=a.kodero WHERE  a.kodekeg = :kodekeg and d.bendid = :bendid', array(':kodekeg' => $kodekeg, ':bendid' => $bendid));
		foreach ($query as $data) {

			$i++; 
			$kode = $data->kodero;
			$uraian = $data->uraian;
			$anggaran = $data->anggaran;
			$jumlah = $data->jumlah;
			$keterangan = $data->keterangan;

			//cair
			$cair = apbd_readrealisasikegiatan_rekening($kodekeg, $data->kodero, $spjtgl);
			
			$linkprint1 = apbd_link_print_small('/kuitansi/edit/' . $bendid . '/' . $kode, '');
			$linkprint2 = apbd_link_print_small('/kuitansi/edita2/' . $bendid . '/' . $kode, '');
			
			//$linkprint = apbd_link_print_small('/kuitansi/edita2/' . $bendid . '/' . $kode, '');
			
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
			$form['formdokumen']['tablerekening']['anggaran' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> '<p align="right">' . apbd_fn($anggaran) . '</p>' , 
				'#suffix' => '</td>',
			); 
			$form['formdokumen']['tablerekening']['cair' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> '<p align="right">' . apbd_fn($cair) . '</p>' , 
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
				'#suffix' => '</td>',
			);	
			$form['formdokumen']['tablerekening']['print1' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> $linkprint1, 
				'#suffix' => '</td>',
			);	
			$form['formdokumen']['tablerekening']['print2' . $i]= array(
				'#prefix' => '<td>',
				'#markup'=> $linkprint2, 
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
		'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="90px">KODE</th><th>URAIAN</th><th width="150px">JUMLAH</th><th width="200px">KETERANGAN</th></tr>',
		 '#suffix' => '</table>',
	);	 
	
	$i = 0;
	if ($reset=='resetpajak') {

		$query = db_select('ltpajak', 'p');
		$query->fields('p', array('kodepajak', 'uraian'));
		$results = $query->execute();
		foreach ($results as $data) {

			$i++; 
			$kode = $data->kodepajak;
			$uraian = $data->uraian;
			$jumlah = 0;
			$keterangan = '';
			$tag = 'new';

			$query_pajak = db_query('SELECT jumlah,keterangan FROM bendaharapajak' . $kodeuk . ' WHERE bendid=:bendid and kodepajak=:kodepajak', array(':bendid' => $bendid, ':kodepajak' => $kode));
			foreach ($query_pajak as $data_pajak) {
				$jumlah = $data_pajak->jumlah;
				$keterangan = $data_pajak->keterangan;
				$tag = 'old';
			}
		
			$form['formpajak']['tablepajak']['kodepajak' . $i]= array(
					'#type' => 'value',
					'#value' => $kode,
			); 
			$form['formpajak']['tablepajak']['tagpajak' . $i]= array(
					'#type' => 'value',
					'#value' => $tag,
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
	
		
		$form['jumlahrekpajak']= array(
			'#type' => 'value',
			'#value' => $i,
		);   
	
	} else {		//non reset

		$query = db_select('ltpajak', 'p');
		$query->leftJoin('bendaharapajak'. $kodeuk, 'bp', 'bp.kodepajak=p.kodepajak');
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
			$form['formpajak']['tablepajak']['tagpajak' . $i]= array(
					'#type' => 'value',
					'#value' => 'old',
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
		
		if (($i==0) and ($sudahproses=='0')) {
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
				$form['formpajak']['tablepajak']['tagpajak' . $i]= array(
						'#type' => 'value',
						'#value' => 'new',
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
		
		$form['formpajak']['submitreset']= array(
			'#type' => 'submit',
			'#value' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> Reset Pajak',
			'#disabled' => ($sudahproses==1),
			'#attributes' => array('class' => array('btn btn-info btn-sm pull-right')),			
		);
		
	}
	
	//SIMPAN
	//disable = ($sudahproses==0);
	
	if (isSuperuser()) {
		$form['formdata']['submitunlock']= array(
			'#type' => 'submit',
			'#value' => '<span class="glyphicon glyphicon-off" aria-hidden="true"></span> Unlock',
			//'#value' => 'Unlock',
			'#attributes' => array('class' => array('btn btn-danger btn-sm')),
			//'#disabled' => ($sudahproses==1),
			//'#suffix' => "&nbsp;<a href='" . $referer . "' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
			
		);
		$form['formcetak']['submitprint-reset']= array(
			'#type' => 'submit',
			'#value' => '<span class="glyphicon glyphicon-print" aria-hidden="true"></span>Reset',
			'#attributes' => array('class' => array('btn btn-warning btn-sm pull-right')),
		);
	}
	
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
		'#disabled' => ($sudahproses==1),
		'#suffix' => "&nbsp;<a href='" . $referer . "' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	return $form;
}

function spjgu_edit_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	//if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor SPJ harap diisi dengan benar');

	$penerimanama = $form_state['values']['penerimanama'];
	//if ($penerimanama=='') form_set_error('penerimanama', 'Nama penerima pembayaran harap diisi dengan benar');

	$penerimanpwp = $form_state['values']['penerimanpwp'];
	//if ($penerimanpwp=='') form_set_error('penerimanpwp', 'NPWP penerima pembayaran harap diisi dengan benar');
	
}
	
function spjgu_edit_main_form_submit($form, &$form_state) {
$bendid = $form_state['values']['bendid'];
$kodeuk = apbd_getuseruk();

if($form_state['clicked_button']['#value'] == $form_state['values']['submitreset']) {
	drupal_goto('spjgu/edit/' . $bendid . '/resetpajak');

} else if($form_state['clicked_button']['#value'] == $form_state['values']['submitprint-reset']) {

	$query = db_update('bendahara' . $kodeuk)
			->fields( 
			array(
				'sudahproses' => '0',

			)
		);
	$query->condition('bendid', $bendid, '=');
	$res = $query->execute();	
		
} else if($form_state['clicked_button']['#value'] == $form_state['values']['submitunlock']) {

	$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
	->fields(array(
		'dispensasi' => '1',
		  
	))
	->condition('bendid', $bendid, '=')
	->execute();
	
	drupal_set_message('SPJ sudah buka, perubahan bisa dilakukan');
	
} else {

	$spjno = $form_state['values']['spjno'];
	//$spjtgl = $form_state['values']['spjtgl'];
	//$tanggal = $spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
	
	$tanggal = dateapi_convert_timestamp_to_datetime($form_state['values']['spjtgl']);
	
	$keperluan = $form_state['values']['keperluan'];
	$jenis = $form_state['values']['jenis'];

	$nokontrak = $form_state['values']['nokontrak'];
	$penerimanama = $form_state['values']['penerimanama'];
	$penerimanpwp = $form_state['values']['penerimanpwp'];
	
	$jumlahrekening = $form_state['values']['jumlahrekening'];
	$jumlahrekpajak = $form_state['values']['jumlahrekpajak'];
	
	$sudahproses = $form_state['values']['sudahproses'];
	
	if ($sudahproses) {
		$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
		->fields(array(
			'spjno' => $spjno,
			'keperluan' => $keperluan,
			'nokontrak' => $nokontrak,
			'penerimanama' => $penerimanama,
			'penerimanpwp' => $penerimanpwp,
			  
		))
		->condition('bendid', $bendid, '=')
		->execute();
		
		drupal_set_message('SPJ sudah di-SPP-kan, perubahan tidak menyimpan nominal transaksi');
		
	}  else {

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
			$tagpajak = $form_state['values']['tagpajak' . $n];
			
			$pajak += $jumlahpajak;
			
			if ($jumlahpajak>0) {
				
				if ($tagpajak=='old') {						//old
					$query = db_update('bendaharapajak' . $kodeuk) 		// Table name no longer needs {}
					->fields(array(
						'jumlah' => $jumlahpajak,
						'keterangan' => $keterangan,
					))
					->condition('bendid', $bendid, '=')
					->condition('kodepajak', $kodepajak, '=')
					->execute();
					
				} else {									//new
					$query = db_insert('bendaharapajak' . $kodeuk) // Table name no longer needs {}
								->fields(array(
								  'bendid' => $bendid,
								  'kodepajak' => $kodepajak,
								  'jumlah' => $jumlahpajak,
								  'keterangan' => $keterangan,				  
						))
						->execute();				
				}	
				
			
			} else {										//delete
				if ($tagpajak=='old') {
					$num_deleted = db_delete('bendaharapajak' . $kodeuk)
						  ->condition('bendid', $bendid)
						  ->condition('kodepajak', $kodepajak)
						  ->execute();				
				}
			}
		}
		
		$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
		->fields(array(
			'spjno' => $spjno,
			'jenis' => $jenis,
			'tanggal' => $tanggal,
			'keperluan' => $keperluan,
			'nokontrak' => $nokontrak,
			'penerimanama' => $penerimanama,
			'penerimanpwp' => $penerimanpwp,
			
			'total' => $total,
			'pajak' => $pajak,

			'kasbendaharakeluar' => $total,
			  
		))
		->condition('bendid', $bendid, '=')
		->execute();
		
	}
	
	$referer = $_SESSION["spjlastpage"];
	drupal_goto($referer);
}	
}



?>