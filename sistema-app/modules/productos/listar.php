<?php

// Obtiene los productos
$productos = $db->select('p.*, u.unidad, c.categoria')->from('inv_productos p')->join('inv_unidades u', 'p.unidad_id = u.id_unidad', 'left')->join('inv_categorias c', 'p.categoria_id = c.id_categoria', 'left')->order_by('p.id_producto')->fetch();



// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();

$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = in_array('cambiar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Productos</strong>
	</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $productos)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos productos hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/productos/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/productos/crear" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
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
	<?php if ($productos) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap text-middle width-collapse">#</th>
				<th class="text-nowrap text-middle width-collapse">Imagen</th>
				<th class="text-nowrap text-middle width-collapse">Código</th>
				<th class="text-nowrap text-middle width-collapse">Código de barras</th>
				<th class="text-nowrap text-middle">Nombre del producto</th>
				<th class="text-nowrap text-middle">Nombre en la factura</th>
                <th class="text-nowrap text-middle">Medidas</th>
                <th class="text-nowrap text-middle width-collapse">Tipo</th>
                <th class="text-nowrap text-middle width-collapse">Color</th>
				<th class="text-nowrap text-middle width-collapse">Cantidad mínima</th>
				<th class="text-nowrap text-middle width-collapse">Unidad</th>
				<th class="text-nowrap text-middle width-collapse">Precio actual <?= $moneda; ?></th>
				<th class="text-nowrap text-middle">Ubicación</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar) { ?>
				<th class="text-nowrap text-middle width-collapse">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Imagen</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código de barras</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre del producto</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre en la factura</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Medidas</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Color</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad mínima</th>	
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio actual <?= $moneda; ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Ubicación</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($productos as $nro => $producto) { ?>
			<tr>
				<th class="text-nowrap text-middle text-right"><?= $nro + 1; ?></th>
				<td class="text-nowrap text-middle text-center">
					<img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>"  class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="75" height="75">
				</td>
				<td class="text-nowrap text-middle" data-codigo="<?= $producto['id_producto']; ?>">
					<samp class="lead"><?= escape($producto['codigo']); ?></samp>
				</td>
				<td class="text-nowrap text-middle">
					<samp class="lead"><?= substr($producto['codigo_barras'], 2); ?></samp>
				</td>
				<td class="text-middle"><?= escape($producto['nombre']); ?></td>
				<td class="text-middle"><?= escape($producto['nombre_factura']); ?></td>
                <td class="text-middle"><?= str_replace("\n", "<br>", escape($producto['descripcion'])); ?></td>
                <td class="text-nowrap text-middle"><?= escape($producto['categoria']); ?></td>

                <td class="text-nowrap text-middle"><?= escape($producto['color']); ?></td>
				<td class="text-nowrap text-middle text-right"><?= escape($producto['cantidad_minima']); ?></td>

				<!--obtiene las asignaciones de unidad por producto, con sus respectivos precios -->

				<?php $asignaciones= $db->select('*')->from('inv_asignaciones')->where('producto_id',$producto['id_producto'] ); ?>
				<?php $unidades= $db->select('*')->from('inv_unidades')->where('producto_id',$producto['id_producto'] ); ?>

				<?php 
					$precios = $db->query("select
					p.id_producto,
					p.unidad_id,
					tu.cantidad_unidad,
					tp.precio
					from
						inv_productos p
						left join (
							select
								a.producto_id,
								a.cantidad_unidad
							from
								inv_asignaciones a
								left join inv_unidades u on u.id_unidad = a.unidad_id
						) as tu 
						on tu.producto_id = p.id_producto
				
						left join (
							select
								pr.producto_id,
								pr.id_precio,
								pr.precio
							from
								inv_asignaciones a
								left join inv_precios pr on pr.asignacion_id = a.id_asignacion
						) as tp 
						on tp.producto_id = p.id_producto
					order by p.id_producto")->fetch();
				?>

				<td class="text-nowrap text-middle">
					<?= escape($producto['unidad']); ?>
				</td>
				<td class="text-nowrap text-middle text-right lead" data-precio="<?= $producto['id_producto']; ?>">
					<?= escape($producto['precio_actual']); ?>
				</td>


				<td class="text-middle"><?= str_replace("\n", "<br>", escape($producto['ubicacion'])); ?></td>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar) { ?>
				<td class="text-nowrap text-middle">
					<?php if ($permiso_ver) { ?>
					<a href="?/productos/ver/<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Ver producto"><i class="glyphicon glyphicon-search"></i></a>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/productos/editar/<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Editar producto"><i class="glyphicon glyphicon-edit"></i></a>
					<?php } ?>
					<?php if ($permiso_eliminar) { ?>
					<a href="?/productos/eliminar/<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Eliminar producto" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
					<?php } ?>
					<?php if ($permiso_cambiar) { ?>
					<a href="#" data-toggle="tooltip" data-title="Actualizar precio" data-actualizar="<?= $producto['id_producto']; ?>"><span class="glyphicon glyphicon-refresh"></span></a>
					<?php } ?>
					<a href="#" data-toggle="tooltip" data-title="Asignar otra unidad" data-asignar="<?= $producto['id_producto']; ?>"><span class="glyphicon glyphicon-tint"></span></a>
				</td>
				<?php } ?>

			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen productos registrados en la base de datos, para crear nuevos productos hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
	</div>
	<?php } ?>
</div>

<!-- Inicio modal precio-->
<?php if ($permiso_cambiar) { ?>
<div id="modal_precio" class="modal fade">
	<div class="modal-dialog">
		<form id="form_precio" class="modal-content loader-wrapper">
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
							<label class="control-label">Precio actual <?= $moneda; ?>:</label>
							<p id="actual_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nuevo_precio">Precio nuevo <?= $moneda; ?>:</label>
							<input type="text" value="" id="producto_precio" class="translate" tabindex="-1" data-validation="required number">
							<input type="text" value="" id="nuevo_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000000],float">
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
			<div id="loader_precio" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<!-- Fin modal precio-->

<!-- Modal mostrar inicio -->
<div id="modal_mostrar" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content loader-wrapper">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<img src="" class="img-responsive img-rounded" data-modal-image="">
			</div>
			<div id="loader_mostrar" class="loader-wrapper-backdrop">
				<span class="loader"></span>
			</div>
		</div>
	</div>
</div>
<!-- Modal mostrar fin -->


<!-- Inicio modal precio-->
<?php //if ($permiso_cambiar) { ?>
<div id="modal_unidad" class="modal fade">
	<div class="modal-dialog">
		<form id="form_unidad" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Actualizar unidad</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Código:</label>
							<span id="codigo_unidad" class="form-control"></span>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Unidades asignadas:</label>
						</div>
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="actual_unidad" checked disabled>
							<label class="form-check-label" for="actual_unidad"></label>
						</div>
					</div>

					<div class="col-sm-6">
						<div class="form-group">
							<label for="nuevo_unidad">Nueva unidad:</label>
							<input type="text" value="" id="nuevo_unidad" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000000],float">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="nuevo_unidad">Sigla:</label>
							<input type="text" value="" id="nuevo_unidad" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000000],float">
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nuevo_unidad">Descripción:</label>
							<input type="text" value="" id="nuevo_unidad" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000000],float">
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
			<div id="loader_unidad" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<?php //} ?>

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
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
					window.location = '?/productos/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_cambiar) { ?>
	var $modal_precio = $('#modal_precio');
	var $form_precio = $('#form_precio');
	var $loader_precio = $('#loader_precio');

	$form_precio.on('submit', function (e) {
		e.preventDefault();
	});

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
		var id_producto = $(this).attr('data-actualizar');
		var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
		var precio = $.trim($('[data-precio=' + id_producto + ']').text());

		$('#producto_precio').val(id_producto);
		$('#codigo_precio').text(codigo);
		$('#actual_precio').text(precio);
		
		$modal_precio.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	

	var $modal_unidad = $('#modal_unidad');
	var $form_unidad = $('#form_unidad');
	var $loader_unidad = $('#loader_unidad');

	$form_unidad.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_unidad.on('hidden.bs.modal', function () {
		$form_unidad.trigger('reset');
	});

	$modal_unidad.on('shown.bs.modal', function () {
		$modal_unidad.find('.form-control:first').focus();
	});

	$modal_unidad.find('[data-cancelar]').on('click', function () {
		$modal_unidad.modal('hide');
	});

	$('[data-asignar]').on('click', function (e) {
		e.preventDefault();
		var id_producto = $(this).attr('data-asignar');
		var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
		var unidad = $.trim($('[data-unidad=' + id_producto + ']').text());

		$('#producto_unidad').val(id_producto);
		$('#codigo_unidad').text(codigo);
		$('#actual_unidad').text(unidad);
		
		$modal_unidad.modal({
			backdrop: 'static'
		});
	});





	<?php if ($productos) : ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'productos',
		reports: 'excel|word|pdf|html',
		size: 8
	});

	$.validate({
		form: '#form_precio',
		modules: 'basic',
		onSuccess: function () {
			var producto = $('#producto_precio').val();
			var precio = $('#nuevo_precio').val();

			$loader_precio.fadeIn(100);

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/productos/cambiar',
				data: {
					id_producto: producto,
					precio: parseFloat(precio).toFixed(2)
				}
			}).done(function (producto) {
				var cell = table.cell($('[data-precio=' + producto.producto_id + ']'));
				cell.data(producto.precio).draw();

				$.notify({
					message: 'El precio del producto se actualizó correctamente.'
				}, {
					type: 'success'
				});
			}).fail(function () {
				$.notify({
					message: 'Ocurrió un problema y el precio del producto no se actualizó correctamente.'
				}, {
					type: 'danger'
				});
			}).always(function () {
				$loader_precio.fadeOut(100, function () {
					$modal_precio.modal('hide');
				});
			});
		}
	});

	var $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar'), size, title, image;

	$modal_mostrar.on('hidden.bs.modal', function () {
		$loader_mostrar.show();
		$modal_mostrar.find('.modal-dialog').attr('class', 'modal-dialog');
		$modal_mostrar.find('.modal-title').text('');
	}).on('show.bs.modal', function (e) {
		size = $(e.relatedTarget).attr('data-modal-size');
		title = $(e.relatedTarget).attr('data-modal-title');
		image = $(e.relatedTarget).attr('src');
		size = (size) ? 'modal-dialog ' + size : 'modal-dialog';
		title = (title) ? title : 'Imagen';
		$modal_mostrar.find('.modal-dialog').attr('class', size);
		$modal_mostrar.find('.modal-title').text(title);
		$modal_mostrar.find('[data-modal-image]').attr('src', image);
	}).on('shown.bs.modal', function () {
		$loader_mostrar.hide();
	});
	<?php endif ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>