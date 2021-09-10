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

			// Obtiene el almacen principal
			$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
			$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

			// Obtiene los productos con el valor buscado
				$productos = $db->query("select p.id_producto, p.imagen, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos, ifnull(s.cantidad_egresos, 0) as cantidad_egresos, u.unidad, u.sigla, c.categoria from inv_productos p left join (select d.producto_id, sum(d.cantidad) as cantidad_ingresos from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id where i.almacen_id = '$id_almacen' group by d.producto_id) as e on e.producto_id = p.id_producto left join (select d.producto_id, sum(d.cantidad) as cantidad_egresos from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = '$id_almacen' group by d.producto_id) as s on s.producto_id = p.id_producto left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id where p.codigo like '%" . $busqueda . "%' or p.codigo_barras like '%" . $busqueda . "%' or p.nombre like '%" . $busqueda . "%' or c.categoria like '%" . $busqueda . "%' or p.precio_actual like '%" . $busqueda . "%' order by p.precio_actual desc")->fetch();

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