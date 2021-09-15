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
	if (isset($_POST['almacen_id']) && isset($_POST['nombre_proveedor']) && isset($_POST['descripcion']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['productos']) && isset($_POST['cantidades']) && isset($_POST['costos'])) {
		// Obtiene los datos del producto
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
        $almacen_id = trim($_POST['almacen_id']);
        $al = $db->from('inv_almacenes')->where('id_almacen',$almacen_id)->fetch_first();
        $almacen2 = $al['almacen'];
        $nombre_proveedor = trim($_POST['nombre_proveedor']);
		$descripcion = trim($_POST['descripcion']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos']: array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$costos = (isset($_POST['costos'])) ? $_POST['costos']: array();
        $fechas = (isset($_POST['fechas'])) ? $_POST['fechas']: array();
        $nombre_producto = (isset($_POST['nprod'])) ? $_POST['nprod']: array();

        // obtiene el proeevedor
        $proveedor = $db->select('*')->from('inv_proveedores')->where('proveedor',$nombre_proveedor)->fetch_first();

        if($proveedor){

        }else{
            $p = array(
                'proveedor' => $nombre_proveedor,
                'nit' => ''
            );
            $db->insert('inv_proveedores',$p);
        }

		// Obtiene el almacen
		$almacen = $db->from('inv_almacenes')->where('id_almacen', $almacen_id)->fetch_first();

		// Instancia el ingreso
		$ingreso = array(
			'fecha_ingreso' => date('Y-m-d'),
			'hora_ingreso' => date('H:i:s'),
			'tipo' => 'Compra',
			'descripcion' => $descripcion,
			'monto_total' => $monto_total,
			'nombre_proveedor' => $nombre_proveedor,
			'nro_registros' => $nro_registros,
			'almacen_id' => $almacen_id,
			'empleado_id' => $_user['persona_id']
		);
        //var_dump($productos);
		// Guarda la informacion
		$ingreso_id = $db->insert('inv_ingresos', $ingreso);
        $subtotales = array();
		foreach ($productos as $nro => $elemento) {
			// Forma el detalle
			$detalle = array(
				'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
				'costo' => (isset($costos[$nro])) ? $costos[$nro]: 0,
                'fecha_vencimiento'=>(isset($fechas[$nro])) ? $fechas[$nro]: null,
				'producto_id' => $productos[$nro],
				'ingreso_id' => $ingreso_id
			);
            $subtotales[$nro] = number_format($costos[$nro] * $cantidades[$nro], 2, '.', '');
            $product[$nro] = $productos[$nro];
			// Guarda la informacion
			$db->insert('inv_ingresos_detalles', $detalle);
		}
        $a = 0;
       foreach ($product as $key => $elemento) {
            $product[$key] = $db->select('nombre_factura')->from('inv_productos')->where('id_producto',$key)->fetch();                 
        }



        $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
        $moneda = ($moneda) ? $moneda['moneda'] : '';

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $monto_total);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

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
            'nota_titulo' => 'NOTA DE REMISIÓN DE INGRESO Nº'.$ingreso_id,
            'nota_numero' => $almacen2,
            'nota_fecha' => date_decode($ingreso['fecha_ingreso'], 'd/m/Y'),
            'nota_hora' => substr($ingreso['hora_ingreso'], 0, 5),
            'cliente_nit' => $ingreso['tipo'],
            'cliente_nombre' => $ingreso['nombre_proveedor'],
            'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
            'venta_cantidades' => $cantidades,
            'venta_detalles' => $nombre_producto,
            'venta_precios' => $costos,
            'venta_subtotales' => $subtotales,
            'venta_total_numeral' => $ingreso['monto_total'],
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