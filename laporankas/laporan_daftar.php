<?php
function laporan_daftar($arg=NULL, $nama=NULL) {
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
	
	
	// $output = getLaporan(arg(1),arg(2));
	//$output2= footer();
	$output = 'x';
	// apbd_ExportPDF('L', 'F4', $output, 'CEK');
	//print_pdf_p($output,$output2);
	return $output;
	
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

function getLaporan($setorid,$jenis){
	//$kodeuk='81';
	//..................................
	$header=array();
	$rows[]=array(
		array('data' => 'DAFTAR TRANSAKSI HARIAN BELANJA DAERAH(DTH)', 'width' => '875px','align'=>'center','style'=>'border:none;font-weight:bold;font-size:120%;'),
		
	);
	$rows[]=array(
		array('data' => 'KABUPATEN JEPARA', 'width' => '875px','align'=>'center','style'=>'border:none;font-weight:bold;font-size:120%;'),
		
	);
	$rows[]=array(
		array('data' => 'BULAN DESEMBER 2016', 'width' => '875px','align'=>'center','style'=>'border:none;font-weight:bold;font-size:120%;'),
		
	);
	
	$rows[]=array(
		array('data' => 'Kode', 'width' => '120px','align'=>'left','style'=>'border:none;'),
		array('data' => ': 102.14', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	$rows[]=array(
		array('data' => 'SKPD', 'width' => '120px','align'=>'left','style'=>'border:none;'),
		array('data' => ': PUSKESMAS KEDUNG II', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => 'No. Urut', 'width' => '30px','rowspan'=>2,'align'=>'center','style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SPM/SPD', 'width' => '120px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'SP2D', 'width' => '120px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Kode Akun Belanja', 'rowspan'=>2, 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Potongan Pajak', 'width' => '180px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Rekaman/Bendahara', 'width' => '280px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Ket', 'rowspan'=>2, 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		
	);
	$rows[]=array(
		array('data' => 'Nomor', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Nilai Belanja', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Nomor', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Nilai Belanja', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Kode Akun', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Jenis Pajak', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Jumlah', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'NPWP', 'width' => '140px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => 'Nama', 'width' => '140px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		
	);
	
	for($n=0;$n<3;$n++){
		
	$rows[]=array(
		array('data' => $n, 'width' => '30px','align'=>'center','style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '140px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '140px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => ' ', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		
	);
	
	}
	
	$rows[]=array(
		array('data' => 'TOTAL', 'width' => '90px','align'=>'left','style'=>'border-left:1px solid black;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => '1', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => '2', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => '3', 'width' => '180px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => '4', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => '5', 'width' => '60px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		array('data' => '6', 'width' => '340px','align'=>'center','style'=>'border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'),
		
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => '<blockquote>Bersama ini terlampir SSP lembar ke-3.</blockquote>', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => '<blockquote>Yang bertandatangan dibawah ini menyatakan bahwa Daftar Transaksi Harian Belanja Daerah ini dibuat dengan sebenarnya dan saya bertanggungjawab penuh atas kebenaran data yang tercantum dalam Daftar Transaksi Harian Belanja Daerah ini.</blockquote>', 'width' => '800px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => 'Mengetahui', 'width' => '400px','align'=>'center','style'=>'border:none'),
		array('data' => 'Jepara, .............................', 'width' => '400px','align'=>'center','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => 'PENGGUNA ANGGARAN', 'width' => '400px','align'=>'center','style'=>'border:none'),
		array('data' => 'BENDAHARA PENGELUARAN', 'width' => '400px','align'=>'center','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '400px','align'=>'left','style'=>'border:none'),
	);
	
	$rows[]=array(
		array('data' => '<u>dr. PRIYO PURWANTO</u>', 'width' => '400px','align'=>'center','style'=>'border:none;font-weight:bold;'),
		array('data' => '<u>ABDUL MANAN</u>', 'width' => '400px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	
	$rows[]=array(
		array('data' => 'NIP. 196001131999031001', 'width' => '400px','align'=>'center','style'=>'border:none;'),
		array('data' => 'NIP. 197001052007011021', 'width' => '400px','align'=>'center','style'=>'border:none;font-weight:bold;'),
	);
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	$rows=null;
	$output .= createT($header,$rows,null);
	//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
	return $output;
}


?>
