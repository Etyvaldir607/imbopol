<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_empleado
$id_cliente = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el empleado
$cliente = $db->from('inv_proveedores')->where('id_proveedor', $id_cliente)->fetch_first();

// Verifica si el empleado existe
if ($cliente) {
	// Elimina el empleado
	$db->delete()->from('inv_proveedores')->where('id_proveedor', $id_cliente)->limit(1)->execute();

	// Verifica si fue el empleado eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/proveedores/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>