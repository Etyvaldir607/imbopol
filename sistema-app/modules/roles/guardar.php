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
	if (isset($_POST['id_rol']) && isset($_POST['rol']) && isset($_POST['descripcion'])) {
		// Obtiene los datos del rol
		$id_rol = trim($_POST['id_rol']);
		$rol = trim($_POST['rol']);
		$descripcion = trim($_POST['descripcion']);
		
		// Instancia el rol
		$rol = array(
			'rol' => $rol,
			'descripcion' => $descripcion
		);
		
		// Verifica si es creacion o modificacion
		if ($id_rol > 0) {
			// Genera la condicion
			$condicion = array('id_rol' => $id_rol);
			
			// Actualiza la informacion
			$db->where($condicion)->update('sys_roles', $rol);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// Guarda la informacion
			$db->insert('sys_roles', $rol);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		
		// Redirecciona a la pagina principal
		redirect('?/roles/listar');
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