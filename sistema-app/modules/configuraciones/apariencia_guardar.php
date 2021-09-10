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
	if (isset($_POST['tema'])) {
		// Obtiene los datos de la institucion
		$id_institucion = $_institution['id_institucion'];
		$tema = $_POST['tema'];

		// Instancia la institucion
		$institucion = array(
			'tema' => trim($tema)
		);

		// Actualiza la informacion
		$db->where('id_institucion', $id_institucion)->update('sys_instituciones', $institucion);

		// Define el mensaje de exito
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Actualización satisfactoria!',
			'message' => 'El registro se actualizó correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/configuraciones/apariencia');
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