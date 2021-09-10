<?php

// Obtiene los almacenes
$almacenes = $db->select('z.*')->from('inv_almacenes z')->order_by('z.id_almacen')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Almacenes</b>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $almacenes)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos almacenes hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/almacenes/imprimir" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/almacenes/crear" class="btn btn-primary">
				<span class="glyphicon glyphicon-plus"></span>
				<span>Nuevo</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($almacenes) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap text-middle width-collapse">#</th>
				<th class="text-nowrap text-middle width-collapse">Almacén</th>
				<th class="text-nowrap text-middle">Dirección</th>
				<th class="text-nowrap text-middle width-collapse">Teléfono</th>
				<th class="text-nowrap text-middle width-collapse">Principal</th>
				<th class="text-nowrap text-middle">Descripción</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap text-middle width-collapse">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Almacén</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Dirección</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Teléfono</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Principal</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($almacenes as $nro => $almacen) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($almacen['almacen']); ?></td>
				<td class="text-nowrap"><?= escape($almacen['direccion']); ?></td>
				<td class="text-nowrap">
					<?php $telefono = explode(',', escape($almacen['telefono'])); ?>
					<?php foreach ($telefono as $elemento) { ?>
					<span class="label label-success"><?= $elemento; ?></span>
					<?php } ?>
				</td>
				<td class="text-nowrap"><?= (escape($almacen['principal']) == 'S') ? 'Si' : 'No'; ?></td>
				<td class="text-nowrap"><?= escape($almacen['descripcion']); ?></td>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_ver) { ?>
					<a href="?/almacenes/ver/<?= $almacen['id_almacen']; ?>" class="underline-none" data-toggle="tooltip" data-title="Ver almacén">
						<span class="glyphicon glyphicon-search"></span>
					</a>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/almacenes/editar/<?= $almacen['id_almacen']; ?>" class="underline-none" data-toggle="tooltip" data-title="Modificar almacén">
						<span class="glyphicon glyphicon-edit"></span>
					</a>
					<?php } ?>
					<?php if ($permiso_eliminar) { ?>
					<a href="?/almacenes/eliminar/<?= $almacen['id_almacen']; ?>" class="underline-none" data-toggle="tooltip" data-title="Eliminar almacén" data-eliminar="true">
						<span class="glyphicon glyphicon-trash"></span>
					</a>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen almacenes registrados en la base de datos, para crear nuevos almacenes hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
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
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el almacén?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/almacenes/crear';
				break;
			}
		}
	});
	<?php } ?>
	
	<?php if ($almacenes) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'almacenes',
		reports: 'excel|word|pdf|html'
	});

	$('#states_0').find(':radio[value="hide"]').trigger('click');
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>