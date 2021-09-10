<?php

// Obtiene el id_producto
$id_producto = (isset($params[0])) ? $params[0] : 0;

// Obtiene el producto
$producto = $db->select('p.*, u.unidad, c.categoria')->from('inv_productos p')->join('inv_unidades u', 'p.unidad_id = u.id_unidad', 'left')->join('inv_categorias c', 'p.categoria_id = c.id_categoria', 'left')->where('p.id_producto', $id_producto)->fetch_first();

// Verifica si existe el producto
if (!$producto) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? $moneda['sigla'] : '';

// Obtiene los productos y sus precios
$precios = $db->select("p.*, ifnull(e.nombres, '') as nombres, ifnull(e.paterno, '') as paterno, ifnull(e.materno, '') as materno")->from('inv_precios p')->join('sys_empleados e', 'p.empleado_id = e.id_empleado', 'left')->where('producto_id', $id_producto)->order_by('fecha_registro asc, hora_registro asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_actualizar = in_array('actualizar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Historial de precios</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Información del producto</h3>
				</div>
				<div class="panel-body">
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-sm-3 control-label">Código del producto:</label>
							<div class="col-sm-9">
								<p class="form-control-static"><?= escape($producto['codigo']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nombre del producto:</label>
							<div class="col-sm-9">
								<p class="form-control-static"><?= escape($producto['nombre']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Precio actual:</label>
							<div class="col-sm-9">
								<p class="form-control-static"><strong class="text-success"><?= escape($producto['precio_actual'] . ' ' . $moneda); ?></strong></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-stats"></i> Historial de precios</h3>
				</div>
				<div class="panel-body">
					<?php if ($permiso_actualizar || $permiso_imprimir || $permiso_listar) { ?>
					<div class="row">
						<div class="col-sm-7 col-md-6 hidden-xs">
							<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
						</div>
						<div class="col-xs-12 col-sm-5 col-md-6 text-right">
							<?php if ($permiso_actualizar) { ?>
							<a href="#" class="btn btn-default" data-actualizar="<?= $producto['id_producto']; ?>" data-codigo="<?= $producto['codigo']; ?>" data-precio="<?= $producto['precio_actual']; ?>"><i class="glyphicon glyphicon-usd"></i><span class="hidden-xs hidden-sm"> Cambiar</span></a>
							<?php } ?>
							<?php if ($permiso_imprimir) { ?>
							<a href="?/precios/imprimir/<?= $producto['id_producto']; ?>" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs hidden-sm"> Imprimir</span></a>
							<?php } ?>
							<?php if ($permiso_listar) { ?>
							<a href="?/precios/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs hidden-sm"> Listado</span></a>
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
					<?php if ($precios) { ?>
					<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
						<thead>
							<tr class="active">
								<th class="text-nowrap">#</th>
								<th class="text-nowrap">Fecha</th>
								<th class="text-nowrap">Hora</th>
								<th class="text-nowrap">Precio <?= ($moneda != '') ? ' (' . $moneda . ')' : ''; ?></th>
								<th class="text-nowrap">Empleado</th>
								<?php if ($permiso_eliminar) { ?>
								<th class="text-nowrap">Opciones</th>
								<?php } ?>
							</tr>
						</thead>
						<tfoot>
							<tr class="active">
								<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
								<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
								<th class="text-nowrap text-middle" data-datafilter-filter="true">Hora</th>
								<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio <?= ($moneda != '') ? ' (' . $moneda . ')' : ''; ?></th>
								<th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>
								<?php if ($permiso_eliminar) { ?>
								<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
								<?php } ?>
							</tr>
						</tfoot>
						<tbody>
							<?php foreach ($precios as $nro => $precio) { ?>
							<tr>
								<th class="text-nowrap"><?= $nro + 1; ?></th>
								<td class="text-nowrap"><?= escape(date_decode($precio['fecha_registro'], $_institution['formato'])); ?></td>
								<td class="text-nowrap"><?= escape($precio['hora_registro']); ?></td>
								<td class="text-nowrap"><?= escape($precio['precio']); ?></td>
								<td class="text-nowrap"><?= escape($precio['paterno'] . ' ' . $precio['materno'] . ' ' . $precio['nombres']); ?></td>
								<?php if ($permiso_eliminar) { ?>
								<td class="text-nowrap">
									<a href="?/precios/eliminar/<?= $producto['id_producto']; ?>/<?= $precio['id_precio']; ?>" data-toggle="tooltip" data-title="Eliminar precio del historial" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
								</td>
								<?php } ?>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Este producto no tiene precio, es muy importante que asigne un precio para que el proceso de compra y venta sea correcto.</p>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Inicio modal precio -->
<?php if ($permiso_actualizar) { ?>
<div id="modal_precio" class="modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/precios/actualizar" id="form_precio" class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Actualizar precio</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Código:</label>
							<p id="codigo_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Precio actual<?= ($moneda != '') ? ' (' . $moneda . ')' : ''; ?>:</label>
							<p id="actual_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nuevo_precio">Precio nuevo<?= ($moneda != '') ? ' (' . $moneda . ')' : ''; ?>:</label>
							<input type="text" name="id_producto" value="" id="producto_precio" class="translate" tabindex="-1" data-validation="required number">
							<input type="text" name="precio" value="" id="nuevo_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000],float">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Guardar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<!-- Fin modal precio -->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el precio del historial?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_actualizar) { ?>
	$.validate();

	var $modal_precio = $('#modal_precio');
	var $form_precio = $('#form_precio');

	$modal_precio.on('hidden.bs.modal', function () {
		$form_precio.trigger('reset');
	});

	$modal_precio.on('shown.bs.modal', function () {
		$modal_precio.find('.form-control:first').focus();
	});

	$modal_precio.find('[data-cancelar]').on('click', function () {
		$modal_precio.modal('hide');
	});

	$('[data-actualizar]').on('click', function (e) {
		e.preventDefault();
		var $this = $(this);
		var id_producto = $.trim($this.attr('data-actualizar'));
		var codigo = $.trim($this.attr('data-codigo'));
		var precio = $.trim($this.attr('data-precio'));

		$('#producto_precio').val(id_producto);
		$('#codigo_precio').text(codigo);
		$('#actual_precio').text(precio);
		
		$modal_precio.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>

	<?php if ($precios) { ?>
	$('#table tbody tr:last').addClass('warning');
	
	var table = $('#table').DataFilter({
		filter: false,
		name: 'historial_precios',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>