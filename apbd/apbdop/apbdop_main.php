<?php

function apbdop_main($arg=NULL, $nama=NULL) {
    $h = '<style>label{font-weight: bold; display: block; width: 150px; float: left;}</style>';
    //drupal_set_html_head($h);
	//drupal_add_css('apbd.css');
	//drupal_add_css('files/css/tablenew.css');
	//drupal_add_js('files/js/kegiatancam.js');
	$qlike='';
	$limit = 20;
	
	
	//drupal_set_title('PENDAPATAN #' . $bulan);
	
	$output_form = drupal_get_form('apbdop_main_form');
	$header = array (
		array('data' => 'No','width' => '10px', 'valign'=>'top'),
		array('data' => 'Username',  'field'=> 'username', 'valign'=>'top'), 
		array('data' => 'Nama', 'field'=> 'nama', 'valign'=>'top'),
		array('data' => 'SKPD', 'field'=> 'namasingkat', 'valign'=>'top'),
		array('data' => 'Hak Akses', 'field'=> 'rid', 'valign'=>'top'),
		array('data' => 'Akses Terakhir', 'field'=> 'access', 'valign'=>'top'),
		array('data' => '', 'width' => '20px', 'valign'=>'top'),
	);
		
		$query = db_select('apbdop', 'u')->extend('PagerDefault')->extend('TableSort');
		$query->join('unitkerja', 'uk', 'u.kodeuk=uk.kodeuk');
		$query->innerJoin('users', 's', 'u.username=s.name');
		$query->innerJoin('users_roles', 'ur', 's.uid=ur.uid');
		
		# get the desired fields from the database
		$query->fields('u', array('username','nama'));
		$query->fields('uk', array('namasingkat'));
		$query->fields('s', array('access'));
		$query->fields('ur', array('rid'));
		
		if (!isSuperuser()) {
			$kodeuk = apbd_getuseruk();
			$query->condition('uk.kodeuk', $kodeuk, '=');	
			
			/*
			$or = db_or();
			$or->condition('ur.rid', '6', '=');	
			$or->condition('ur.rid', '5', '=');	
			$query->condition($or);			
			*/
		}
		
		$query->orderByHeader($header);
		$query->orderBy('u.username', 'ASC');
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
			
			//$keterangan = l($data->jumlahrekening . ' Rekening <span class="glyphicon glyphicon-th-large" aria-hidden="true"></span>' , 'pendapatanrek/filter/' . $bulan . '/' . $data->kodeskpd . '/' . $kodek . '/5' , array ('html' => true, 'attributes'=> array ('class'=>'text-success pull-right')));
			
			$username = l($data->username, 'operator/edit/' . $data->username , array ('html' => true));
			
			if ($data->access==0)
				$access = 'Belum Pernah';
			else
				$access = gmdate("d M Y H:i", $data->access);
			
			$editlink =  apbd_button_hapus('operator/delete/' . $data->username);
			
			
			if ($data->rid=='3')
				$ha = 'Administrator';
			else if ($data->rid=='4')	
				$ha = 'Superuser';
			else if ($data->rid=='5')	
				$ha = 'SKPD';
			else if ($data->rid=='6')	
				$ha = 'Bidang';
			else if ($data->rid=='7')	
				$ha = 'Seksi';
			else if ($data->rid=='8')	
				$ha = 'Auditor';
			else
				$ha = '';
			
			$rows[] = array(
							array('data' => $no, 'width' => '10px', 'align' => 'right', 'valign'=>'top'),
							array('data' => $username, 'align' => 'left', 'valign'=>'top'),
							array('data' => $data->nama, 'align' => 'left', 'valign'=>'top'),
							array('data' => $data->namasingkat, 'align' => 'left', 'valign'=>'top'),
							array('data' => $ha, 'align' => 'left', 'valign'=>'top'),
							array('data' => $access, 'align' => 'left', 'valign'=>'top'),
							array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
						);
		}

		//BUTTON
		//$btn = apbd_button_baru('operator/edit');
		$btn = l('<span class="glyphicon glyphicon-user" aria-hidden="true"></span> Baru', 'operator/edit' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary btn-sm')));
		//$btn .= "&nbsp;" . apbd_button_excel('');	
		//$btn .= apbd_button_chart('pendapatan/chart/' . $bulan.'/##/jenis_rb');
		
		
		$output = theme('table', array('header' => $header, 'rows' => $rows ));
		$output .= theme('pager');

		return drupal_render($output_form) . $btn . $output . $btn;
		//return $btn . $output . $btn;
}

function apbdop_main_form_submit($form, &$form_state) {
	/*
	$bulan= $form_state['values']['bulan'];
	$kodek = $form_state['values']['kodek'];
	
	$uri = 'pendapatan/filter/' . $bulan.'/' . $kodek;
	drupal_goto($uri);	
	*/
}

