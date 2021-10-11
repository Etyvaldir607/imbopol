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
	if (isset($_POST['id_producto']) && isset($_POST['codigo']) && isset($_POST['codigo_barras']) && isset($_POST['nombre']) && isset($_POST['nombre_factura']) && isset($_POST['cantidad_minima']) && isset($_POST['precio_actual']) && isset($_POST['categoria_id']) && isset($_POST['ubicacion']) && isset($_POST['descripcion'])) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$codigo = trim($_POST['codigo']);
		$codigo_barras = trim($_POST['codigo_barras']);
        $nombre = trim($_POST['nombre']);
        $color = trim($_POST['color']);
        //$fecha_ven = trim($_POST['ven_fecha']);
		$nombre_factura = trim($_POST['nombre_factura']);
		$cantidad_minima = trim($_POST['cantidad_minima']);
		$precio_actual = trim($_POST['precio_actual']);
		//$unidad_id = trim($_POST['unidad_id']);
		$categoria_id = trim($_POST['categoria_id']);
		$ubicacion = trim($_POST['ubicacion']);
		$descripcion = trim($_POST['descripcion']);

		// obtiene la unidad minima
		$unidad = $db->select('*')->from('inv_unidades')->where( 'id_unidad', 1)->fetch_first();
		$unidad_id = $unidad['id_unidad'];
		
		// Instancia el producto
		$producto = array(
			'codigo' => $codigo,
			'codigo_barras' => 'CB' . $codigo_barras,
			'nombre' => $nombre,
			'nombre_factura' => $nombre_factura,
			'color' => $color,
			
            'precio_actual' => $precio_actual,
			'cantidad_minima' => $cantidad_minima,
			
			'ubicacion' => $ubicacion,
			'descripcion' => $descripcion,
			'unidad_id' => $unidad_id,
			'categoria_id' => $categoria_id,
            
		);
		
		// Verifica si es creacion o modificacion
		if ($id_producto > 0) {
			// Genera la condicion
			$condicion = array('id_producto' => $id_producto);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_productos', $producto);
			
			// Instancia la variable de notificacion
			$respuesta = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// adiciona la fecha y hora de creacion
			$producto['fecha_registro'] = date('Y-m-d');
			$producto['hora_registro'] = date('H:i:s');
			$producto['imagen'] = '';

			// Guarda la informacion
			$id_producto = $db->insert('inv_productos', $producto);

			// Instancia la asignacion
			$asignacion= array(
				'producto_id' => $id_producto,
				'unidad_id' => $unidad_id,
				'cantidad_unidad' => 1,
				'otro_precio' => $precio_actual,
				'fecha_asignacion' => date('Y-m-d'),
				'estado' => 'a',
				'asignacion'=>'principal'
			);

			// Guarda la informacion
			$id_asignacion = $db->insert('inv_asignaciones', $asignacion);
			
			// Instancia el precio
			$precio = array(
				'precio' => $precio_actual,
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
				'title' => 'Modificación satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}

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