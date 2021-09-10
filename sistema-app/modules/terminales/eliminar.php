<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_terminal
$id_terminal = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la terminal
$terminal = $db->from('inv_terminales')->where('id_terminal', $id_terminal)->fetch_first();

// Verifica si la terminal existe
if ($terminal) {
	// Elimina la terminal
	$db->delete()->from('inv_terminales')->where('id_terminal', $id_terminal)->limit(1)->execute();

	// Verifica si fue la terminal eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/terminales/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>