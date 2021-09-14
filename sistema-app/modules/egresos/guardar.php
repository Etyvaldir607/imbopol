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
	if (isset($_POST['almacen_id']) && isset($_POST['tipo']) && isset($_POST['descripcion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['nro_registros']) && isset($_POST['monto_total'])) {
		// Obtiene los datos de la venta
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
        $almacen_id = trim($_POST['almacen_id']);
        $al = $db->from('inv_almacenes')->where('id_almacen',$almacen_id)->fetch_first();
        $almacen1 = $al['almacen']; 
		$tipo = trim($_POST['tipo']);
		$descripcion = trim($_POST['descripcion']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos']: array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios']: array();
		$nro_registros = trim($_POST['nro_registros']);
        $rol_id = $_POST['rol_id'];
        $monto_total = trim($_POST['monto_total']);

        if($tipo == 'Traspaso'){
            $otro_almacen = trim($_POST['almac']);
            $al2 = $db->from('inv_almacenes')->where('id_almacen',$otro_almacen)->fetch_first();
            $almacen2 = $al2['almacen'];

            $ingreso = array(
                'fecha_ingreso' => date('Y-m-d'),
                'hora_ingreso' => date('H:i:s'),
                'tipo' => 'Traspaso',
                'descripcion' => $descripcion,
                'monto_total' => $monto_total,
                'nombre_proveedor' => 'Almacen '.$almacen_id,
                'nro_registros' => $nro_registros,
                'almacen_id' => $otro_almacen,
                'empleado_id' => $_user['persona_id']
            );
            // Guarda la informacion
            $ingreso_id = $db->insert('inv_ingresos', $ingreso);

            foreach ($productos as $nro => $elemento) {
                // Forma el detalle
                $detalle = array(
                    'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
                    'costo' => (isset($precios[$nro])) ? $precios[$nro]: 0,
                    'producto_id' => $productos[$nro],
                    'ingreso_id' => $ingreso_id
                );

                // Guarda la informacion
                $db->insert('inv_ingresos_detalles', $detalle);
            }
            $almacen1 = $almacen1.' a  '.$almacen2;
        }
        $subtotales = array();
		// Instancia la venta
		$venta = array(
			'fecha_egreso' => date('Y-m-d'),
			'hora_egreso' => date('H:i:s'),
			'tipo' => $tipo,
			'provisionado' => 'N',
			'descripcion' => $descripcion,
			'nro_factura' => 0,
			'nro_autorizacion' => 0,
			'codigo_control' => '',
			'fecha_limite' => date('Y-m-d'),
			'monto_total' => $monto_total,
			'nombre_cliente' => 'erick',
			'nit_ci' => 0,
			'nro_registros' => $nro_registros,
			'dosificacion_id' => 0,
			'almacen_id' => $almacen_id,
			'empleado_id' => $_user['persona_id']
		);

		// Guarda la informacion
		$egreso_id = $db->insert('inv_egresos', $venta);

		// Recorre los productos
		foreach ($productos as $nro => $elemento) {
			// Forma el detalle
			$detalle = array(
                'precio' => (isset($precios[$nro])) ? $precios[$nro]: 0,
                'unidad_id'=>'1',
				'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
				'descuento' => (isset($descuentos[$nro])) ? $descuentos[$nro]: 0,
				'producto_id' => $productos[$nro],
				'egreso_id' => $egreso_id
			);
            $subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');
            $pres[$nro] = '00.00'; 
			// Guarda la informacion
			$db->insert('inv_egresos_detalles', $detalle);
		}

		// Instancia la variable de notificacion
		/*$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Adición satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);*/

		// Redirecciona a la pagina principal
		//redirect('?/egresos/listar');

        // Obtiene la moneda
        $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
        $moneda = ($moneda) ? $moneda['moneda'] : '';

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $monto_total);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));
         if ($rol_id>2) {
             $precios = $pres ;
             $subtotales = $pres;
         }


        // Instancia la respuesta
        $respuesta = array(
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
            'empresa_agradecimiento' => '',
            'nota_titulo' => 'NOTA   DE   REMISIÓN   DE   SALIDA   Nº'.$egreso_id,
            'nota_numero' => $venta['nro_factura'],
            'nota_fecha' => date_decode($venta['fecha_egreso'], 'd/m/Y'),
            'nota_hora' => substr($venta['hora_egreso'], 0, 5),
            'cliente_nit' => $tipo,
            'cliente_nombre' => $almacen1,
            'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
            'venta_cantidades' => $cantidades,
            'venta_detalles' => $nombres,
            'venta_precios' => $precios,
            'venta_subtotales' => $subtotales,
            'venta_total_numeral' => $venta['monto_total'],
            'venta_total_literal' => $monto_literal,
            'venta_total_decimal' => $monto_decimal . '/100',
            'venta_moneda' => $moneda,
            'impresora' => $_terminal['impresora']
        );

        // Envia respuesta
        echo json_encode($respuesta);
	} else {
		// Error 401
        echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>