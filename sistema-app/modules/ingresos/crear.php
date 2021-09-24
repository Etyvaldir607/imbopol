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

//var_dump($_SESSION);
//echo "<br><br>";
foreach ($_SESSION as $key) {
	if($key['rol_id'] == '2' || $key['rol_id'] == '1')
		$rol_id =$key['rol_id'];
	elseif ($key['rol_id'] == '3' || $key['rol_id'] == '4') {
		$rol_id =$key['rol_id'];
	}
}
//echo $rol_id;
// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;
// Verifica si existe el almacen
if ($id_almacen != 0) {
	// Obtiene los productos
	$productos = $db->query("select p.id_producto, p.codigo, p.nombre_factura, p.descripcion, p.color, p.nombre_factura, p.cantidad_minima, p.precio_actual, ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos, ifnull(s.cantidad_egresos, 0) as cantidad_egresos, u.unidad, u.sigla, c.categoria from inv_productos p left join (select d.producto_id, sum(d.cantidad) as cantidad_ingresos from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id where i.almacen_id = $id_almacen group by d.producto_id ) as e on e.producto_id = p.id_producto left join (select d.producto_id, sum(d.cantidad) as cantidad_egresos from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = $id_almacen group by d.producto_id ) as s on s.producto_id = p.id_producto left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id")->fetch();
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
						<li>Para un mejor control del ingreso de productos se recomienda escribir una pequeña descripción acerca de la compra.</li>
						<li>La moneda con la que se esta trabajando es <?= escape($moneda); ?>.</li>
						<li>Los stocks que se muestra en la búsqueda de productos son del almacén principal.</li>
					</ul>
				</div>
				<?php if ($rol_id >= 3) { ?>
					<form id="formulario" method="post" class="form-horizontal">
						<div class="form-group">
							<label for="almacen" class="col-md-4 control-label">Almacén:</label>
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
							<label for="descripcion" class="col-sm-4 control-label">Descripción:</label>
							<div class="col-sm-8">
								<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true"></textarea>
							</div>
						</div>
						<div class="table-responsive margin-none" >
							<table id="compras" class="table table-bordered table-condensed table-striped table-hover table-xl margin-none table-responsive-md">
								<thead>
									<tr class="active">
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
								<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-compras="" data-validation="required number"  data-validation-error-msg="Debe existir como mínimo 1 producto y como máximo 50 productos">
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
							<label for="almacen" class="col-md-4 control-label">Almacén:</label>
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
							<label for="descripcion" class="col-sm-4 control-label">Descripción:</label>
							<div class="col-sm-8">
								<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true"></textarea>
							</div>
						</div>
						<div class="table-responsive margin-none">
							<table id="compras" class="table table-bordered table-condensed table-striped table-hover table-xl margin-none table-responsive-md">
								<thead>
									<tr class="active">
										<th class="text-nowrap">Código</th>
										<th class="text-nowrap">Nombre</th>
										<th class="text-nowrap">Color</th>
										<th class="text-nowrap">Fecha de vencimiento</th>
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
								<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-compras="" data-validation="required number" data-validation-allowing="range[1;50]" data-validation-error-msg="Debe existir como mínimo 1 producto y como máximo 50 productos">
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
					<strong>Búsqueda de productos</strong>
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
								<th class="text-nowrap">Código</th>
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
										<td class="text-nowrap text-right"><?= escape($producto['precio_actual']); ?></td>
									<?php }elseif ($rol_id == '2') {?>
										<td class="text-nowrap text-right"><?= escape($producto['precio_actual']); ?></td>
									<?php	}elseif ($rol_id == '3') {?>
										<td style="display: none;" class="text-nowrap text-right"><?= escape($producto['precio_actual']); ?></td>
									<?php	}elseif ($rol_id == '4') {?>
										<td style="display: none;" class="text-nowrap text-right"><?= escape($producto['precio_actual']); ?></td>
									<?php	}?>

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
//console.log(rol_id);
$(function () {
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
	$('[data-comprar]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-comprar')));
	});
	$('#productos').dataTable({
		info: false,
		lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
		order: []
	});
	$('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');
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
	$(':reset').on('click', function () {
		$('#proveedor')[0].selectize.clear();
		$('#almacen')[0].selectize.clear();
	});
	$.validate({
		modules: 'basic'
	});
	$('#formulario').on('reset', function () {
	//$('#compras tbody').find('[data-importe]').text('0.00');
		$('#compras tbody').empty();
			calcular_total();
		});
		$('#formulario :reset').trigger('click');
	});

