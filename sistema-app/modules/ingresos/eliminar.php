<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_ingreso
$id_ingreso = (isset($params[0])) ? $params[0] : 0;

// Obtiene el ingreso
$ingreso = $db->select('i.*, a.principal')
			  ->from('inv_ingresos i')
			  ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
			  ->where('i.id_ingreso', $id_ingreso)
			  ->fetch_first();

// Verifica si el ingreso existe
if ($ingreso) {
	// Elimina el ingreso
	$db->delete()->from('inv_ingresos')->where('id_ingreso', $id_ingreso)->limit(1)->execute();

	// Elimina los detalles
	$db->delete()->from('inv_ingresos_detalles')->where('ingreso_id', $id_ingreso)->execute();

	// Verifica si fue el ingreso eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El ingreso y todo su detalle fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/ingresos/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>