function apbdop_main_form($form, &$form_state) {
	
	if(arg(2)!=null){
		
		$kodeuk = arg(2);
	} 

	$akses_id = '';
	$arr_akses_by_id = array();
	
	$roles = user_roles(TRUE);
	foreach( array_keys($roles) as $rid) {
		//drupal_set_message($rid . $roles[$rid]);
		switch ($roles[$rid]) {
			
			
			case 'administrator' :
				//$arr_akses_by_id[$rid] = 'Super User';
				if (isAdministrator()) {
					$arr_akses_by_id[$rid] = 'Administrator';
				}
				break;
			case 'superuser' :
				//$arr_akses_by_id[$rid] = 'Super User';
				if (isSuperuser()) {
					$arr_akses_by_id[$rid] = 'Superuser';
				}
				break;
			case 'bidang':
				//$arr_akses_by_id[$rid] = 'User Kecamatan (Musrenbangcam)';
				$arr_akses_by_id[$rid] = 'Bidang';
				break;
			case 'pembantu':
				//$arr_akses_by_id[$rid] = 'User Kecamatan (Musrenbangcam)';
				$arr_akses_by_id[$rid] = 'Bidang';
				break;
			case 'seksi':
				$arr_akses_by_id[$rid] = 'Seksi';
				break;
			case 'skpd':
				//$arr_akses_by_id[$rid] = 'User SKPD (non Kecamatan)';
				$arr_akses_by_id[$rid] = 'SKPD';
				break;
			case 'verifikator':
				if (isSuperuser()) {
					$arr_akses_by_id[$rid] = 'Verifikator';
				}
				break;
			case 'auditor':
				if (isSuperuser()) {
					$arr_akses_by_id[$rid] = 'Auditor';
				}
				break;
				
		}
		
	}
	
	if (isSuperuser()) {
		
		$options = array();
		$options[] = '-SUPERUSER/VERIFIKATOR-';
		$results = db_query('select namasingkat,kodeuk,kodedinas from {unitkerja} order by kodedinas');
		foreach($results as $data) {
			$options[$data->kodeuk] = $data->namasingkat;
		}		
		
		$form['kodeuk']= array(
			'#type'         => 'select', 
			'#title'        => 'Unit Kerja',
			'#options'		=> $options,
			//'#description'  => 'kodeuk', 
			//'#maxlength'    => 60, 
			//'#size'         => 20, 
			//'#required'     => !$disabled, 
			//'#disabled'     => $disabled, 
			'#default_value'=> $kodeuk, 
		);

		$form['kodesuk']= array(
			'#type'         => 'hidden', 
			'#default_value'=> $kodesuk, 
		);		
		$form['akses']= array(
			'#type'         => 'select', 
			'#title'        => 'Hak Akses',
			'#options'		=> $arr_akses_by_id,
			//'#description'  => 'kodeuk', 
			//'#maxlength'    => 60, 
			//'#size'         => 20, 
			//'#required'     => !$disabled, 
			//'#disabled'     => $disabled, 
			'#default_value'=> $akses_id, 
		); 
		
	} else {

		$kodeuk = apbd_getuseruk();
		
		$form['kodeuk']= array(
			'#type'         => 'hidden', 
			'#default_value'=> $kodeuk, 
		);

		$form['akses']= array(
			'#type'         => 'select', 
			'#title'        => 'Hak Akses',
			'#options'		=> $arr_akses_by_id,
			'#default_value'=> $akses_id, 
		); 
		
		
		$subskpd = array();
		$subskpd[''] = '- Pilih Bidang -';
		
		/*
		$query = db_select('subunitkerja', 's');
		$query->fields('s', array('kodesuk','namasuk'));	
		$query->condition('s.kodeuk', $kodeuk, '=');	
		$query->orderBy('s.kodeuk', 'ASC');	
		$results = $query->execute();
		*/
		
		$results = db_query('select kodesuk, namasuk from subunitkerja where kodeuk=:kodeuk order by kodeuk', array(':kodeuk'=>$kodeuk));
		foreach ($results as $data) {
			$subskpd[$data->kodesuk] = $data->namasuk;
			
			$respa = db_query('select kodepa, namapa from PelakuAktivitas where kodesuk=:kodesuk order by kodepa', array(':kodesuk'=>$data->kodesuk));
			foreach ($respa as $datapa) {
				$subskpd[$datapa->kodepa] = '- ' . $datapa->kodepa . ' ' . $datapa->namapa;				
			}
		}
		
		$form['kodesuk']= array(
			'#type'         => 'select', 
			'#title'        => 'Bidang/Bagian/Seksi',
			'#options'		=> $subskpd,
			//'#description'  => 'kodesuk', 
			//'#maxlength'    => 60, 
			//'#size'         => 20,  
			//'#required'     => !$disabled, 
			//'#disabled'     => $disabled, 
			'#default_value'=> $kodesuk, 
		); 
		
		
	}
		
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span> Tampilkan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
	);
	return $form;
}

?>
