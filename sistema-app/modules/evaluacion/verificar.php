<?php

// Importa la libreria para generar el reporte
require_once libraries . '/controlcode-class/ControlCode.php';

// Instancia el objeto para la generacion de los codigos de control
$controlCode = new ControlCode();

// Obtiene los datos
$datos = $_POST;

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title" data-header="true">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Certificación del sistema</b>
	</h3>
</div>
<div class="panel-body">
	<div class="alert alert-warning">Llene los campos con los datos obtenidos del sistema de "Impuestos Nacionales", generar los códigos de control y copiar en los campos correspondientes del sistema de "Impuestos Nacionales".</div>
	<div class="well cursor-pointer" data-copiar="true">
		<div class="table-display">
			<div class="tbody">
				<div class="tr">
					<div class="th text-nowrap">Número de autorización:</div>
					<div class="td text-ellipsis">105401600000570</div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">Número de factura:</div>
					<div class="td text-ellipsis">378</div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">NIT/CI del cliente:</div>
					<div class="td text-ellipsis">1373988</div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">Fecha de la transacción:</div>
					<div class="td text-ellipsis">11/04/2016</div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">Monto de la transacción:</div>
					<div class="td text-ellipsis">11837,61</div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">Llave de dosificación:</div>
					<div class="td text-ellipsis">%K@-BsG9L=]62#+D=W\HtNZY-Fi%CMmNs5GEDPWfE7I6eKB%53Z%PIT6kbY464ww</div>
				</div>
				<div class="tr">
					<div class="th text-nowrap">Código de control:</div>
					<div class="td text-ellipsis">FB-91-4C-A8-4E</div>
				</div>
			</div>
		</div>
	</div>
	<p class="lead text-primary">Datos para la certificación del código de control</p>
	<form method="post" action="?/evaluacion/verificar" class="margin-none">
		<div class="table-responsive">
			<table class="table table-bordered table-condensed">
				<tr class="active">
					<th class="text-nowrap text-middle width-collapse text-right">#</th>
					<th class="text-nowrap text-middle width-collapse">Número de autorización</th>
					<th class="text-nowrap text-middle width-collapse">Número de factura</th>
					<th class="text-nowrap text-middle width-collapse">NIT/CI del cliente</th>
					<th class="text-nowrap text-middle width-collapse">Fecha de la transacción</th>
					<th class="text-nowrap text-middle width-collapse">Monto de la transacción</th>
					<th class="text-nowrap text-middle">Llave de dosificación</th>
					<th class="text-nowrap text-middle width-collapse">Código de control</th>
				</tr>
				<?php for ($i = 0; $i < 10; $i = $i + 1) : ?>
				<?php
				if ($datos) {
					$autorizacion = $datos['autorizacion'][$i];
					$factura = $datos['factura'][$i];
					$nitci = $datos['nitci'][$i];
					$fecha = $datos['fecha'][$i];
					$fecha = explode('/', $fecha);
					$fecha = $fecha[2] . $fecha[1] . $fecha[0];
					$monto = floatval(str_replace(',', '.', $datos['monto'][$i]));
					$monto = round($monto, 0);
					$llave = $datos['llave'][$i];
					$codigo = $controlCode->generate($autorizacion, $factura, $nitci, $fecha, $monto, $llave);
				} else {
					$codigo = '';
				}
				?>
				<tr>
					<th class="text-nowrap text-middle text-right"><?= $i + 1; ?></th>
					<td class="text-nowrap text-middle">
						<input value="<?= (isset($datos['autorizacion'][$i])) ? $datos['autorizacion'][$i] : ''; ?>" type="text" name="autorizacion[]" class="form-control text-right" data-validation="required number" data-posicion="0">
					</td>
					<td class="text-nowrap text-middle">
						<input value="<?= (isset($datos['factura'][$i])) ? $datos['factura'][$i] : ''; ?>" type="text" name="factura[]" class="form-control text-right" data-validation="required number" data-posicion="1">
					</td>
					<td class="text-nowrap text-middle">
						<input value="<?= (isset($datos['nitci'][$i])) ? $datos['nitci'][$i] : ''; ?>" type="text" name="nitci[]" class="form-control text-right" data-validation="required number" data-posicion="2">
					</td>
					<td class="text-nowrap text-middle">
						<input value="<?= (isset($datos['fecha'][$i])) ? $datos['fecha'][$i] : ''; ?>" type="text" name="fecha[]" class="form-control" data-validation="date" data-validation-format="dd/mm/yyyy" data-posicion="3">
					</td>
					<td class="text-nowrap text-middle">
						<input value="<?= (isset($datos['monto'][$i])) ? $datos['monto'][$i] : ''; ?>" type="text" name="monto[]" class="form-control text-right" data-validation="required number" data-validation-allowing="float" data-posicion="4">
					</td>
					<td class="text-nowrap text-middle">
						<input value="<?= (isset($datos['llave'][$i])) ? $datos['llave'][$i] : ''; ?>" type="text" name="llave[]" class="form-control" data-validation="required" data-posicion="5">
					</td>
					<td class="text-nowrap text-middle">
						<samp class="lead"><?= $codigo; ?></samp>
					</td>
				</tr>
				<?php endfor ?>
			</table>
		</div>
		<div class="form-group text-right margin-none">	
			<button type="submit" class="btn btn-primary">
				<span class="glyphicon glyphicon-ok"></span>
				<span>Validar</span>
			</button>
			<a href="?/evaluacion/verificar" class="btn btn-default">
				<span class="glyphicon glyphicon-refresh"></span>
				<span>Restablecer</span>
			</a>
		</div>
	</form>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script>
$(function () {
	$.validate({
    	decimalSeparator: ','
	});

	$('[data-posicion="3"]').mask('99/99/9999');

	$('[data-copiar]').on('dblclick', function () {
		$(this).find('.td').each(function (i) {
			$('[data-posicion="' + i + '"]').val($.trim($(this).text()));
			$('[data-posicion="' + i + '"]').trigger('blur');
		});
	});
});
</script>
<?php require_once show_template('footer-advanced'); ?>