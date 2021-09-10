<?php

// Obtiene los parametros
$id_almacen = (isset($params[0])) ? $params[0] : 0;
$id_producto = (isset($params[1])) ? $params[1] : 0;

// Obtiene los movimientos
$movimientos = $db->query("select m.*, ifnull(concat(e.nombres, ' ', e.paterno, ' ', e.materno), '') as empleado from (select i.id_ingreso as id_movimiento, d.id_detalle, i.fecha_ingreso as fecha_movimiento, i.hora_ingreso as hora_movimiento, i.descripcion, d.cantidad, d.costo as monto, 'i' as tipo, i.empleado_id, i.almacen_id from inv_ingresos_detalles d left join inv_ingresos i on d.ingreso_id = i.id_ingreso where d.producto_id = $id_producto union select e.id_egreso as id_movimiento, d.id_detalle, e.fecha_egreso as fecha_movimiento, e.hora_egreso as hora_movimiento, e.descripcion, d.cantidad, d.precio as monto, 'e' as tipo, e.empleado_id, e.almacen_id from inv_egresos_detalles d left join inv_egresos e on d.egreso_id = e.id_egreso where d.producto_id = $id_producto) m left join sys_empleados e on m.empleado_id = e.id_empleado where m.almacen_id = $id_almacen order by m.fecha_movimiento asc, m.hora_movimiento asc")->fetch();

// Verifica si existen movimientos
if (!$movimientos) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene el almacen
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

