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
		// Importa la libreria para el codigo de control
		require_once libraries . '/controlcode-class/ControlCode.php';
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la venta
		$nit_ci = trim($_POST['nit_ci']);
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$almacen_id = trim($_POST['almacen_id']);

		// Obtiene la fecha de hoy
		$hoy = date('Y-m-d');

		// Obtiene la dosificacion del periodo actual
		$dosificacion = $db->from('inv_dosificaciones')->where('fecha_registro <=', $hoy)->where('fecha_limite >=', $hoy)->where('activo', 'S')->fetch_first();

		// Verifica si la dosificación existe
		if ($dosificacion) {
			// Obtiene los datos para el codigo de control
			$nro_autorizacion = $dosificacion['nro_autorizacion'];
			$nro_factura = intval($dosificacion['nro_facturas']) + 1;
			$nit_ci = $nit_ci;
			$fecha = date('Ymd');
			$total = round($monto_total, 0);
			$llave_dosificacion = base64_decode($dosificacion['llave_dosificacion']);

			// Genera el codigo de control
			$codigo_control = new ControlCode();
			$codigo_control = $codigo_control->generate($nro_autorizacion, $nro_factura, $nit_ci, $fecha, $total, $llave_dosificacion);

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

			// Instancia la venta
			$venta = array(
				'fecha_egreso' => date('Y-m-d'),
				'hora_egreso' => date('H:i:s'),
				'tipo' => 'Venta',
				'provisionado' => 'N',
				'descripcion' => 'Venta de productos',
				'nro_factura' => $nro_factura,
				'nro_autorizacion' => $nro_autorizacion,
				'codigo_control' => $codigo_control,
				'fecha_limite' => $dosificacion['fecha_limite'],
				'monto_total' => $monto_total,
				'nit_ci' => $nit_ci,
				'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8'),
				'nro_registros' => $nro_registros,
				'dosificacion_id' => $dosificacion['id_dosificacion'],
				'almacen_id' => $almacen_id,
				'empleado_id' => $_user['persona_id']
			);

			// Guarda la informacion
			$egreso_id = $db->insert('inv_egresos', $venta);

			// Recorre los productos
			foreach ($productos as $nro => $elemento) {
				// Forma el detalle
				$detalle = array(
					'cantidad' => $cantidades[$nro],
					'precio' => $precios[$nro],
					'descuento' => $descuentos[$nro],
					'producto_id' => $productos[$nro],
					'egreso_id' => $egreso_id
				);

				// Genera los subtotales
				$subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

				// Guarda la informacion
				$db->insert('inv_egresos_detalles', $detalle);
			}
		
			// Actualiza la informacion
			$db->where('id_dosificacion', $dosificacion['id_dosificacion'])->update('inv_dosificaciones', array('nro_facturas' => $nro_factura));
			
			// Instancia la respuesta
			/*$respuesta = array(
				'papel_ancho' => 10,
				'papel_alto' => 30,
				'papel_limite' => 576,
				'empresa_nombre' => $_institution['nombre'],
				'empresa_sucursal' => 'SUCURSAL Nº 1',
				'empresa_direccion' => $_institution['direccion'],
				'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
				'empresa_ciudad' => 'LA PAZ - BOLIVIA',
				'empresa_actividad' => $_institution['razon_social'],
				'empresa_nit' => $_institution['nit'],
				'factura_titulo' => 'F  A  C  T  U  R  A',
				'factura_numero' => $venta['nro_factura'],
				'factura_autorizacion' => $venta['nro_autorizacion'],
				'factura_fecha' => date_decode($venta['fecha_egreso'], 'd/m/Y'),
				'factura_hora' => substr($venta['hora_egreso'], 0, 5),
				'factura_codigo' => $venta['codigo_control'],
				'factura_limite' => date_decode($venta['fecha_limite'], 'd/m/Y'),
				'factura_autenticidad' => '"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO DE ACUERDO A LEY"',
				'factura_leyenda' => 'Ley Nº 453: "' . $dosificacion['leyenda'] . '".',
				'cliente_nit' => $venta['nit_ci'],
				'cliente_nombre' => $venta['nombre_cliente'],
				'venta_titulos' => array('CANTIDAD', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
				'venta_cantidades' => $cantidades,
				'venta_detalles' => $nombres,
				'venta_precios' => $precios,
				'venta_subtotales' => $subtotales,
				'venta_total_numeral' => $venta['monto_total'],
				'venta_total_literal' => $monto_literal,
				'venta_total_decimal' => $monto_decimal . '/100',
				'venta_moneda' => $moneda,
				'importe_base' => '0',
				'importe_ice' => '0',
				'importe_venta' => '0',
				'importe_credito' => '0',
				'importe_descuento' => '0',
				'impresora' => $_terminal['impresora']
			);*/

			// Envia respuesta
			echo json_encode($egreso_id);
		} else {
			// Envia respuesta
			echo 'error';
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