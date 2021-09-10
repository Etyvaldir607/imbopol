<?php

// Obtiene los almacenes
$almacenes = $db->get('inv_almacenes');

// Obtiene los productos
$productos = $db->select('p.*, u.unidad, c.categoria')->from('inv_productos p')->join('inv_unidades u', 'p.unidad_id = u.id_unidad', 'left')->join('inv_categorias c', 'p.categoria_id = c.id_categoria', 'left')->order_by('p.id_producto')->fetch();

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista general de productos</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($productos) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Código</th>
                <th class="text-nowrap">Nombre del producto</th>
                <th class="text-nowrap">Medidas</th>
                <th class="text-nowrap">Color</th>
				<th class="text-nowrap">Tipo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap"><?= $almacen['almacen']; ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre del producto</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Medidas</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Color</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false"><?= $almacen['almacen']; ?></th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($productos as $nro => $producto) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($producto['codigo']); ?></td>
				<td class="text-nowrap"><?= escape($producto['nombre']); ?></td>
                <td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
                <td class="text-nowrap"><?= escape($producto['color']); ?></td>
                <td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<td class="text-nowrap">
					<a href="?/kardex/detallar/<?= $almacen['id_almacen']; ?>/<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Ver kardex"><span class="glyphicon glyphicon-book"></span></a>
				</td>
				<?php } ?>
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