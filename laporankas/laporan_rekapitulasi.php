<?php
function laporan_rekapitulasi($arg=NULL, $nama=NULL) {
    $h = '<style>label{  display: block; width: 140px; float: left;}</style>';
    //drupal_set_html_head($h);
	//drupal_add_css('apbd.css');
	//drupal_add_css('files/css/tablenew.css');
	//drupal_add_js('files/js/kegiatancam.js');
	$qlike='';
	$limit = 10;
	
	$pem_nub = arg(1);
	drupal_set_message($pem_nub);
	
	$output = getLaporan($pem_nub);
	//$output2= footer();
	apbd_ExportPDF('P', 'A4', $output, 'CEK');
	print_pdf_p($output,$output2);
	
	
}



/**
 * Selects just the second dropbottom to be returned for re-rendering.
 *
 * Since the controlling logic for populating the form is in the form builder
 * function, all we do here is select the element and return it to be updated.
 *
 * @return array
 *   Renderable array (the second dropbottom)
 */

function getLaporan($pem_nub){
	
	$header=array();
	
	$rows[]=array(
		array('data' => '<b>REKAPITULASI TRANSAKSI HARIAN BELANJA DAERAH (RTH)</b> <br /> KABUPATEN JEPARA <br /> BULAN : OKTOBER 2016', 'width' => '500px','align'=>'center','style'=>'border:none; font-size:25px;'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '500px','align'=>'center','style'=>'border:none; font-size:30px;'),
	);
	
	$rows[]=array(
		array('data' => 'No. Urut', 'rowspan'=>2, 'width' => '20px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => 'SKPD', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => 'SPM/SPD', 'width' => '110px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black; font-size:20px; '),
		array('data' => 'SP2D', 'width' => '110px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black; font-size:20px; '),
		array('data' => 'Jumlah Potongan Pajak(Rp)', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => 'Keterangan', 'rowspan'=>2, 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
	);
	
	$rows[]=array(
		array('data' => 'Jumlah', 'width' => '30px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => 'Nilai Belanja(Rp)', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black;border-bottom: 1px solid black; font-size:20px; '),
		array('data' => 'Jumlah', 'width' => '30px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black; font-size:20px;border-bottom: 1px solid black; '),
		array('data' => 'Nilai Belanja(Rp)', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-top: 1px solid black; border-right: 1px solid black; font-size:20px;border-bottom: 1px solid black; '),
	);
	
	for($n=0;$n<3;$n++){
		$rows[]=array(
			array('data' => $n, 'width' => '20px','align'=>'center','style'=>'border-left:1px solid black; font-size:20px; border-bottom: 1px solid black;'),
			array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black; font-size:20px; border-bottom: 1px solid black;'),
			array('data' => ' ', 'width' => '30px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px;border-bottom: 1px solid black; '),
			array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px;border-bottom: 1px solid black; '),
			array('data' => ' ', 'width' => '30px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px;border-bottom: 1px solid black; '),
			array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black; border-right: 1px solid black; font-size:20px;border-bottom: 1px solid black; '),
			array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black; border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
			array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black; border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		);
	}
	
	$rows[]=array(
		array('data' => 'TOTAL', 'width' => '130px','align'=>'center','style'=>'border-left:1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => ' ', 'width' => '30px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => ' ', 'width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
		array('data' => ' ','width' => '80px','align'=>'center','style'=>'border-left:1px solid black;border-right: 1px solid black; font-size:20px; border-bottom: 1px solid black;'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '500px','align'=>'center','style'=>'border:none; font-size:30px;'),
	);
	
	$rows[]=array(
		array('data' => '<blockquote>Bersama ini terampir :</blockquote>', 'width' => '500px','align'=>'left','style'=>'border:none; font-size:20px;'),
	);
	
	$rows[]=array(
		array('data' => '<blockquote>a. Daftar Transaksi Harian Belanja yang dibuat oleh Bendahara Pengeluaran<br />b. Daftar Transaksi Harian Belanja Daerah yang dibuat oleh Kuasa BUD<br />c. SSP lembar ke-3</blockquote>', 'width' => '500px','align'=>'left','style'=>'border:none; font-size:20px;'),
	);
	
	$rows[]=array(
	array('data' => 'Yang bertanda tangan dibawah ini menyatakan bahwa Rekapitulasi Tansaksi Harian Belanja Daerah ini dibuat dengan sebenarnya dan saya bertanggungjawab penuh atas kebenaran data yang tercantum dalam Rekapitulasi Transaksi Harian Belanja Daerah ini.', 'width' => '500px','align'=>'left','style'=>'border:none; font-size:20px;'),
	);
	
	$rows[]=array(
		array('data' => ' ', 'width' => '500px','align'=>'center','style'=>'border:none; font-size:30px;'),
	);
	
	$rows[]=array(
		array('data' => 'Mengetahui', 'width' => '250px','align'=>'center','style'=>'border:none;font-size:20px;'),
		array('data' => 'Jepara, .............................', 'width' => '250px','align'=>'center','style'=>'border:none;font-size:20px;'),
	);
	
	$rows[]=array(
		array('data' => 'BENDAHARA UMUM DAERAH', 'width' => '250px','align'=>'center','style'=>'border:none;font-size:20px;'),
		array('data' => 'KUASA BENDAHARA UMUM DAERAH', 'width' => '250px','align'=>'center','style'=>'border:none;font-size:20px;'),
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
		array('data' => '<u>SURURI, SH. MH</u>', 'width' => '250px','align'=>'center','style'=>'border:none;font-weight:bold;font-size:20px;'),
		array('data' => '<u>SUYITNO, S.E</u>', 'width' => '250px','align'=>'center','style'=>'border:none;font-weight:bold;font-size:20px;'),
	);
	
	$rows[]=array(
		array('data' => 'NIP. 19600501 198903 1 008', 'width' => '250px','align'=>'center','style'=>'border:none;font-size:20px;'),
		array('data' => 'NIP. 19620421 198703 1 010', 'width' => '250px','align'=>'center','style'=>'border:none;font-weight:bold;font-size:20px;'),
	);
	
	$output = theme('table', array('header' => $header, 'rows' => $rows ));
	
	$rows=null;
	
	$output .= createT($header,$rows,null);
	//$output .= theme('table', array('header' => $header, 'rows' => $rows ));
	return $output;
}


?>