// Obtiene el producto
$producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Kardex valorado</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar de almacén o de producto hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/kardex/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-menu-left"></span>
				<span>Regresar</span>
			</a>
			<a href="?/kardex/imprimir/<?= $id_almacen; ?>/<?= $id_producto; ?>" target="_blank" class="btn btn-default">
				<span class="glyphicon glyphicon-print"></span>
				<span>Imprimir</span>
			</a>
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
					<dd>
						<a href="?/precios/ver/<?= $id_producto; ?>" target="_blank"><?= escape($producto['precio_actual']); ?></a>
					</dd>
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
	<h3 class="text-center">KARDEX VALORADO</h3>
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap text-center text-middle" rowspan="2">#</th>
					<th class="text-nowrap text-center text-middle" rowspan="2">Fecha</th>
					<th class="text-nowrap text-center text-middle" rowspan="2">Descripción</th>
					<th class="text-nowrap text-center text-middle" colspan="3">Entradas</th>
					<th class="text-nowrap text-center text-middle" colspan="3">Salidas</th>
					<th class="text-nowrap text-center text-middle" colspan="3">Saldos</th>
					<th class="text-nowrap text-center text-middle" rowspan="2">Empleado</th>
				</tr>
				<tr class="active">
					<th class="text-nowrap text-center text-middle">Cantidad</th>
					<th class="text-nowrap text-center text-middle">Costo</th>
					<th class="text-nowrap text-center text-middle">Total</th>
					<th class="text-nowrap text-center text-middle">Cantidad</th>
					<th class="text-nowrap text-center text-middle">Costo</th>
					<th class="text-nowrap text-center text-middle">Total</th>
					<th class="text-nowrap text-center text-middle">Cantidad</th>
					<th class="text-nowrap text-center text-middle">Costo</th>
					<th class="text-nowrap text-center text-middle">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php $saldo_cantidad = 0; ?>
				<?php $saldo_costo = 0; ?>
				<?php $ingresos = array(); ?>
				<?php foreach ($movimientos as $nro => $movimiento) { ?>
					<?php if ($movimiento['tipo'] == 'i') : ?>

					<?php array_push($ingresos, array('cantidad' => $movimiento['cantidad'], 'costo' => $movimiento['monto'])); ?>
					<?php $saldo_cantidad = $saldo_cantidad + $movimiento['cantidad']; ?>
					<?php $saldo_costo = $saldo_costo + ($movimiento['cantidad'] * $movimiento['monto']); ?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
						<td class="text-nowrap">
							<span><?= escape($movimiento['fecha_movimiento']); ?></span>
							<span class="text-primary"><?= escape($movimiento['hora_movimiento']); ?></span>
						</td>
						<td class="text-nowrap"><?= (escape($movimiento['descripcion']) == '') ? 'Ingreso de productos a almacén': escape($movimiento['descripcion']); ?></td>
						<td class="text-nowrap text-right success text-primary"><strong><?= escape($movimiento['cantidad']); ?></strong></td>
						<td class="text-nowrap text-right success"><?= escape($movimiento['monto']); ?></td>
						<td class="text-nowrap text-right success"><strong><?= number_format(($movimiento['cantidad'] * $movimiento['monto']), 2, '.', ''); ?></strong></td>
						<td class="text-nowrap text-right"></td>
						<td class="text-nowrap text-right"></td>
						<td class="text-nowrap text-right"></td>
						<td class="text-nowrap text-right info text-primary"><strong><?= $saldo_cantidad; ?></strong></td>
						<td class="text-nowrap text-right info"></td>
						<td class="text-nowrap text-right info"><strong><?= number_format($saldo_costo, 2, '.', ''); ?></strong></td>
						<td class="text-nowrap"><?= escape($movimiento['empleado']); ?></td>
					</tr>
					<?php else : ?>
					<?php $ciclo = true;
					do {
						$ingreso = array_shift($ingresos);
						if ($ingreso['cantidad'] >= $movimiento['cantidad']) {
							$ingreso['cantidad'] = $ingreso['cantidad'] - $movimiento['cantidad'];
							if ($ingreso['cantidad'] > 0) {
								array_unshift($ingresos, $ingreso);
							}
							$ciclo = false;
							?>
							<?php $saldo_cantidad = $saldo_cantidad - $movimiento['cantidad']; ?>
							<?php $saldo_costo = $saldo_costo - ($movimiento['cantidad'] * $ingreso['costo']); ?>
							<tr>
								<th class="text-nowrap"><?= $nro + 1; ?></th>
								<td class="text-nowrap">
									<span><?= escape($movimiento['fecha_movimiento']); ?></span>
									<span class="text-primary"><?= escape($movimiento['hora_movimiento']); ?></span>
								</td>
								<td class="text-nowrap"><?= (escape($movimiento['descripcion']) == '') ? 'Ingreso de productos a almacén': escape($movimiento['descripcion']); ?></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right danger text-primary"><strong><?= escape($movimiento['cantidad']); ?></strong></td>
								<td class="text-nowrap text-right danger"><?= escape($ingreso['costo']); ?></td>
								<td class="text-nowrap text-right danger"><strong><?= number_format(($movimiento['cantidad'] * $ingreso['costo']), 2, '.', ''); ?></strong></td>
								<td class="text-nowrap text-right info text-primary"><strong><?= $saldo_cantidad; ?></strong></td>
								<td class="text-nowrap text-right info"></td>
								<td class="text-nowrap text-right info"><strong><?= number_format($saldo_costo, 2, '.', ''); ?></strong></td>
								<td class="text-nowrap"><?= escape($movimiento['empleado']); ?></td>
							</tr>
							<?php
						} else {
							?>
							<?php $saldo_cantidad = $saldo_cantidad - $ingreso['cantidad']; ?>
							<?php $saldo_costo = $saldo_costo - ($ingreso['cantidad'] * $ingreso['costo']); ?>
							<tr>
								<th class="text-nowrap"><?= $nro + 1; ?></th>
								<td class="text-nowrap">
									<span><?= escape($movimiento['fecha_movimiento']); ?></span>
									<span class="text-primary"><?= escape($movimiento['hora_movimiento']); ?></span>
								</td>
								<td class="text-nowrap"><?= (escape($movimiento['descripcion']) == '') ? 'Ingreso de productos a almacén': escape($movimiento['descripcion']); ?></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right danger text-primary"><strong><?= escape($ingreso['cantidad']); ?></strong></td>
								<td class="text-nowrap text-right danger"><?= escape($ingreso['costo']); ?></td>
								<td class="text-nowrap text-right danger"><strong><?= number_format(($ingreso['cantidad'] * $ingreso['costo']), 2, '.', ''); ?></strong></td>
								<td class="text-nowrap text-right info text-primary"><strong><?= $saldo_cantidad; ?></strong></td>
								<td class="text-nowrap text-right info"></td>
								<td class="text-nowrap text-right info"><strong><?= number_format($saldo_costo, 2, '.', ''); ?></strong></td>
								<td class="text-nowrap"><?= escape($movimiento['empleado']); ?></td>
							</tr>
							<?php
							$movimiento['cantidad'] = $movimiento['cantidad'] - $ingreso['cantidad'];
						}
					} while ($ciclo); ?>
					<?php  ?>


					<?php endif ?>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>El kardex valorado no puede mostrarse por que no existen movimientos registrados.</p>
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
/*$(function () {
	var table = $('#table').DataFilter({
		filter: true,
		name: 'reporte_de_existencias',
		reports: 'excel|word|pdf|html'
	});
});*/
</script>
<?php require_once show_template('footer-advanced'); ?>