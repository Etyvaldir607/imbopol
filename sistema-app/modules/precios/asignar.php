<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// Verifica la peticion post
if (is_post()) {
	// Verifica la cadena csrf
	if (true) {
		// Obtiene los parametros
		$producto_id = (isset($params[0])) ? $params[0] : 0;

		// Obtiene el producto
		$producto = $db->select('id_producto')->from('inv_productos')->where('id_producto', $producto_id)->fetch_first();

		// Verifica si existen los productos
		if ($producto) {
			// Verifica la existencia de datos
			if (isset($_POST['unidad_id']) && isset($_POST['precio']) && isset($_POST['observacion'])) {
                // Obtiene los datos
                $unidad_id = clear($_POST['unidad_id']);
                $precio = clear($_POST['precio']);
                $tamano = clear($_POST['tamano']);
                $observacion = clear($_POST['observacion']);
                $precio = (is_numeric($precio)) ? $precio : 0;
                $estado_asignacion = false;
                $estado_precio = false;

                //busqueda de exitenncia
                $ex = $db->select('*')->from('inv_asignaciones')->where(array('producto_id' => $producto_id, 'unidad_id' => $unidad_id))->fetch_first();
                if($ex){
                    $asigna = array(
                        'cantidad_unidad' => $tamano,
                        'otro_precio' => $precio
                    );

                    // Cambia la asignacion
                    $db->where(array('producto_id' => $producto_id, 'unidad_id' => $unidad_id))->update('inv_asignaciones', $asigna);

                    $precio = array(
                        'precio' => $precio,
                        'fecha_registro' => date('Y-m-d'),
                        'hora_registro' => date('H:i:s'),
                        'empleado_id' => $_user['id_user']
                    );

                    // Cambia el precio
                    $db->where(array('asignacion_id' => $ex['id_asignacion']))->update('inv_precios', $precio);
                    $estado_precio = true;
                    $estado_asignacion = true;

                }else{
                    $asigna = array(
                        'producto_id' => $producto_id,
                        'unidad_id' => $unidad_id,
                        'cantidad_unidad' => $tamano,
                        'otro_precio' => $precio
                    );
                    // Obtiene la asignacion
                    $id_asignacion = $db->insert('inv_asignaciones', $asigna);

                    $precio = array(
                        'precio' => $precio,
                        'fecha_registro' => date('Y-m-d'),
                        'hora_registro' => date('H:i:s'),
                        'asignacion_id' => $id_asignacion,
                        'producto_id' => $producto_id,
                        'empleado_id' => $_user['id_user']
                    );

                    // Crea el precio
                    $id_precio = $db->insert('inv_precios', $precio);
                    $estado_precio = true;
                    $estado_asignacion = true;
                }

				// Verifica los estados
				if ($estado_asignacion && $estado_precio) {
					// Crea la notificacion
					set_notification('success', 'Asignación exitosa!', 'La unidad se asignó y el precio se fijó satisfactoriamente.');
                    // Redirecciona la pagina
                    redirect('?/precios/listar');
				} else {
					if ($estado_asignacion) {
						// Crea la notificacion
						set_notification('success', 'Asignación exitosa!', 'La unidad se asignó satisfactoriamente.');
                        // Redirecciona la pagina
                        redirect('?/precios/listar');
					} else {
						if ($estado_precio) {
							// Crea la notificacion
							set_notification('success', 'Asignación exitosa!', 'El precio se fijó satisfactoriamente.');
                            // Redirecciona la pagina
                            redirect('?/precios/listar');
						} else {
							// Crea la notificacion
							set_notification('danger', 'Asignación fallida!', 'Los cambios no fueron registrados.');
                            // Redirecciona la pagina
                            redirect('?/precios/listar');
						}
					}
				}


			} else {
				// Error 400
				require_once bad_request();
				exit;
			}
		} else {
			// Error 400
			require_once bad_request();
			exit;
		}
	} else {
		// Redirecciona la pagina
		redirect(back());
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>
<select name="" id="op">
    <option value=""></option></select>