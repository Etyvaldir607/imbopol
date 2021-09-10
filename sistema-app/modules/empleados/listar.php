<?php

// Obtiene los empleados
$empleados = $db->select('z.*')->from('sys_empleados z')->order_by('z.id_empleado')->fetch();

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
		<strong>Empleados</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $empleados)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos empleados hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/empleados/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/empleados/crear" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<?php if ($empleados) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Nombres</th>
				<th class="text-nowrap">Apellido paterno</th>
				<th class="text-nowrap">Apellido materno</th>
				<th class="text-nowrap">Género</th>
				<th class="text-nowrap">Fecha de nacimiento</th>
				<th class="text-nowrap">Teléfono</th>
				<th class="text-nowrap">Cargo</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombres</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Apellido paterno</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Apellido materno</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Género</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha de nacimiento</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Teléfono</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cargo</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($empleados as $nro => $empleado) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($empleado['nombres']); ?></td>
				<td class="text-nowrap"><?= escape($empleado['paterno']); ?></td>
				<td class="text-nowrap"><?= escape($empleado['materno']); ?></td>
				<td class="text-nowrap"><?= escape($empleado['genero']); ?></td>
				<td class="text-nowrap"><?= date_decode(escape($empleado['fecha_nacimiento']), $_institution['formato']); ?></td>
				<td class="text-nowrap">
					<?php $telefono = explode(',', escape($empleado['telefono'])); ?>
					<?php foreach ($telefono as $elemento) { ?>
						<span class="label label-success"><?= $elemento; ?></span>
					<?php } ?>
				</td>
				<td class="text-nowrap"><?= escape($empleado['cargo']); ?></td>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_ver) { ?>
					<a href="?/empleados/ver/<?= $empleado['id_empleado']; ?>" data-toggle="tooltip" data-title="Ver empleado"><i class="glyphicon glyphicon-search"></i></a>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/empleados/editar/<?= $empleado['id_empleado']; ?>" data-toggle="tooltip" data-title="Editar empleado"><i class="glyphicon glyphicon-edit"></i></a>
					<?php } ?>
					<?php if ($permiso_eliminar) { ?>
					<a href="?/empleados/eliminar/<?= $empleado['id_empleado']; ?>" data-toggle="tooltip" data-title="Eliminar empleado" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
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
		<p>No existen empleados registrados en la base de datos, para crear nuevos empleados hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
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
		bootbox.confirm('Está seguro que desea eliminar el empleado?', function (result) {
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
					window.location = '?/empleados/crear';
				break;
			}
		}
	});
	<?php } ?>
	
	<?php if ($empleados) { ?>
	var table = $('#table').DataFilter({
		filter: false,
		name: 'empleados',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>