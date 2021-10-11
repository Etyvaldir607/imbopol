<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author  Erick Valdir Machicado Calle
 */

// Verifica si es una peticion post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_producto']) && isset($_POST['unidad']) && isset($_POST['sigla']) && isset($_POST['descripcion']) && isset($_POST['cantidad']) && isset($_POST['precio']) ) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene el detalle de la asigancion
		$id_producto = trim($_POST['id_producto']);
		$nombre_unidad = trim($_POST['unidad']);
		$sigla= trim($_POST['sigla']);
		$descripcion = trim($_POST['descripcion']);
        $cantidad = trim($_POST['cantidad']);
        $precio = trim($_POST['precio']);


		//obtiene la unidad
        $unidad = $db->select('*')->from('inv_unidades')->where(array('unidad' => $nombre_unidad, 'sigla' => $sigla))->fetch_first();

		// obtiene la ultima asignacion de la unidad actual al producto
		$asignacion = $db->select('*')->from('inv_asignaciones')->where(array('producto_id' => $id_producto, 'unidad_id' => $unidad['id_unidad']))->fetch_first();
		$id_asignacion_old = $asignacion['id_asignacion'];	


		// consulta si existe una asignacion
		if ($id_asignacion_old == null) {

			// consulta si existe la unidad
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
		}else {

			// consulta si existe la unidad
			if($unidad){
				$unidad_id = $unidad['id_unidad'];
				$db->where('id_asignacion', $id_asignacion_old)->update('inv_asignaciones', array('estado' => 'i'));
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
				'alert' => 'danger',
				'title' => 'Asignación modificada!',
				'message' => 'La modificacion se realizo correctamente.'
			);
			// Envia respuesta
			echo json_encode($respuesta);
		}


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