<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['fechai']) && isset($_POST['fechaf']) && isset($_POST['codigo']) && isset($_POST['precio']) ) {


		 // Obtiene los formatos para la fecha
		$formato_textual = get_date_textual($_institution['formato']);
		$formato_numeral = get_date_numeral($_institution['formato']);

		// Obtiene el rango de fechas
		$gestion = date('Y');
		$gestion_base = date('Y-m-d');
		$fechaactual = date("Y")."-".date("m")."-".date("d");
		//$gestion_base = ($gestion - 16) . date('-m-d');
		$gestion_limite = ($gestion) . date('-m-d');

		// Obtiene fecha inicial
		$fecha_inicial = (isset($_POST['fechai'])) ? $_POST['fechai']: $gestion_base;
		$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
		$fecha_inicial = date_encode($fecha_inicial);
		// Obtiene fecha final
		$fecha_final = (isset($_POST['fechaf'])) ? $_POST['fechaf']: $gestion_base;
		$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_base;
		$fecha_final = date_encode($fecha_final);

        $codigo = $_POST['codigo'];
        $precio = $_POST['precio'];

        $respuesta = array('fechai' => $fecha_inicial,
        					'fechaf' => $fecha_final,
        					'codigo' => $codigo,
        					'precio' => $precio );

        $sql = "UPDATE inv_ingresos_detalles d INNER JOIN inv_ingresos i ON d.ingreso_id=i.id_ingreso INNER JOIN sys_users u ON u.persona_id=i.empleado_id  SET d.costo='$precio' WHERE d.producto_id='$codigo' AND u.rol_id>2 AND i.fecha_ingreso BETWEEN '$fecha_inicial' AND '$fecha_final'";
        $db->query($sql)->execute() ;

		$respuesta = $db->affected_rows ;  // Outputs the affected rows

        // Envia respuesta
        echo json_encode($respuesta);
	} else {
		// Error 401
        echo 'error';
	}
	//echo json_encode($_POST);

} else {
	// Error 404
	require_once not_found();
	exit;
}

?>