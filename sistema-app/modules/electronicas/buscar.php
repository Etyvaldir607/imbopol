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
				$productos = $db->query("select
				pf.id_producto,
				pf.color,
				pf.descripcion,
				pf.imagen,
				pf.codigo,
				pf.codigo_barras,
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
							i.almacen_id = $id_almacen
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
							e.almacen_id = $id_almacen
			
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
			where  fecha_vencimiento IS NOT NULL and (pf.codigo like '%" . $busqueda . "%' OR pf.codigo_barras like '%" . $busqueda . "%' OR pf.nombre like '%" . $busqueda . "%' OR c.categoria like '%" . $busqueda . "%')
			group by pf.id_producto
			order by pf.nombre asc")->fetch();

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