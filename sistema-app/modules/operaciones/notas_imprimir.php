<?php

// Obtiene el orden de compra
$id_orden = (isset($params[0])) ? $params[0] : 0;

if ($id_orden == 0) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Importa la libreria para convertir el numero a letra
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// Operaciones con la imagen del header
list($ancho_header, $alto_header) = getimagesize(imgs . '/header.jpg');
$relacion = $alto_header / $ancho_header;
$ancho_header = 612;
$alto_header = round($ancho_header * $relacion);
define('ancho_header', $ancho_header);
define('alto_header', $alto_header);

// Operaciones con la imagen del footer
list($ancho_footer, $alto_footer) = getimagesize(imgs . '/footer.jpg');
$relacion = $alto_footer / $ancho_footer;
$ancho_footer = 612;
$alto_footer = round($ancho_footer * $relacion);
define('ancho_footer', $ancho_footer);
define('alto_footer', $alto_footer);


// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
	public function Header() {
		$this->Image(imgs . '/header.jpg', 0, 0, ancho_header, alto_header);
	}
	public function Footer() {
		$this->Image(imgs . '/footer.jpg', 0, 698, ancho_footer, alto_footer);
	}
}

// Instancia el documento PDF
$pdf = new MYPDF('P', 'pt', 'LETTER', true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(30, alto_header + 15, 30);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(true, alto_footer + 15);


$id_ordenes = explode('-', $id_orden);

foreach ($id_ordenes as $id_orden) {

// Obtiene el orden de compra
$orden = $db->select('n.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos n')->join('inv_almacenes a', 'n.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'n.empleado_id = e.id_empleado', 'left')->where('n.id_egreso', $id_orden)->where('n.tipo', 'Venta')->where('n.provisionado', 'S')->fetch_first();

// Obtiene los detalles
$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura')->from('inv_egresos_detalles d')->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')->where('d.egreso_id', $id_orden)->order_by('id_detalle asc')->fetch();

// Asigna la orientacion de la pagina
$pdf->SetPageOrientation('P');

// Adiciona la pagina
$pdf->AddPage();

// Establece la fuente del titulo
$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);

// Titulo del documento
$pdf->Cell(0, 10, 'ORDEN DE COMPRA # ' . $orden['nro_factura'], 0, true, 'C', false, '', 0, false, 'T', 'M');

// Salto de linea
$pdf->Ln(5);

// Establece la fuente del contenido
$pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);

// Define las variables
$valor_fecha = escape(date_decode($orden['fecha_egreso'], $_institution['formato']) . ' ' . $orden['hora_egreso']);
$valor_nombre_cliente = escape($orden['nombre_cliente']);
$valor_nit_ci = escape($orden['nit_ci']);
$valor_direccion = escape($orden['direccion']);
$valor_telefono = escape($orden['telefono']);
$valor_monto_total = escape($orden['monto_total']);
$valor_empleado = escape($orden['nombres'] . ' ' . $orden['paterno'] . ' ' . $orden['materno']);
$valor_descuento = escape($orden['descuento']);
$valor_observacion = escape($orden['observacion']);
$valor_moneda = $moneda;
$total = 0;

// Establece la fuente del contenido
$pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);

// Estructura la tabla
$body = '';
foreach ($detalles as $nro => $detalle) {
    //var_dump($detalle);exit();
	$cantidad = escape($detalle['cantidad']);
	$precio = escape($detalle['precio']);
	$descuento = escape($detalle['descuento']);
	$importe = $cantidad * $precio;
	$total = $total + $importe;
	$body .= '<tr>';
	$body .= '<td class="left-right" align="right">' . ($nro + 1) . '</td>';
	$body .= '<td class="left-right">' . escape($detalle['codigo']) . '</td>';
	$body .= '<td class="left-right">' . escape($detalle['nombre_factura']) . '</td>';
	$body .= '<td class="left-right" align="right">' . $cantidad . '</td>';
	$body .= '<td class="left-right" align="right">' . $precio . '</td>';
	$body .= '<td class="left-right" align="right">' . $descuento . '</td>';
	$body .= '<td class="left-right" align="right">' . number_format($importe, 2, '.', '') . '</td>';
	$body .= '</tr>';
}

// Obtiene el valor total
$valor_total = number_format($total, 2, '.', '');

// Obtiene los datos del monto total
$conversor = new NumberToLetterConverter();
$monto_textual = explode('.', $valor_total);
$monto_numeral = $monto_textual[0];
$monto_decimal = $monto_textual[1];
$monto_literal = upper($conversor->to_word($monto_numeral));

$body = ($body == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body;

// Formateamos la tabla
$tabla = <<<EOD
	<style>
	th {
		background-color: #eee;
		font-weight: bold;
	}
	.left-right {
		border-left: 1px solid #444;
		border-right: 1px solid #444;
	}
	.none {
		border: 1px solid #fff;
	}
	.all {
		border: 1px solid #444;
	}
	</style>
	<table cellpadding="1">
		<tr>
			<td width="15%" class="none"><b>FECHA Y HORA:</b></td>
			<td width="35%" class="none">$valor_fecha</td>
			<td width="15%" class="none"><b>EMPLEADO:</b></td>
			<td width="35%" class="none">$valor_empleado</td>
		</tr>
		<tr>
			<td class="none"><b>SEÑOR(ES):</b></td>
			<td class="none">$valor_nombre_cliente</td>
			<td class="none"><b>TELÉFONO:</b></td>
			<td class="none">$valor_telefono</td>
		</tr>
		<tr>
			<td class="none"><b>NIT / CI:</b></td>
			<td class="none">$valor_nit_ci</td>
			<td class="none"><b>DIRECCIÓN:</b></td>
			<td class="none">$valor_direccion</td>
		</tr>
	</table>
	<br><br>
	<table>
		<tr>
			<td width="15%"><b>DESCUENTO</b></td>
			<td>$valor_descuento</td>
		</tr>
		<tr>
			<td width="15%"><b>OBSERVACIÓN</b></td>
			<td>$valor_observacion</td>
		</tr>
	</table>
	<br><br>
	<table cellpadding="5">
		<tr>
			<th width="5%" class="all" align="right">#</th>
			<th width="10%" class="all" align="left">CÓDIGO</th>
			<th width="39%" class="all" align="left">DETALLE</th>
			<th width="10%" class="all" align="right">CANT.</th>
			<th width="13%" class="all" align="right">PRECIO $valor_moneda</th>
			<th width="10%" class="all" align="right">DESC. (%)</th>
			<th width="13%" class="all" align="right">IMPORTE $valor_moneda</th>
		</tr>
		$body
		<tr>
			<th class="all" align="left" colspan="6">IMPORTE TOTAL $valor_moneda</th>
			<th class="all" align="right">$valor_total</th>
		</tr>
	</table>
	<p>$monto_literal $monto_decimal /100</p>
EOD;
	
// Imprime la tabla
$pdf->writeHTML($tabla, true, false, false, false, '');

}

// Genera el nombre del archivo
$nombre = 'orden_compra' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
