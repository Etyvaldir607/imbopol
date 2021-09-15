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

#error al listar la tabla proveeedores (modules/proveeedores/listar.php)

#error al crear proveedor (Acceso a la ruta restringido)
#error crear proveedor corregido (editandolos permisos del susper usuario)

#error al listar la tabla proveeedores (modules/ingresos/listar.php)
#error al crear comprar (modules/ingresos/crear.php)

#error al crear egresos (modules/egresos/crear.php)
#error al crear egresos (modules/egresos/guardar.php)

#error al crear egresos (modules/notas/crear.php)
#modificar acciones - listar el cliente, buscar por id

#error al crear proformas (modules/proformas/crear.php)
#error al guardar proformas (modules/proformas/guardar.php)

#error al crear manuales (modules/manuales/crear.php)
#error al guardar manuales (modules/manuales/guardar.php)

#error al crear electronicas (modules/electronicas/crear.php)
#error al guardar electronicas (modules/electronicas/guardar.php)

#modificar guardar notas (modules/notas/guardar.php)
#modificar guardar proformas (modules/proformas/guardar.php)