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

-- Volcando estructura para tabla imbopol.caj_movimientos
CREATE TABLE IF NOT EXISTS `caj_movimientos` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_movimiento` date NOT NULL,
  `hora_movimiento` time NOT NULL,
  `nro_comprobante` varchar(50) NOT NULL,
  `tipo` enum('i','e','g') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `concepto` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `observacion` text NOT NULL,
  `empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`id_movimiento`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_almacenes
CREATE TABLE IF NOT EXISTS `inv_almacenes` (
  `id_almacen` int(11) NOT NULL AUTO_INCREMENT,
  `almacen` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `principal` enum('N','S') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_almacen`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_asignaciones
CREATE TABLE IF NOT EXISTS `inv_asignaciones` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad_unidad` int(11) NOT NULL,
  `otro_precio` decimal(20,2) NOT NULL,
  PRIMARY KEY (`id_asignacion`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_categorias
CREATE TABLE IF NOT EXISTS `inv_categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(100) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=MyISAM AUTO_INCREMENT=103 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_dosificaciones
CREATE TABLE IF NOT EXISTS `inv_dosificaciones` (
  `id_dosificacion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `nro_tramite` varchar(50) NOT NULL,
  `nro_autorizacion` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `llave_dosificacion` varchar(200) CHARACTER SET latin1 NOT NULL,
  `fecha_limite` date NOT NULL,
  `leyenda` text NOT NULL,
  `activo` enum('N','S') NOT NULL,
  `nro_facturas` int(11) NOT NULL,
  `observacion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_dosificacion`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_egresos
CREATE TABLE IF NOT EXISTS `inv_egresos` (
  `id_egreso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_factura` int(11) NOT NULL,
  `nro_autorizacion` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `codigo_control` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fecha_limite` date NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `nombre_cliente` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nit_ci` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_registros` int(11) NOT NULL,
  `dosificacion_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`id_egreso`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_egresos_detalles
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `egreso_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_ingresos
CREATE TABLE IF NOT EXISTS `inv_ingresos` (
  `id_ingreso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_ingreso` date NOT NULL,
  `hora_ingreso` time DEFAULT NULL,
  `tipo` enum('Compra','Traspaso') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `monto_total` decimal(10,2) DEFAULT NULL,
  `nombre_proveedor` varchar(100) NOT NULL,
  `nro_registros` int(11) DEFAULT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`id_ingreso`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_ingresos_detalles
CREATE TABLE IF NOT EXISTS `inv_ingresos_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `ingreso_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_monedas
CREATE TABLE IF NOT EXISTS `inv_monedas` (
  `id_moneda` int(11) NOT NULL AUTO_INCREMENT,
  `moneda` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL,
  `oficial` enum('N','S') CHARACTER SET latin1 NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id_moneda`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_precios
CREATE TABLE IF NOT EXISTS `inv_precios` (
  `id_precio` int(11) NOT NULL AUTO_INCREMENT,
  `precio` decimal(10,2) NOT NULL,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `asignacion_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`id_precio`)
) ENGINE=MyISAM AUTO_INCREMENT=333 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_productos
CREATE TABLE IF NOT EXISTS `inv_productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `codigo_barras` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nombre_factura` varchar(100) NOT NULL,
  `color` varchar(30) NOT NULL,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `precio_actual` decimal(10,2) NOT NULL,
  `cantidad_minima` int(11) NOT NULL,
  `imagen` varchar(100) NOT NULL,
  `ubicacion` text NOT NULL,
  `descripcion` text NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  PRIMARY KEY (`id_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=401 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_proformas
CREATE TABLE IF NOT EXISTS `inv_proformas` (
  `id_proforma` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_proforma` date NOT NULL,
  `hora_proforma` time NOT NULL,
  `descripcion` text NOT NULL,
  `nro_proforma` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `adelanto` decimal(10,2) NOT NULL,
  `nombre_cliente` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nit_ci` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_registros` int(11) NOT NULL,
  `validez` int(11) NOT NULL,
  `observacion` text NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`id_proforma`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_proformas_detalles
CREATE TABLE IF NOT EXISTS `inv_proformas_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `proforma_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_proveedores
CREATE TABLE IF NOT EXISTS `inv_proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor` varchar(200) NOT NULL,
  `nit` varchar(50) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(50) NOT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_terminales
CREATE TABLE IF NOT EXISTS `inv_terminales` (
  `id_terminal` int(11) NOT NULL AUTO_INCREMENT,
  `terminal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `identificador` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `impresora` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_terminal`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.inv_unidades
CREATE TABLE IF NOT EXISTS `inv_unidades` (
  `id_unidad` int(11) NOT NULL AUTO_INCREMENT,
  `unidad` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_unidad`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.sys_empleados
CREATE TABLE IF NOT EXISTS `sys_empleados` (
  `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) CHARACTER SET latin1 NOT NULL,
  `paterno` varchar(100) CHARACTER SET latin1 NOT NULL,
  `materno` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `genero` enum('Masculino','Femenino') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Masculino',
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(100) CHARACTER SET latin1 NOT NULL,
  `cargo` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id_empleado`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.sys_instituciones
CREATE TABLE IF NOT EXISTS `sys_instituciones` (
  `id_institucion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL,
  `lema` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `razon_social` text CHARACTER SET latin1 NOT NULL,
  `propietario` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nit` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(100) NOT NULL,
  `imagen_encabezado` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pie_pagina` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `formato` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tema` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_institucion`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.sys_menus
CREATE TABLE IF NOT EXISTS `sys_menus` (
  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
  `menu` varchar(100) CHARACTER SET latin1 NOT NULL,
  `icono` varchar(100) CHARACTER SET latin1 NOT NULL,
  `ruta` varchar(200) CHARACTER SET latin1 NOT NULL,
  `modulo` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL,
  `antecesor_id` int(11) NOT NULL,
  PRIMARY KEY (`id_menu`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.sys_permisos
CREATE TABLE IF NOT EXISTS `sys_permisos` (
  `rol_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `archivos` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`rol_id`,`menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.sys_roles
CREATE TABLE IF NOT EXISTS `sys_roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(100) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla imbopol.sys_users
CREATE TABLE IF NOT EXISTS `sys_users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET latin1 NOT NULL,
  `password` varchar(100) CHARACTER SET latin1 NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 NOT NULL,
  `avatar` varchar(100) CHARACTER SET latin1 NOT NULL,
  `active` int(11) NOT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime NOT NULL,
  `rol_id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
