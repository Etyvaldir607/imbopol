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
	if (isset($_POST['id_producto']) && isset($_POST['unidad']) && isset($_POST['sigla']) && isset($_POST['descripcion']) && isset($_POST['cantidad']) && isset($_POST['precio']) ) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$nombre_unidad = trim($_POST['unidad']);
		$sigla= trim($_POST['sigla']);
		$descripcion = trim($_POST['descripcion']);
        $cantidad = trim($_POST['cantidad']);
        $precio = trim($_POST['precio']);


		//obtiene el producto
        $unidad = $db->select('*')->from('inv_unidades')->where(array('unidad' => $nombre_unidad, 'sigla' => $sigla))->fetch_first();
        if(!$unidad){
			// Instancia nueva unidad
            $new = array(
                'unidad' => $nombre_unidad,
                'sigla' => $sigla,
				'descripcion'=>$descripcion
            );
            $unidad_id = $db->insert('inv_unidades',$new);
        }else{
			$unidad_id = $unidad['id_unidad'];
		}

		// Instancia la asignacion
		$asignacion= array(
			'producto_id' => $id_producto,
			'unidad_id' => $unidad_id,
			'cantidad_unidad' => $cantidad,
			'otro_precio' => $precio,
			'fecha_asignacion' => date('Y-m-d'),
			'estado' => 'a'
		);

		// Guarda la informacion
		$id_asignacion = $db->insert('inv_asignaciones', $asignacion);
		
		// Instancia el precio
		$precio = array(
			'precio' => $precio,
			'fecha_registro' => date('Y-m-d'),
			'hora_registro' => date('H:i:s'),
			'asignacion_id' => $id_asignacion,
			'producto_id' => $id_producto,
			'empleado_id' => $_user['persona_id'],
		);

		// Guarda la informacion
		$id_precio = $db->insert('inv_precios', $precio);



		// Instancia la variable de notificacion
		$respuesta = array(
			'alert' => 'success',
			'title' => 'Asignación satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);

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