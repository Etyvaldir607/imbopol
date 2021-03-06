<?php

// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;

// Verifica si hay parametros
if ($id_almacen == 0) {
	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();
} else {
	// Obtiene los id_almacen
	$id_almacen = explode('-', $id_almacen);

	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->where_in('id_almacen', $id_almacen)->order_by('id_almacen')->fetch();
}

// Verifica si existen almacenes
if (!$almacenes) {
	// Error 404
	require_once not_found();
	exit;
}

// Genera la consulta
$select = "select p.id_producto, p.codigo, p.nombre, p.nombre_factura, p.descripcion, p.color, p.cantidad_minima, u.unidad, u.sigla, c.categoria";
$from = " from inv_productos p left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id";
$join = "";

// recorre los almacenes
foreach ($almacenes as $nro => $almacen) {
	$id = $almacen['id_almacen'];
	$select = $select . ", ifnull(e$id.ingresos$id, 0) as ingresos$id, ifnull(s$id.egresos$id, 0) as egresos$id";
	$join = $join . " left join (select d.producto_id, sum(d.cantidad * a.cantidad_unidad) as ingresos$id from inv_ingresos_detalles d left join inv_asignaciones a on a.unidad_id = d.unidad_id and a.producto_id = d.producto_id and a.estado='a' left join inv_ingresos i on i.id_ingreso = d.ingreso_id where i.almacen_id = $id group by d.producto_id) as e$id on e$id.producto_id = p.id_producto";
	$join = $join . " left join (select d.producto_id, sum(d.cantidad * a.cantidad_unidad) as egresos$id from inv_egresos_detalles d left join inv_asignaciones a on a.unidad_id = d.unidad_id and a.producto_id = d.producto_id and a.estado='a' left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = $id group by d.producto_id) as s$id on s$id.producto_id = p.id_producto";
}

// Arma la consulta
$query = $select . $from . $join . "";

// Obtiene las lista de productos y los stocks en cada almacen
$productos = $db->query($query)->fetch();

// Obtiene los ubicaciones
$ubicaciones = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();

?>
<?php require_once show_template('header-advanced'); ?>
<style>
.table-xs tbody {
	font-size: 12px;
}
.width-sm {
	min-width: 150px;
}
.width-md {
	min-width: 200px;
}
.width-lg {
	min-width: 250px;
}
</style>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Reporte de existencias por almac??n</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($productos) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar de almac??n hacer clic en el siguiente bot??n: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<button type="button" class="btn btn-primary" data-cambiar="true">
				<span class="glyphicon glyphicon-refresh"></span>
				<span>Cambiar</span>
			</button>
		</div>
	</div>
	<hr>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">C??digo</th>
				<th class="text-nowrap">Nombre del producto</th>
                <th class="text-nowrap">Medidas</th>
                <th class="text-nowrap">Color</th>
                <th class="text-nowrap">Tipo</th>
				<th class="text-nowrap">M??nimo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap"><?= $almacen['almacen']; ?></th>
				<?php } ?>
				<th class="text-nowrap">Total existencias</th>
				<th class="text-nowrap">Unidad</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="true">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">C??digo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre del producto</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Medidas</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Color</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">M??nimo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="true"><?= $almacen['almacen']; ?></th>
				<?php } ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Total existencias</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad</th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($productos as $nro => $producto) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($producto['codigo']); ?></td>
				<td class="width-lg"><?= escape($producto['nombre']); ?></td>
                <td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
                <td class="text-nowrap"><?= escape($producto['color']); ?></td>
                <td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
				<td class="text-nowrap text-right"><?= escape($producto['cantidad_minima']); ?></td>
				<?php $total = 0; ?>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<?php $stock = escape($producto['ingresos' . $almacen['id_almacen']] - $producto['egresos' . $almacen['id_almacen']]); ?>
				<td class="text-nowrap text-right"><strong class="<?= ($stock < escape($producto['cantidad_minima'])) ? 'text-danger' : 'text-success'; ?>"><?= $stock; ?></strong></td>
				<?php $total = $total + $stock; ?>
				<?php } ?>
				<td class="text-nowrap text-right"><strong class="text-primary"><?= $total; ?></strong></td>
				<td class="text-nowrap"><?= escape($producto['unidad']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>El inventario no puede mostrarse por que no existen productos registrados.</p>
	</div>
	<?php } ?>
</div>

<!-- Inicio modal almacen-->
<?php if ($productos) { ?>
<div id="modal_almacen" class="modal fade">
	<div class="modal-dialog">
		<form id="form_almacen" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Cambiar almac??n</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<?php foreach ($ubicaciones as $nro => $almacen) { ?>
							<div class="checkbox">
								<label>
									<input type="checkbox" value="<?= escape($almacen['id_almacen']); ?>" data-seleccion="<?= escape($almacen['id_almacen']); ?>">
									<span><?= escape($almacen['almacen']); ?></span>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary" data-aceptar="true">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Aceptar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_almacen" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<!-- Fin modal almacen-->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
$(function () {	
	<?php if ($productos) { ?>
		var table = $('#table').DataFilter({
		filter: true,
		name: 'reporte_de_existencias',
		reports: 'excel|word|pdf|html',
		size: 8
	});

	var $modal_almacen = $('#modal_almacen');
	var $form_almacen = $('#form_almacen');

	$form_almacen.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_almacen.find('[data-cancelar]').on('click', function () {
		$modal_almacen.modal('hide');
	});

	$modal_almacen.find('[data-aceptar]').on('click', function () {
		var almacenes = [];

		$('[data-seleccion]:checked').each(function () {
			almacenes.push($(this).attr('data-seleccion'));
		});

		almacenes = almacenes.join('-');
		$modal_almacen.modal('hide');
		
		if (almacenes != '') {
			window.location = '?/existencias/listar/' + almacenes;
		} else {
			window.location = '?/existencias/listar';
		}
		
	});

	$('[data-cambiar]').on('click', function () {
		$modal_almacen.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>