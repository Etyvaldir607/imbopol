<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_producto']) && isset($_POST['precio'])) {
		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$precio = trim($_POST['precio']);

		// Instancia el producto
		$producto = array(
			'precio' => $precio,
			'fecha_registro' => date('Y-m-d'),
			'hora_registro' => date('H:i:s'),
			'producto_id' => $id_producto,
			'empleado_id' => $_user['persona_id']
		);
		
		// Guarda la informacion
		$db->insert('inv_precios', $producto);

		// Actualiza la informacion
		$db->where('id_producto', $id_producto)->update('inv_productos', array('precio_actual' => $precio));

		// Instancia la variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Adición satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);
		
		// Redirecciona a la pagina principal
		redirect('?/precios/ver/'. $id_producto);
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