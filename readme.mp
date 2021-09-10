#sistema implementado de forma local
#error al iniciar sesión (modules/site/auth.php)
#error al registrar usuario (modules/usuarios/guardar.php)

#error al eliminar tipo de producto (modules/tipo/eliminar.php)

#error al crear clientes (no existe tabla inv_clientes)

-- Volcando estructura para tabla imbopol.sys_clientes
CREATE TABLE IF NOT EXISTS `inv_clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `cliente` VARCHAR(200) CHARACTER SET latin1 NOT NULL,
  `nit` VARCHAR(50) CHARACTER SET latin1 NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `estado` enum('No','Si') CHARACTER SET latin1 NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id_cliente`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=UTF8;

#error al listar la tabla clientes (modules/clientes/listar.php)