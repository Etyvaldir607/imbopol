<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_almacen
$id_almacen = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el almacén
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

// Verifica si el almacén existe
if ($almacen) {
	// Elimina el almacén
	$db->delete()->from('inv_almacenes')->where('id_almacen', $id_almacen)->limit(1)->execute();

	// Verifica si fue el almacén eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/almacenes/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>