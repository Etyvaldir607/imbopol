<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Verifica si es una peticion post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_asignacion'])) {
        // Obtiene la asignacion actual
        
        $id_asignacion= trim($_POST['id_asignacion']);
        $asignacion = $db->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->fetch_first();
        $ingreso = $db->select('*')->from('inv_ingresos_detalles')->where(array('producto_id'=> $asignacion['producto_id'], 'unidad_id'=> $asignacion['unidad_id']))->fetch_first();

        if (!$ingreso) {

            $db->delete()->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->limit(1)->execute();
            $db->delete()->from('inv_precios')->where('asignacion_id', $id_asignacion)->limit(1)->execute();
             // Instancia la variable de notificacion
            $respuesta = array(
                'alert' => 'success',
                'title' => 'Asignación satisfactoria!',
                'message' => 'El registro se guardó correctamente.'
            );
        }else{
            $respuesta = array(
                'alert' => 'danger',
                'title' => 'No se realizo la operacion!',
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