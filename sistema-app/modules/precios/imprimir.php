<?php

// Obtiene el id_producto
$id_producto = (isset($params[0])) ? $params[0] : 0;

if ($id_producto == 0) {
	// Obtiene los productos
	$productos = $db->select('z.*, a.unidad as unidad, b.categoria as categoria')
					->from('inv_productos z')
					->join('inv_unidades a', 'z.unidad_id = a.id_unidad', 'left')
					->join('inv_categorias b', 'z.categoria_id = b.id_categoria', 'left')
					->order_by('z.id_producto')
					->fetch();
} else {
	// Obtiene los permisos
	$permisos = explode(',', permits);
	
	// Almacena los permisos en variables
	$permiso_ver = in_array('ver', $permisos);
	
	// Obtiene el producto
	$producto = $db->select('z.*, a.unidad as unidad, b.categoria as categoria')
	->from('inv_productos z')
	->join('inv_unidades a', 'z.unidad_id = a.id_unidad', 'left')
	->join('inv_categorias b', 'z.categoria_id = b.id_categoria', 'left')
	->where('z.id_producto', $id_producto)
	->fetch_first();
	
	// Verifica si existe el producto
	if (!$producto) {
		// Error 404
		require_once not_found();
		exit;
	} elseif (!$permiso_ver) {
		// Error 401
		require_once bad_request();
		exit;
	}

	// Obtiene los productos y sus precios
	$precios = $db->select("p.*, ifnull(e.nombres, '') as nombres, ifnull(e.paterno, '') as paterno, ifnull(e.materno, '') as materno")
				  ->from('inv_precios p')
				  ->join('sys_empleados e', 'p.empleado_id = e.id_empleado', 'left')
				  ->where('producto_id', $id_producto)
				  ->order_by('fecha_registro asc, hora_registro asc')
				  ->fetch();
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? $moneda['sigla'] : '';

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Define variables globales
define('NOMBRE', escape($_institution['nombre']));
define('IMAGEN', escape($_institution['imagen_encabezado']));
define('PROPIETARIO', escape($_institution['propietario']));
define('PIE', escape($_institution['pie_pagina']));
define('FECHA', date(escape($_institution['formato'])) . ' ' . date('H:i:s'));

// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
	public function Header() {
		$this->Ln(5);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$this->Cell(0, 5, NOMBRE, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, PROPIETARIO, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, FECHA, 'B', true, 'R', false, '', 0, false, 'T', 'M');
		$imagen = (IMAGEN != '') ? institucion . '/' . IMAGEN : imgs . '/empty.jpg' ;
		$this->Image($imagen, PDF_MARGIN_LEFT, 5, '', 14, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}
	
	public function Footer() {
		$this->SetY(-10);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$length = ($this->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT) / 2;
		$number = $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
		$this->Cell($length, 5, $number, 'T', false, 'L', false, '', 0, false, 'T', 'M');
		$this->Cell($length, 5, PIE, 'T', true, 'R', false, '', 0, false, 'T', 'M');
	}
}

// Instancia el documento PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// ------------------------------------------------------------

if ($id_producto == 0) {
	// Documento general -----------------------------------------------------

	// Adiciona la pagina
	$pdf->AddPage('L', array(PDF_PAGE_FORMAT_WIDTH, PDF_PAGE_FORMAT_HEIGHT));
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'LISTA DE PRECIOS', 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	// Define variables
	$valor_moneda = ($moneda != '') ? ' (' . $moneda . ')' : '';
	
	// Estructura la tabla
	$body = '';
	foreach ($productos as $nro => $producto) {
		$body .= '<tr>';
		$body .= '<td>' . ($nro + 1) . '</td>';
		$body .= '<td>' . escape($producto['codigo']) . '</td>';
		$body .= '<td>' . str_replace("*", "'", escape($producto['nombre'])) . '</td>';
		$body .= '<td>' . escape($producto['precio_actual']) . '</td>';
		$body .= '<td>' . escape($producto['categoria']) . '</td>';
		$body .= '</tr>';
	}
	
	$body = ($body == '') ? '<tr><td colspan="5" align="center">No existen productos registrados en la base de datos</td></tr>' : $body;
	
	// Formateamos la tabla
	$tabla = <<<EOD
	<style>
	th {
		background-color: #eee;
		border: 1px solid #444;
		font-weight: bold;
	}
	td {
		border-left: 1px solid #444;
		border-right: 1px solid #444;
	}
	table {
		border-bottom: 1px solid #444;
	}
	</style>
	<table cellpadding="5">
		<tr>
			<th width="6%">#</th>
			<th width="15%">Código</th>
			<th width="49%">Nombre</th>
			<th width="15%">Precio actual $valor_moneda</th>
			<th width="15%">Categoría</th>
		</tr>
		$body
	</table>
EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'lista_precios_' . date('Y-m-d_H-i-s') . '.pdf';
} else {
	// Documento individual --------------------------------------------------
	
	// Asigna la orientacion de la pagina
	$pdf->SetPageOrientation('P');
	
	// Adiciona la pagina
	$pdf->AddPage();
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'DETALLE DE PRODUCTO', 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Establece la fuente del titulo
	$pdf->SetFont('helvetica', 'I', 10);
	
	// Subtitulo del documento
	$pdf->Cell(0, 5, 'ID: ' . $id_producto, 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
	
	// Define las variables
	$valor_codigo = escape($producto['codigo']);
	$valor_nombre = str_replace("*", "'", escape($producto['nombre']));
	$valor_precio_actual = escape($producto['precio_actual']) . ' ' . $moneda;
	$valor_moneda = ($moneda != '') ? ' (' . $moneda . ')' : '';

	// Estructura la tabla
	$body = '';
	foreach ($precios as $nro => $precio) {
		$body .= '<tr>';
		$body .= '<td class="left-right">' . ($nro + 1) . '</td>';
		$body .= '<td class="left-right">' . escape(date_decode($precio['fecha_registro'], $_institution['formato'])) . '</td>';
		$body .= '<td class="left-right">' . escape($precio['hora_registro']) . '</td>';
		$body .= '<td class="left-right">' . escape($precio['precio']) . '</td>';
		$body .= '<td class="left-right">' . escape($precio['paterno'] . ' ' . $precio['materno'] . ' ' . $precio['nombres']) . '</td>';
		$body .= '</tr>';
	}
	
	$body = ($body == '') ? '<tr><td colspan="5" align="center" class="all">Este producto no tiene precio, es muy importante que asigne un precio para que el proceso de compra y venta sea correcto.</td></tr>' : $body;
	
	// Formateamos la tabla
	$tabla = <<<EOD
	<style>
	table {
		border-bottom: 1px solid #444;
	}
	th {
		background-color: #eee;
		font-weight: bold;
	}
	.left-right {
		border-left: 1px solid #444;
		border-right: 1px solid #444;
	}
	.all {
		border: 1px solid #444;
	}
	</style>
	<table cellpadding="5">
		<tr>
			<td colspan="2" class="all"><b>Infomación del producto</b></td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Código del producto:</th>
			<td width="60%" class="left-right">$valor_codigo</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right">Nombre del producto:</th>
			<td width="60%" class="left-right">$valor_nombre</td>
		</tr>
		<tr>
			<th width="40%" align="right" class="left-right" style="border-bottom: 1px solid #444;">Precio actual:</th>
			<td width="60%" class="left-right" style="border-bottom: 1px solid #444;">$valor_precio_actual</td>
		</tr>
		<tr>
			<td colspan="2" class="left-right"><b>Historial de precios</b></td>
		</tr>
	</table>
	<table cellpadding="5">
		<tr>
			<th width="6%" class="all">#</th>
			<th width="15%" class="all">Fecha</th>
			<th width="15%" class="all">Hora</th>
			<th width="14%" class="all">Precio $valor_moneda</th>
			<th width="50%" class="all">Empleado</th>
		</tr>
		$body
	</table>
EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'historial_de_precios_' . $id_producto . '_' . date('Y-m-d_H-i-s') . '.pdf';
}

// ------------------------------------------------------------

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
