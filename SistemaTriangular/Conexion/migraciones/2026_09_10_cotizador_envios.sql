-- Cotizador de Envios (Ventas > Cotizador de Envios).
--
-- Guarda cada cotizacion que arma el operador: la ruta (origen + destino + N
-- puntos intermedios), los N paquetes, el modo de calculo y el desglose completo
-- del precio. Tabla nueva (la vieja `Cotizaciones` es de 1 solo bulto y sin
-- waypoints ni desglose; se depura mas adelante).
--
-- Modo de calculo:
--   'servicio' -> tarifa por bulto de Productos (Grupo='Web') segun dimensiones
--                 + km, con convencion multi-bulto (1er bulto 100%, 2do 0%,
--                 3ro+ al mismo domicilio 50%).
--   'km'       -> ValorxKilometro (segmento de flota) x km totales.
--
-- Correr a mano en produccion.

CREATE TABLE IF NOT EXISTS `CotizacionesEnvio` (
  `id`                INT(11)        NOT NULL AUTO_INCREMENT,
  `Fecha`             DATETIME       NOT NULL,
  `Usuario`           VARCHAR(80)    NOT NULL DEFAULT '',
  `Titulo`            VARCHAR(140)   NOT NULL DEFAULT '',
  `idCliente`         INT(11)        NULL,
  `RazonSocial`       VARCHAR(120)   NOT NULL DEFAULT '',

  `Modo`              VARCHAR(12)    NOT NULL DEFAULT 'servicio',
  `idValorxKilometro` INT(11)        NULL,
  `VehiculoNombre`    VARCHAR(60)    NOT NULL DEFAULT '',

  `OrigenTexto`       VARCHAR(255)   NOT NULL DEFAULT '',
  `OrigenLocalidad`   VARCHAR(120)   NOT NULL DEFAULT '',
  `OrigenLat`         DECIMAL(10,7)  NULL,
  `OrigenLng`         DECIMAL(10,7)  NULL,
  `DestinoTexto`      VARCHAR(255)   NOT NULL DEFAULT '',
  `DestinoLocalidad`  VARCHAR(120)   NOT NULL DEFAULT '',
  `DestinoLat`        DECIMAL(10,7)  NULL,
  `DestinoLng`        DECIMAL(10,7)  NULL,
  `WaypointsJSON`     TEXT           NULL,

  `KmTotales`         DECIMAL(10,2)  NOT NULL DEFAULT 0,
  `TiempoManejoMin`   INT(11)        NOT NULL DEFAULT 0,
  `DemorasMin`        INT(11)        NOT NULL DEFAULT 0,
  `TiempoTotalMin`    INT(11)        NOT NULL DEFAULT 0,

  `ValorDeclarado`    DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `LlevaSeguro`       TINYINT(1)     NOT NULL DEFAULT 0,
  `SeguroPct`         DECIMAL(6,3)   NOT NULL DEFAULT 1,
  `SeguroMonto`       DECIMAL(12,2)  NOT NULL DEFAULT 0,

  `LlevaCobranza`     TINYINT(1)     NOT NULL DEFAULT 0,
  `CobranzaBase`      DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `CobranzaPct`       DECIMAL(6,3)   NOT NULL DEFAULT 6,
  `CobranzaMonto`     DECIMAL(12,2)  NOT NULL DEFAULT 0,

  `Viatico`          DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `DemorasMonto`     DECIMAL(12,2)  NOT NULL DEFAULT 0,

  `PrecioTransporte` DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `Subtotal`         DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `DescuentoTipo`   VARCHAR(8)     NOT NULL DEFAULT 'monto',
  `DescuentoValor`  DECIMAL(12,3)  NOT NULL DEFAULT 0,
  `DescuentoMonto`  DECIMAL(12,2)  NOT NULL DEFAULT 0,

  `Neto`            DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `Iva`             DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `Total`           DECIMAL(12,2)  NOT NULL DEFAULT 0,

  `PaquetesJSON`    TEXT           NULL,
  `DesgloseJSON`    TEXT           NULL,
  `Observaciones`   TEXT           NULL,
  `Eliminado`       TINYINT(1)     NOT NULL DEFAULT 0,
  `TimeStamp`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cotenvio_cliente` (`idCliente`),
  KEY `idx_cotenvio_fecha` (`Fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Idempotente: por si la tabla ya existia sin la columna.
ALTER TABLE `CotizacionesEnvio` ADD COLUMN IF NOT EXISTS `Titulo` VARCHAR(140) NOT NULL DEFAULT '' AFTER `Usuario`;
