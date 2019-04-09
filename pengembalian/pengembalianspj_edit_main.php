<?php
function pengembalianspj_edit_main($arg=NULL, $nama=NULL) {
	//drupal_add_css('files/css/textfield.css');
	
	$bendid = arg(2);	
	if(arg(3)=='pdf'){		
	
	} else {
	
		//$btn = l('Cetak', '');
		//$btn .= "&nbsp;" . l('Excel', '' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary')));
		
		//$output = theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('pager');
		$output_form = drupal_get_form('pengembalianspj_edit_main_form');
		return drupal_render($output_form);// . $output;
	}		
	
}

function pengembalianspj_edit_main_form($form, &$form_state) {

	//FORM NAVIGATION	
	$current_url = url(current_path(), array('absolute' => TRUE));
	$referer = $_SERVER['HTTP_REFERER'];
	
	if (strpos($referer, 'arsip')>0)
		$_SESSION["spjlastpage"] = $referer;
	else
		$referer = $_SESSION["spjlastpage"];
	
	//db_set_active('penatausahaan');
	$bendid = arg(2);
	
	$kodeuk = apbd_getuseruk();
	$query = db_select('bendahara' . $kodeuk, 'd');

	# get the desired fields from the database
	$query->fields('d', array('bendid','keperluan', 'kodeuk', 'kodekeg', 'noref', 'spjno', 'tanggal', 'jenispanjar', 'sudahproses'));
	$query->condition('d.bendid', $bendid, '=');
	
	# execute the query
	$results = $query->execute();
		
	$rows = array();
	$no = 0;
	foreach ($results as $data) {
		
		$bendid = $data->bendid;
		$kodekeg = $data->kodekeg;
		
		$spjtgl = strtotime($data->tanggal);		
		$spjno=$data->spjno;
		
		$noref = $data->noref;
		
		$kodeuk = $data->kodeuk;
		$keperluan = $data->keperluan;

		$jenispanjar = $data->jenispanjar;
		
		$sudahproses = $data->sudahproses;
		
		
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

	//JENIS KAS
	$opt_panjar = array();
	$opt_panjar['gu'] = 'Ganti Uang';
	$opt_panjar['tu'] = 'Tambahan Uang';
	$opt_panjar['ls'] = 'Langsung/Gaji';
	$form['jenispanjar'] = array(
		'#type' => 'radios',
		'#title' =>  t('Jenis SPJ (GU/TU/Langsung/Gaji)'),
		'#options' => $opt_panjar,
		'#default_value' => $jenispanjar,
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
			'#prefix' => '<table class="table table-hover"><tr><th width="10px">NO</th><th width="75px">KODE</th><th>URAIAN</th><th width="110px">ANGGARAN</th><th width="110px">CAIR</th><th width="120px">JUMLAH</th><th width="200px">KETERANGAN</th></tr>',
			 '#suffix' => '</table>',
		);	
		
		$i = 0;
		//$query = db_query('SELECT d.kodero,ro.uraian,a.anggaran,d.jumlah,d.keterangan FROM `bendaharaitem`  as d inner join `rincianobyek` as ro on d.kodero=ro.kodero inner join `anggperkeg` as a on d.kodekeg=a.kodekeg and d. WHERE d.bendid= :bendid', array(':bendid'=>$bendid));
		//$query = db_query('SELECT bi.kodero,ro.uraian,a.anggaran,bi.jumlah,bi.keterangan FROM `bendaharaitem` as bi inner join `rincianobyek` as ro on bi.kodero=ro.kodero right join `anggperkeg` as a on bi.kodero=a.kodero WHERE bi.bendid=:bendid AND a.kodekeg=:kodekeg', array(':bendid' => $bendid, ':kodekeg' => $kodekeg));
		$query = db_query('SELECT ro.kodero,ro.uraian,a.anggaran,bi.jumlah,bi.keterangan FROM anggperkeg as a inner join rincianobyek as ro on a.kodero=ro.kodero left join bendaharaitem' . $kodeuk . ' as bi on a.kodero=bi.kodero WHERE a.kodekeg=:kodekeg AND bi.bendid=:bendid', array(':kodekeg' => $kodekeg, ':bendid' => $bendid));
		foreach ($query as $data) {
			
			$cair = apbd_readrealisasikegiatan_rekening($kodekeg, $data->kodero, $spjtgl);
			
			$i++; 
			$kode = $data->kodero;
			$uraian = $data->uraian;
			$anggaran = $data->anggaran;
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
				'#suffix' => '</td></tr>',
			);	
		}
		$form['formdokumen']['jumlahrekening']= array(
			'#type' => 'value',
			'#value' => $i,
		);	

	//SIMPAN
	//$disable = ($sudahproses==0);
	$form['formdata']['submit']= array(
		'#type' => 'submit',
		'#value' => '<span class="glyphicon glyphicon-file" aria-hidden="true"></span> Simpan',
		'#attributes' => array('class' => array('btn btn-success btn-sm')),
		//'#disabled' => ($sudahproses==1),
		'#suffix' => "&nbsp;<a href='" . $referer . "' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>Tutup</a>",
		
	);
	
	return $form;
}

function pengembalianspj_edit_main_form_validate($form, &$form_state) {
	$spjno = $form_state['values']['spjno'];
	if (($spjno=='') or ($spjno=='BARU')) form_set_error('spjno', 'Nomor SPJ harap diisi dengan benar');

}
	
function pengembalianspj_edit_main_form_submit($form, &$form_state) {
$bendid = $form_state['values']['bendid'];

$spjno = $form_state['values']['spjno'];
$spjtgl = $form_state['values']['spjtgl'];
$tanggal = $spjtgl['year'].'-'.$spjtgl['month'].'-'.$spjtgl['day'];
$keperluan = $form_state['values']['keperluan'];
$jenispanjar = $form_state['values']['jenispanjar'];

$jumlahrekening = $form_state['values']['jumlahrekening'];

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

$query = db_update('bendahara' . $kodeuk) // Table name no longer needs {}
->fields(array(
	'spjno' => $spjno,
	'jenispanjar' => $jenispanjar,
	'tanggal' => $tanggal,
	'keperluan' => $keperluan,
	
	'total' => $total,

	  
))
->condition('bendid', $bendid, '=')
->execute();

//AKUNTANSI
/*
db_set_active('akuntansi');
for($n=1;$n<=$jumlahrekening;$n++){
	$kodero = $form_state['values']['kodero' . $n];
	$keterangan = $form_state['values']['keterangan' . $n];
	$jumlah = $form_state['values']['jumlah' . $n];
	
	$query = db_update('bendaharaitem') // Table name no longer needs {}
	->fields(array(
		'jumlah' => $jumlah,
		'keterangan' => $keterangan,
	))
	->condition('bendid', $bendid, '=')
	->condition('kodero', $kodero, '=')
	->execute();
	
}
$query = db_update('bendahara') // Table name no longer needs {}
->fields(array(
	'spjno' => $spjno,
	'jenispanjar' => $jenispanjar,
	'tanggal' => $tanggal,
	'keperluan' => $keperluan,
	
	'total' => $total,

	  
))
->condition('bendid', $bendid, '=')
->execute();
db_set_active();
*/

$referer = $_SESSION["spjlastpage"];
drupal_goto($referer);

}



?>