// inicia date picker para cada celda
function adicionar_fecha(id_producto){
	var $producto = $('[data-producto=' + id_producto + ']');
	var $inicial_fecha = $producto.find('[data-fecha]');
	var $fecha = $producto.find('#fecha-'+id_producto );
	

	var formato = $('[data-formato]').attr('data-formato');
	var mascara = $('[data-mascara]').attr('data-mascara');
	var gestion = $('[data-gestion]').attr('data-gestion');

	$inicial_fecha.datetimepicker({
		format: formato
	});

	$inicial_fecha.on('click', function (e) {
		$inicial_fecha.val(e.date);
		$fecha.data('DateTimePicker').minDate('now');
	});
}

function adicionar_producto(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var $compras = $('#compras tbody');
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var color = $.trim($('[data-color=' + id_producto + ']').text());
	var plantilla = '';
	var cantidad;
	//console.log(nombre,color);
	if ($producto.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		if ("<?= $rol_id >= 3;?>") {
			plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
			'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
			'<td><input type="hidden" value="' + nombre + '" name="nprod[]">' + nombre + '</td>' + '<td>' + color + '</td>' +
			'<td><input type="text" name="fechas[]" value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="fecha-' + id_producto + '" class="form-control input-xs text-right" autocomplete="off" data-fecha="" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true" onclick="adicionar_fecha(' + id_producto + ')"></td>'+
			'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-error-msg="Debe ser número entero positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
			'<td style="display: none;"><input type="text" value="0.00" name="costos[]" class="form-control input-xs text-right" autocomplete="off" data-costo="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')" onblur="redondear_importe(' + id_producto + ')"></td>' +
			'<td style="display: none;" class="text-nowrap text-right" data-importe="">0.00</td>' +
			'<td class="text-nowrap text-center">' +
			'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
			'</td>' +
			'</tr>';
		}else{
			if ("<?= $rol_id <= 2;?> " ) {
				plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
				'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
				'<td><input type="hidden" value="' + nombre + '" name="nprod[]">' + nombre + '</td>' + '<td>' + color + '</td>' +
				'<td><input type="text" name="fechas[]"  value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="fecha-' + id_producto + '"  class="form-control input-xs text-right" autocomplete="off" data-fecha="" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true" onclick="adicionar_fecha(' + id_producto + ')"> </td>'+
				'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-error-msg="Debe ser número entero positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
				'<td><input type="text" value="0.00" name="costos[]" class="form-control input-xs text-right" autocomplete="off" data-costo="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')" onblur="redondear_importe(' + id_producto + ')"></td>' +
				'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
				'<td class="text-nowrap text-center">' +
				'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
				'</td>' +
				'</tr>';
			}
		}
		//console.log(plantilla);
		$compras.append(plantilla);
		$compras.find('[data-cantidad], [data-costo]').on('click', function () {
			$(this).select();
		});
		$compras.find('[title]').tooltip({
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
	adicionar_fecha(id_producto);
}

function eliminar_producto(id_producto) {
	bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
		if(result){
			$('[data-producto=' + id_producto + ']').remove();
			calcular_total();
		}
	});
}
function redondear_importe(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $costo = $producto.find('[data-costo]');
	var costo;
	costo = $.trim($costo.val());
	costo = ($.isNumeric(costo)) ? parseFloat(costo).toFixed(2) : costo;
	$costo.val(costo);
	calcular_importe(id_producto);
}
function calcular_importe(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var $costo = $producto.find('[data-costo]');
	var $importe = $producto.find('[data-importe]');
	var cantidad, costo, importe;
	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	costo = $.trim($costo.val());
	costo = ($.isNumeric(costo)) ? parseFloat(costo) : 0.00;
	importe = cantidad * costo;
	importe = importe.toFixed(2);
	$importe.text(importe);
	calcular_total();
}
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
function guardar_nota() {
	var data = $('#formulario').serialize();
	//console.log(data);
	$('#loader').fadeIn(100);
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/ingresos/guardar',
		data: data
	}).done(function (venta) {
		//console.log(venta);
		if (venta) {
			$.notify({
				message: 'La nota de entrega fue realizada satisfactoriamente.'
			}, {
				type: 'success'
			});
			//imprimir_nota(venta);
			$('#loader').fadeOut(100);
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
	}).always(function () {
		$('#compras tbody').empty();
		calcular_total();
		$('#formulario :reset').trigger('click');
	});
}
function imprimir_nota(nota) {
	var servidor = $.trim($('[data-servidor]').attr('data-servidor'));
//console.log(servidor);
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