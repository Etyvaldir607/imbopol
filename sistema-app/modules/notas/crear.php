<?php

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
	//
	//$productos = $db->query("select p.id_producto,p.color, p.descripcion, p.imagen, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, p.unidad_id, ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos, ifnull(s.cantidad_egresos, 0) as cantidad_egresos, u.unidad, u.sigla, c.categoria from inv_productos p left join (select d.producto_id, sum(d.cantidad) as cantidad_ingresos from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id where i.almacen_id = $id_almacen group by d.producto_id ) as e on e.producto_id = p.id_producto left join (select d.producto_id, sum(d.cantidad) as cantidad_egresos from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = $id_almacen group by d.producto_id ) as s on s.producto_id = p.id_producto left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id")->fetch();
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


// Obtiene el almacen principal
/*$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los clientes
$clientes = $db->query("select * from ((select nombre_cliente, nit_ci from inv_egresos) union (select nombre_cliente, nit_ci from inv_proformas)) c group by c.nombre_cliente, c.nit_ci order by c.nombre_cliente asc, c.nit_ci asc")->fetch();
//$clientes = $db->query("select DISTINCT a.nombre_cliente, a.nit_ci from inv_egresos a LEFT JOIN inv_clientes b ON a.nit_ci = b.nit UNION
//select DISTINCT b.cliente as nombre_cliente, b.nit as nit_ci from inv_egresos a RIGHT JOIN inv_clientes b ON a.nit_ci = b.nit
//ORDER BY nombre_cliente asc, nit_ci asc")->fetch();*/

// Define el limite de filas
$limite_longitud = 200;

// Define el limite monetario
$limite_monetario = 10000000;
$limite_monetario = number_format($limite_monetario, 2, '.', '');

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

