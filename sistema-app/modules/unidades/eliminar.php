<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_unidad
$id_unidad = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la unidad
$unidad = $db->from('inv_unidades')->where('id_unidad', $id_unidad)->fetch_first();

// Verifica si la unidad existe
if ($unidad) {
	// Elimina la unidad
	$db->delete()->from('inv_unidades')->where('id_unidad', $id_unidad)->limit(1)->execute();

	// Verifica si fue la unidad eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/unidades/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>