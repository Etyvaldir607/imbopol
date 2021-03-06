<?php

// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Verifica si existe el almacen
if ($id_almacen != 0) {
	// Obtiene los productos por fecha de vencimiento
	$productos = $db->query("
	select
    pf.id_producto,
    pf.color,
    pf.descripcion,
    pf.imagen,
    pf.codigo,
    pf.nombre,
    pf.nombre_factura,
    pf.cantidad_minima,
    GROUP_CONCAT(ifnull(tf.cantidad_ingresos, 0)) AS cantidad_ingresos,
    GROUP_CONCAT(ifnull(tf.cantidad_egresos, 0)) AS cantidad_egresos,
    GROUP_CONCAT(tf.fecha_vencimiento) AS fecha_vencimiento,
    GROUP_CONCAT(
        ifnull(tf.cantidad_ingresos, 0) - ifnull(tf.cantidad_egresos, 0)
    ) as stock,
    ta.id_asignacion,
    ta.id_unidad,
    ta.unidad,
    ta.cantidad_unidad,
    ta.id_precio,
    ta.precio,
    ta.stock AS stock_unidad,
    c.categoria
from
    inv_productos pf
    left join(
        select
            p.id_producto,
            ifnull(ti.cantidad_ingresos, 0) AS cantidad_ingresos,
            ifnull(te.cantidad_egresos, 0) AS cantidad_egresos,
            ti.fecha_vencimiento AS fecha_vencimiento,
            ifnull(
                ifnull(ti.cantidad_ingresos, 0) - ifnull(te.cantidad_egresos, 0),
                0
            ) as stock
        from
            inv_productos p
            left join (
                select
                    d.producto_id,
                    d.fecha_vencimiento,
                    sum(d.cantidad * a.cantidad_unidad) as cantidad_ingresos
                from
                    inv_ingresos_detalles d
                    left join inv_asignaciones a ON a.id_asignacion = d.asignacion_id
                    left join inv_ingresos i on i.id_ingreso = d.ingreso_id
                where
                    i.almacen_id = $id_almacen
                group by
                    d.producto_id,
                    d.fecha_vencimiento
            ) as ti on ti.producto_id = p.id_producto
            left join (
                select
                    d.producto_id,
                    d.fecha_vencimiento,
                    sum(d.cantidad * a.cantidad_unidad) as cantidad_egresos
                from
                    inv_proformas_detalles d
                    left join inv_asignaciones a ON a.id_asignacion = d.asignacion_id
                    left join inv_proformas p on p.id_proforma = d.proforma_id
                where
                    p.almacen_id = $id_almacen
                group by
                    d.producto_id,
                    d.fecha_vencimiento
            ) as te on te.producto_id = p.id_producto
            and ti.fecha_vencimiento = te.fecha_vencimiento
        where
            ifnull(
                ifnull(ti.cantidad_ingresos, 0) - ifnull(te.cantidad_egresos, 0),
                0
            ) >= 1
        order by
            ti.fecha_vencimiento
    ) as tf on tf.id_producto = pf.id_producto
    left JOIN (
        select
            p.id_producto,
            ifnull(ti.cantidad_ingresos, 0) AS stock,
            GROUP_CONCAT(ifnull(tu.id_asignacion, 0)) AS id_asignacion,
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
                order by
                    u.id_unidad asc
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
                    left join inv_asignaciones a ON a.id_asignacion = d.asignacion_id
                where
                    i.almacen_id = $id_almacen
                group by
                    d.producto_id
            ) as ti on ti.producto_id = p.id_producto
        GROUP BY
            p.id_producto
    ) as ta on ta.id_producto = pf.id_producto
    left join inv_categorias c on c.id_categoria = pf.categoria_id
where
    fecha_vencimiento IS NOT NULL
GROUP BY
    ta.id_producto
	")->fetch();
	//
	//$productos = $db->query("select p.id_producto,p.color, p.descripcion, p.imagen, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, p.unidad_id, ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos, ifnull(s.cantidad_egresos, 0) as cantidad_egresos, u.unidad, u.sigla, c.categoria from inv_productos p left join (select d.producto_id, sum(d.cantidad) as cantidad_ingresos from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id where i.almacen_id = $id_almacen group by d.producto_id ) as e on e.producto_id = p.id_producto left join (select d.producto_id, sum(d.cantidad) as cantidad_egresos from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = $id_almacen group by d.producto_id ) as s on s.producto_id = p.id_producto left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id")->fetch();
} else {
	$productos = null;
}




// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los clientes
$clientes = $db->select('cliente, nit, telefono, count(cliente) as nro_visitas')->from('inv_clientes')->group_by('cliente, nit, telefono')->order_by('cliente asc, nit asc')->fetch();
//$clientes = $db->query("select * from ((select nombre_cliente, nit_ci from inv_egresos) union (select nombre_cliente, nit_ci from inv_proformas)) c group by c.nombre_cliente, c.nit_ci order by c.nombre_cliente asc, c.nit_ci asc")->fetch();

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
					<strong>Proforma</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-success">Proforma</h2>
				<hr>
				<form id="formulario" class="form-horizontal">
					<div style="zoom: 1;">
						<div class="form-group">
							<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
							<div class="col-sm-8">
								<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true">
									<option value="">Buscar</option>
									<?php foreach ($clientes as $cliente) { ?>
									<option value="<?= escape($cliente['nit']) . '|' . escape($cliente['cliente']). '|' . escape($cliente['telefono']); ?>"><?= escape($cliente['nit']) . ' &mdash; ' . escape($cliente['cliente']); ?></option>
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
                            <label for="nombre_cliente" class="col-sm-4 control-label">Se??or(es):</label>
                            <div class="col-sm-8">
                                <input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
                            </div>
                        </div>
                        <div class="form-group hidden">
                            <label for="adelanto" class="col-md-4 control-label">Adelanto:</label>
                            <div class="col-md-8">
                                <input type="hidden" value="0" name="adelanto" id="adelanto" class="form-control" data-validation="required number" data-validation-allowing="float">
                            </div>
                        </div>
						<div class="form-group">
							<label for="telefono_cliente" class="col-sm-4 control-label">Tel??fono:</label>
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
									<th class="text-nowrap text-center width-collapse">C??DIGO</th>
									<th class="text-nowrap text-center">PRODUCTO</th>
                                    <th class="text-nowrap">FECHA DE VENCIMIENTO</th>
                                    <th class="text-nowrap text-center ">UNIDAD</th>
									<th class="text-nowrap text-center ">PRECIO</th>
									<th class="text-center width-collapse" width="8%">CANTIDAD</th>
									<th class="text-center width-collapse" width="8%">DESCUENTO</th>
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
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almac??n no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;<?= $limite_longitud; ?>]" data-validation-error-msg="El n??mero de productos a vender debe ser mayor a cero y menor a <?= $limite_longitud; ?>">
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
		<div class="panel panel-success" data-servidor="<?= ip_local . name_project . '/proforma.php'; ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
					<strong>Informaci??n sobre la transacci??n</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-success">Informaci??n sobre la transacci??n</h2>
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
						<?php if (true) : ?>
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
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-search"></span>
					<strong>B??squeda de productos</strong>
				</h3>
			</div>

			<div class="panel-body">
				<?php if ($permiso_mostrar) { ?>
				<div class="row">
					<div class="col-xs-12 text-right">
					<a href="?/proformas/mostrar" class="btn btn-success"></i><span>Mis proformas</span></a>
					</div>
				</div>
				<hr>
				<?php } ?>
				<?php if ($productos) { ?>
				<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
					<thead>
						<tr class="active">
							<th class="text-nowrap">Imagen</th>
							<th class="text-nowrap">C??digo</th>
							<th class="text-nowrap">Nombre</th>
                            <th class="text-nowrap">Descripci??n</th>
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
								<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>">
									<span><?= escape($producto['codigo']); ?></span>
									<span class="hidden"><?= escape($producto['codigo_barras']); ?></span>
								</td>
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

								<!--obtiene las asignaciones de unidad por producto, con sus respectivos costos -->
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
					<p>Usted no puede realizar proformas, verifique que la siguiente informaci??n sea correcta:</p>
					<ul>
						<li>El almac??n principal no esta definido, ingrese al apartado de "almacenes" y designe a uno de los almacenes como principal.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
<h2 class="btn-success position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es una proforma" data-placement="right">
	<span class="glyphicon glyphicon-edit display-cell"></span>
</h2>

<!-- Plantillas filtrar inicio -->
<div id="tabla_filtrar" class="hidden">
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap text-middle text-center width-none">Imagen</th>
					<th class="text-nowrap text-middle text-center">C??digo</th>
					<th class="text-nowrap text-middle text-center">Producto</th>
					<th class="text-nowrap text-middle text-center">Categor??a</th>
					<th class="text-nowrap text-middle text-center">Stock</th>
					<th class="text-middle text-center" width="18%">Precio</th>
					<th class="text-nowrap text-middle text-center width-none">Acciones</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<table class="hidden">
	<tbody id="fila_filtrar" data-negativo="<?= imgs; ?>/" data-positivo="<?= files; ?>/productos/">
		<tr onclick="desp(this)">
			<td class="text-nowrap text-middle text-center width-none">
				<img src="" class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="75" height="75">
			</td>
			<td class="text-nowrap text-middle" data-codigo=""></td>
			<td class="text-middle">
				<em></em>
				<span class="hidden" data-nombre=""></span>
			</td>
			<td class="text-nowrap text-middle"></td>
			<td class="text-nowrap text-middle text-right" data-stock=""></td>
			<td class=" text-middle text-right" data-valor=""></td>
			<td class="text-nowrap text-middle text-center width-none">
				<button type="button" class="btn btn-success" data-cotizar="" onclick="cotizar(this)">Cotizar</button>
				<button type="button" class="btn btn-default" data-actualizar="" onclick="actualizar(this)">Actualizar</button>
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
</div>
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
$(function () {
	var $cliente = $('#cliente');
	var $nit_ci = $('#nit_ci');
    var $nombre_cliente = $('#nombre_cliente');
	var $telefono_cliente = $('#telefono_cliente');
    var $adelanto = $('#adelanto');
	var $formulario = $('#formulario');

	// inicia el datatable para el filtrado
	var table = $('#productos').DataTable({
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

	// valida todo el formulario
	$.validate({
		form: '#formulario',
		modules: 'basic',
		onSuccess: function () {
			guardar_proforma();
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
		$telefono_cliente.prop('readonly', false);
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
						$ultimo.eq(4).attr('data-stock', productos[i].id_producto);
						$ultimo.eq(4).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
						$ultimo.eq(5).attr('data-valor', productos[i].id_producto);
						$ultimo.eq(5).text(res);
						$ultimo.eq(6).find(':button:first').attr('data-cotizar', productos[i].id_producto);
						$ultimo.eq(6).find(':button:last').attr('data-actualizar', productos[i].id_producto);
					}
					if (productos.length == 1) {
					    $contenido_filtrar.find('table tbody tr button').trigger('click');
					}
					$.notify({
						message: 'La operaci??n fue ejecutada con ??xito, se encontraron ' + productos.length + ' resultados.'
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
					message: 'La operaci??n fue interrumpida por un fallo.'
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

// funcion disparador de evento vender
$('[data-vender]').on('click', function () {
	adicionar_producto($.trim($(this).attr('data-vender')));
});


/** funcion adicionar producto */
function adicionar_producto(id_producto) {
	// definiendo base de la tabla
	var $ventas = $('#ventas tbody');
	// busca el dom venta - producto
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
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
	// recupera un array de unidades
	var unidades =$('[data-valor=' + id_producto + ']')[0].dataset.valUnidades.split(',');
	// recupera un array de cantidades
	var cantidades =$('[data-valor=' + id_producto + ']')[0].dataset.valCantidades.split(',');
	// recupera un array de precios
	var precios =$('[data-valor=' + id_producto + ']')[0].dataset.valPrecios.split(',');
	// recupera un contador para cada producto
	var contador = parseInt($('[data-fecha=' + id_producto + ']')[0].dataset.contador);
	var limit = parseInt($('[data-valor=' + id_producto + ']')[0].dataset.limit);
	
	var posicion_stock = contador;

    var valor = $.trim($('[data-valor=' + id_producto + ']').text());
	//console.log(valor)
    var posicion = valor.indexOf(':');
    var porciones = valor.split('-');

	var plantilla = '';
	var cantidad;

	if (contador < fechas.length + limit) {
		plantilla =
		'<tr class="active" data-producto="' + id_producto + '" data-position="'+numero+'">'+
			'<td class="text-nowrap">' + numero + '</td>'+
			'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser n??mero">' + codigo + '</td>'+
			'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre +' '+ color  +'</td>'+
		
			// seleccionar fecha de vencimiento para agregar a la venta
			'<td>'+
				'<select name="fecha[]" id="fecha' + numero + '" class="form-control input-xs" onchange="actualizar_stock(' + numero + ',' + id_producto + ')">';
					for(var i = 0; i < fechas.length; i++){
						if(i === contador ){
							// selecciona la primera fecha por defecto
							plantilla = plantilla+ '<option value="' +fechas[i]+ '" data-fecha="' +fechas[i]+ '" data-stock="' +stocks[i]+ '"selected>' +fechas[i]+ '</option>';
						}else{
							plantilla = plantilla+ '<option value="' +fechas[i]+ '" data-fecha="' +fechas[i]+ '" data-stock="' +stocks[i]+ '">' +fechas[i]+ '</option>';
						}
					}
					plantilla = plantilla +
				'</select>'+
			'</td>';

			// seleccionar unidad de venta
			if(unidades.length > 1 ){
				plantilla = plantilla +
				'<td>'+
					'<select name="unidad[]" id="unidad' + numero + '"  data-xxx="true" class="form-control input-xs" onchange="actualizar_stock(' + numero + ',' + id_producto + ')">';
						for(var c = 0; c < unidades.length; c++){
							if(c === 0 ){
								plantilla = plantilla+ '<option value="' + unidades[c] + '" data-yyy="' +precios[c]+ '" data-unidad="' +unidades[c]+ '" data-cantidad-unidad="' +cantidades[c]+ '"selected>' +unidades[c]+ '</option>';
							}else{
								plantilla = plantilla+ '<option value="' + unidades[c] + '" data-yyy="' +precios[c]+ '" data-unidad="' +unidades[c]+ '" data-cantidad-unidad="' +cantidades[c]+ '">' +unidades[c]+ '</option>';
							}
						}
						plantilla = plantilla +
					'</select>'+
				'</td>';
				plantilla = plantilla+ '<td><input type="text" value="' + parseFloat(precios[0]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(precios[0]) + '"  data-validation-error-msg="Debe ser un n??mero decimal positivo" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';
			}
			else{
				plantilla = plantilla + '<td><input type="text" value="' + unidades[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + unidades[0] + '" readonly data-validation-error-msg="Debe ser un n??mero decimal positivo"></td>'+
										'<td><input type="text" value="' + parseFloat(precios[0]) + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parseFloat(precios[0])+ '"  data-validation-error-msg="Debe ser un n??mero decimal positivo" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';
			}
			plantilla = plantilla +
			'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stocks[posicion_stock] + ']" data-validation-error-msg="Debe ser un n??mero positivo entre 1 y ' + stocks[posicion_stock] + '" onkeyup="calcular_importe('+numero +',' + id_producto + ')"></td>';

			plantilla = plantilla + 
			'<td><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="2" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="range[0;50]" data-validation-error-msg="Debe ser un n??mero positivo entre 0 y 50" onkeyup="descontar_precio('+numero +',' + id_producto + ')"></td>'+
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
            // $(this).parent().parent().find('[data-precio]').attr(parseFloat(v));
            calcular_importe(numero, id_producto);
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
				guardar_proforma();
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
	// busca el dom venta - producto
	var $producto = $ventas.find('[data-producto=' + id_producto + '][data-position='+numero+ ']');
	// recupera stock seleccionado
	var stock_seleccionado =  $ventas.find('[data-producto=' + id_producto + ']').find('#fecha' + numero + ' :selected').attr('data-stock');
	// recupera cantidad que contiene tipo de unidad seleccionada
	var cantidad_seleccionada =  $ventas.find('[data-producto=' + id_producto + ']').find('#unidad' + numero + ' :selected').attr('data-cantidad-unidad');
	// recupera unidad seleccionada
	var unidad_seleccionada =  $ventas.find('[data-producto=' + id_producto + ']').find('#unidad' + numero + ' :selected').attr('data-unidad');
	// recupera un array de fechas de vencimiento
	var fechas =$('[data-fecha=' + id_producto + ']')[0].dataset.valFecha.split(',');
	// recupera un array de stocks
	var stocks =$('[data-stock=' + id_producto + ']')[0].dataset.valStock.split(',');
	// recupera posicion de fecha seleccionada
	var position = fechas.indexOf(fecha_seleccionada);
	// calcular cantidad ya usada
	// var cantidad_asignada = calcular_asignaciones(id_producto, fecha_seleccionada);
	// define cantidad minima para la fila
	var cantidad_limite_celda = parseInt(stock_seleccionado/cantidad_seleccionada);
	//actualizando limite
	$producto.find('[data-cantidad]').attr("data-validation-allowing", 'range[1;' + cantidad_limite_celda + ']')
	//actulaizando msg de error
	$producto.find('[data-cantidad]').attr("data-validation-error-msg", 'Debe ser un n??mero positivo entre 1 y ' + cantidad_limite_celda );
//adicionar_item(fechas, id_producto);
}


function eliminar_producto(id_producto) {
	bootbox.confirm('Est?? seguro que desea eliminar el producto?', function (result) {
		if(result){
			$('[data-producto=' + id_producto + ']').remove();
			renumerar_productos();
			calcular_total();
		}
	});
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
	//console.log($cantidad.val());
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

function guardar_proforma() {
	var data = $('#formulario').serialize();
	$('#loader').fadeIn(100);
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/proformas/guardar',
		data: data
	}).done(function (proforma) {
		if (proforma) {
			$.notify({
				message: 'La proforma fue realizada satisfactoriamente.'
			}, {
				type: 'success'
			});
			generar_pdf_cotizacion(proforma.id_proforma);
		} else {
			
			$('#loader').fadeOut(100);
			$.notify({
				message: 'Ocurri?? un problema en el proceso, no se puedo guardar los datos de la proforma, verifique si la se guard?? parcialmente.'
			}, {
				type: 'danger'
			});
			
		}
	}).fail(function () {
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurri?? un problema en el proceso, no se puedo guardar los datos de la proforma, verifique si la se guard?? parcialmente.'
		}, {
			type: 'danger'
		});
	}).always(function (){
		$('#formulario :reset').trigger('click');
		window.location.reload();
	});
}


/**  inicia la peticion para generar pdf de la compra */
function generar_pdf_cotizacion(id) {
	$id_proforma = parseInt(id);
	url = '?/proformas/imprimir/' + $id_proforma;
	//console.log(url)
	window.open(url,'_blank');
}

function imprimir_proforma(proforma) {
	var servidor = $.trim($('[data-servidor]').attr('data-servidor'));
	$('#loader').fadeOut(100);
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: servidor,
		data: proforma
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
		/*
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurri?? un problema durante el proceso, reinicie la terminal para dar soluci??n al problema y si el problema persiste contactese con el con los desarrolladores.'
		}, {
			type: 'danger'
		});
		*/
	}).always(function () {
		$('#formulario').trigger('reset');
		$('#form_buscar_0').trigger('submit');
	});
}

function cotizar(elemento) {
	var $elemento = $(elemento), vender;
	vender = $elemento.attr('data-vender');
	adicionar_producto(vender);
}

function actualizar(elemento) {
	var $elemento = $(elemento), actualizar;
	actualizar = $elemento.attr('data-actualizar');
		
	$('#loader').fadeIn(100);

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/proformas/actualizar',
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

			if ($producto.size()) {
				$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
				$cantidad.attr('data-validation-error-msg', 'Debe ser un n??mero positivo entre 1 y ' + stock);
				$precio.val(precio);
				$precio.attr('data-precio', precio);
				descontar_precio(producto.id_producto);
			}

			$.notify({
				message: 'El stock y el precio del producto se actualizaron satisfactoriamente.'
			}, {
				type: 'success'
			});
		} else {
			$.notify({
				message: 'Ocurri?? un problema durante el proceso, es posible que no existe un almac??n principal.'
			}, {
				type: 'danger'
			});
		}
	}).fail(function () {
		$.notify({
			message: 'Ocurri?? un problema durante el proceso, no se pudo actualizar el stock ni el precio del producto.'
		}, {
			type: 'danger'
		});
	}).always(function () {
		$('#loader').fadeOut(100);
	});
}
</script>
<?php require_once show_template('footer-empty'); ?>