-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.3.16-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando datos para la tabla imbopol.sys_empleados: 6 rows
/*!40000 ALTER TABLE `sys_empleados` DISABLE KEYS */;
INSERT INTO `sys_empleados` (`id_empleado`, `nombres`, `paterno`, `materno`, `genero`, `fecha_nacimiento`, `telefono`, `cargo`) VALUES
	(5, 'Rodrigo', 'Rodrigo', 'Rodrigo', 'Masculino', '2000-01-01', '', 'vendedor');
/*!40000 ALTER TABLE `sys_empleados` ENABLE KEYS */;

-- Volcando datos para la tabla imbopol.sys_instituciones: 1 rows
/*!40000 ALTER TABLE `sys_instituciones` DISABLE KEYS */;
INSERT INTO `sys_instituciones` (`id_institucion`, `nombre`, `sigla`, `lema`, `razon_social`, `propietario`, `direccion`, `telefono`, `nit`, `correo`, `imagen_encabezado`, `pie_pagina`, `formato`, `tema`) VALUES
	(1, 'IMBOPOL', 'FA', 'IMBOPOL', 'VENTA AL POR MENOR DE\r\nMATERIAL DE CONSTRUCCION', 'JOSE GABRIEL NINA FERNANDEZ', 'AV. MONTES', '2915915', '2476916014', 'mnina840@gmail.com', '710b660f692d14b3c15cb11bd86b791c.jpg', 'El Alto - Bolivia', 'Y/m/d', 'yeti');
/*!40000 ALTER TABLE `sys_instituciones` ENABLE KEYS */;

