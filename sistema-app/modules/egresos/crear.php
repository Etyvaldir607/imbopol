<?php
foreach ($_SESSION as $key) {
	if($key['rol_id'] == '2' || $key['rol_id'] == '1')
		$rol_id =$key['rol_id'];
	elseif ($key['rol_id'] == '3' || $key['rol_id'] == '4') {
		$rol_id =$key['rol_id'];
	}
}
// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;
// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();
// Verifica si existe el almacen
if (!$almacen) {
	// Error 404
	require_once not_found();
	exit;
}
// Obtiene los productos
$productos = $db->query("select p.id_producto, p.codigo, p.nombre, p.descripcion, p.color, p.cantidad_minima, p.precio_actual,
ifnull(e.costo, 0) as costo,
ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos,
ifnull(s.cantidad_egresos, 0) as cantidad_egresos, u.unidad, u.sigla, c.categoria 
from inv_productos p 
left JOIN (
   select d.producto_id, d.costo, sum(d.cantidad) as cantidad_ingresos 
   from inv_ingresos_detalles d
   left join inv_ingresos i on i.id_ingreso = d.ingreso_id where i.almacen_id = $id_almacen  
   group by d.producto_id, d.costo) as e on e.producto_id = p.id_producto 
   left join (
	   select d.producto_id, sum(d.cantidad) as cantidad_egresos 
	   from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = $id_almacen 
	   group by d.producto_id) as s on s.producto_id = p.id_producto 
	   left join inv_unidades u on u.id_unidad = p.unidad_id 
	   left join inv_categorias c on c.id_categoria = p.categoria_id
		")->fetch();
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
// Obtiene los permisos
$permisos = explode(',', permits);
//otro almacen
$otro_alma = $db->select('*')->from('inv_almacenes')->where('id_almacen !=',$id_almacen)->fetch();
// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);
?>
<?php require_once show_template('header-empty'); ?>
<style>
	.table-xs tbody {
		font-size: 12px;
	}
	.input-xs {
		height: 22px;
		padding: 1px 5px;
		font-size: 12px;
		line-height: 1.5;
		border-radius: 3px;
	}
	.position-left-bottom {
		bottom: 0;
		left: 0;
		position: fixed;
		z-index: 1030;
	}
	.margin-all {
		margin: 15px;
	}
	.display-table {
		display: table;
	}
	.display-cell {
		display: table-cell;
		text-align: center;
		vertical-align: middle;
	}
	.btn-circle {
		border-radius: 50%;
		height: 75px;
		width: 75px;
	}
</style>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos del egreso</strong>
				</h3>
			</div>
			<div class="panel-body">
				<?php if ($rol_id >= 3) { ?>
					<form id="formulario" method="post" class="form-horizontal">
						<div class="form-group">
							<label class="col-sm-4 control-label">Almacén:</label>
							<div class="col-sm-8">
								<p class="form-control-static"><?= escape($almacen['almacen']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label for="tipo" class="col-sm-4 control-label">Tipo de egreso:</label>
							<div class="col-sm-8">
								<select name="tipo" id="tipo" class="form-control" data-validation="required" onchange="ca(this,1)">
									<option value="">Seleccionar</option>
									<option value="Traspaso">Egreso como traspaso</option>
									<option value="Baja">Egreso como baja</option>
								</select>
							</div>
						</div>
						<div class="form-group" id="alma">
							<label for="almac" class="col-sm-4 control-label">Almacén:</label>
							<div class="col-sm-8">
								<select name="almac" id="almac" class="form-control" data-validation="required">
									<?php foreach($otro_alma as $otro){ ?>
										<option value="<?= $otro['id_almacen'] ?>"><?= $otro['almacen'] ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="descripcion" class="col-sm-4 control-label">Descripción:</label>
							<div class="col-sm-8">
								<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true"></textarea>
							</div>
						</div>
						<div class="table-responsive margin-none">
							<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
								<thead>
									<tr class="active">
										<th class="text-nowrap">#</th>
										<th class="text-nowrap">Código</th>
										<th class="text-nowrap">Nombre</th>
										<th class="text-nowrap">Color</th>
										<th class="text-nowrap">Cantidad</th>
										<th style="display: none;" class="text-nowrap">Costo</th>
										<th style="display: none;"  class="text-nowrap">Importe</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</thead>
								<tfoot>
									<tr class="active" style="display: none;" >
										<th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right" data-subtotal="">0.00</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</tfoot>
								<tbody></tbody>
							</table>
						</div>
						<div class="form-group">
							<div class="col-xs-12">
								<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
								<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number"  data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 20">
								<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="">
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12 text-right">
								<input type="hidden" value="<?=$rol_id  ?>" name="rol_id">
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
				<?php  }elseif ($rol_id <= 2) { ?>
					<form id="formulario" method="post" class="form-horizontal">
						<div class="form-group">
							<label class="col-sm-4 control-label">Almacén:</label>
							<div class="col-sm-8">
								<p class="form-control-static"><?= escape($almacen['almacen']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label for="tipo" class="col-sm-4 control-label">Tipo de egreso:</label>
							<div class="col-sm-8">
								<select name="tipo" id="tipo" class="form-control" data-validation="required" onchange="ca(this,1)">
									<option value="">Seleccionar</option>
									<option value="Traspaso">Egreso como traspaso</option>
									<option value="Baja">Egreso como baja</option>
								</select>
							</div>
						</div>
						<div class="form-group" id="alma">
							<label for="almac" class="col-sm-4 control-label">Almacén:</label>
							<div class="col-sm-8">
								<select name="almac" id="almac" class="form-control" data-validation="required">
									<?php foreach($otro_alma as $otro){ ?>
										<option value="<?= $otro['id_almacen'] ?>"><?= $otro['almacen'] ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="descripcion" class="col-sm-4 control-label">Descripción:</label>
							<div class="col-sm-8">
								<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true"></textarea>
							</div>
						</div>
						<div class="table-responsive margin-none">
							<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
								<thead>
									<tr class="active">
										<th class="text-nowrap">#</th>
										<th class="text-nowrap">Código</th>
										<th class="text-nowrap">Nombre</th>
										<th class="text-nowrap">Color</th>
										<th class="text-nowrap">Cantidad</th>
										<th class="text-nowrap">Costo</th>
										<th class="text-nowrap">Importe</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</thead>
								<tfoot>
									<tr class="active">
										
										<th class="text-nowrap text-right" colspan="6">Importe total <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right" data-subtotal="">0.00</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</tfoot>
								<tbody></tbody>
							</table>
						</div>
						<div class="form-group">
							<div class="col-xs-12">
								<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
								<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;20]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 20">
								<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12 text-right">
								<input type="hidden" value="<?=$rol_id  ?>" name="rol_id">
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
				<?php  }  ?>
			</div>
		</div>
		<div class="panel panel-default" data-servidor="<?= ip_local . name_project . '/egreso.php'; ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-cog"></span>
					<strong>Datos generales</strong>
				</h3>
			</div>
			<div class="panel-body">
				<ul class="list-group">
					<li class="list-group-item">
						<i class="glyphicon glyphicon-home"></i>
						<strong>Casa Matriz: </strong>
						<span><?= escape($_institution['nombre']); ?></span>
					</li>
					<li class="list-group-item">
						<i class="glyphicon glyphicon-qrcode"></i>
						<strong>NIT: </strong>
						<span><?= escape($_institution['nit']); ?></span>
					</li>
					<li class="list-group-item">
						<i class="glyphicon glyphicon-user"></i>
						<strong>Empleado: </strong>
						<span><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></span>
					</li>
				</ul>
			</div>
			<div class="panel-footer text-center"><?= credits; ?></div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-search"></span>
					<strong>Búsqueda de productos</strong>
				</h3>
			</div>
			<div class="panel-body">
				<?php if ($permiso_listar) { ?>
					<div class="row">
						<div class="col-xs-12 text-right">
							<a href="?/egresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Lista de egresos</span></a>
						</div>
					</div>
					<hr>
				<?php } ?>
				<?php if ($productos) { ?>
					<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
						<thead>
							<tr class="active">
								<th class="text-nowrap">Código</th>
								<th class="text-nowrap">Nombre</th>
								<th class="text-nowrap">Medidas</th>
								<th class="text-nowrap">Color</th>
								<th class="text-nowrap">Tipo</th>
								<th class="text-nowrap">Stock</th>
								<?php if ($rol_id == '1' ) { ?>
									<th class="text-nowrap">Precio</th>
								<?php }elseif ($rol_id == '2') {?>
									<th class="text-nowrap">Precio</th>
								<?php	}elseif ($rol_id == '3') {?>
									<th style="display: none;" class="text-nowrap">Precio</th>
								<?php	}elseif ($rol_id == '4') {?>
									<th style="display: none;" class="text-nowrap">Precio</th>
								<?php	}?>
								<th class="text-nowrap"><i class="glyphicon glyphicon-cog"></i></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($productos as $nro => $producto) { ?>
								<tr>
									<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
									<td>
										<span data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre']); ?></span>
									</td>
									<td class=""><?= escape($producto['descripcion']); ?></td>
									<td data-color="<?= $producto['id_producto']; ?>" class=""><?= escape($producto['color']); ?></td>
									<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
									<td class="text-nowrap text-right" data-stock="<?= $producto['id_producto']; ?>"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
									<?php if ($rol_id == '1' ) { ?>
										<td  class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>"><?= escape($producto['costo']); ?></td>
									<?php }elseif ($rol_id == '2') {?>
										<td  class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>"><?= escape($producto['costo']); ?></td>
									<?php	}elseif ($rol_id == '3') {?>
										<td style="display: none;" class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>"><?= escape($producto['costo']); ?></td>
									<?php	}elseif ($rol_id == '4') {?>
										<td style="display: none;" class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>"><?= escape($producto['costo']); ?></td>
									<?php	}?>
									<td class="text-nowrap">
										<button type="button" class="btn btn-xs btn-primary" data-egresar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Egresar"><span class="glyphicon glyphicon-share-alt"></span></button>
										<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto'] . '|' . $almacen['id_almacen']; ?>" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
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
		</div>
	</div>
</div>
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
<h2 class="btn-info position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es un egreso" data-placement="right"><i class="glyphicon glyphicon-log-out display-cell"></i></h2>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>
<script>
	var rol_id= "rol_id=<?= $rol_id;?>";
	$(function () {
		var table;
		var $formulario = $('#formulario');
		var blup = new buzz.sound('<?= media; ?>/blup.mp3');
		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function () {
				guardar_nota();
			}
		});
		$formulario.on('submit', function (e) {
			e.preventDefault();
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
		$('[data-egresar]').on('click', function () {
			adicionar_producto($.trim($(this).attr('data-egresar')));
		});
		$('[data-actualizar]').on('click', function () {
			var actualizar = $.trim($(this).attr('data-actualizar'));
			actualizar = actualizar.split('|');
			var id_producto = actualizar[0];
			var id_almacen = actualizar[1];

			$('#loader').fadeIn(100);
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/egresos/actualizar',
				data: {
					id_producto: id_producto,
					id_almacen: id_almacen
				}
			}).done(function (producto) {
				if (producto) {
					var precio = parseFloat(producto.precio).toFixed(2);
					var stock = parseInt(producto.stock);
					var cell;
					cell = table.cell($('[data-valor=' + producto.id_producto + ']'));
					cell.data(precio);
					cell = table.cell($('[data-stock=' + producto.id_producto + ']'));
					cell.data(stock);
					table.draw();
					var $producto = $('[data-producto=' + producto.id_producto + ']');
					var $cantidad = $producto.find('[data-cantidad]');
					var $precio = $producto.find('[data-precio]');
					if ($producto.size()) {
						$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
						$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock);
						$precio.val(precio);
						$precio.attr('data-precio', precio);
						calcular_importe(producto.id_producto);
					}
					$.notify({
						title: '<strong>Actualización satisfactoria!</strong>',
						message: '<div>El stock y el precio del producto se actualizaron correctamente.</div>'
					}, {
						type: 'success'
					});
				} else {
					$.notify({
						title: '<strong>Advertencia!</strong>',
						message: '<div>Ocurrió un problema, no existe almacén principal.</div>'
					}, {
						type: 'danger'
					});
				}
			}).fail(function () {
				$.notify({
					title: '<strong>Advertencia!</strong>',
					message: '<div>Ocurrió un problema y no se pudo actualizar el stock ni el precio del producto.</div>'
				}, {
					type: 'danger'
				});
			}).always(function () {
				$('#loader').fadeOut(100);
			});
		});
		table = $('#productos').DataTable({
			info: false,
			lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
			order: []
		});
		$('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');
		$('#formulario').on('reset', function () {
			$('#ventas tbody').empty();
			calcular_total();
		});
	});
	function ca( obj , x )
	{
		var aa = obj[ obj.selectedIndex ].value;
		if(aa=="Traspaso"){
			$('#alma').show();
		}else{
			$('#alma').hide();
		}
	}
	function adicionar_producto(id_producto) {
		var $ventas = $('#ventas tbody');
		var $producto = $ventas.find('[data-producto=' + id_producto + ']');
		var $cantidad = $producto.find('[data-cantidad]');
		var numero = $ventas.find('[data-producto]').size() + 1;
		var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
		var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
		var stock = $.trim($('[data-stock=' + id_producto + ']').text());
		var valor = $.trim($('[data-valor=' + id_producto + ']').text());
		var color = $.trim($('[data-color=' + id_producto + ']').text());
		var plantilla = '';
		var cantidad;
		if ($producto.size()) {
			cantidad = $.trim($cantidad.val());
			cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
			cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
			$cantidad.val(cantidad).trigger('blur');
		} else {
			if ("<?= $rol_id >= 3;?>") {
				plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
				'<td class="text-nowrap">' + numero + '</td>' +
				'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
				'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' + '<td>' + color + '</td>' +
				'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
				'<td style="display: none;"><input type="text" value="' + valor + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + valor + '" data-validation="required number" readonly data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
				'<td style="display: none;" class="text-nowrap text-right" data-importe="">0.00</td>' +
				'<td class="text-nowrap text-center">' +
				'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
				'</td>' +
				'</tr>';
			}else{
				if ("<?= $rol_id <= 2;?> " ) {
					plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
					'<td class="text-nowrap">' + numero + '</td>' +
					'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
					'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' + '<td>' + color + '</td>' +
					'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
					'<td><input type="text" value="' + valor + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + valor + '" data-validation="required number"  data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
					'<td  class="text-nowrap text-right" data-importe="">0.00</td>' +
					'<td class="text-nowrap text-center">' +
					'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
					'</td>' +
					'</tr>';
				}
			}
			$ventas.append(plantilla);
			$ventas.find('[data-cantidad], [data-precio]').on('click', function () {
				$(this).select();
			});
			$ventas.find('[title]').tooltip({
				container: 'body',
				trigger: 'hover'
			});
			$.validate({
				form: '#formulario',
				modules: 'basic',
				onSuccess: function () {
					guardar_nota();
				}
			});
		}
		calcular_importe(id_producto);
	}
	function eliminar_producto(id_producto) {
		bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
			if(result){
				$('[data-producto=' + id_producto + ']').remove();
				renumerar_productos();
				calcular_total();
			}
		});
	}
	function renumerar_productos() {
		var $ventas = $('#ventas tbody');
		var $productos = $ventas.find('[data-producto]');
		$productos.each(function (i) {
			$(this).find('td:first').text(i + 1);
		});
	}
	function calcular_importe(id_producto) {
		var $ventas = $('#ventas tbody');
		var $producto = $ventas.find('[data-producto=' + id_producto + ']');
		var $cantidad = $producto.find('[data-cantidad]');
		var $precio = $producto.find('[data-precio]');
		var $importe = $producto.find('[data-importe]');
		var cantidad, precio, importe;
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		precio = $.trim($precio.val());
		precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
		importe = cantidad * precio;
		importe = importe.toFixed(2);
		$importe.text(importe);
		calcular_total();
	}
	function calcular_total() {
		var $ventas = $('#ventas tbody');
		var $total = $('[data-subtotal]:first');
		var $importes = $ventas.find('[data-importe]');
		var importe, total = 0;
		$importes.each(function (i) {
			importe = $.trim($(this).text());
			importe = parseFloat(importe);
			total = total + importe;
		});
		$total.text(total.toFixed(2));
		$('[data-ventas]:first').val($importes.size()).trigger('blur');
		$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
	}
	function guardar_nota() {
		var data = $('#formulario').serialize();
		//console.log(data);
		$('#loader').fadeIn(100);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '?/egresos/guardar',
			data: data,
		}).done(function (venta) {
			//console.log(venta);
			if (venta) {
				$.notify({
					message: 'La nota de entrega fue realizada satisfactoriamente.'
				}, {
					type: 'success'
				});
				imprimir_nota(venta);
			} else {
				$('#loader').fadeOut(100);
				$.notify({
					message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de entrega, verifique si la se guardó parcialmente.'
				}, {
					type: 'danger'
				});
			}
		}).fail(function () {
			$('#loader').fadeOut(100);
			$.notify({
				message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de entrega, verifique si la se guardó parcialmente2.'
			}, {
				type: 'danger'
			});
		});
	}
	function imprimir_nota(nota) {
		var servidor = $.trim($('[data-servidor]').attr('data-servidor'));
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: servidor,
			data: nota
		}).done(function (respuesta) {
			$('#loader').fadeOut(100);
			switch (respuesta.estado) {
				case 's':
				window.location.reload();
				break;
				case 'p':
				$.notify({
					message: 'La impresora no responde, asegurese de que este conectada y registrada en el sistema, una vez solucionado el problema vuelva a intentarlo nuevamente.'
				}, {
					type: 'danger'
				});
				break;
				default:
				$.notify({
					message: 'Ocurrió un problema durante el proceso, no se envió los datos para la impresión de la factura.'
				}, {
					type: 'danger'
				});
				break;
			}
		}).fail(function () {
			$('#loader').fadeOut(100);
			$.notify({
				message: 'Ocurrió un problema durante el proceso, reinicie la terminal para dar solución al problema y si el problema persiste contactese con el con los desarrolladores.'
			}, {
				type: 'danger'
			});
		}).always(function () {
			$('#formulario').trigger('reset');
			$('#form_buscar_0').trigger('submit');
		});
	}
</script>
<?php require_once show_template('footer-empty'); ?>