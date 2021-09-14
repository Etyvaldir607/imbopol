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
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['nro_factura']) && isset($_POST['nro_autorizacion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['almacen_id']) && isset($_POST['nro_registros']) && isset($_POST['monto_total'])) {
		// Obtiene los datos de la venta
		$nit_ci = trim($_POST['nit_ci']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
		$nro_factura = trim($_POST['nro_factura']);
		$nro_autorizacion = trim($_POST['nro_autorizacion']);
        $productos = (isset($_POST['productos'])) ? $_POST['productos']: array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad']: array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios']: array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos']: array();
		$almacen_id = trim($_POST['almacen_id']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);

        //obtiene a el cliente
        $cliente = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombre_cliente, 'nit' => $nit_ci))->fetch_first();
        if(!$cliente){
            $cl = array(
                'cliente' => $nombre_cliente,
                'nit' => $nit_ci
            );
            $db->insert('inv_clientes',$cl);
        }

        // Instancia la venta
		$venta = array(
			'fecha_egreso' => date('Y-m-d'),
			'hora_egreso' => date('H:i:s'),
			'tipo' => 'Venta',
			'provisionado' => 'N',
			'descripcion' => 'Venta de productos con factura manual',
			'nro_factura' => $nro_factura,
			'nro_autorizacion' => $nro_autorizacion,
			'codigo_control' => '',
			'fecha_limite' => date('Y-m-d'),
			'monto_total' => $monto_total,
			'nombre_cliente' => strtoupper($nombre_cliente),
			'nit_ci' => $nit_ci,
			'nro_registros' => $nro_registros,
			'dosificacion_id' => 0,
			'almacen_id' => $almacen_id,
			'empleado_id' => $_user['persona_id']
		);

		// Guarda la informacion
		$egreso_id = $db->insert('inv_egresos', $venta);

		// Recorre los productos
		foreach ($productos as $nro => $elemento) {
			$id_unidade=$db->select('*')->from('inv_asignaciones a')->join('inv_unidades u','a.unidad_id=u.id_unidad')->where(array('u.unidad' => $unidad[$nro], 'a.producto_id' => $productos[$nro]))->fetch_first();
            if($id_unidade){
                $id_unidad = $id_unidade['id_unidad'];
                $cantidad = $cantidades[$nro]*$id_unidade['cantidad_unidad'];
            }else{
                $id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidad[$nro])->fetch_first();
                $id_unidad = $id_uni['id_unidad'];
                $cantidad = $cantidades[$nro];
            }
			// Forma el detalle
			$detalle = array(
				'cantidad' => $cantidad,
				'precio' => $precios[$nro],
				'descuento' => $descuentos[$nro],
                'unidad_id' => $id_unidad,
				'producto_id' => $productos[$nro],
				'egreso_id' => $egreso_id
			);

			// Guarda la informacion
			$db->insert('inv_egresos_detalles', $detalle);
		}

		// Instancia la variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Adición satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/manuales/crear');
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