?>
<?php require_once show_template('header-empty'); ?>
<style>
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
.width-none {
	width: 10px;
}
.table-display > .thead > .tr,
.table-display > .tbody > .tr,
.table-display > .tfoot > .tr {
	margin-bottom: 15px;
}
.table-display > .thead > .tr > .th,
.table-display > .tbody > .tr > .th,
.table-display > .tfoot > .tr > .th {
	font-weight: bold;
}
span.block.text-right.text-success, span.block.text-right.text-danger {
		display: block;
}
@media (min-width: 768px) {
	.table-display {
		display: table;
	}
	.table-display > .thead,
	.table-display > .tbody,
	.table-display > .tfoot {
		display: table-row-group;
	}
	.table-display > .thead > .tr,
	.table-display > .tbody > .tr,
	.table-display > .tfoot > .tr {
		display: table-row;
	}
	.table-display > .thead > .tr > .th,
	.table-display > .thead > .tr > .td,
	.table-display > .tbody > .tr > .th,
	.table-display > .tbody > .tr > .td,
	.table-display > .tfoot > .tr > .th,
	.table-display > .tfoot > .tr > .td {
		display: table-cell;
	}
	.table-display > .tbody > .tr > .td,
	.table-display > .tbody > .tr > .th,
	.table-display > .tfoot > .tr > .td,
	.table-display > .tfoot > .tr > .th,
	.table-display > .thead > .tr > .td,
	.table-display > .thead > .tr > .th {
		padding-bottom: 15px;
		vertical-align: top;
	}
	.table-display > .tbody > .tr > .td:first-child,
	.table-display > .tbody > .tr > .th:first-child,
	.table-display > .tfoot > .tr > .td:first-child,
	.table-display > .tfoot > .tr > .th:first-child,
	.table-display > .thead > .tr > .td:first-child,
	.table-display > .thead > .tr > .th:first-child {
		padding-right: 15px;
	}

}
</style>
<div class="row">
	<?php if ($almacen) { ?>
	<div class="col-md-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Nota de entrega</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-warning">Nota de entrega</h2>
				<hr>
				<form id="formulario" class="form-horizontal">
					<div style="zoom: 1;">
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
								<input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
							</div>
						</div>
						<div class="form-group">
							<label for="telefono_cliente" class="col-sm-4 control-label">Teléfono:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="telefono_cliente" id="telefono_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required" data-validation-length="max100">
							</div>
						</div>
					</div>
					<div class="table-responsive margin-none">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center width-collapse">#</th>
									<th class="text-nowrap text-center width-collapse">CÓDIGO</th>
									<th class="text-nowrap text-center">PRODUCTO</th>
									<th class="text-nowrap">FECHA DE VENCIMIENTO</th>
									<th class="text-nowrap text-center width-collapse">CANTIDAD</th>
                                    <th class="text-nowrap text-center">UNIDAD</th>
                                    <th class="text-nowrap text-center">PRECIO</th>
									<th class="text-nowrap text-center width-collapse">DESCUENTO</th>
									<th class="text-nowrap text-center width-collapse">IMPORTE</th>
									<th class="text-nowrap text-center width-collapse">ACCIONES</th>
								</tr>
							</thead>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="8">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal="">0.00</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</tfoot>
							<tbody></tbody>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;<?= $limite_longitud; ?>]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a <?= $limite_longitud; ?>">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;<?= $limite_monetario; ?>],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a <?= $limite_monetario; ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-12 text-right">
							<button type="submit" class="btn btn-success">Guardar</button>
							<button type="reset" class="btn btn-default">Restablecer</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="panel panel-success" data-servidor="<?= ip_local . name_project . '/nota.php'; ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
					<strong>Información sobre la transacción</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-warning">Información sobre la transacción</h2>
				<hr>
				<div class="table-display">
					<div class="tbody">
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-home"></span>
								<span>Casa matriz:</span>
							</div>
							<div class="td"><?= escape($_institution['nombre']); ?></div>
						</div>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-qrcode"></span>
								<span>NIT:</span>
							</div>
							<div class="td"><?= escape($_institution['nit']); ?></div>
						</div>
						<?php if ($_terminal) : ?>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-phone"></span>
								<span>Terminal:</span>
							</div>
							<div class="td"><?= escape($_terminal['terminal']); ?></div>
						</div>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-print"></span>
								<span>Impresora:</span>
							</div>
							<div class="td"><?= escape($_terminal['impresora']); ?></div>
						</div>
						<?php endif ?>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-user"></span>
								<span>Empleado:</span>
							</div>
							<div class="td"><?= ($_user['persona_id'] == 0) ? 'No asignado' : escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer text-center"><?= credits; ?></div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<!-- <div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-search"></span>
					<strong>Búsqueda de productos</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead">Búsqueda de productos</h2>
				<hr>
				<?php if ($permiso_mostrar) : ?>
				<p class="text-right">
					<a href="?/notas/mostrar" class="btn btn-warning">Mis notas de entrega</a>
				</p>
				<?php endif ?>
				<form method="post" action="?/notas/buscar" id="form_buscar_0" class="margin-bottom" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" value="" name="busqueda" class="form-control" placeholder="Buscar por código" autofocus="autofocus">
						<span class="glyphicon glyphicon-barcode form-control-feedback"></span>
					</div>
					<button type="submit" class="translate" tabindex="-1"></button>
				</form>
				<form method="post" action="?/notas/buscar" id="form_buscar_1" class="margin-bottom" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" value="" name="busqueda" class="form-control" placeholder="Buscar por código, producto o categoría">
						<span class="glyphicon glyphicon-search form-control-feedback"></span>
					</div>
					<button type="submit" class="translate" tabindex="-1"></button>
				</form>
				<div id="contenido_filtrar"></div>
			</div> -->
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
					<a href="?/notas/mostrar" class="btn btn-warning"></i><span> Ventas por nota</span></a>
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
                            <?php $otro_precio = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id=b.id_unidad')->where('a.producto_id',$producto['id_producto'])->fetch(); ?>
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

								<td class="text-right block " data-stock="<?= $producto['id_producto']; ?>" data-val-stock="<?= $producto['stock']; ?>">

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

								
								<td class="text-right" data-valor="<?= $producto['id_producto']; ?>">
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
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Proforma</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-danger">
					<p>Usted no puede realizar notas de entrega, verifique que la siguiente información sea correcta:</p>
					<ul>
						<li>El almacén principal no esta definido, ingrese al apartado de "almacenes" y designe a uno de los almacenes como principal.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
