<?php

foreach ($_SESSION as $key) {
		  if($key['rol_id'] == '2' || $key['rol_id'] == '1') 
		  	$rol_id =$key['rol_id'];
		 elseif ($key['rol_id'] == '3' || $key['rol_id'] == '4') {
		 	 $rol_id =$key['rol_id'];
		 }
	}
if ($rol_id <= 2) {
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

// Obtiene los ingresos
$ingresos = $db->select('i.id_ingreso,d.producto_id,d.costo,p.codigo,i.fecha_ingreso,i.hora_ingreso,d.producto_id,p.nombre_factura,p.color,i.tipo,i.nombre_proveedor,i.descripcion,i.monto_total,u.username,u.rol_id,r.rol')->from('inv_ingresos_detalles d ')->join('inv_ingresos i', 'd.ingreso_id=i.id_ingreso', 'left')->join('sys_users u', 'u.persona_id=i.empleado_id', 'left')->join('inv_productos p','d.producto_id=p.id_producto','left')->where('u.rol_id>', '2')->join('sys_roles r','r.id_rol=u.rol_id','left')->where('i.fecha_ingreso >= ', $fecha_inicial)->where('i.fecha_ingreso <= ', $fecha_final)->group_by('d.producto_id')->order_by('i.fecha_ingreso desc, i.hora_ingreso desc')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
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
		<strong>Listado de productos ingresados por vendedores - Edicion de Costo de Producto</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear || $permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para editar el costo de los productos ingresados seleccionar el rango de fechas; para ello hacer clic en el siguiente bot??n: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_cambiar) { ?>
			<button class="btn btn-default" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar</span></button>
			<?php } ?>
<!-- 			<?php if ($permiso_imprimir) { ?>
			<a href="?/ingresos/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
			<?php } ?> -->
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
	<?php if ($ingresos) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Ingreso</th>
				<th class="text-nowrap">Codigo de Prod.</th>
				<th class="text-nowrap">Nombre de Producto y color</th>
				<th class="text-nowrap">Descripcion</th>
				<th class="text-nowrap">Costo <?= escape($moneda); ?></th>
				<th class="text-nowrap">Tipo de Ingreso</th>
				<th class="text-nowrap">Proveedor</th>
				<th class="text-nowrap">Descripci??n</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Ingreso</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Codigo de Prod.</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre de Prod.</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Color</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Costo <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo de Ingreso</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Proveedor</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Descripci??n</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($ingresos as $nro => $ingreso) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape(date_decode($ingreso['fecha_ingreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($ingreso['hora_ingreso']); ?></small></td>
				<td class="text-nowrap" data-codigo="<?= $ingreso['producto_id']; ?>"><?= escape($ingreso['codigo']); ?></td>
				<td class="text-nowrap"><?= escape($ingreso['nombre_factura']. ' ' . $ingreso['color']); ?></td>
				<td class="width-md"><?= escape(strtoupper($ingreso['descripcion'])); ?></td>
				<td class="text-nowrap text-right <?= ($ingreso['costo'] == '0') ? 'danger' : 'success'; ?> "><?= escape($ingreso['costo']); ?></td>
				<td class="text-nowrap text-right"><?= escape(strtoupper($ingreso['tipo'])); ?></td>
				<td class="text-nowrap text-right"><?= escape(strtoupper($ingreso['nombre_proveedor'])); ?></td>
				<td class="width-md"><?= escape( strtoupper($ingreso['rol']) . ' ' . strtoupper($ingreso['username'])); ?></td>
<!-- 				<td class="text-nowrap <?= ($ingreso['principal'] == 'S') ? 'info' : ''; ?>"><?= escape($ingreso['almacen']); ?></td>
				<td class="width-md"><?= escape($ingreso['nombres'] . ' ' . $ingreso['paterno'] . ' ' . $ingreso['materno']); ?></td>
 -->				
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<td class="text-nowrap">
			<!-- 		<?php if ($permiso_ver) { ?>
					<a href="?/ingresos/editar_producto/<?= $ingreso['producto_id']; ?>" data-toggle="tooltip" data-title="Editar costo de producto"><i class="glyphicon glyphicon-edit"></i></a>
					<?php } ?> -->
					<?php if ($permiso_eliminar) { ?>
					<button type="button" class="btn btn-xs btn-success" data-toggle="tooltip" data-title="Editar producto" tabindex="-1" onclick="editar_costo('<?= $ingreso["producto_id"] ?>')"><i class="glyphicon glyphicon-edit"></i></button>		
					<!-- <a onclick="editar_costo(<?= $ingreso['codigo'] ?>)" data-toggle="tooltip" data-title="Editar costo de producto" ><i class="glyphicon glyphicon-edit"></i></a> -->
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen ingresos registrados en la base de datos.</p>
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
	var formato = $('[data-formato]').attr('data-formato');
	var mascara = $('[data-mascara]').attr('data-mascara');
	var gestion = $('[data-gestion]').attr('data-gestion');
	var $inicial_fecha = $('#inicial_fecha');
	var $final_fecha = $('#final_fecha');

	function editar_costo(codigo) {
		bootbox.prompt({
			title: 'Est?? seguro que desea modificar el costo del producto?', 
			required: true,
			inputType: 'number',
			placeholder: "Introducir el nuevo costo del producto",
			callback: function (result) {
			if(result){

			var inicial_fecha = $.trim($('#inicial_fecha').val());
			var final_fecha = $.trim($('#final_fecha').val());
			//console.log(inicial_fecha,final_fecha);
			var vacio = gestion.replace(new RegExp('9', 'g'), '0');

			inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
			inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
			vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
			vacio = vacio.replace(new RegExp('/', 'g'), '-');
			final_fecha = (final_fecha != '') ? (final_fecha ) : '';
			inicial_fecha = (inicial_fecha != '') ? (inicial_fecha) : ((final_fecha != '') ? (vacio) : ''); 
			var datos = {
							"fechai":inicial_fecha,
							"fechaf":final_fecha,
							"codigo":codigo,
							"precio":result
			}
			console.log(datos);
				    $.ajax({
				        type: 'post',
				        dataType: 'json',
				        url: '?/ingresos/editar_producto',
				        data: datos
				    }).done(function (objeto) {
				    	console.log(objeto);
				    	 location.reload();
				    }).fail(function () {
				        console.log('Error..');
				    });				
			}
		}
	});
	}



$(function () {	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/ingresos/crear';
				break;
			}
		}
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
			console.log(inicial_fecha);
			var vacio = gestion.replace(new RegExp('9', 'g'), '0');

			inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
			inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
			vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
			vacio = vacio.replace(new RegExp('/', 'g'), '-');
			final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
			inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : ''); 
			
			window.location = '?/ingresos/editar_costo' + inicial_fecha + final_fecha;
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
	
	<?php if ($ingresos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'ingresos',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});
</script>
<?php } require_once show_template('footer-advanced'); ?>