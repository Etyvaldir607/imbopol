<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene los parametros
$id_egreso = (isset($params[0])) ? $params[0] : 0;
$id_detalle = (isset($params[1])) ? $params[1] : 0;

// Obtiene el egreso
$egreso = $db->from('inv_egresos')
			 ->where('id_egreso', $id_egreso)
			 ->fetch_first();

// Obtiene el detalle del egreso
$detalle = $db->from('inv_egresos_detalles')->where(array('id_detalle' => $id_detalle, 'egreso_id' => $id_egreso))->fetch_first();

// Verifica si el egreso existe
if ($detalle) {
	// Instancia el egreso
	$egreso = array(
		'monto_total' => $egreso['monto_total'] - ($detalle['cantidad'] * $detalle['precio']),
		'nro_registros' => $egreso['nro_registros'] - 1
	);

	// Actualiza el egreso
	$db->where('id_egreso', $id_egreso)->update('inv_egresos', $egreso);

	// Elimina el detalle
	$db->delete()->from('inv_egresos_detalles')->where('id_detalle', $id_detalle)->limit(1)->execute();

	// Verifica si fue el egreso eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El detalle de la venta fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/manuales/ver/' . $id_egreso);
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>