<h2 class="btn-warning position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es una nota de entrega" data-placement="right">
	<span class="glyphicon glyphicon-star display-cell"></span>
</h2>










<!-- Plantillas filtrar inicio -->
<!-- <div id="tabla_filtrar" class="hidden">
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap text-middle text-center">Imagen</th>
					<th class="text-nowrap text-middle text-center">Código</th>
					<th class="text-nowrap text-middle text-center">Producto</th>
					<th class="text-nowrap text-middle text-center">Color</th>					
					<th class="text-nowrap text-middle text-center">Tipo</th>
					<th class="text-nowrap text-middle text-center">Stock</th>
					<th class="text-nowrap text-middle text-center" width="18%">Precio</th>
					<th class="text-nowrap text-middle text-center">Acciones</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<table class="hidden">
	<tbody id="fila_filtrar" data-negativo="<?= imgs; ?>/" data-positivo="<?= files; ?>/productos/">
    <tr onclick="desp(this)">
			<td class="text-nowrap text-middle text-center">
				<img src="" class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="75" height="75">
			</td>
			<td class="text-nowrap text-middle" data-codigo="" ></td>
			<td id="desap_2" class="text-middle">
				<em></em>
				<span class="hidden" data-nombre=""></span>
			</td>
			<td class="text-nowrap text-middle desap_2"></td>
			<td class="text-nowrap text-middle text-right" data-stock="" ></td>
			<td class=" text-middle text-right" data-valor=""></td>
			<td class="text-nowrap text-middle text-center" >
				<button type="button" class="btn btn-warning" data-vender="" onclick="vender(this)" data-title="Vender"><span class="glyphicon glyphicon-shopping-cart"></span></button>
				<button type="button" class="btn btn-default" data-actualizar="" onclick="actualizar(this)"><span class="glyphicon glyphicon-refresh"></span></button>
			</td>
		</tr>
        <tr>
            <td colspan="6" class="text-nowrap text-middle text-center width-none" data-desc="">
                <em2></em2>
            </td>
        </tr>
	</tbody>
</table>
<div id="mensaje_filtrar" class="hidden">
	<div class="alert alert-danger">No se encontraron resultados</div>
</div> -->
<!-- Plantillas filtrar fin -->

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

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>
<script>
var carrito;

