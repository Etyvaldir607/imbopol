<?php

// Obtiene los parametros
$id_almacen = (isset($params[0])) ? $params[0] : 0;
$id_producto = (isset($params[1])) ? $params[1] : 0;

// Obtiene el almacen
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

// Obtiene el producto
$producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();

// Verifica si existe el almacen y el producto
if (!$almacen || !$producto) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los movimientos
$movimientos = $db->query("select * from ((select d.*, concat(p.nombres, ' ', p.paterno, ' ', p.materno) as empleado from (select d.id_detalle, d.cantidad, d.producto_id, d.egreso_id as movimiento_id, e.fecha_egreso as fecha_movimiento, e.hora_egreso as hora_movimiento, 'Egreso' as estado, e.tipo, e.descripcion, e.almacen_id, e.empleado_id from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where d.producto_id = $id_producto and e.almacen_id = $id_almacen) as d left join sys_empleados p on p.id_empleado = d.empleado_id) union (select d.*, concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado from (select d.id_detalle, d.cantidad, d.producto_id, d.ingreso_id as movimiento_id, i.fecha_ingreso as fecha_movimiento, i.hora_ingreso as hora_movimiento, 'Ingreso' as estado, i.tipo, i.descripcion, i.almacen_id, i.empleado_id from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id where d.producto_id = $id_producto and i.almacen_id = $id_almacen) as d left join sys_empleados e on e.id_empleado = d.empleado_id)) as m order by m.fecha_movimiento asc, m.hora_movimiento asc")->fetch();

?>
<?php require_once show_template('header-advanced'); ?>
<style>
.table-xs tbody {
	font-size: 12px;
}
</style>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Stock general de productos</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al stock de productos hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/stocks/listar" class="btn btn-primary"><i class="glyphicon glyphicon-stats"></i><span> Stock</span></a>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-6">
			<div class="well">
				<h4 class="margin-none"><u>Producto</u></h4>
				<dl class="margin-none">
					<dt>Código:</dt>
					<dd><?= escape($producto['codigo']); ?></dd>
					<dt>Producto:</dt>
					<dd><?= escape($producto['nombre']); ?></dd>
					<dt>Precio:</dt>
					<dd><?= escape($producto['precio_actual']); ?></dd>
				</dl>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="well">
				<h4 class="margin-none"><u>Almacén</u></h4>
				<dl class="margin-none">
					<dt>Almacén:</dt>
					<dd><?= escape($almacen['almacen']); ?></dd>
					<dt>Dirección:</dt>
					<dd><?= escape($almacen['direccion']); ?></dd>
					<dt>Principal:</dt>
					<dd><?= ($almacen['principal'] == 'S') ? 'Si' : 'No'; ?></dd>
				</dl>
			</div>
		</div>
		</div>
	<?php if ($movimientos) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Fecha</th>
				<th class="text-nowrap">Tipo</th>
				<th class="text-nowrap">Descripción</th>
				<th class="text-nowrap">Ingreso</th>
				<th class="text-nowrap">Egreso</th>
				<th class="text-nowrap">Saldo</th>
				<th class="text-nowrap">Empleado</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Ingreso</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Egreso</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Saldo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>
			</tr>
		</tfoot>
		<tbody>
			<?php $saldo = 0; ?>
			<?php foreach ($movimientos as $nro => $movimiento) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= date_decode($movimiento['fecha_movimiento'], $_institution['formato']); ?> <small class="text-primary"><?= escape($movimiento['hora_movimiento']); ?></small></td>
				<td class="text-nowrap"><?= escape($movimiento['tipo']); ?></td>
				<td class="text-nowrap"><?= escape($movimiento['descripcion']); ?></td>
				<td class="text-nowrap"><?= ($movimiento['estado'] == 'Ingreso') ? escape($movimiento['cantidad']) : ''; ?></td>
				<td class="text-nowrap"><?= ($movimiento['estado'] == 'Egreso') ? escape($movimiento['cantidad']) : ''; ?></td>
				<?php $saldo = ($movimiento['estado'] == 'Ingreso') ? $saldo + $movimiento['cantidad'] : $saldo - $movimiento['cantidad']; ?>
				<td class="text-nowrap"><?= escape($saldo); ?></td>
				<td class="text-nowrap"><?= escape($movimiento['empleado']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>El producto no cuenta con movimientos de ingreso y egreso en este almacén.</p>
	</div>
	<?php } ?>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
$(function () {	
	<?php if ($movimientos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'detalle_movimientos_producto',
		reports: 'excel|word|pdf|html',
		size: 8
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>