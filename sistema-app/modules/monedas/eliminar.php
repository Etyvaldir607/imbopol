<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_moneda
$id_moneda = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la moneda
$moneda = $db->from('inv_monedas')->where('id_moneda', $id_moneda)->fetch_first();

// Verifica si la moneda existe
if ($moneda) {
	// Elimina la moneda
	$db->delete()->from('inv_monedas')->where('id_moneda', $id_moneda)->limit(1)->execute();

	// Verifica si fue la moneda eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/monedas/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>