<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_rol
$id_rol = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el rol
$rol = $db->from('sys_roles')->where('id_rol', $id_rol)->fetch_first();

// Verifica si el rol existe
if ($rol) {
	// Elimina el rol
	$db->delete()->from('sys_roles')->where('id_rol', $id_rol)->limit(1)->execute();

	// Verifica si fue el rol eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/roles/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>