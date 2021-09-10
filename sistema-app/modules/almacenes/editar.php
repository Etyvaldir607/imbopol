<?php

// Obtiene el id_almacen
$id_almacen = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el almacén
$almacen = $db->select('z.*')->from('inv_almacenes z')->where('z.id_almacen', $id_almacen)->fetch_first();

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
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Modificar almacén</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_listar) { ?>
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
			<?php if ($permiso_ver) { ?>
			<a href="?/almacenes/ver/<?= $almacen['id_almacen']; ?>" class="btn btn-warning">
				<span class="glyphicon glyphicon-search"></span>
				<span class="hidden-xs hidden-sm">Ver</span>
			</a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/almacenes/eliminar/<?= $almacen['id_almacen']; ?>" class="btn btn-danger" data-eliminar="true">
				<span class="glyphicon glyphicon-trash"></span>
				<span class="hidden-xs hidden-sm">Eliminar</span>
			</a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/almacenes/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span class="hidden-xs">Listado</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/almacenes/guardar" class="form-horizontal">
				<div class="form-group">
					<label for="almacen" class="col-md-3 control-label">Almacén:</label>
					<div class="col-md-9">
						<input type="hidden" value="<?= $almacen['id_almacen']; ?>" name="id_almacen" data-validation="required">
						<input type="text" value="<?= $almacen['almacen']; ?>" name="almacen" id="almacen" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-#()_ ">
					</div>
				</div>
				<div class="form-group">
					<label for="direccion" class="col-md-3 control-label">Dirección:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $almacen['direccion']; ?>" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-/.,#º() ">
					</div>
				</div>
				<div class="form-group">
					<label for="telefono" class="col-md-3 control-label">Teléfono:</label>
					<div class="col-md-9">
						<input type="text" value="<?= $almacen['telefono']; ?>" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="alphanumeric length" data-validation-allowing="-+,() " data-validation-length="max100" data-validation-optional="true">
					</div>
				</div>
				<div class="form-group">
					<label for="principal" class="col-md-3 control-label">Principal:</label>
					<div class="col-md-9">
						<div class="radio">
							<label>
								<input type="radio" name="principal" value="N" <?= ($almacen['principal'] == 'N') ? 'checked' : ''; ?>>
								<span>No</span>
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="principal" value="S" <?= ($almacen['principal'] == 'S') ? 'checked' : ''; ?>>
								<span>Si</span>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;@#()_\n " data-validation-optional="true"><?= escape($almacen['descripcion']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-9 col-md-offset-3">
						<button type="submit" class="btn btn-primary">
							<span class="glyphicon glyphicon-floppy-disk"></span>
							<span>Guardar</span>
						</button>
						<button type="reset" class="btn btn-default">
							<span class="glyphicon glyphicon-refresh"></span>
							<span>Restablecer</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script>
$(function () {
	$.validate({
		modules: 'basic'
	});

	$('#telefono').selectize({
		persist: false,
		createOnBlur: true,
		create: true,
		onInitialize: function () {
			$('#telefono').css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function () {
			$('#telefono').trigger('blur');
		},
		onBlur: function () {
			$('#telefono').trigger('blur');
		}
	});

	$(':reset').on('click', function () {
		$('#telefono')[0].selectize.clear();
	});
	
	$('.form-control:first').select();
	
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
});
</script>
<?php require_once show_template('footer-advanced'); ?>