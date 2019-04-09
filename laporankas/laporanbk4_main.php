<?php
function laporanbk4_main($arg=NULL, $nama=NULL) {
    $h = '<style>label{font-weight: bold; display: block; width: 140px; float: left;}</style>';
    //drupal_set_html_head($h);
	//drupal_add_css('apbd.css');
	//drupal_add_css('files/css/tablenew.css');
	//drupal_add_js('files/js/kegiatancam.js');
	$qlike='';
	$limit = 10;
    
	if ($arg) {
		
				$tahun = arg(2);
				$jurnalid = arg(3);
		
	} else {
		$tahun = 2015;		//variable_get('apbdtahun', 0);
		$jurnalid = '';
		
	}
	
	drupal_set_title('Jurnal');
	//drupal_set_message($tahun);
	//drupal_set_message($kodeuk);
	
	
	$output = getLaporanbk4();
	//$output2= footer();
	apbd_ExportPDF('P', 'F4', $output, 'CEK');
	//print_pdf_p($output,$output2);
	
	
}



/**
 * Selects just the second dropdown to be returned for re-rendering.
 *
 * Since the controlling logic for populating the form is in the form builder
 * function, all we do here is select the element and return it to be updated.
 *
 * @return array
 *   Renderable array (the second dropdown)
 */

function getLaporanbk4(){
	$styleheader='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;';
	$style='border-right:1px solid black;';
	
	$header=array();
	$rows[]=array(
		array('data' => 'BUKU SIMPANAN BANK', 'width' => '500px','align'=>'center','style'=>'border:none;font-weight:bold;font-size:120%;'),
	);
	$rows[]=array(
		array('data' => 'Tanggal 1 Jan 2016 s/d 20 Jan 2016', 'width' => '500px','align'=>'center','style'=>'border:none;font-size:100%;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	
	$rows[]=array(
		array('data' => 'SKPD', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => 'DINAS SOSIAL, TENAGA KERJA DAN TRANSMIGRASI', 'width' => '380px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'Pengguna Anggaran / Kuasa PA', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => 'Drs. MOHAMMAD ZAHID, M.Pd', 'width' => '380px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'Bendahara Pengeluaran', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => 'YAWANAH', 'width' => '380px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => 'Pembantu Bend. Pengeluaran', 'width' => '100px','align'=>'left','style'=>'border:none;'),
		array('data' => ':', 'width' => '20px','align'=>'center','style'=>'border:none;'),
		array('data' => 'YAWANAH', 'width' => '380px','align'=>'left','style'=>'border:none;'),
		
	);
	$rows[]=array(
		array('data' => '', 'width' => '500px','align'=>'center','style'=>'border-bottom:1px solid black;font-weight:bold'),
	);
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	
	$header=null;
	$rows=null;
	$header[]=array(
		array('data' => 'No. Urut', 'width' => '30px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;'),
		array('data' => 'Tanggal', 'width' => '50px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;'),
		array('data' => 'Uraian', 'width' => '160px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;'),
		array('data' => 'Ref', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;'),
		array('data' => 'Pemotongan (Rp)', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;'),
		array('data' => 'Penyetoran (Rp)', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;'),
		array('data' => 'S a l d o  (Rp)', 'width' => '70px','align'=>'center','style'=>'border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;'),
	);
	
	
	//Content
	
	$rows[]=array(
		array('data' => '', 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;'),
		array('data' => '', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;'),
		array('data' => 'Saldo sebelumnya', 'width' => '160px','align'=>'left','style'=>'border-right:1px solid black;'),
		array('data' => '', 'width' => '60px','align'=>'left','style'=>'border-right:1px solid black;'),
		array('data' => '0', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;'),
		array('data' => '0', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;'),
		array('data' => '0', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;'),
	);
	for($n=1;$n<5;$n++){
		$rows[]=array(
			array('data' => $n, 'width' => '30px','align'=>'right','style'=>'border-right:1px solid black;border-left:1px solid black;'),
			array('data' => '04-01-16', 'width' => '50px','align'=>'left','style'=>'border-right:1px solid black;'),
			array('data' => 'Gaji PNS & CPNS Dinsosnakertrans Kab Jepara bulan Januari 2016 / PPh Pasal 21/0', 'width' => '160px','align'=>'left','style'=>'border-right:1px solid black;'),
			array('data' => '', 'width' => '60px','align'=>'left','style'=>'border-right:1px solid black;'),
			array('data' => '1.969.764', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;'),
			array('data' => '0', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;'),
			array('data' => '0', 'width' => '70px','align'=>'right','style'=>'border-right:1px solid black;'),
		);
	}
	$rows[]=array(
		array('data' => 'TOTAL', 'width' => '300px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-left:1px solid black;border-bottom:2px solid black;font-weight:bold;'),
		
		
		array('data' => '215.261.900', 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;'),
		array('data' => '215.261.900', 'width' => '70px','align'=>'right','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;font-weight:bold;'),
		array('data' => '', 'width' => '70px','align'=>'right','style'=>'border-bottom:2px solid black;border-top:1px solid black;font-weight:bold;border-right:1px solid black;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => 'Mengesahkan','width' => '250px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => '','width' => '670px', 'align'=>'center','style'=>'border:none;'),
	);
	$rows[] = array(
					array('data' => 'NIP.','width' => '250px', 'align'=>'center','style'=>'border:none;'),
					array('data' => '','width' => '250px', 'align'=>'center','style'=>'border:none;'),
	);
	
	$output.=createT($header, $rows);
		return $output;
}

/**
 * Helper function to populate the first dropdown.
 *
 * This would normally be pulling data from the database.
 *
 * @return array
 *   Dropdown options.
 */



?>