// funcion general para la busqueda y modales
$(function () {
	// definicion de variables globales
	var $cliente = $('#cliente');
	var $nit_ci = $('#nit_ci');
	var $nombre_cliente = $('#nombre_cliente');
	var $telefono_cliente = $('#telefono_cliente');
	var $formulario = $('#formulario');


	// inicia el datatable para el filtrado
	table = $('#productos').DataTable({
		info: false,
		scrollY: 508,
		lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
		order: []
	});

	// rellena el formulaario del cliente en caso de que exista y crea nueva instancia en caso de que no exista
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

	var blup = new buzz.sound('<?= media; ?>/blup.mp3');

	var $form_filtrar = $('#form_buscar_0, #form_buscar_1'), $contenido_filtrar = $('#contenido_filtrar'), $tabla_filtrar = $('#tabla_filtrar'), $fila_filtrar = $('#fila_filtrar'), $mensaje_filtrar = $('#mensaje_filtrar'), $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar');

	$form_filtrar.on('submit', function (e) {
		e.preventDefault();
		var $this, url, busqueda;
		$this = $(this);
		url = $this.attr('action');
		busqueda = $this.find(':text').val();
		$this.find(':text').attr('value', '');
		$this.find(':text').val('');
		if ($.trim(busqueda) != '') {
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: url,
				data: {
					busqueda: busqueda
				}
			}).done(function (productos) {
				if (productos.length) {
					var $ultimo;
                    var $ultimo2;
					$contenido_filtrar.html($tabla_filtrar.html());
					for (var i in productos) {
						productos[i].imagen = (productos[i].imagen == '') ? $fila_filtrar.attr('data-negativo') + 'image.jpg' : $fila_filtrar.attr('data-positivo') + productos[i].imagen;
						productos[i].codigo = productos[i].codigo;
						$contenido_filtrar.find('tbody').append($fila_filtrar.html());
						$contenido_filtrar.find('tbody tr:last').attr('data-busqueda', productos[i].id_producto);
                        $ultimo = $contenido_filtrar.find('tbody tr:nth-last-child(2)').children();
                        $ultimo2 = $contenido_filtrar.find('tbody tr:last').children();
                        $ultimo2.eq(0).find('em2').text(productos[i].descripcion);
						$ultimo.eq(0).find('img').attr('src', productos[i].imagen);
						$ultimo.eq(1).attr('data-codigo', productos[i].id_producto);
						$ultimo.eq(1).text(productos[i].codigo);
						$ultimo.eq(2).find('em').text(productos[i].nombre);
						$ultimo.eq(2).find('span').attr('data-nombre', productos[i].id_producto);
						$ultimo.eq(2).find('span').text(productos[i].nombre_factura);
						$ultimo.eq(3).text(productos[i].categoria);
                        var str = productos[i].unidade;

                        if(!str){
                            str='';
                            str = '*'+productos[i].unidad+':'+productos[i].precio_actual;
                        }else{
                            str = '*'+productos[i].unidad+':'+productos[i].precio_actual+'\n'+'*'+str;
                        }

                        var res = str.replace(/&/g, "\n*");
                        //console.log(res);
						$ultimo.eq(4).attr('data-stock', productos[i].id_producto);
						$ultimo.eq(4).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
						$ultimo.eq(5).attr('data-valor', productos[i].id_producto);
                        $ultimo.eq(5).text(res);
                        console.log($ultimo);
						$ultimo.eq(6).find(':button:first').attr('data-vender', productos[i].id_producto);
						$ultimo.eq(6).find(':button:last').attr('data-actualizar', productos[i].id_producto);

                    }
					if (productos.length == 1) {
					    $contenido_filtrar.find('table tbody tr button').trigger('click');
					}
					$.notify({
						message: 'La operación fue ejecutada con éxito, se encontraron ' + productos.length + ' resultados.'
					}, {
						type: 'success'
					});
					blup.stop().play();
				} else {
					$contenido_filtrar.html($mensaje_filtrar.html());
				}
			}).fail(function () {
				$contenido_filtrar.html($mensaje_filtrar.html());
				$.notify({
					message: 'La operación fue interrumpida por un fallo.'
				}, {
					type: 'danger'
				});
				blup.stop().play();
			});
		} else {
			$contenido_filtrar.html($mensaje_filtrar.html());
		}
	}).trigger('submit');

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
});

// funcion verificar si es nit
function es_nit(texto) {
    var numeros = '0123456789';
    for(i = 0; i < texto.length; i++){
        if (numeros.indexOf(texto.charAt(i), 0) != -1){
            return true;
        }
    }
    return false;
}

function desp(elemento) {
    $(elemento).next('tr').toggle();
}

