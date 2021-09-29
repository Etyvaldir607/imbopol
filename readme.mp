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
#modificar guardar manuales (modules/manuales/guardar.php)
#modificar guardar electronicas (modules/electronicas/guardar.php)

#añadir parametro de compra - fecha de vencimiento en la vista
#añadir parametro de compra - fecha de vencimiento en la base datos
#añadir parametro de compra - fecha de vencimiento en el procesador (modules/ingresos/guardar.php)

#agregar parametro de venta - fecha de vencimiento en (modules/notas/crear.php)

#corregir crear nota (no mostrar los datos que sean menores a cero en el stock)
#corregir crear nota (debo poder agregar productos de distintas fechas de vencimiento)

#delimitar las fechas de agregacióm

#cambio de metodo para sincornizar los stocks
#estructuracion de la consulta para que se devuelva los productos asociados a:
  un array de fechas de vencimiento y un arrray de stocks.

#creación  de metodo para agregar producto por fecha
#modificación de para adicionar item por fecha
#creación de metodo para eliminar por fechas


#estudio de los archivos de configuracion
#creacion de metodo adicionar por fecha
#creacion de metodo para importe por producto y fecha
#creacion de metodo para cantidad por producto y fecha

#creacion de metodo para realizar descuentos por producto y fechas de vencimiento
#realizar reportes por producto y fecha de vencimiento


#realizar modificacion en proformas
#realizar modificacion en ventas electronicas
#realizar modificacion en ventas manuales

#modificacion reportes ventas manuales
#modificacion reportes ventas manuales en operaciones

#actualizacion de metodos en ventas electronicas
#actualizacion de consulta en ventas electronicas
#actualizacion en guardar ventas electronicas
#modificacion reportes ventas electronicas
#modificacion reportes operaciones electronicas

#actualizacion de metodos en proformas
#actualizacion de consulta en proformas
#actualizacion en guardar proformas
#modificacion reportes proformas
#modificacion reportes operaciones proformas


#fecha: tareas asignasdas (27-09-2021)
#modelar logica para agregar un tipo de unidad a cualquier producto
#modificacion de la base de datos para cumplir el objetivo


#modificacion en la tabla inv_asignaciones
ALTER TABLE `inv_asignaciones` ADD `fecha_asignacion` DATE NOT NULL AFTER `otro_precio`;

#modificacion en crear producto
#modificacion en guardar producto

#creacion de estructura para asignacion de unidades
#creacion de consulta para asignacion de unidades



#fecha: tareas asignasdas (28-09-2021)
#modificacion de estructura para asignacion de unidades
#agregando busqueda de unidades en el formulario
#modificacion de lista de productos
#modificacion de asignacion de unidades

#fecha: tareas asignadas (29-09-2021)
#modificacion de estructura para modificacion de unidades
#agregando modal para la edicion de unidad asignada
#modificacion de lista de productos
#modificacion de modificacion de unidades




#fecha: tareas asignadas (29-09-2021) para el lunes ()
    compra
    venta

    con cualquier fecha
    con cualquier unidad

    reporte de detalle
    generar pdf

    inventario - cualquiera