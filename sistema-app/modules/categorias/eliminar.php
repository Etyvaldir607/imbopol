<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_categoria
$id_categoria = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la categoría
$categoria = $db->from('inv_categorias')->where('id_categoria', $id_categoria)->fetch_first();

// Verifica si la categoría existe
if ($categoria) {
	// Elimina la categoría
	$db->delete()->from('inv_categorias')->where('id_categoria', $id_categoria)->limit(1)->execute();

	// Verifica si fue la categoría eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/categorias/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>