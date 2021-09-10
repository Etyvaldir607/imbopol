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

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene las ventas
$ventas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('i.tipo', 'Venta')->where('i.codigo_control', '')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = in_array('manuales_ver', $permisos);
$permiso_eliminar = in_array('manuales_eliminar', $permisos);
$permiso_cambiar = true;

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
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista de todas las ventas manuales </strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar la fecha hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
            <button class="btn btn-primary" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar fecha</span></button>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($ventas) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
                <th class="text-nowrap">ESPECIFICACION</th>
                <th class="text-nowrap">N°</th>
				<th class="text-nowrap">FECHA DE LA FACTURA</th>
				<th class="text-nowrap">N° DE LA FACTURA</th>
				<th class="text-nowrap">N° DE AUTORIZACION</th>
				<th class="text-nowrap">ESTADO</th>
                <th class="text-nowrap">NIT/CLIENTE</th>
                <th class="text-nowrap">NOMBRE O RAZON SOCIAL</th>
				<th class="text-nowrap">IMPORTE TOTAL DE LA VENTA <?= escape($moneda); ?></th>
                <th class="text-nowrap">IMPORTE ICE/IEHD/IPJ/TASAS/OTROS NO SUJETOS AL IVA</th>
                <th class="text-nowrap">EXPORTACIONES Y OPERACIONES EXENTAS</th>
                <th class="text-nowrap">VENTAS GRAVADAS A TASA CERO</th>
                <th class="text-nowrap">SUBTOTAL</th>
                <th class="text-nowrap">DESCUENTOS, BONIFICACIONES Y REBAJAS SUJETAS AL IVA</th>
                <th class="text-nowrap">IMPORTE BASE PARA DEBITO FISCAL</th>
                <th class="text-nowrap">DEBITO FISCAL</th>
				<th class="text-nowrap">CODIGO DE CONTROL</th>
                <?php if ($permiso_ver) { ?>
                    <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                <?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
                <th class="text-nowrap text-middle" data-datafilter-filter="true">ESPECIFICACION</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">N°</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">FECHA DE LA FACTURA</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">N° DE LA FACTURA</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">N° DE AUTORIZACION</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">ESTADO</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CLIENTE</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">NOMBRE O RAZON SOCIAL</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">IMPORTE TOTAL DE LA VENTA <?= escape($moneda); ?></th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">IMPORTE ICE/IEHD/IPJ/TASAS/OTROS NO SUJETOS AL IVA</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">EXPORTACIONES Y OPERACIONES EXENTAS</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">VENTAS GRAVADAS A TASA CERO</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">SUBTOTAL</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">DESCUENTOS, BONIFICACIONES Y REBAJAS SUJETAS AL IVA</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">IMPORTE BASE PARA DEBITO FISCAL</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">DEBITO FISCAL</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">CODIGO DE CONTROL</th>
                <?php if ($permiso_ver) { ?>
                    <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                <?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($ventas as $nro => $venta) { ?>
			<tr>
                <th class="text-nowrap"><?= 3; ?></th>
                <th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?></td>
				<td class="text-nowrap"><?= escape($venta['nro_factura']); ?></td>
				<td class="text-nowrap"><?= escape($venta['nro_autorizacion']); ?></td>
				<td class="text-nowrap"><?php if($venta['nit_ci']==0){ echo 'A'; }else{echo 'V';}; ?></td>
				<td class="text-nowrap text-right"><?= escape($venta['nit_ci']); ?></td>
                <td class="text-nowrap text-right"><?= escape($venta['nombre_cliente']); ?></td>
                <td class="text-nowrap text-right"><?= escape($venta['monto_total']); ?></td>
                <td class="text-nowrap text-right"><?= round(0,2) ?></td>
                <td class="text-nowrap text-right"><?= round(0,2) ?></td>
                <td class="text-nowrap text-right"><?= round(0,2) ?></td>
                <td class="text-nowrap text-right"><?= escape($venta['monto_total']); ?></td>
                <td class="text-nowrap text-right"><?= round(0,2) ?></td>
                <td class="text-nowrap text-right"><?= escape($venta['monto_total']); ?></td>
                <td class="text-nowrap text-right"><?= escape($venta['monto_total']*0.13); ?></td>
                <td class="text-nowrap text-right"></td>
                    <td class="text-nowrap">
                        <?php if ($permiso_ver) { ?>
                            <a href="?/operaciones/manuales_ver/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de venta"><i class="glyphicon glyphicon-list-alt"></i></a>
                        <?php } ?>
                        <?php if ($permiso_eliminar) { ?>
                            <a href="?/operaciones/manuales_eliminar/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Eliminar venta" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
                        <?php } ?>
                        <?php if ($permiso_eliminar) { ?>
                            <a href="?/manuales/editar_venta/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Modificar venta"><i class="glyphicon glyphicon-edit"></i></a>
                        <?php } ?>
                    </td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen ventas electrónicas registrados en la base de datos.</p>
	</div>
	<?php } ?>
</div>

<!-- Inicio modal fecha -->
<?php if ($permiso_cambiar) { ?>
<div id="modal_fecha" class="modal fade">
	<div class="modal-dialog">
		<form id="form_fecha" class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Cambiar fecha</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="inicial_fecha">Fecha inicial:</label>
							<input type="text" name="inicial" value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="final_fecha">Fecha final:</label>
							<input type="text" name="final" value="<?= ($fecha_final != $gestion_limite) ? date_decode($fecha_final, $_institution['formato']) : ''; ?>" id="final_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-aceptar="true">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Aceptar</span>
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
<!-- Fin modal fecha -->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
    <?php if ($permiso_eliminar) { ?>
    $('[data-eliminar]').on('click', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        bootbox.confirm('Está seguro que desea eliminar la proforma y todo su detalle?', function (result) {
            if(result){
                window.location = url;
            }
        });
    });
    <?php } ?>
	<?php if ($permiso_cambiar) { ?>
	var formato = $('[data-formato]').attr('data-formato');
	var mascara = $('[data-mascara]').attr('data-mascara');
	var gestion = $('[data-gestion]').attr('data-gestion');
	var $inicial_fecha = $('#inicial_fecha');
	var $final_fecha = $('#final_fecha');

	$.validate({
		form: '#form_fecha',
		modules: 'date',
		onSuccess: function () {
			var inicial_fecha = $.trim($('#inicial_fecha').val());
			var final_fecha = $.trim($('#final_fecha').val());
			var vacio = gestion.replace(new RegExp('9', 'g'), '0');

			inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
			inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
			vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
			vacio = vacio.replace(new RegExp('/', 'g'), '-');
			final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
			inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : ''); 
			
			window.location = '?/operaciones/listar_manuales' + inicial_fecha + final_fecha;
		}
	});

	//$inicial_fecha.mask(mascara).datetimepicker({
	$inicial_fecha.datetimepicker({
		format: formato
	});

	//$final_fecha.mask(mascara).datetimepicker({
	$final_fecha.datetimepicker({
		format: formato
	});

	$inicial_fecha.on('dp.change', function (e) {
		$final_fecha.data('DateTimePicker').minDate(e.date);
	});
	
	$final_fecha.on('dp.change', function (e) {
		$inicial_fecha.data('DateTimePicker').maxDate(e.date);
	});

	var $form_fecha = $('#form_fecha');
	var $modal_fecha = $('#modal_fecha');

	$form_fecha.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_fecha.on('show.bs.modal', function () {
		$form_fecha.trigger('reset');
	});

	$modal_fecha.on('shown.bs.modal', function () {
		$modal_fecha.find('[data-aceptar]').focus();
	});

	$modal_fecha.find('[data-cancelar]').on('click', function () {
		$modal_fecha.modal('hide');
	});

	$modal_fecha.find('[data-aceptar]').on('click', function () {
		$form_fecha.submit();
	});

	$('[data-cambiar]').on('click', function () {
		$('#modal_fecha').modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	
	<?php if ($ventas) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'reporte_ventas_manuales',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>