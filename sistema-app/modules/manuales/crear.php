<?php

// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Verifica si existe el almacen
if ($id_almacen != 0) {
	// Obtiene los productos por fecha de vencimiento
	$productos = $db->query("select
		pf.id_producto,
		pf.color,
		pf.descripcion,
		pf.imagen,
		pf.codigo,
		pf.nombre,
		pf.nombre_factura,
		pf.cantidad_minima,
		pf.precio_actual,
		pf.unidad_id,
		GROUP_CONCAT(
		ifnull(tf.cantidad_ingresos, 0)
		) AS cantidad_ingresos,
		GROUP_CONCAT(
		ifnull(tf.cantidad_egresos, 0)            
		) AS cantidad_egresos,
		GROUP_CONCAT(tf.fecha_vencimiento
		) AS fecha_vencimiento,
		GROUP_CONCAT(
		ifnull(tf.cantidad_ingresos, 0) - ifnull(tf.cantidad_egresos , 0)
		) as stock,
		u.unidad,
		u.sigla,
		c.categoria
	from
		inv_productos pf
	left join(
		select
			p.id_producto,
			ifnull(ti.cantidad_ingresos, 0) AS cantidad_ingresos,
			ifnull(te.cantidad_egresos, 0) AS cantidad_egresos,
			ti.fecha_vencimiento AS fecha_vencimiento,
			ifnull(ifnull(ti.cantidad_ingresos, 0) - ifnull(te.cantidad_egresos , 0), 0) as stock
		from
			inv_productos p

			left join (
				select
					d.producto_id,
					d.fecha_vencimiento,
					sum(d.cantidad) as cantidad_ingresos
				from
					inv_ingresos_detalles d
					left join inv_ingresos i on i.id_ingreso = d.ingreso_id
				where
					i.almacen_id = 8
				group by
					d.producto_id,
					d.fecha_vencimiento
			) as 
			ti on ti.producto_id = p.id_producto
			left join (
				select
					d.producto_id,
					d.fecha_vencimiento,
					sum(d.cantidad) as cantidad_egresos
				from
					inv_egresos_detalles d
					left join inv_egresos e on e.id_egreso = d.egreso_id
				where
					e.almacen_id = 8

				group by
					d.producto_id,
					d.fecha_vencimiento
			) as te 
			on te.producto_id = p.id_producto and ti.fecha_vencimiento = te.fecha_vencimiento
		where ifnull(ifnull(ti.cantidad_ingresos, 0) - ifnull(te.cantidad_egresos , 0), 0) >= 1
		order by ti.fecha_vencimiento asc
	) as tf on tf.id_producto = pf.id_producto
		left join inv_unidades u on u.id_unidad = pf.unidad_id
		left join inv_categorias c on c.id_categoria = pf.categoria_id
	where  fecha_vencimiento IS NOT NULL
	group by pf.id_producto")->fetch();
} else {
	$productos = null;
}

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene la fecha de hoy
$hoy = date('Y-m-d');

// Obtiene los clientes

$clientes = $db->select('cliente, nit, telefono, count(cliente) as nro_visitas')->from('inv_clientes')->group_by('cliente, nit, telefono')->order_by('cliente asc, nit asc')->fetch();

//$clientes = $db->select('nombre_cliente, nit_ci, count(nombre_cliente) as nro_visitas, sum(monto_total) as total_ventas')->from('inv_egresos')->group_by('nombre_cliente, nit_ci')->order_by('nombre_cliente asc, nit_ci asc')->fetch();
//$clientes = $db->query("select DISTINCT a.nombre_cliente, a.nit_ci from inv_egresos a LEFT JOIN inv_clientes b ON a.nit_ci = b.nit UNION
//select DISTINCT b.cliente as nombre_cliente, b.nit as nit_ci from inv_egresos a RIGHT JOIN inv_clientes b ON a.nit_ci = b.nit
//ORDER BY nombre_cliente asc, nit_ci asc")->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

?>
<?php require_once show_template('header-empty'); ?>
<style>

span.block.text-right.text-success, span.block.text-right.text-danger {
	display: block;
}
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
	<?php if ($almacen) { ?>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos de la venta</strong>
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
				<form id="formulario" class="form-horizontal">
					<div class="form-group">
						<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
						<div class="col-sm-8">
							<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true">
								<option value="">Buscar</option>
								<?php foreach ($clientes as $cliente) { ?>
								<option value="<?= escape($cliente['nit']) . '|' . escape($cliente['cliente']) . '|' . escape($cliente['telefono']); ?>"><?= escape($cliente['nit']) . ' &mdash; ' . escape($cliente['cliente']); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="nit_ci" class="col-sm-4 control-label">NIT / CI:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./& " data-validation-length="max100">
						</div>
					</div>
					<div class="form-group">
						<label for="nro_factura" class="col-sm-4 control-label">Número de factura:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nro_factura" id="nro_factura" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="nro_autorizacion" class="col-sm-4 control-label">Número de autorización:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nro_autorizacion" id="nro_autorizacion" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="table-responsive margin-none">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Código</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap">Fecha de vencimiento</th>
                                    <th class="text-nowrap">Cantidad</th>
                                    <th class="text-nowrap">Unidad</th>
									<th class="text-nowrap">Precio</th>
									<th class="text-nowrap">Descuento</th>
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
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;20]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 20">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
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
			</div>
		</div>
		<div class="panel panel-default" data-servidor="<?= ip_local . name_project . '/factura.php'; ?>">
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
				<?php if ($permiso_mostrar) { ?>
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/manuales/mostrar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Ventas personales</span></a>
					</div>
				</div>
				<hr>
				<?php } ?>
				<?php if ($productos) { ?>
				<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
					<thead>
						<tr class="active">
							<th class="text-nowrap">Imagen</th>
							<th class="text-nowrap">Código</th>
							<th class="text-nowrap">Nombre</th>
                            <th class="text-nowrap">Descripción</th>
							<th class="text-nowrap">Fecha de vencimiento</th>
                            <th class="text-nowrap">Tipo</th>
							<th class="text-nowrap">Stock</th>
							<th class="text-nowrap">Precio</th>
							<th class="text-nowrap"><i class="glyphicon glyphicon-cog"></i></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($productos as $nro => $producto) {?>
                            <?php $otro_precio = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id=b.id_unidad')->where('a.producto_id',$producto['id_producto'])->fetch();?>
							<tr>
								<td class="text-nowrap"><img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" width="75" height="75"></td>
								<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
								<td>
									<span ><?= escape($producto['nombre']); ?> <?= escape($producto['color']); ?></span>
									<span class="hidden" data-color="<?= $producto['id_producto']; ?>"><?= escape($producto['color']); ?></span>
									<span class="hidden" data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre_factura']); ?></span>
								</td>
								<td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
								<?php 
								// obteniendo fechas de vencimiento	
								$fechas_ven  = explode(',', $producto['fecha_vencimiento']);
								// obteniendo stocks
								$stocks  = explode(',', $producto['stock']);	 
								?>
								

								<td class="text-right" data-fecha="<?= $producto['id_producto']; ?>" data-contador="0" data-val-fecha="<?= $producto['fecha_vencimiento'];?>">
									<?php for ($x = 0; $x <= count($stocks) - 1; $x++) {?>
										<!-- obteniendo fechas de productos por fecha de vencimiento -->	
										<?php if($stocks[$x] < 1){ ?>
											<span class="block text-right text-danger " style="display:none">
												<?= escape($fechas_ven[$x] ); ?></br>
											</span>
										<?php } else { ?>
											<span class="block text-right text-success" >
												<?= escape($fechas_ven[$x]); ?></br>
											</span>
										<?php } ?>

									<?php } ?>
								</td>
								
								
								<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>

								<td class="text-right block" data-stock="<?= $producto['id_producto']; ?>" data-val-stock="<?= $producto['stock']; ?>">

									<?php for ($x = 0; $x <= count($stocks) - 1; $x++) {?>
										<!-- obteniendo el stock de productos por fecha de vencimiento -->	
										<?php if($stocks[$x] < 1){ ?>
											<span class="block text-right text-danger " style="display:none">
												<?= escape($stocks[$x]); ?>
											</span>
										<?php } else { ?>
											<span class="block text-right text-success" >
												<?= escape($stocks[$x]); ?>
											</span>
										<?php } ?>

									<?php } ?>
								</td>

								<td class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>">
									*<?= escape($producto['unidad'].': '); ?><b><?= escape($producto['precio_actual']); ?></b>
									<?php foreach($otro_precio as $otro){ ?>
										<br/>*<?= escape($otro['unidad'].': '); ?><b><?= escape($otro['otro_precio']); ?></b>
									<?php } ?>
								</td>
								<td class="text-nowrap">
									<button type="button" class="btn btn-xs btn-primary" data-vender="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Vender"><span class="glyphicon glyphicon-shopping-cart"></span></button>
									<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto']; ?>" onclick="actualizar(this)" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
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
	<?php } else { ?>
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="glyphicon glyphicon-home"></i> Ventas manuales</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-danger">
					<strong>Advertencia!</strong>
					<p>Usted no puede realizar esta operación, verifique que todo este en orden tomando en cuenta las siguientes sugerencias:</p>
					<ul>
						<li>No existe el almacén principal de la cual se puedan tomar los productos necesarios para la venta, debe fijar un almacén principal.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
<h2 class="btn-primary position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es una venta manual" data-placement="right"><i class="glyphicon glyphicon-edit display-cell"></i></h2>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
	var $cliente = $('#cliente');
	var $nit_ci = $('#nit_ci');
	var $nombre_cliente = $('#nombre_cliente');
	var $telefono_cliente = $('#telefono_cliente');
	var $formulario = $('#formulario');


	// inicia el datatable para el filtrado
	var table = $('#productos').DataTable({
		info: false,
		scrollY: 508,
		lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
		order: []
	});

	// rellena el formulario del cliente en caso de que exista y crea nueva instancia en caso de que no exista
	$cliente.selectize({
		persist: false,
		createOnBlur: true,
		create: true,
		onInitialize: function () {
			$cliente.css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function () {
			$cliente.trigger('blur');
		},
		onBlur: function () {
			$cliente.trigger('blur');
		}
	}).on('change', function (e) {
		var valor = $(this).val();
		valor = valor.split('|');
		$(this)[0].selectize.clear();
		if (valor.length != 1) {
			$nit_ci.prop('readonly', true);
			$nombre_cliente.prop('readonly', true);
			$telefono_cliente.prop('readonly', true);
			$nit_ci.val(valor[0]);
			$nombre_cliente.val(valor[1]);
			$telefono_cliente.val(valor[2]);
		} else {
			$nit_ci.prop('readonly', false);
			$nombre_cliente.prop('readonly', false);
			$telefono_cliente.prop('readonly', false);
			if (es_nit(valor[0])) {
				$nit_ci.val(valor[0]);
				$nombre_cliente.val('').focus();
				$telefono_cliente.val('');
			} else {
				$nombre_cliente.val(valor[0]);
				$nit_ci.val('').focus();
				$telefono_cliente.val('');
			}
		}
	});

	// valida toda la tabla y el formulario del cliente
	$.validate({
		form: '#formulario',
		modules: 'basic',
		onSuccess: function () {
			guardar_nota();
		}
	});

	// envia toda la tabla y el formulaario del cliente 
	$formulario.on('submit', function (e) {
		e.preventDefault();
	});

	// vacia toda la tabla y el formulaario del cliente 
	$formulario.on('reset', function () {
		$('#ventas tbody').empty();
		$nit_ci.prop('readonly', false);
		$nombre_cliente.prop('readonly', false);
		calcular_total();
	}).trigger('reset');

	
	$('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');

});

function es_nit(texto) {
	var numeros = '0123456789';
	for(i = 0; i < texto.length; i++){
		if (numeros.indexOf(texto.charAt(i), 0) != -1){
			return true;
		}
	}
	return false;
}


$('[data-vender]').on('click', function () {
	adicionar_producto($.trim($(this).attr('data-vender')));
});
/*
function adicionar_producto(id_producto) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock = $.trim($('[data-stock=' + id_producto + ']').text());
    var valor = $.trim($('[data-valor=' + id_producto + ']').text());

    var posicion = valor.indexOf(':');
    var porciones = valor.split('*');
    //console.log(porciones);
	var plantilla = '';
	var cantidad;

	if ($producto.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
						'<td class="text-nowrap">' + numero + '</td>' +
						'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
						'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
		if(porciones.length>2){
            plantilla = plantilla+'<td><select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control input-xs" >';
            aparte = porciones[1].split(':');
            for(var ic=1;ic<porciones.length;ic++){
                    parte = porciones[ic].split(':');
                //console.log(parte);
                plantilla = plantilla+'<option value="' +parte[0]+ '" data-yyy="' +parte[1]+ '" >' +parte[0]+ '</option>';
            }
            plantilla = plantilla+'</select></td>'+
            '<td><input type="text" value="' + parseFloat(aparte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(aparte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
        }
        else{
            parte = porciones[1].split(':');
            plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>'+
                                    '<td><input type="text" value="' + parseFloat(parte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(parte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
        }
						plantilla = plantilla +'<td><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="2" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="range[0;50]" data-validation-error-msg="Debe ser un número positivo entre 0 y 50" onkeyup="descontar_precio(' + id_producto + ')"></td>' +
						'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap text-center">' +
							'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
						'</td>' +
					'</tr>';

		$ventas.append(plantilla);

        $ventas.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function () {
            $(this).select();
        });

        $ventas.find('[data-xxx]').on('change', function () {
            var v = $(this).find('option:selected').attr('data-yyy');
            $(this).parent().parent().find('[data-precio]').val(parseFloat(v));
            $(this).parent().parent().find('[data-precio]').attr(parseFloat(v));
            calcular_importe(id_producto);
        });

		$ventas.find('[title]').tooltip({
			container: 'body',
			trigger: 'hover'
		});

		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function () {
				guardar_factura();
			}
		});
	}

	calcular_importe(id_producto);
}
*/

function sincronizar_fechas(numero){
	for (let i = 0; i < numero; i++) {
		$(document).on('change','#fecha'+i ,function(){
			$(this).siblings().find('option[value="'+$(this).val()+'"]').remove();
		});
	}
}

/** funcion adicionar producto */
function adicionar_producto(id_producto) {
	// definiendo base de la tabla
	var $ventas = $('#ventas tbody');
	// busca el dom venta - producto
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	console.log($producto.val())
	// busca el dom venta - producto - cantidad
	var $cantidad = $producto.find('[data-cantidad]');
	// define un contador anonimo
	var numero = $ventas.find('[data-producto]').size() + 1;
	// recupera el codigo de producto
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	// recupera el nombre de producto
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	// recupera el color de producto
	var color = $.trim($('[data-color=' + id_producto + ']').text());
	// recupera un array de fechas de vencimiento
	var fechas =$('[data-fecha=' + id_producto + ']')[0].dataset.valFecha.split(',');
	// recupera un array de stocks
	var stocks =$('[data-stock=' + id_producto + ']')[0].dataset.valStock.split(',');
	// recupera un contador para cada producto
	var contador = parseInt($('[data-fecha=' + id_producto + ']')[0].dataset.contador);
	
	var posicion_stock = contador;

    var valor = $.trim($('[data-valor=' + id_producto + ']').text());
	//console.log(valor)
    var posicion = valor.indexOf(':');
    var porciones = valor.split('*');

	var plantilla = '';
	var cantidad;

	if (contador < fechas.length) {
		console.log(fechas, stocks);
		// incrementa cantidad
		console.log(contador);
		plantilla =
		'<tr class="active" data-producto="' + id_producto + '" data-position="'+numero+'">'+
			'<td class="text-nowrap">' + numero + '</td>'+
			'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>'+
			'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre +' '+ color  +'</td>'+
		
			// seleccionar fecha de vencimiento para agregar a la venta
			'<td>'+
				'<select name="fecha[]" id="fecha' + numero + '" class="form-control input-xs" onchange="actualizar_stock(' + numero + ',' + id_producto + ')">';
			for(var i = 0; i < fechas.length; i++){
				if(i === contador ){
					// selecciona la `rimera fecha por defecto
					plantilla = plantilla+ '<option value="' +fechas[i]+ '" selected>' +fechas[i]+ '</option>';
				}else{
					plantilla = plantilla+ '<option value="' +fechas[i]+ '" >' +fechas[i]+ '</option>';
				}

			}

			plantilla = plantilla +
				'</select>'+
			'</td>';
			
			plantilla = plantilla +
			'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stocks[posicion_stock] + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stocks[posicion_stock] + '" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';
			if(porciones.length>2){
				plantilla = plantilla+'<td><select name="unidad[]" id="unidad" data-xxx="true" class="form-control input-xs" >';
				aparte = porciones[1].split(':');
				for(var ic=1;ic<porciones.length;ic++){
						parte = porciones[ic].split(':');
					//console.log(parte);
					plantilla = plantilla+'<option value="' +parte[0]+ '" data-yyy="' +parte[1]+ '" >' +parte[0]+ '</option>';
				}
				plantilla = plantilla+'</select></td>'+
				'<td><input type="text" value="' + parseFloat(aparte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(aparte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';
			}
			else{
				parte = porciones[1].split(':');
				plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>'+
										'<td><input type="text" value="' + parseFloat(parte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(parte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';
			}
			plantilla = plantilla + 
			'<td><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="2" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="range[0;50]" data-validation-error-msg="Debe ser un número positivo entre 0 y 50" onkeyup="descontar_precio('+numero +',' + id_producto + ')"></td>'+
			'<td class="text-nowrap text-right" data-importe="">0.00</td>'+
			'<td class="text-nowrap text-center">'+
				'<button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip"  data-title="Item por fecha"  title=""  onclick="adicionar_producto_fecha('+numero +','+ id_producto+')"><span class="glyphicon glyphicon-plus"></span></button>'+
				'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto_fecha('+numero +', ' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>'+
			'</td>' +
		'</tr>';


		$ventas.append(plantilla);
		contador = contador + 1;
		$('[data-fecha=' + id_producto + ']').attr("data-contador",   + contador );
        
		
		$ventas.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function () {
            $(this).select();
        });

		//obtendra el precio inicial por cada producto
        $ventas.find('[data-xxx]').on('change', function () {
            var v = $(this).find('option:selected').attr('data-yyy');
            $(this).parent().parent().find('[data-precio]').val(parseFloat(v));
            $(this).parent().parent().find('[data-precio]').attr(parseFloat(v));
            calcular_importe(id_producto);
        });

		$ventas.find('[title]').tooltip({
			container: 'body',
			trigger: 'hover'
		});

		// validar datos
		$.validate({
			form: '#formulario',
			modules: 'basic',
			onSuccess: function () {
				guardar_nota();
			}
		});
	}

	calcular_importe(numero, id_producto);
	// sincronizar_fechas(contador);
}

// actualizar el stock por fecha de vencimiento
function actualizar_stock(numero, id_producto){
	// definiendo base de la tabla
	var $ventas = $('#ventas tbody');
	// recupera fecha seleccionada
	var fecha_seleccionada =  $ventas.find('[data-producto=' + id_producto + ']').find('#fecha' + numero + ' :selected').val();
	console.log(fecha_seleccionada)
	// busca el dom venta - producto
	var $producto = $ventas.find('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	// recupera un array de fechas de vencimiento
	var fechas =$('[data-fecha=' + id_producto + ']')[0].dataset.valFecha.split(',');
	// recupera un array de stocks
	var stocks =$('[data-stock=' + id_producto + ']')[0].dataset.valStock.split(',');
	// recupera posicion de fecha seleccionada
	var position = fechas.indexOf(fecha_seleccionada);
	console.log(position)
	fechas = fechas.filter(function(item) {
		return fechas.indexOf(item) !== position;
	});
	
	//actualizando fecha_vencimiento
	$ventas.find('[data-producto=' + id_producto + ']').attr("data-fecha", fechas[position] );
	//actualizando limite
	$producto.find('[data-cantidad]').attr("data-validation-allowing", 'range[1;' + stocks[position] + ']')
	//actulaizando msg de error
	$producto.find('[data-cantidad]').attr("data-validation-error-msg", 'Debe ser un número positivo entre 1 y ' + stocks[position] + '');
	//adicionar_item(fechas, id_producto);
}

// realiza descuento de cada item
function descontar_precio(numero, id_producto) {
	var $producto = $('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	var $precio = $producto.find('[data-precio]');
	var $descuento = $producto.find('[data-descuento]');
	var precio, descuento;

	precio = $.trim($precio.attr('data-precio'));
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0;
	descuento = $.trim($descuento.val());
	descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
	precio = precio - (precio * descuento / 100);
	$precio.val(precio.toFixed(2));

	calcular_importe(numero, id_producto);
}


// adiciona item por fecha de vencimiento
function adicionar_producto_fecha(numero, id_producto){
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	var $cantidad = $producto.find('[data-cantidad]');
	console.log($cantidad.val());
	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
	$cantidad.val(cantidad).trigger('blur');
	calcular_importe(numero, id_producto);
}

// calcula el importe de cada item de producto
function calcular_importe(numero, id_producto) {
	var $producto = $('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	// var $producto = $('[data-producto=' + id_producto + ']').find();
	var $cantidad = $producto.find('[data-cantidad]');
	var $precio = $producto.find('[data-precio]');
	var $descuento = $producto.find('[data-descuento]');
	var $importe = $producto.find('[data-importe]');
	var cantidad, precio, importe, fijo;

	fijo = $descuento.attr('data-descuento');
	fijo = ($.isNumeric(fijo)) ? parseFloat(fijo) : 0;
	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio = $.trim($precio.val());
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	descuento = $.trim($descuento.val());
	descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
	importe = cantidad * precio;
	importe = importe.toFixed(2);
	$importe.text(importe);
	calcular_total();
}

// eliminar item generado por la fecha de vencimiento
function eliminar_producto_fecha(numero, id_producto) {
	// definiendo base de la tabla
	var $ventas = $('#ventas tbody');
	// elimina item de la posicion "numero"
	$ventas.find('[data-producto=' + id_producto + '][data-position='+numero+ ']').remove();
	// recupera un contador para cada producto
	var contador = parseInt($('[data-fecha=' + id_producto + ']')[0].dataset.contador);
	$('[data-fecha=' + id_producto + ']').attr("data-contador",   + contador - 1 );
	renumerar_productos();
    calcular_total();
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
	$('[data-total]:first').val(total.toFixed(3)).trigger('blur');
}

function vender(elemento) {
    var $elemento = $(elemento), vender;
    vender = $elemento.attr('data-vender');
    adicionar_producto(vender);
}

function guardar_nota() {
	var data = $('#formulario').serialize();
	console.log(data)
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/manuales/guardar',
		data: data
	}).done(function (venta) {
		if (venta) {
			$.notify({
				message: 'La Venta manual fue realizada satisfactoriamente.'
			}, {
				type: 'success'
			});
			//imprimir_nota(venta);
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
			message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de entrega, verifique si la se guardó parcialmente.'
		}, {
			type: 'danger'
		});
	}).always(function (){
		$('#formulario :reset').trigger('click');
	});
}


function actualizar(elemento) {
	var $elemento = $(elemento), actualizar;
	actualizar = $elemento.attr('data-actualizar');
	//console.log(actualizar);	
	$('#loader').fadeIn(100);

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/manuales/actualizar',
		data: {
			id_producto: actualizar
		}
	}).done(function (producto) {
		if (producto) {
			var $busqueda = $('[data-busqueda="' + producto.id_producto + '"]');
			var precio = parseFloat(producto.precio).toFixed(2);
			var stock = parseInt(producto.stock);

			$busqueda.find('[data-stock]').text(stock);
			$busqueda.find('[data-valor]').text(precio);

			var $producto = $('[data-producto=' + producto.id_producto + ']');
			var $cantidad = $producto.find('[data-cantidad]');
			var $precio = $producto.find('[data-precio]');
			//console.log($precio);
			if ($producto.size()) {
				$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
				$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock);
				$precio.val(precio);
				$precio.attr('data-precio', precio);
				//console.log($cantidad);
				descontar_precio(producto.id_producto);
			}
			
			$.notify({
				message: 'El stock y el precio del producto se actualizaron satisfactoriamente.'
			}, {
				type: 'success'
			});
		} else {
			$.notify({
				message: 'Ocurrió un problema durante el proceso, es posible que no existe un almacén principal.'
			}, {
				type: 'danger'
			});
		}
	}).fail(function () {
		$.notify({
			message: 'Ocurrió un problema durante el proceso, no se pudo actualizar el stock ni el precio del producto.'
		}, {
			type: 'danger'
		});
	}).always(function () {
		$('#loader').fadeOut(100);
	});
}
</script>
<?php require_once show_template('footer-empty'); ?>