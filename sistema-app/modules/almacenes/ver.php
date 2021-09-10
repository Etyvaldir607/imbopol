<?php

// Obtiene el id_almacen
$id_almacen = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el almacén
$almacen = $db->select('z.*')
->from('inv_almacenes z')
->where('z.id_almacen', $id_almacen)
->fetch_first();

// Verifica si existe el almacén
if (!$almacen) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Ver almacén</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_editar || $permiso_eliminar || $permiso_imprimir || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/almacenes/crear" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				<span class="hidden-xs hidden-sm">Nuevo</span>
			</a>
			<?php } ?>
			<?php if ($permiso_editar) { ?>
			<a href="?/almacenes/editar/<?= $almacen['id_almacen']; ?>" class="btn btn-warning">
				<span class="glyphicon glyphicon-edit"></span>
				<span class="hidden-xs hidden-sm">Modificar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/almacenes/eliminar/<?= $almacen['id_almacen']; ?>" class="btn btn-danger" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
				<span class="hidden-xs hidden-sm">Eliminar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/almacenes/imprimir/<?= $almacen['id_almacen']; ?>" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs hidden-sm">Imprimir</span>
			</a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/almacenes/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span class="hidden-xs hidden-sm <?= ($permiso_imprimir) ? 'hidden-md' : ''; ?>">Listado</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="col-md-3 control-label">#:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($almacen['id_almacen']); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Almacén:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($almacen['almacen']); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Dirección:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($almacen['direccion']); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Teléfono:</label>
					<div class="col-md-9">
						<p class="form-control-static">
						<?php $telefono = explode(',', escape($almacen['telefono'])); ?>
						<?php foreach ($telefono as $elemento) { ?>
							<span class="label label-success"><?= $elemento; ?></span>
						<?php } ?>
						</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Principal:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= (escape($almacen['principal']) == 'S') ? 'Si' : 'No'; ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($almacen['descripcion']); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php if ($permiso_eliminar) { ?>
<script>
$(function () {
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el almacén?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
});
</script>
<?php } ?>
<?php require_once show_template('footer-advanced'); ?>