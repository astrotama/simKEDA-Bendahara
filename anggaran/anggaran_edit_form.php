<?php
    
function anggaran_edit_form(){
	
	$kodekeg = arg(2);
	
	$result = db_query('select kegiatan, tw1, tw2, tw3, tw4, anggaran from {kegiatanskpd} where kodekeg=:kodekeg', array(':kodekeg'=>$kodekeg));
	foreach ($result as $data) {
		$kegiatan = $data->kegiatan;
		$tw1 = $data->tw1;
		$tw2 = $data->tw2;
		$tw3 = $data->tw3;
		$tw4 = $data->tw4;
		$anggaran = $data->anggaran;
	}
	

	//NAMA
	$form['kodekeg']= array(
		'#type' => 'value', 
		'#value' => $kodekeg,
	);

	//NAMA
	$form['kegiatan']= array(
		'#type'         => 'item', 
		'#title'        => 'Kegiatan',  
		//'#required'     => !$disabled, 
		'#markup' => '<p>' . $kegiatan . '</p>',
	);
		
	$form['tw1']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Triwulan 1', 
		'#attributes' => array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 100, 
		'#size'         => 40, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $tw1, 
	); 
	
	$form['tw2']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Triwulan 2',
		'#attributes' => array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 100, 
		'#size'         => 40, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $tw2, 
	);
	
	$form['tw3']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Triwulan 3',
		'#attributes' => array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 100, 
		'#size'         => 40, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $tw3, 
	);

	$form['tw4']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Triwulan 4',
		'#attributes' => array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 100, 
		'#size'         => 40, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $tw4, 
	);
	
	$form['anggaran']= array(
		'#type'         => 'textfield', 
		'#title'        => 'Anggaran',
		'#attributes' => array('style' => 'text-align: right'),
		'#description'  => '', 
		'#maxlength'    => 100, 
		'#size'         => 40, 
		//'#required'     => !$disabled, 
		//'#disabled'     => $disabled, 
		'#default_value'=> $anggaran, 
	);
	
    $form['submit'] = array (
        '#type' => 'submit',
		//'#suffix' => "&nbsp;<a href='/operators' class='btn_blue' style='color: white'>Batal</a>",
        '#value' => 'Simpan'
    );
    return $form;
}

function anggaran_edit_form_validate($form, &$form_state) {

}
function anggaran_edit_form_submit($form, &$form_state) {
    
	//MEMBACA VARIABLE HASIL INPUT USER
	$kodekeg = $form_state['values']['kodekeg'];
    $kegiatan = $form_state['values']['kegiatan'];
    $tw1 = $form_state['values']['tw1'];
	$tw2 = $form_state['values']['tw2'];
	$tw3 = $form_state['values']['tw3'];
	$tw4 = $form_state['values']['tw4'];
	$anggaran = $form_state['values']['anggaran'];

	$num_updated = db_update('kegiatanskpd') // Table name no longer needs {}
	  ->fields(array(
		'tw1' => $tw1,
		'tw2' => $tw2,
		'tw3' => $tw3,
		'tw4' => $tw4,
	  ))
	  ->condition('kodekeg', $kodekeg, '=')
	  ->execute();
	

    drupal_goto('anggaran'); 
}

?>