/*function adicionar_producto(id_producto) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock = $.trim($('[data-stock=' + id_producto + ']').text());
	var valor = $.trim($('[data-valor=' + id_producto + ']').text());
	var plantilla = '';
	var cantidad;

    var posicion = valor.indexOf(':');
    var porciones = valor.split('*');

	if ($producto.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1 : cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
						'<td class="text-nowrap text-middle"><b>' + numero + '</b></td>' +
						'<td class="text-nowrap text-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td class="text-nowrap text-middle"><input type="text" value=\'' + nombre + '\' name="nombres[]" class="form-control" data-validation="required"></td>' +
						'<td class="text-middle"><input type="text" value="1" name="cantidades[]" class="form-control text-right" style="width: 100px;" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
		if(porciones.length>2){
            plantilla = plantilla+'<td><select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control " >';
            aparte = porciones[1].split(':');
            for(var ic=1;ic<porciones.length;ic++){
                parte = porciones[ic].split(':');
                //console.log(parte);
                plantilla = plantilla+'<option value="' +parte[0]+ '" data-yyy="' +parte[1]+ '" >' +parte[0]+ '</option>';
            }
            plantilla = plantilla+'</select></td>'+
            '<td><input type="text" value="' + parseFloat(aparte[1]) + '" name="precios[]" class="form-control  text-right" autocomplete="off" data-precio="' + parseFloat(aparte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
        }else{
            parte = porciones[1].split(':');
            plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" name="unidad[]" class="form-control  text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>'+
            '<td><input type="text" value="' + parseFloat(parte[1]) + '" name="precios[]" class="form-control  text-right" autocomplete="off" data-precio="' + parseFloat(parte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
        }
                        //'<td class="text-middle"><input type="text" value="' + valor + '" name="precios[]" class="form-control text-right" style="width: 100px;" autocomplete="off" data-precio="' + valor + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
                        plantilla = plantilla +'<td class="text-middle"><input type="text" value="0" name="descuentos[]" class="form-control text-right" style="width: 100px;" maxlength="10" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="float,range[-100.00;100.00],negative" data-validation-error-msg="Debe ser un número entre -100.00 y 100.00" onkeyup="descontar_precio(' + id_producto + ')"></td>' +
						'<td class="text-nowrap text-middle text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap text-middle text-center">' +
							'<button type="button" class="btn btn-warning" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')">Eliminar</button>' +
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
				guardar_nota();
			}
		});
	}

	calcular_importe(id_producto);
}*/
	$('[data-vender]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-vender')));
	});

function adicionar_item(fecha_ven, id_producto){
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());


	var color = $.trim($('[data-color=' + id_producto + ']').text());

	//var fecha =$('[data-fecha=' + id_producto + ']')[0].dataset.valFecha;
	var fechas = fecha_ven
	//console.log(fechas);


	var ingresos =$('[data-stock=' + id_producto + ']')[0].dataset.valI.split(',');
	var egresos =$('[data-stock=' + id_producto + ']')[0].dataset.valE.split(',');
	//console.log(ingresos, egresos);

	var stocks = new Array();
	for (let i = 0; i < ingresos.length; i++) {
		stocks[i] = parseInt(ingresos[i]) - parseInt(egresos[i]);
		//console.log(stocks[i]);
	}


    var valor = $.trim($('[data-valor=' + id_producto + ']').text());

    var posicion = valor.indexOf(':');
    var porciones = valor.split('*');
    console.log(color);
	var plantilla = '';
	var cantidad;

	plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
					'<td class="text-nowrap">' + numero + '</td>' +
					'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
					'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre +' '+color  +'</td>';

	// seleccionar fecha de vencimiento para agregar a la venta
	plantilla = plantilla+'<td><select name="fecha[]" id="fecha" class="form-control input-xs" onchange="actualizar_stock(event.target.value, ' + id_producto + ')">';
	for(var i=0;i<fechas.length ;i++){
			if(ingresos[i] - egresos[i] > 0) {
				plantilla = plantilla+ '<option value="' +fechas[i]+ '" >' +fechas[i]+ '</option>';
			}
		}
	plantilla = plantilla+'</select></td>';

	plantilla = plantilla+ '<td><input type="text" value="1" id="cantidades-' + id_producto + '" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stocks[0] + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stocks[0] + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
	if(porciones.length>2){
		plantilla = plantilla+'<td><select name="unidad[]" id="unidad" data-xxx="true" class="form-control input-xs" >';
		aparte = porciones[1].split(':');
		for(var ic=1;ic<porciones.length;ic++){
				parte = porciones[ic].split(':');
			//console.log(parte);
			plantilla = plantilla+'<option value="' +parte[0]+ '" data-yyy="' +parte[1]+ '" >' +parte[0]+ '</option>';
		}
		plantilla = plantilla+'</select></td>'+
		'<td><input type="text" value="' + parseFloat(aparte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(aparte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importeplantilla = plantilla+></td>';
	}
	else{
		parte = porciones[1].split(':');
		plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>'+
								'<td><input type="text" value="' + parseFloat(parte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(parte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
	}
					plantilla = plantilla +'<td><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="2" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="range[0;50]" data-validation-error-msg="Debe ser un número positivo entre 0 y 50" onkeyup="descontar_precio(' + id_producto + ')"></td>' +
					'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
					'<td class="text-nowrap text-center">' +
						'<button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip"  data-title="Otra fecha"  title=""  onclick="adicionar_item_fecha('+ id_producto+')"><span class="glyphicon glyphicon-plus"></span></button>'+
						'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
					'</td>' +
				'</tr>';

	$ventas.append(plantilla);
	//console.log(plantilla);
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
			guardar_nota();
		}
	});

}




