<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de parametros
	if (isset($params)) {
		// Verifica la existencia de datos
		if (isset($_POST['busqueda'])) {
			// Obtiene los datos
			$busqueda = trim($_POST['busqueda']);
			//var_dump($busqueda);exit();

			// Obtiene el almacen principal
			$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
			$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

			// Obtiene los productos con el valor buscado
				$productos = $db->query("SELECT p.id_producto, z.id_asignacion, z.unidad_id, z.unidade, p.descripcion, p.imagen, p.codigo, p.codigo_barras, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, IFNULL(s.cantidad_egresos, 0) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
					FROM inv_productos p
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
						   FROM inv_ingresos_detalles d
						   LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
						   WHERE i.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
						   FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
						   WHERE e.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
					LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
					LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade
					   FROM (SELECT *
							FROM inv_asignaciones q
								  LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad
										 ORDER BY u.unidad DESC) w GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id
					WHERE p.codigo like '%" . $busqueda . "%' OR p.codigo_barras like '%" . $busqueda . "%' OR p.nombre like '%" . $busqueda . "%' OR c.categoria like '%" . $busqueda . "%' order by p.nombre asc")->fetch();

			// Devuelve los resultados
			echo json_encode($productos);
		} else {
			// Error 401
			require_once bad_request();
			exit;
		}		
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>