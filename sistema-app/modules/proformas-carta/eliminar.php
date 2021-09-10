<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_proforma
$id_proforma = (isset($params[0])) ? $params[0] : 0;

// Obtiene el proforma
$proforma = $db->from('inv_proformas')
			   ->where('id_proforma', $id_proforma)
			   ->fetch_first();

// Verifica si el proforma existe
if ($proforma) {
	// Elimina el proforma
	$db->delete()->from('inv_proformas')->where('id_proforma', $id_proforma)->limit(1)->execute();

	// Elimina los detalles
	$db->delete()->from('inv_proformas_detalles')->where('proforma_id', $id_proforma)->execute();

	// Verifica si fue el proforma eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'La proforma y todo su detalle fueron eliminados correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/proformas/mostrar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>