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
	if (isset($_POST['id_unidad']) && isset($_POST['unidad']) && isset($_POST['sigla']) && isset($_POST['descripcion'])) {
		// Obtiene las datos de la unidad
		$id_unidad = trim($_POST['id_unidad']);
		$unidad = trim($_POST['unidad']);
		$sigla = trim($_POST['sigla']);
		$descripcion = trim($_POST['descripcion']);
		
		// Instancia la unidad
		$unidad = array(
			'unidad' => $unidad,
			'sigla' => $sigla,
			'descripcion' => $descripcion
		);
		
		// Verifica si es creacion o modificacion
		if ($id_unidad > 0) {
			// Genera la condicion
			$condicion = array('id_unidad' => $id_unidad);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_unidades', $unidad);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// Guarda la informacion
			$db->insert('inv_unidades', $unidad);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		
		// Redirecciona a la pagina principal
		redirect('?/unidades/listar');
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