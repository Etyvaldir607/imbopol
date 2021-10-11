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
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['nro_factura']) && isset($_POST['nro_autorizacion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['almacen_id']) && isset($_POST['nro_registros']) && isset($_POST['monto_total'])) {
		// Obtiene los datos de la venta
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la venta manual
		$nit_ci = trim($_POST['nit_ci']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
		$nro_factura = trim($_POST['nro_factura']);
		$nro_autorizacion = trim($_POST['nro_autorizacion']);
        $productos = (isset($_POST['productos'])) ? $_POST['productos']: array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad']: array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios']: array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos']: array();
		$almacen_id = trim($_POST['almacen_id']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);

        //obtiene a el cliente
        //obtiene al cliente
        $cliente = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombre_cliente, 'nit' => $nit_ci))->fetch_first();
        if(!$cliente){
            $cl = array(
                'cliente' => $nombre_cliente,
                'nit' => $nit_ci,
				'direccion'=>null,
                'telefono' => null,
				'estado'=>'Si'
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
			// recupera unidades
			// $id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidad[$nro])->fetch_first()['id_unidad'];
			/*
			$id_unidade=$db->select('*')->from('inv_asignaciones a')->join('inv_unidades u','a.unidad_id=u.id_unidad')->where(array('u.unidad' => $unidad[$nro], 'a.producto_id' => $productos[$nro]))->fetch_first();
			if($id_unidade){
				$id_unidad = $id_unidade['id_unidad'];
				$cantidad = $cantidades[$nro]*$id_unidade['cantidad_unidad'];
			}else{
				$id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidad[$nro])->fetch_first();
				$id_unidad = $id_uni['id_unidad'];
				$cantidad = $cantidades[$nro];
			}
			*/
			// recupera unidades
			$id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidad[$nro])->fetch_first()['id_unidad'];
            $id_asignacion = $db->select('id_asignacion')->from('inv_asignaciones')->where(array('unidad_id'=>$id_unidad, 'estado' =>'a'))->fetch_first()['id_asignacion'];

			// Forma el detalle
			$detalle = array(
				'precio' => (isset($precios[$nro])) ? $precios[$nro]: 0,
				'unidad_id' => $id_unidad,
				'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
				'descuento' =>(isset($descuentos[$nro])) ? $descuentos[$nro]: 0,
				'fecha_vencimiento' => (isset($fechas[$nro])) ? $fechas[$nro]: 0,
				'producto_id' => $productos[$nro],
				'egreso_id' => $egreso_id,
				'asignacion_id'=>$id_asignacion
			);

			// Instancia la respuesta
			$respuesta = array(
				'id_egreso'=>$egreso_id,
				'papel_ancho' => 10,
				'papel_alto' => 25,
				'papel_limite' => 576,
				'empresa_nombre' => $_institution['nombre'],
				'empresa_sucursal' => 'SUCURSAL Nº 1',
				'empresa_direccion' => $_institution['direccion'],
				'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
				'empresa_ciudad' => 'EL ALTO - BOLIVIA',
				'empresa_actividad' => $_institution['razon_social'],
				'empresa_nit' => $_institution['nit'],
				'empresa_empleado' => ($_user['persona_id'] == 0) ? upper($_user['username']) : upper(trim($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno'])),
				'empresa_agradecimiento' => '¡Gracias por tu compra!',
				'nota_titulo' => 'VENTA MANUAL',
				'nota_numero' => $nota['nro_factura'],
				'nota_fecha' => date_decode($nota['fecha_egreso'], 'd/m/Y'),
				'nota_hora' => substr($nota['hora_egreso'], 0, 5),
				'cliente_nit' => $nota['nit_ci'],
				'cliente_nombre' => $nota['nombre_cliente'],
				'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
				'venta_cantidades' => $cantidades,
				'venta_detalles' => $nombres,
				'venta_precios' => $precios,
				'venta_subtotales' => $subtotales,
				'venta_total_numeral' => $nota['monto_total'],
				'venta_total_literal' => $monto_literal,
				'venta_total_decimal' => $monto_decimal . '/100',
				'venta_moneda' => $moneda,
				'impresora' => $_terminal['impresora']
			);

			// Guarda la informacion
			$db->insert('inv_egresos_detalles', $detalle);
		}
		// Envia respuesta
		echo json_encode($respuesta);
		
		// Instancia la variable de notificacion
		/*
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Adición satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);
		*/
		// Redirecciona a la pagina principal
		//redirect('?/manuales/crear');
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