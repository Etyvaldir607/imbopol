<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['unidad']) && isset($_POST['sigla']) && isset($_POST['descripcion']) && isset($_POST['cantidad']) && isset($_POST['precio']) ) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos del producto
		$unidad = trim($_POST['unidad']);
		$sigla= trim($_POST['sigla']);
		$descripcion = trim($_POST['descripcion']);
        $cantiadad = trim($_POST['cantiadad']);
        $precio = trim($_POST['precio']);

        



		// Envia respuesta
		echo json_encode($respuesta);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>