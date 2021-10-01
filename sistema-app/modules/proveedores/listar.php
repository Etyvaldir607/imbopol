<?php

// Obtiene los proveedores
$proveedores = $db->select('id_proveedor, proveedor, nit, direccion, telefono, count(proveedor) as nro_visitas')
->from('inv_proveedores')
->group_by('proveedor, nit, direccion, telefono')
->order_by('proveedor asc, nit, direccion, telefono asc')
->fetch();
//$proveedores = $db->query('SELECT a.id_proveedor, a.proveedor, a.nit, a.telefono, a.direccion,  count(a.proveedor) as nro_visitas, sum(b.monto_total) as total_compras FROM inv_proveedores a LEFT OUTER JOIN inv_ingresos b ON a.proveedor = b.proveedor')->group_by('a.proveedor')->order_by('proveedor asc, nit asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_modificar = in_array('editar', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Proveedores</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para ver el reporte hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/proveedores/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
            <a href="?/proveedores/crear" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs"> Crear proveedor</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($proveedores) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
                <th class="text-nowrap">Proveedor</th>
                <th class="text-nowrap">Nit</th>
                <th class="text-nowrap">Telefono</th>
                <th class="text-nowrap">Dirección</th>
                <th class="text-nowrap">Visitas</th>
                <th class="text-nowrap">Ventas <?= escape($moneda); ?></th>
                <?php if ($permiso_modificar || $permiso_eliminar) : ?>
                <th class="text-nowrap">Opciones</th>
                <?php endif ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Proveedor</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Nit</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Telefono</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Dirección</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Visitas</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Ventas <?= escape($moneda); ?></th>
                <?php if ($permiso_modificar || $permiso_eliminar) : ?>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Opciones</th>
                <?php endif ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($proveedores as $nro => $proveedor) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
                <td class="text-nowrap"><?= escape($proveedor['proveedor']); ?></td>
                <td class="text-nowrap"><?= escape($proveedor['nit']); ?></td>
                <td class="text-nowrap"><?= escape($proveedor['telefono']); ?></td>
                <td class="text-nowrap"><?= escape($proveedor['direccion']); ?></td>
				<td class="text-nowrap"><?= escape($proveedor['nro_visitas']); ?></td>
                <td class="text-nowrap"><?= escape($proveedor['total_compras']); ?></td>
                <?php if ($permiso_modificar || $permiso_eliminar) : ?>
                <td class="text-nowrap">
                    <?php if ($permiso_modificar) : ?>
                        <a href="?/proveedores/editar/<?= $proveedor['id_proveedor']; ?>" data-toggle="tooltip" data-title="Modificar cliente"><span class="glyphicon glyphicon-edit"></span></a>
                    <?php endif ?>
                    <?php if ($permiso_eliminar) : ?>
                        <a href="?/proveedores/eliminar/<?= $proveedor['id_proveedor']; ?>" data-toggle="tooltip" data-title="Eliminar cliente" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
                    <?php endif ?>
                </td>
                <?php endif ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen proveedores registrados en la base de datos.</p>
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
	<?php if ($permiso_imprimir) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'p':
					e.preventDefault();
					window.location = '?/proveedores/imprimir';
				break;
			}
		}
	});
	<?php } ?>
	
	<?php if ($proveedores) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'proveedores',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>