<?php

// Obtiene los almacenes
$almacenes = $db->get('inv_almacenes');

// Obtiene los productos
$productos = $db->select('p.*, u.unidad, c.categoria')->from('inv_productos p')->join('inv_unidades u', 'p.unidad_id = u.id_unidad', 'left')->join('inv_categorias c', 'p.categoria_id = c.id_categoria', 'left')->order_by('p.id_producto')->fetch();

// Obtiene a los clientes
$clientes = $db->select('id_egreso, nombre_cliente, nit_ci, count(nombre_cliente) as nro_visitas, sum(monto_total) as total_ventas')->from('inv_egresos')->group_by('nombre_cliente, nit_ci')->order_by('total_ventas desc, nro_visitas desc')->fetch();
?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista general de clientes</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($productos) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Cliente</th>
                <th class="text-nowrap">NIT/CI</th>
                <th class="text-nowrap">Visitas</th>
                <th class="text-nowrap">Total</th>
				<th class="text-nowrap">Detalles</th>

			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Visitas</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Detalles</th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($clientes as $nro => $cliente) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($cliente['nombre_cliente']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['nit_ci']); ?></td>
                <td class="text-nowrap"><?= escape($cliente['nro_visitas']); ?></td>
                <td class="text-nowrap"><?= escape($cliente['total_ventas']); ?></td>
				<td class="text-nowrap">
					<a href="?/clientes/detallar/<?= $cliente['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle"><span class="glyphicon glyphicon-book"></span></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen productos registrados en la base de datos.</p>
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
	<?php if ($productos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'lista_productos',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>