function sincronizar_fechas(fecha){
	// definiendo base de la tabla
	var $ventas = $('#ventas tbody');
	// busca el dom venta - producto
	var $producto = $ventas.find('[data-producto=' + id_producto + ']').fecha;
	
	
	console.log($producto)

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
			'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stocks[posicion_stock] + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stocks[posicion_stock] + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
			if(porciones.length>2){
				plantilla = plantilla+'<td><select name="unidad[]" id="unidad" data-xxx="true" class="form-control input-xs" >';
				aparte = porciones[1].split(':');
				for(var ic=1;ic<porciones.length;ic++){
						parte = porciones[ic].split(':');
					//console.log(parte);
					plantilla = plantilla+'<option value="' +parte[0]+ '" data-yyy="' +parte[1]+ '" >' +parte[0]+ '</option>';
				}
				plantilla = plantilla+'</select></td>'+
				'<td><input type="text" value="' + parseFloat(aparte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(aparte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importeplantilla = plantilla+></td>';
			}
			else{
				parte = porciones[1].split(':');
				plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>'+
										'<td><input type="text" value="' + parseFloat(parte[1]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(parte[1]) + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
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
	sincronizar_fechas(id_producto, fechas);
	calcular_importe(id_producto);
}

// actualizar el stock por fecha de vencimiento
function actualizar_stock(numero, id_producto){
	// definiendo base de la tabla
	var $ventas = $('#ventas tbody');
	// recupera fecha seleccionada
	var fecha_seleccionada =  $ventas.find('[data-producto=' + id_producto + ']').find('#fecha' + numero + '  :selected').val();
	// busca el dom venta - producto
	var $producto = $ventas.find('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	// recupera un array de fechas de vencimiento
	var fechas =$('[data-fecha=' + id_producto + ']')[0].dataset.valFecha.split(',');
	// recupera un array de stocks
	var stocks =$('[data-stock=' + id_producto + ']')[0].dataset.valStock.split(',');
	// recupera posicion de fecha seleccionada
	var position = fechas.indexOf(fecha_seleccionada);
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
	$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
}

function guardar_nota() {
	var data = $('#formulario').serialize();
	//console.log(data);
	//$('#loader').fadeIn(100);

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/notas/guardar',
		data: data
	}).done(function (venta) {
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
			message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de entrega, verifique si la se guardó parcialmente.'
		}, {
			type: 'danger'
		});
	});
}

function imprimir_nota(nota) {
	var servidor = $.trim($('[data-servidor]').attr('data-servidor'));
	console.log(servidor,nota);
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: servidor,
		data: nota
	}).done(function (respuesta) {
		console.log(respuesta);
		$('#loader').fadeOut(100);
/*		switch (respuesta.estado) {
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
		}*/
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

function vender(elemento) {
    var $elemento = $(elemento), vender;
    vender = $elemento.attr('data-vender');
    adicionar_producto(vender);
}


function actualizar(elemento) {
	var $elemento = $(elemento), actualizar;
	actualizar = $elemento.attr('data-actualizar');
	//console.log(actualizar);	
	$('#loader').fadeIn(100);

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/notas/actualizar',
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