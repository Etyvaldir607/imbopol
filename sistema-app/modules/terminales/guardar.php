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
	if (isset($_POST['id_terminal']) && isset($_POST['terminal']) && isset($_POST['impresora']) && isset($_POST['descripcion'])) {
		// Obtiene las datos de la terminal
		$id_terminal = trim($_POST['id_terminal']);
		$terminal = trim($_POST['terminal']);
		$impresora = trim($_POST['impresora']);
		$descripcion = trim($_POST['descripcion']);
		
		// Instancia la terminal
		$terminal = array(
			'terminal' => $terminal,
			'impresora' => $impresora,
			'descripcion' => $descripcion
		);
		
		// Verifica si es creacion o modificacion
		if ($id_terminal > 0) {
			// Genera la condicion
			$condicion = array('id_terminal' => $id_terminal);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_terminales', $terminal);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// Adiciona el identificador
			$terminal['identificador'] = random_string(20);

			// Guarda la informacion
			$db->insert('inv_terminales', $terminal);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		
		// Redirecciona a la pagina principal
		redirect('?/terminales/listar');
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