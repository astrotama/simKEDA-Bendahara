<?php
function opd_main($arg=NULL, $nama=NULL) {
  
	$qlike='';
	$limit = 20;
	
	$cetakpdf = arg(1);
	$hal1 = 1;
	$margin = 10;
	$marginkiri = 20;
	
	if ($cetakpdf == 'pdf') {
		$output = data_opd_main_print();
		
		$_SESSION["hal1"] = $hal1;
		apbd_ExportPDF_P($output, $margin, $marginkiri,"Laporan_OPD.pdf");
		//return $output;
		
	} else if ($cetakpdf=='excel') {

		header( "Content-Type: application/vnd.ms-excel" );
		header( "Content-disposition: attachment; filename=Laporan_OPD.xls" );
		header("Pragma: no-cache"); 
		header("Expires: 0");
		$output = data_opd_main_print();
		echo $output;
		//echo 'John' . "\t" . 'Doe' . "\t" . '555-5555' . "\n";
		 
	} else {
		//drupal_set_message(arg(4));
		$output = data_opd_main();
		
		
		$btn = l('Cetak', 'opd/pdf' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary glyphicon glyphicon-print')));
		$btn .= '&nbsp;' . l('Excel', 'opd/excel' , array ('html' => true, 'attributes'=> array ('class'=>'btn btn-primary glyphicon glyphicon-floppy-save')));
		

		//$btn = '';
		
		return $btn . $output;
		
	}
	
}

function data_opd_main() {
	
	$qlike='';
	$limit = 20;
	
	$header = array (
		array('data' => 'No','width' => '10px', 'valign'=>'top'),
		array('data' => 'Kode',  'field'=> 'kodedinas', 'valign'=>'top'), 
		array('data' => 'Nama', 'field'=> 'namauk', 'valign'=>'top'),
		array('data' => 'Singkatan', 'field'=> 'namasingkat', 'valign'=>'top'),
		array('data' => 'Pimpinan', 'field'=> 'pimpinannama', 'valign'=>'top'),
		array('data' => 'Bendahara', 'field'=> 'bendaharanama', 'valign'=>'top'),
		//array('data' => '', 'width' => '20px', 'valign'=>'top'),
	);
		
		$query = db_select('unitkerja', 'uk')->extend('PagerDefault')->extend('TableSort');
		
		# get the desired fields from the database
		$query->fields('uk', array('kodeuk', 'namasingkat', 'namauk', 'kodedinas', 'pimpinannama', 'bendaharanama'));

		if (!isSuperuser()) {
			$kodeuk = apbd_getuseruk();
			$query->condition('uk.kodeuk', $kodeuk, '=');	
			
		}
		
		$query->orderByHeader($header);
		$query->orderBy('uk.kodedinas', 'ASC');
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
			
			
			$skpd = l($data->namauk, 'opd/edit/' . $data->kodeuk , array ('html' => true));
			
			//$editlink =  apbd_button_hapus('operator/delete/' . $data->username);
			
			
			$rows[] = array(
							array('data' => $no, 'width' => '10px', 'align' => 'right', 'valign'=>'top'),
							array('data' => $data->kodedinas, 'align' => 'left', 'valign'=>'top'),
							array('data' => $skpd, 'align' => 'left', 'valign'=>'top'),
							array('data' => $data->namasingkat, 'align' => 'left', 'valign'=>'top'),
							array('data' => $data->pimpinannama, 'align' => 'left', 'valign'=>'top'),
							array('data' => $data->bendaharanama, 'align' => 'left', 'valign'=>'top'),
							//array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
						);
		}

		//BUTTON
		//$btn = apbd_button_baru('operator/edit');
		//$btn .= "&nbsp;" . apbd_button_excel('');	
		
		
		$output = theme('table', array('header' => $header, 'rows' => $rows ));
		$output .= theme('pager');

		return $output;
	
}

function data_opd_main_print() {
	
	$qlike='';
	//$limit = 20;
	
	$header = array (
		array('data' => ' No','width' => '20px', 'valign'=>'top','style'=>'font-weight: bold;border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' Kode', 'width' => '50px', 'valign'=>'top','style'=>'font-weight: bold;border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'), 
		array('data' => ' Nama' , 'width' => '120px' , 'valign'=>'top','style'=>'font-weight: bold;border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' Singkatan', 'width' => '100px', 'valign'=>'top','style'=>'font-weight: bold;border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' Pimpinan', 'width' => '120px','style'=>'font-weight: bold;border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' Bendahara', 'valign'=>'top','style'=>'font-weight: bold;border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		//array('data' => '', 'width' => '20px', 'valign'=>'top'),
	);
		
		$query = db_select('unitkerja', 'uk')->extend('TableSort');
		
		# get the desired fields from the database
		$query->fields('uk', array('kodeuk', 'namasingkat', 'namauk', 'kodedinas', 'pimpinannama', 'bendaharanama'));

		if (!isSuperuser()) {
			$kodeuk = apbd_getuseruk();
			$query->condition('uk.kodeuk', $kodeuk, '=');	
			
		}
		
		$query->orderByHeader($header);
		$query->orderBy('uk.kodedinas', 'ASC');
		//$query->limit($limit);	
		
			
		# execute the query
		$results = $query->execute();
			
			/*
		# build the table fields
		$no=0;

		if (isset($_GET['page'])) {
			$page = $_GET['page'];
			$no = $page * $limit;
		} else {
			$no = 0;
		} 
		*/

			
		$rows = array();
		foreach ($results as $data) {
			$no++;  
			
			
			
			
			//$editlink =  apbd_button_hapus('operator/delete/' . $data->username);
			
			
			$rows[] = array(
							array('data' => ' ' . $no . ' ','width' => '20px', 'valign'=>'top', 'style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;;border-bottom:1px solid black;'),
							array('data' => ' ' . $data->kodedinas . ' ', 'width' => '50px', 'align' => 'left', 'valign'=>'top', 'style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;;border-bottom:1px solid black;'),
							array('data' => ' ' . $data->namauk . ' ', 'width' => '120px', 'align' => 'left', 'valign'=>'top', 'style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;;border-bottom:1px solid black;'),
							array('data' => ' ' . $data->namasingkat . ' ', 'width' => '100px', 'align' => 'left', 'valign'=>'top', 'style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;;border-bottom:1px solid black;'),
							array('data' => ' ' . $data->pimpinannama . ' ', 'width' => '120px', 'align' => 'left', 'valign'=>'top', 'style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;;border-bottom:1px solid black;'),
							array('data' => ' ' .$data->bendaharanama . ' ', 'align' => 'left', 'valign'=>'top', 'style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;;border-bottom:1px solid black;'),
							//array('data' => $editlink, 'align' => 'left', 'valign'=>'top'),
						);
		}

		//BUTTON
		//$btn = apbd_button_baru('operator/edit');
		//$btn .= "&nbsp;" . apbd_button_excel('');	
		
		
		$output = theme('table', array('header' => $header, 'rows' => $rows ));
		//$output .= theme('pager');

		return $output;
	
}

function opd_main_form_submit($form, &$form_state) {
	
}

function opd_main_form($form, &$form_state) {
	
}

?>