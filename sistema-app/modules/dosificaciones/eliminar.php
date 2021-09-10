<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_dosificacion
$id_dosificacion = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la dosificación
$dosificacion = $db->from('inv_dosificaciones')->where('id_dosificacion', $id_dosificacion)->fetch_first();

// Verifica si la dosificación existe
if ($dosificacion) {
	// Elimina la dosificación
	$db->delete()->from('inv_dosificaciones')->where('id_dosificacion', $id_dosificacion)->limit(1)->execute();

	// Verifica si fue la dosificación eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/dosificaciones/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>