-- Volcando datos para la tabla imbopol.sys_menus: 56 rows
/*!40000 ALTER TABLE `sys_menus` DISABLE KEYS */;
INSERT INTO `sys_menus` (`id_menu`, `menu`, `icono`, `ruta`, `modulo`, `orden`, `antecesor_id`) VALUES
	(1, 'Módulo Administración', 'dashboard', '', '', 1, 0),
	(2, 'Configuración general', 'cog', '', '', 1, 1),
	(3, 'Apariencia del sistema', 'tint', '?/configuraciones/apariencia', 'configuraciones', 4, 2),
	(4, 'Información de la empresa', 'home', '?/configuraciones/institucion', 'configuraciones', 1, 2),
	(5, 'Ajustes sobre la fecha', 'cog', '?/configuraciones/preferencias', 'configuraciones', 3, 2),
	(6, 'Ajustes sobre los reportes', 'print', '?/configuraciones/reportes', 'configuraciones', 2, 2),
	(7, 'Registro de roles', 'stats', '?/roles/listar', 'roles', 2, 1),
	(8, 'Asignación de permisos', 'lock', '?/permisos/listar', 'permisos', 3, 1),
	(9, 'Registro de usuarios', 'user', '?/usuarios/listar', 'usuarios', 5, 1),
	(10, 'Registro de empleados', 'eye-open', '?/empleados/listar', 'empleados', 4, 1),
	(11, 'Módulo General', 'globe', '', '', 2, 0),
	(12, 'Registro de almacenes', 'home', '?/almacenes/listar', 'almacenes', 0, 11),
	(13, 'Registro de tipo de prod', 'tag', '?/tipo/listar', 'tipo', 0, 11),
	(14, 'Registro de monedas', 'piggy-bank', '?/monedas/listar', 'monedas', 0, 11),
	(15, 'Personas', 'user', '', '', 0, 11),
	(16, 'Lista de clientes', 'briefcase', '?/clientes/listar', 'clientes', 0, 15),
	(17, 'Lista de proveedores', 'plane', '?/proveedores/listar', 'proveedores', 0, 15),
	(18, 'Registro de unidades', 'filter', '?/unidades/listar', 'unidades', 0, 11),
	(19, 'Módulo Inventario', 'inbox', '', '', 3, 0),
	(20, 'Catálogo de productos', 'scale', '?/productos/listar', 'productos', 0, 19),
	(21, 'Inventario de productos', 'book', '?/inventarios/listar', 'inventarios', 0, 19),
	(22, 'Lista de precios', 'usd', '?/precios/listar', 'precios', 0, 19),
	(23, 'Stock de productos', 'stats', '?/stocks/listar', 'stocks', 0, 19),
	(24, 'Módulo Caja', 'usd', '', '', 6, 0),
	(25, 'Reporte de existencias', 'search', '?/existencias/listar', 'existencias', 0, 19),
	(26, 'Reporte de ventas', 'stats', '', '', 6, 30),
	(27, 'Módulo Facturación', 'qrcode', '', '', 5, 0),
	(28, 'Registro de terminales', 'phone', '?/terminales/listar', 'terminales', 3, 27),
	(29, 'Registro de dosificaciones', 'lock', '?/dosificaciones/listar', 'dosificaciones', 2, 27),
	(30, 'Módulo Ventas', 'shopping-cart', '', '', 4, 0),
	(31, 'Compras', 'log-in', '?/ingresos/listar', 'ingresos', 0, 19),
	(32, 'Salidas', 'log-out', '?/egresos/listar', 'egresos', 0, 19),
	(33, 'Proformas', 'list-alt', '?/proformas/crear', 'proformas', 3, 30),
	(34, 'Ventas computarizadas', 'shopping-cart', '?/electronicas/crear', 'electronicas', 1, 30),
	(35, 'Reporte de ventas generales', 'briefcase', '?/reportes/ventas_generales', 'reportes', 1, 26),
	(36, 'Ventas manuales', 'edit', '?/manuales/crear', 'manuales', 4, 30),
	(37, 'Reporte de ventas computarizadas', 'qrcode', '?/reportes/ventas_electronicas', 'reportes', 2, 26),
	(38, 'Reporte de ventas manuales', 'paste', '?/reportes/ventas_manuales', 'reportes', 4, 26),
	(39, 'Reporte de ventas personales', 'user', '?/reportes/ventas_personales', 'reportes', 5, 26),
	(41, 'Operaciones', 'list', '', '', 5, 30),
	(42, 'Listado de facturas', 'qrcode', '?/operaciones/facturas_listar', 'operaciones', 1, 41),
	(43, 'Listado de proformas', 'list-alt', '?/operaciones/proformas_listar', 'operaciones', 3, 41),
	(44, 'Cierre de caja', 'stats', '?/movimientos/cerrar', 'movimientos', 0, 24),
	(45, 'Ingreso de dinero a caja', 'plus-sign', '?/movimientos/ingresos_listar', 'movimientos', 0, 24),
	(46, 'Egreso de dinero de caja', 'minus-sign', '?/movimientos/egresos_listar', 'movimientos', 0, 24),
	(48, 'Notas de entrega', 'edit', '?/notas/crear', 'notas', 2, 30),
	(50, 'Registro directo', 'plus', '?/registros/crear ', 'registros', 7, 0),
	(51, 'Registro de gastos', 'remove-sign', '?/movimientos/gastos_listar', 'movimientos', 0, 24),
	(52, 'Reporte general de caja', 'file', '?/movimientos/mostrar', 'movimientos', 0, 24),
	(53, 'Kardex físico y valorado', 'folder-close', '?/kardex/listar', 'kardex', 0, 19),
	(54, 'Listado de notas de entrega', 'edit', '?/operaciones/notas_listar', 'operaciones', 2, 41),
	(55, 'Reporte de ventas notas de entrega', 'edit', '?/reportes/ventas_notas', 'reportes', 3, 26),
	(56, 'Reporte de ventas a detalle', 'file', '?/reportes/diario', 'reportes', 7, 30),
	(57, 'Certificación del sistema', 'ok', '?/evaluacion/verificar', 'evaluacion', 1, 27),
	(58, 'Listado de ventas manuales', 'paste', '?/operaciones/listar_manuales', 'operaciones', 0, 41),
	(59, 'Reporte de clientes', 'user', '?/clientes/reporte', 'clientes', 0, 19);
/*!40000 ALTER TABLE `sys_menus` ENABLE KEYS */;

