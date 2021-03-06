<?php
// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// obtiene permisos des sesion por rol
foreach ($_SESSION as $key) {
	if($key['rol_id'] == '2' || $key['rol_id'] == '1')
		$rol_id =$key['rol_id'];
	elseif ($key['rol_id'] == '3' || $key['rol_id'] == '4') {
		$rol_id =$key['rol_id'];
	}
}

// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;
// Verifica si existe el almacen
if ($id_almacen != 0) {
	// Obtiene los productos
	$productos = $db->query("
	select
		p.id_producto,
		p.codigo,
		p.nombre_factura,
		p.descripcion,
		p.color,
		p.nombre_factura,
		p.cantidad_minima,
		p.precio_actual,
		tu.cantidad_unidad,
		tp.id_precio,
		tp.precio,
		ifnull(ti.cantidad_ingresos, 0) AS cantidad_ingresos,
		ifnull(te.cantidad_egresos, 0) AS cantidad_egresos,
		ifnull(ifnull(ti.cantidad_ingresos, 0) - ifnull(te.cantidad_egresos, 0), 0) as stock,
		c.categoria,
		GROUP_CONCAT(ifnull(tu.id_asignacion, 0)) AS id_asignacion,
		GROUP_CONCAT(ifnull(tu.asignacion, 0)) AS asignacion,
		GROUP_CONCAT(ifnull(tu.id_unidad, 0)) AS id_unidad,
		GROUP_CONCAT(ifnull(tu.unidad, 0)) AS unidad,
		GROUP_CONCAT(ifnull(tu.cantidad_unidad, 0)) AS cantidad_unidad,
		GROUP_CONCAT(ifnull(tp.id_precio, 0)) AS id_precio,
		GROUP_CONCAT(ifnull(tp.precio, 0)) AS precio
	from
		inv_productos p
		left join (
			select
				a.id_asignacion,
				a.producto_id,
				a.cantidad_unidad,
				a.asignacion,
				u.id_unidad,
				u.unidad,
				u.sigla,
				u.descripcion
			from
				inv_asignaciones a
				left join inv_unidades u on u.id_unidad = a.unidad_id
			where
				a.estado = 'a'
		) as tu on tu.producto_id = p.id_producto
		left join (
			select
				asignacion_id,
				producto_id,
				id_precio,
				precio
			from
				inv_precios
			group by
				asignacion_id
		) as tp on tp.producto_id = p.id_producto
		AND tu.id_asignacion = tp.asignacion_id
		left join (
			select
				d.producto_id,
				sum(d.cantidad * a.cantidad_unidad) as cantidad_ingresos
			from
				inv_ingresos_detalles d
				left join inv_ingresos i on i.id_ingreso = d.ingreso_id
				left join inv_asignaciones a on a.id_asignacion = d.asignacion_id
			where
				i.almacen_id = $id_almacen
			group by
				d.producto_id
		) as ti on ti.producto_id = p.id_producto
		left join (
			select
				d.producto_id,
				sum(d.cantidad * a.cantidad_unidad) as cantidad_egresos
			from
				inv_egresos_detalles d
				left join inv_egresos e on e.id_egreso = d.egreso_id
				left join inv_asignaciones a on a.id_asignacion = d.asignacion_id
			where
				e.almacen_id = $id_almacen
			group by
				d.producto_id
		) as te on te.producto_id = p.id_producto
		left join inv_categorias c on c.id_categoria = p.categoria_id
	GROUP BY
		p.id_producto
	")->fetch();
} else {
	$productos = null;
}
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
// Obtiene el modelo almacenes
$almacenes = $db->from('inv_almacenes')->order_by('almacen')->fetch();
// Obtiene los proveedores
$proveedores = $db->select('id_proveedor, proveedor')->from('inv_proveedores')->group_by('id_proveedor, proveedor')->order_by('id_proveedor, proveedor asc')->fetch();
// Obtiene los permisos
$permisos = explode(',', permits);
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
</style>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default" data-servidor="<?= ip_local . name_project . '/ingreso.php'; ?>">
			<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos del ingreso</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Advertencia!</strong>
					<ul>
						<li>Para un mejor control del ingreso de productos se recomienda escribir una peque??a descripci??n acerca de la compra.</li>
						<li>La moneda con la que se esta trabajando es <?= escape($moneda); ?>.</li>
						<li>Los stocks que se muestra en la b??squeda de productos son del almac??n principal.</li>
					</ul>
				</div>
				<?php if ($rol_id >= 3) { ?>
					<form id="formulario" method="post" class="form-horizontal">
						<div class="form-group">
							<label for="almacen" class="col-md-4 control-label">Almac??n:</label>
							<div class="col-md-8">
								<select name="almacen_id" id="almacen" class="form-control" data-validation="required number">
									<option value="">Seleccionar</option>
									<?php foreach ($almacenes as $elemento) { ?>
										<option value="<?= $elemento['id_almacen']; ?>"><?= escape($elemento['almacen'] . (($elemento['principal'] == 'S') ? ' (principal)' : '')); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="proveedor" class="col-sm-4 control-label">Proveedor:</label>
							<div class="col-sm-8">
								<select name="proveedor" id="proveedor" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100">
									<option value="">Buscar</option>
									<?php foreach ($proveedores as $elemento) { ?>
										<option value="<?= escape($elemento['proveedor']); ?>"><?= escape($elemento['proveedor']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="descripcion" class="col-sm-4 control-label">Descripci??n:</label>
							<div class="col-sm-8">
								<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#??()\n " data-validation-optional="true"></textarea>
							</div>
						</div>
						<div class="table-responsive margin-none" >
							<table id="compras" class="table table-bordered table-condensed table-striped table-hover table-xl margin-none table-responsive-md">
								<thead>
									<tr class="active">
										<th class="text-nowrap">C??digo</th>
										<th class="text-nowrap">Nombre</th>
										<th class="text-nowrap">Color</th>
										<th class="text-nowrap">Cantidad</th>
										<th style="display: none;" class="text-nowrap">Costo</th>
										<th style="display: none;"  class="text-nowrap">Importe</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</thead>
								<tfoot>
									<tr style="display: none;"  >
										<th class="text-nowrap text-right" colspan="4">Importe total <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right" data-subtotal="">0.00</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</tfoot>
								<tbody></tbody>
							</table>
						</div>
						<div class="form-group">
							<div class="col-xs-12">
								<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-compras="" data-validation="required number"  data-validation-error-msg="Debe existir como m??nimo 1 producto y como m??ximo 50 productos">
								<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total=""   data-validation-error-msg="El costo total de la compra debe ser mayor a cero y menor a 1000000.00">
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12 text-right">
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
							<label for="almacen" class="col-md-4 control-label">Almac??n:</label>
							<div class="col-md-8">
								<select name="almacen_id" id="almacen" class="form-control" data-validation="required number">
									<option value="">Seleccionar</option>
									<?php foreach ($almacenes as $elemento) { ?>
										<option value="<?= $elemento['id_almacen']; ?>"><?= escape($elemento['almacen'] . (($elemento['principal'] == 'S') ? ' (principal)' : '')); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="nombre_proveedor" class="col-sm-4 control-label">Proveedor:</label>
							<div class="col-sm-8">
								<select name="nombre_proveedor" id="proveedor" class="form-control" data-validation="required letternumber length" data-validation-allowing="-.#() " data-validation-length="max100">
									<option value="">Buscar</option>
									<?php foreach ($proveedores as $elemento) { ?>
										<option value="<?= escape($elemento['proveedor']); ?>"><?= escape($elemento['proveedor']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="descripcion" class="col-sm-4 control-label">Descripci??n:</label>
							<div class="col-sm-8">
								<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#??()\n " data-validation-optional="true"></textarea>
							</div>
						</div>
						<div class="table-responsive margin-none">
							<table id="compras" class="table table-bordered table-condensed table-striped table-hover table-xl margin-none table-responsive-md">
								<thead>
									<tr class="active">
										<th class="text-nowrap">N??</th>
										<th class="text-nowrap">C??digo</th>
										<th class="text-nowrap">Nombre</th>
										<th class="text-nowrap">Color</th>
										<th class="text-nowrap">Fecha de vencimiento</th>
										<th class="text-nowrap text-center">Unidad</th>
										<th class="text-nowrap">Costo de compra</th>
										<th class="text-nowrap text-center width-collapse">Cantidad</th>
										<th class="text-nowrap">Importe</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</thead>
								<tfoot>
									<tr class="active">
										<th class="text-nowrap text-right" colspan="8">Importe total <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right" data-subtotal="">0.00</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</tfoot>
								<tbody></tbody>
							</table>
						</div>
						<div class="form-group">
							<div class="col-xs-12">
								<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-compras="" data-validation="required number" data-validation-allowing="range[1;50]" data-validation-error-msg="Debe existir como m??nimo 1 producto y como m??ximo 50 productos">
								<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El costo total de la compra debe ser mayor a cero y menor a 1000000.00">
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12 text-right">
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
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-search"></span>
					<strong>B??squeda de productos</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/ingresos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado de ingresos</span></a>
					</div>
				</div>
				<hr>
				<?php if ($productos) { ?>
					<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs ">
						<thead>
							<tr class="active">
								<th class="text-nowrap">C??digo</th>
								<th class="text-nowrap">Nombre</th>
								<th class="text-nowrap">Medidas</th>
								<th class="">Color</th>
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
									<td data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre_factura']); ?></td>
									<td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
									<td data-color="<?= $producto['id_producto']; ?>" class=""><?= escape($producto['color']); ?></td>
									<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
									<td class="text-nowrap text-right"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
									<?php if ($rol_id == '1' ) { ?>

										<?php 
											// obteniendo unidades
											$unidades  = explode(',', $producto['unidad']);
											// obteniendo asignaciones
											$asignaciones  = explode(',', $producto['id_asignacion']);
											// obteniendo precios
											$precios  = explode(',', $producto['precio']);
										?>

										<td class="text-nowrap text-middle text-right text-sm" data-contador="0" data-limit="<?= count($asignaciones); ?>" data-valor="<?= $producto['id_producto']; ?>" data-val-unidades="<?= $producto['unidad'];?>"  data-val-cantidades="<?= $producto['cantidad_unidad'];?>" data-val-precios="<?= $producto['precio'];?>">
											<!-- obteniendo unidades asignadas -->
											<?php for ($x = 0; $x <= count($asignaciones) - 1; $x++) {?>
												<!-- obteniendo fechas de productos por fecha de vencimiento -->	
												<?php if($asignaciones[$x] != 'principal'){ ?>
													<div class="asignacion-style">
														<div class="col-sm-9">
															<span class="block text-right text-success" >
																-<?= escape($unidades[$x].': '); ?><b><?= escape($precios[$x]); ?>
															</span>
														</div>
													</div>
												<?php } else { ?>
													<div class="asignacion-style">
														<div class="col-sm-9">
															<span class="block text-right text-success" >
																-<?= escape($unidades[$x].': '); ?><b><?= escape($precios[$x]); ?>
															</span>
														</div>
													</div>
												<?php } ?>
											<?php } ?>
										</td>
									<?php }elseif ($rol_id == '2') {?>
										<td class="text-nowrap text-right"><?= escape($producto['precio_actual']); ?></td>
									<?php	}elseif ($rol_id == '3') {?>
										<td style="display: none;" class="text-nowrap text-right"><?= escape($producto['precio_actual']); ?></td>
									<?php	}elseif ($rol_id == '4') {?>
										<td style="display: none;" class="text-nowrap text-right"><?= escape($producto['precio_actual']); ?></td>
									<?php	 }?>

									<td class="text-nowrap">
										<button type="button" class="btn btn-xs btn-primary" data-comprar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Comprar"><span class="glyphicon glyphicon-share-alt"></span></button>
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
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
	var rol_id= "<?= $rol_id;?>";

// funcion general para la busqueda y modales
$(function () {
	// definicion de variables globales
	var $formulario = $('#formulario');
	var blup = new buzz.sound('<?= media; ?>/blup.mp3');

	// inicia el datatable para el filtrado
	$('#productos').dataTable({
		info: false,
		lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
		order: []
	});
	$('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');

	// inicia el selector de proveedor
	$('#proveedor').selectize({
		persist: false,
		createOnBlur: true,
		create: true,
		onInitialize: function () {
			$('#proveedor').css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function () {
			$('#proveedor').trigger('blur');
		},
		onBlur: function () {
			$('#proveedor').trigger('blur');
		}
	});

	// inicia el selector de proveedor
	$('#almacen').selectize({
		persist: false,
		onInitialize: function () {
			$('#almacen').css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function () {
			$('#almacen').trigger('blur');
		},
		onBlur: function () {
			$('#almacen').trigger('blur');
		}
	});

	// valida los datos del formulario
	$.validate({
		form: '#formulario',
		modules: 'basic',
		onSuccess: function () {
			guardar_compra();
		}
	});

	// deshabilita el evento que lleva por defecto
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

	// envia toda la tabla y el formulaario de compra 
	$('[data-comprar]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-comprar')));
	});

	// vacia toda la tabla y el formulaario de la compra
	$('#formulario').on('reset', function () {
		$('#compras tbody').find('[data-importe]').text('0.00');
		$('#compras tbody').empty();
		calcular_total();
	});
	// dispara el evento click
	$('#formulario :reset').trigger('click');

	// escucha el evento reset y limpia los select option
	$(':reset').on('click', function () {
		$('#proveedor')[0].selectize.clear();
		$('#almacen')[0].selectize.clear();
	});
});


/** funcion adicionar producto */
function adicionar_producto(id_producto) {
	// definiendo base de la tabla
	var $compras = $('#compras tbody');
	// busca el dom compra - producto
	var $producto = $compras.find('[data-producto=' + id_producto + ']');
	// busca el dom compra - producto - cantidad
	var $cantidad = $producto.find('[data-cantidad]');

	// define un contador anonimo
	var numero = $compras.find('[data-producto]').size() + 1;
	// recupera el codigo de producto
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	// recupera el nombre de producto
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	// recupera el color de producto
	var color = $.trim($('[data-color=' + id_producto + ']').text());
	// recupera un array de unidades
	var unidades =$('[data-valor=' + id_producto + ']')[0].dataset.valUnidades.split(',');
	// recupera un array de cantidades
	var cantidades =$('[data-valor=' + id_producto + ']')[0].dataset.valCantidades.split(',');
	// recupera un array de precios
	var precios =$('[data-valor=' + id_producto + ']')[0].dataset.valPrecios.split(',');


	// recupera un contador para cada producto
	var contador = parseInt($('[data-valor=' + id_producto + ']')[0].dataset.contador);
	var limit = parseInt($('[data-valor=' + id_producto + ']')[0].dataset.limit);

    var valor =$.trim($('[data-valor=' + id_producto + ']').text());
	var posicion = valor.indexOf(':');
    var porciones = valor.split('-');
	//console.log(limit)
	var plantilla = '';
	var cantidad;
	//console.log(nombre,color);
	if (contador < limit ) {
		/** seccion activa para bucle */
		plantilla = '<tr class="active" data-producto="' + id_producto + '" data-position="'+numero+'">'+
		'<td class="text-nowrap">' + numero + '</td>' +
		'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser n??mero">' + codigo + '</td>' +
		'<td><input type="hidden" value="' + nombre + '" name="nprod[]">' + nombre + '</td>' + '<td>' + color + '</td>' +
		'<td><input type="text" name="fechas[]"  value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : now(); ?>" id="fecha-' + id_producto + '"  class="form-control input-xs text-right" autocomplete="off" data-fecha="<?= now(); ?>" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true" onclick="adicionar_fecha('+numero +',' + id_producto + ')"> </td>';
		
		if(unidades.length > 1 ){
			plantilla = plantilla +
			'<td>'+
				'<select name="unidad[]" id="unidad' + numero + '"  data-xxx="true" class="form-control input-xs" >';
					for(var c = 0; c < unidades.length; c++){
						if(c === 0 ){
							plantilla = plantilla+ '<option value="' + unidades[c] + '" data-yyy="' +precios[c]+ '" data-unidad="' +unidades[c]+ '" data-cantidad-unidad="' +cantidades[c]+ '" selected>' +unidades[c]+ '</option>';
						}else{
							plantilla = plantilla+ '<option value="' + unidades[c] + '" data-yyy="' +precios[c]+ '" data-unidad="' +unidades[c]+ '" data-cantidad-unidad="' +cantidades[c]+ '">' +unidades[c]+ '</option>';
						}
					}
					plantilla = plantilla +
				'</select>'+
			'</td>';
			plantilla = plantilla+ '<td><input type="text" value="' + parseFloat(precios[0]) + '" name="costos[]" class="form-control input-xs text-right" autocomplete="off" data-costo="' + parseFloat(precios[0]) + '"  data-validation-error-msg="Debe ser un n??mero decimal positivo" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';
		}
		else{
			plantilla = plantilla + '<td><input type="text" value="' + unidades[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + unidades[0] + '" readonly data-validation-error-msg="Debe ser un n??mero decimal positivo"></td>'+
									'<td><input type="text" value="' + parseFloat(precios[0]) + '" name="costos[]" class="form-control input-xs text-right" autocomplete="off" data-costo="' + parseFloat(precios[0])+ '"  data-validation-error-msg="Debe ser un n??mero decimal positivo" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';
		}


		plantilla = plantilla + 
		'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-error-msg="Debe ser n??mero entero positivo" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>' +'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
			'<td class="text-nowrap text-center">' +
				'<button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip"  data-title="Item por fecha"  title=""  onclick="adicionar_producto_unidad('+numero +','+ id_producto+')"><span class="glyphicon glyphicon-plus"></span></button>'+
				'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto_unidad('+numero +', ' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>'+
			'</td>' +
		'</tr>';
		//console.log(plantilla);
		$compras.append(plantilla);
		contador = contador + 1;
		
		$('[data-valor=' + id_producto + ']').attr("data-contador",   + contador );
		$compras.find('[data-cantidad], [data-costo]').on('click', function () {
			$(this).select();
		});

		//obtendra el precio inicial por cada producto
		$compras.find('[data-xxx]').on('change', function () {
            var v = $(this).find('option:selected').attr('data-yyy');
            $(this).parent().parent().find('[data-costo]').val(parseFloat(v));
            //$(this).parent().parent().find('[data-costo]').attr('value' ,parseFloat(v));
            calcular_importe(numero, id_producto);
        });


		// inicializa fecha para cada item de producto
		var $producto = $('[data-producto=' + id_producto + '][data-position='+numero+ ']');
		var $inicial_fecha = $producto.find('[data-fecha]');
		var $fecha = $producto.find('#fecha-'+id_producto );
		var formato = $('[data-formato]').attr('data-formato');
		var mascara = $('[data-mascara]').attr('data-mascara');
		$inicial_fecha.datetimepicker({
			format: formato
		});
		$inicial_fecha.on('click', function (e) {
			$inicial_fecha.val(e.date);
			$fecha.data('DateTimePicker').minDate('now');
		});


		$compras.find('[title]').tooltip({
			container: 'body',
			trigger: 'hover'
		});

		// validar datos
		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function () {
				guardar_compra();
			}
		});
	}
	
	calcular_importe(numero, id_producto);
}

/** funcion eliminar producto (no utilizada) */
function eliminar_producto(id_producto) {
	bootbox.confirm('Est?? seguro que desea eliminar el producto?', function (result) {
		if(result){
			$('[data-producto=' + id_producto + ']').remove();
			calcular_total();
		}
	});
}

/** funcion redondear el costo de compra (no utilizada) */
function redondear_importe(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $costo = $producto.find('[data-costo]');
	var costo;
	costo = $.trim($costo.val());
	costo = ($.isNumeric(costo)) ? parseFloat(costo).toFixed(2) : costo;
	$costo.val(costo);
	calcular_importe(id_producto);
}

/** calcula el importe de cada item de producto */
function calcular_importe(numero, id_producto) {
	var $producto = $('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var $precio = $producto.find('[data-costo]');
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

/** adiciona item por unidad */
function adicionar_producto_unidad(numero, id_producto){
	var $compras = $('#compras tbody');
	var $producto = $compras.find('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	var $cantidad = $producto.find('[data-cantidad]');
	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
	$cantidad.val(cantidad).trigger('blur');
	calcular_importe(numero, id_producto);
}

/** eliminar item generado por unidad */
function eliminar_producto_unidad(numero, id_producto) {
	// definiendo base de la tabla
	var $compras = $('#compras tbody');
	// elimina item de la posicion "numero"
	$compras.find('[data-producto=' + id_producto + '][data-position='+numero+ ']').remove();
	// recupera un contador para cada producto
	var contador = parseInt($('[data-valor=' + id_producto + ']')[0].dataset.contador);
	$('[data-valor=' + id_producto + ']').attr("data-contador",   + contador - 1 );
	renumerar_productos();
    calcular_total();
}

/**  reinicia la cantidad de registros actuales */
function renumerar_productos() {
	var $compras = $('#compras tbody');
	var $productos = $compras.find('[data-producto]');
	$productos.each(function (i) {
		$(this).find('td:first').text(i + 1);
	});
}

/**  obtiene el costo total de la compra */
function calcular_total() {
	var $compras = $('#compras tbody');
	var $total = $('[data-subtotal]:first');
	var $importes = $compras.find('[data-importe]');
	var importe, total = 0;
	$importes.each(function (i) {
		importe = $.trim($(this).text());
		importe = parseFloat(importe);
		total = total + importe;
	});
	$total.text(total.toFixed(2));
	$('[data-compras]:first').val($importes.size()).trigger('blur');
	$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
}

/**  inicia la peticion para guardar compra */
function guardar_compra() {
	var data = $('#formulario').serialize();
	//console.log(data)
	//$('#loader').fadeIn(100);
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/ingresos/guardar',
		data: data
	}).done(function (compra) {
		// console.log(compra);
		if (compra) {
			$.notify({
				message: 'La compra fue realizada satisfactoriamente.'
			}, {
				type: 'success'
			}); 
			$('#loader').fadeOut(100);
			generar_pdf_compra(compra['id_ingreso']);
		}
	}).fail(function () {
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurri?? un problema en el proceso, no se puedo guardar los datos de la compra de entrega, verifique si la se guard?? parcialmente2.'
		}, {
			type: 'danger'
		});
		
	}).always(function () {
		$('#formulario :reset').trigger('click');
		window.location.reload();
	});
}

/**  inicia la peticion para generar pdf de la compra */
function generar_pdf_compra(id) {
	$id_ingreso = parseInt(id);
	url = '?/ingresos/imprimir/' + $id_ingreso;
	//console.log(url)
	window.open('?/ingresos/imprimir/' + $id_ingreso,'_blank');
}

/**  inicia la peticion para imprimir la compra */
function imprimir_compra(compra) {
	var servidor = $.trim($('[data-servidor]').attr('data-servidor'));
	//console.log(servidor);
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: servidor,
		data: compra
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
				message: 'Ocurri?? un problema durante el proceso, no se envi?? los datos para la impresi??n de la factura.'
			}, {
				type: 'danger'
			});
			break;
		}
	}).fail(function () {
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurri?? un problema durante el proceso, reinicie la terminal para dar soluci??n al problema y si el problema persiste contactese con el con los desarrolladores.'
		}, {
			type: 'danger'
		});
	}).always(function () {
		$('#formulario').trigger('reset');
		$('#form_buscar_0').trigger('submit');
		location.reload();
	});
}

</script>
<?php require_once show_template('footer-empty'); ?>