<?php
/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
		// Importa la libreria para convertir el numero a letra
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la proforma
		$nit_ci = trim($_POST['nit_ci']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $telefono = trim($_POST['telefono_cliente']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad']: array();
        $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$almacen_id = trim($_POST['almacen_id']);
        //$adelanto = trim($_POST['adelanto']);

        //obtiene al cliente
        $cliente = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombre_cliente, 'nit' => $nit_ci))->fetch_first();
        if(!$cliente){
            $cl = array(
                'cliente' => $nombre_cliente,
                'nit' => $nit_ci,
				'direccion'=>null,
                'telefono' => $telefono,
				'estado'=>'Si'
            );
            $db->insert('inv_clientes',$cl);
        }

        // Define la variable de subtotales
        $subtotales = array();

        // Obtiene la moneda
        $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
        $moneda = ($moneda) ? $moneda['moneda'] : '';

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $monto_total);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

        //if($adelanto == 0){
            // Obtiene el numero de la proforma
            $nro_proforma = $db->query("select ifnull(max(nro_proforma), 0) + 1 as nro_proforma from inv_proformas")->fetch_first();
            $nro_proforma = $nro_proforma['nro_proforma'];

            // Instancia la proforma
            $proforma = array(
                'fecha_proforma' => date('Y-m-d'),
                'hora_proforma' => date('H:i:s'),
                'descripcion' => 'Proforma de venta de productos',
                'nro_proforma' => $nro_proforma,
                'monto_total' => $monto_total,
                'adelanto' => 0,
                'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8'),
                'nit_ci' => $nit_ci,
                'validez' => 1,
                'observacion'=>'proforma',
                'nro_registros' => $nro_registros,
                'almacen_id' => $almacen_id,
                'empleado_id' => $_user['persona_id']
            );

            // Guarda la informacion
            $proforma_id = $db->insert('inv_proformas', $proforma);

            // Recorre los productos
            foreach ($productos as $nro => $elemento) {
                
                // recupera unidades
                $id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidad[$nro])->fetch_first()['id_unidad'];
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

                // Forma el detalle
                $detalle = array(
                    'precio' => (isset($precios[$nro])) ? $precios[$nro]: 0,
                    'unidad_id' => $id_unidad,
                    'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
                    'descuento' =>(isset($descuentos[$nro])) ? $descuentos[$nro]: 0,
                    'fecha_vencimiento' => (isset($fechas[$nro])) ? $fechas[$nro]: 0,
                    'producto_id' => $productos[$nro],
                    'proforma_id' => $proforma_id
                );

                // Genera los subtotales
                $subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

                // Guarda la informacion
                $db->insert('inv_proformas_detalles', $detalle);
            }
        //}else{
            //numero de reserva
            //$nro_rerseva = $db->query("select count(id_egreso) + 1 as nro_factura from inv_egresos where tipo = 'Reserva' and provisionado = 'S'")->fetch_first();
            //$nro_reserva = $nro_reserva['nro_factura'];
            /*
            $nota = array(
                'fecha_egreso' => date('Y-m-d'),
                'hora_egreso' => date('H:i:s'),
                'tipo' => 'Reserva',
                'provisionado' => 'S',
                'descripcion' => 'Adelanto de productos con proforma',
                'nro_factura' => $nro_reserva,
                'nro_autorizacion' => '',
                'codigo_control' => '',
                'fecha_limite' => date('Y-m-d'),
                'monto_total' => $monto_total,
                'nit_ci' => $nit_ci,
                'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8'),
                'nro_registros' => $nro_registros,
                'dosificacion_id' => 0,
                'almacen_id' => $almacen_id,
                'empleado_id' => $_user['persona_id']
            );
            */
        //}

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
			'empresa_agradecimiento' => '¡Gracias por tu compra!',
			'proforma_titulo' => 'P  R  O  F  O  R  M  A',
			'proforma_numero' => $proforma['nro_proforma'],
			'proforma_fecha' => date_decode($proforma['fecha_proforma'], 'd/m/Y'),
			'proforma_hora' => substr($proforma['hora_proforma'], 0, 5),
			'cliente_nit' => $proforma['nit_ci'],
			'cliente_nombre' => $proforma['nombre_cliente'],
			'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
			'venta_cantidades' => $cantidades,
			'venta_detalles' => $nombres,
			'venta_precios' => $precios,
			'venta_subtotales' => $subtotales,
			'venta_total_numeral' => $proforma['monto_total'],
			'venta_total_literal' => $monto_literal,
			'venta_total_decimal' => $monto_decimal . '/100',
			'venta_moneda' => $moneda,
			'impresora' => $_terminal['impresora']
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