<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_user
$id_user = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el user
$usuario = $db->from('sys_users')->where('id_user', $id_user)->fetch_first();

// Verifica si el user existe
if ($usuario && $usuario['id_user'] != 1) {
	// Elimina el user
	$db->delete()->from('sys_users')->where('id_user', $id_user)->limit(1)->execute();

	// Verifica si fue el user eliminado
	if ($db->affected_rows) {
		// Define la variable de error
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El usuario fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/usuarios/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>