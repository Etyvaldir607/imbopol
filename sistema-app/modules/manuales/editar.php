<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {	
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_egreso']) && isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['nro_factura']) && isset($_POST['nro_autorizacion'])) {
		// Importa la libreria para el codigo de control
		require_once libraries . '/codigocontrol-class/class.codigocontrol.php';

		// Obtiene los datos del producto
		$id_egreso = trim($_POST['id_egreso']);
		$nit_ci = trim($_POST['nit_ci']);
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$nro_factura = trim($_POST['nro_factura']);
		$nro_autorizacion = trim($_POST['nro_autorizacion']);

		// Instancia la venta
		$venta = array(
			'nit_ci' => $nit_ci,
			'nombre_cliente' => strtoupper($nombre_cliente),
			'nro_factura' => $nro_factura,
			'nro_autorizacion' => $nro_autorizacion
		);
		
		// Actualiza la informacion
		$db->where('id_egreso', $id_egreso)->update('inv_egresos', $venta);

		// Instancia la variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Actualización satisfactoria!',
			'message' => 'El registro se actualizó correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/manuales/ver/' . $id_egreso);
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>