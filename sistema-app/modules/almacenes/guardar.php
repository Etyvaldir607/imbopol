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
	if (isset($_POST['id_almacen']) && isset($_POST['almacen']) && isset($_POST['direccion']) && isset($_POST['telefono']) && isset($_POST['principal']) && isset($_POST['descripcion'])) {
		// Obtiene los datos del almacén
		$id_almacen = trim($_POST['id_almacen']);
		$almacen = trim($_POST['almacen']);
		$direccion = trim($_POST['direccion']);
		$telefono = trim($_POST['telefono']);
		$principal = trim($_POST['principal']);
		$descripcion = trim($_POST['descripcion']);
		
		// Instancia el almacén
		$almacen = array(
			'almacen' => $almacen,
			'direccion' => $direccion,
			'telefono' => $telefono,
			'principal' => $principal,
			'descripcion' => $descripcion
		);

		// Verifica si sera almacén principal
		if ($principal == 'S') {
			// Elimina almacenes principales
			$db->where('principal', 'S')->update('inv_almacenes', array('principal' => 'N'));
		}
		
		// Verifica si es creacion o modificacion
		if ($id_almacen > 0) {
			// Genera la condicion
			$condicion = array('id_almacen' => $id_almacen);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_almacenes', $almacen);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// Guarda la informacion
			$db->insert('inv_almacenes', $almacen);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		
		// Redirecciona a la pagina principal
		redirect('?/almacenes/listar');
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