-- Volcando datos para la tabla imbopol.sys_permisos: 118 rows
/*!40000 ALTER TABLE `sys_permisos` DISABLE KEYS */;
INSERT INTO `sys_permisos` (`rol_id`, `menu_id`, `archivos`) VALUES
	(3, 43, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(3, 54, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(2, 26, ''),
	(2, 35, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(2, 43, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(2, 41, ''),
	(2, 54, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(2, 36, 'actualizar,crear,editar,editar_venta,eliminar,guardar,mostrar,obtener,suprimir,ver'),
	(2, 48, 'actualizar,buscar,crear,crear3,editar,facturar,guardar,imprimir,mostrar,obtener,ver'),
	(2, 30, ''),
	(2, 34, 'actualizar,buscar,crear,editar,facturar,guardar,mostrar,obtener,ver'),
	(2, 23, 'listar,mostrar'),
	(2, 31, 'crear,eliminar,guardar,imprimir,listar,suprimir,ver'),
	(2, 25, 'listar'),
	(2, 53, 'detallar,imprimir,listar'),
	(2, 22, 'actualizar,asignar,cambiar,eliminar,fijar,imprimir,listar,quitar,ver'),
	(2, 21, 'listar'),
	(2, 20, 'cambiar,crear,editar,eliminar,generar,generarbc,guardar,imprimir,listar,saltar,subir,suprimir,validar,validar_barras,ver'),
	(2, 19, ''),
	(2, 32, 'actualizar,crear,eliminar,guardar,imprimir,listar,suprimir,ver'),
	(2, 12, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(2, 15, ''),
	(2, 16, 'imprimir,listar'),
	(2, 17, 'imprimir,listar'),
	(2, 18, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(1, 38, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 39, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 56, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 26, ''),
	(1, 35, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 37, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 55, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(3, 58, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(3, 17, 'imprimir,listar'),
	(3, 18, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(3, 13, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(3, 19, ''),
	(3, 20, 'cambiar,crear,editar,eliminar,generar,generarbc,guardar,imprimir,listar,saltar,subir,suprimir,validar,validar_barras,ver'),
	(3, 21, 'listar'),
	(3, 23, 'mostrar,listar'),
	(3, 32, 'suprimir,guardar,imprimir,listar,ver,eliminar,actualizar,crear'),
	(3, 25, 'listar'),
	(3, 31, 'suprimir,guardar,imprimir,listar,ver,eliminar,crear'),
	(3, 59, 'crear,detallar,editar,eliminar,guardar,imprimir,imprimir_reporte,listar,reporte'),
	(3, 30, ''),
	(3, 48, 'obtener,buscar,guardar,mostrar,editar,ver,actualizar,crear,facturar'),
	(1, 54, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(1, 43, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(1, 42, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(1, 41, ''),
	(1, 58, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(1, 36, 'actualizar,crear,editar,editar_venta,eliminar,guardar,mostrar,obtener,suprimir,ver'),
	(2, 14, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(2, 11, ''),
	(2, 13, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(2, 8, 'guardar,listar,asignar'),
	(2, 10, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(2, 9, 'validar,guardar,imprimir,listar,editar,capturar,subir,ver,activar,eliminar,actualizar,crear,asignar'),
	(2, 3, 'apariencia_guardar,apariencia,reportes_editar,reportes,institucion_editar,institucion_guardar,institucion,preferencias_editar,reportes_guardar,preferencias,preferencias_guardar'),
	(2, 7, 'guardar,imprimir,listar,editar,ver,eliminar,crear'),
	(1, 48, 'actualizar,buscar,crear,crear2,editar,facturar,guardar,imprimir,mostrar,obtener,ver'),
	(1, 33, 'actualizar,buscar,crear,editar,eliminar,facturar,guardar,imprimir,modificar,mostrar,obtener,ver'),
	(1, 34, 'actualizar,buscar,crear,editar,facturar,guardar,mostrar,obtener,ver'),
	(1, 30, ''),
	(1, 59, 'crear,detallar,editar,eliminar,guardar,imprimir,imprimir_reporte,listar,reporte'),
	(1, 53, 'detallar,imprimir,listar'),
	(1, 21, 'listar'),
	(1, 22, 'actualizar,asignar,cambiar,eliminar,imprimir,listar,quitar,ver'),
	(1, 31, 'crear,eliminar,guardar,imprimir,listar,suprimir,ver'),
	(1, 25, 'listar'),
	(1, 32, 'actualizar,crear,eliminar,guardar,imprimir,listar,suprimir,ver'),
	(1, 23, 'listar,mostrar'),
	(1, 13, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 12, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 19, ''),
	(1, 20, 'cambiar,crear,editar,eliminar,generar,generarbc,guardar,imprimir,listar,saltar,subir,suprimir,validar,validar_barras,ver'),
	(1, 17, 'imprimir,listar'),
	(1, 18, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 14, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 11, ''),
	(1, 15, ''),
	(1, 16, 'crear,detallar,editar,eliminar,guardar,imprimir,imprimir_reporte,listar,reporte'),
	(1, 7, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 8, 'asignar,guardar,listar'),
	(1, 10, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 9, 'activar,actualizar,asignar,capturar,crear,editar,eliminar,guardar,imprimir,listar,subir,validar,ver'),
	(2, 5, 'apariencia_guardar,apariencia,reportes_editar,reportes,institucion_editar,institucion_guardar,institucion,preferencias_editar,reportes_guardar,preferencias,preferencias_guardar'),
	(2, 6, 'apariencia_guardar,apariencia,reportes_editar,reportes,institucion_editar,institucion_guardar,institucion,preferencias_editar,reportes_guardar,preferencias,preferencias_guardar'),
	(2, 2, ''),
	(2, 4, 'apariencia_guardar,apariencia,reportes_editar,reportes,institucion_editar,institucion_guardar,institucion,preferencias_editar,reportes_guardar,preferencias,preferencias_guardar'),
	(2, 1, ''),
	(2, 55, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(2, 38, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(2, 39, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(2, 50, 'guardar,crear'),
	(4, 27, ''),
	(4, 57, 'verificar'),
	(4, 29, 'bloquear,crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(4, 28, 'crear,descargar,editar,eliminar,guardar,imprimir,listar,ver'),
	(3, 33, 'actualizar,crear,editar,eliminar,facturar,guardar,imprimir,modificar,mostrar,obtener,ver'),
	(1, 3, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(1, 5, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(1, 6, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(1, 4, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(1, 1, ''),
	(1, 2, ''),
	(1, 27, ''),
	(1, 57, 'verificar'),
	(1, 29, 'bloquear,crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 28, 'crear,descargar,editar,eliminar,guardar,imprimir,listar,ver'),
	(3, 42, 'exp_manuales,facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(3, 36, 'actualizar,crear,editar,eliminar,guardar,mostrar,obtener,suprimir,ver'),
	(3, 41, ''),
	(3, 11, ''),
	(3, 15, ''),
	(3, 16, 'crear,detallar,editar,eliminar,guardar,imprimir,imprimir_reporte,listar,reporte'),
	(3, 26, ''),
	(3, 39, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales');
/*!40000 ALTER TABLE `sys_permisos` ENABLE KEYS */;

-- Volcando datos para la tabla imbopol.sys_roles: 4 rows
/*!40000 ALTER TABLE `sys_roles` DISABLE KEYS */;
INSERT INTO `sys_roles` (`id_rol`, `rol`, `descripcion`) VALUES
	(1, 'Superusuario', 'Usuario con acceso total del sistema'),
	(2, 'Administrador', 'Usuario con acceso general del sistema'),
	(3, 'Vendedor', 'Usuario con acceso parcial del sistema'),
	(4, 'prueba', '');
/*!40000 ALTER TABLE `sys_roles` ENABLE KEYS */;

-- Volcando datos para la tabla imbopol.sys_users: 8 rows
/*!40000 ALTER TABLE `sys_users` DISABLE KEYS */;
INSERT INTO `sys_users` (`id_user`, `username`, `password`, `email`, `avatar`, `active`, `login_at`, `logout_at`, `rol_id`, `persona_id`) VALUES
	(1, 'checkcode', '6e871b6de59666a0fed3cfd946d5474fbdb74ece', 'info@dominio.com', '', 1, '2020-10-27 12:46:40', '0000-00-00 00:00:00', 1, 0);
/*!40000 ALTER TABLE `sys_users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
