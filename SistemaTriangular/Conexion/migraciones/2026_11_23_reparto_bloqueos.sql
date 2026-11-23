-- Bloqueos del chofer en la app de reparto (23/11/2026).
-- Cada vez que el chofer no puede confirmar la carga o arrancar el recorrido (entregas sin escanear,
-- falta el bolsín) o se va del depósito con entregas sin escanear, queda una fila acá. La oficina la
-- ve en Logística > Órdenes > Control de la orden. La app de reparto la escribe si la tabla existe;
-- sin la tabla no pasa nada (no corta el flujo).
-- Correr en PRODUCCIÓN y en la copia del sandbox.

CREATE TABLE IF NOT EXISTS RepartoBloqueos (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Fecha         DATE         NOT NULL,
  Hora          TIME         NOT NULL,
  idUsuario     INT          NOT NULL DEFAULT 0,
  Usuario       VARCHAR(60)  NOT NULL DEFAULT '',
  Recorrido     VARCHAR(20)  NOT NULL DEFAULT '',
  NumerodeOrden INT          NOT NULL DEFAULT 0,
  Accion        VARCHAR(30)  NOT NULL DEFAULT '',  -- confirmar_carga | iniciar_recorrido | salio_sin_escanear
  Motivo        VARCHAR(30)  NOT NULL DEFAULT '',  -- sin_escanear | bolsin | sin_bolsin
  Cantidad      INT          NOT NULL DEFAULT 0,
  Codigos       TEXT         NULL,
  Mensaje       VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY idx_orden (NumerodeOrden),
  KEY idx_fecha (Fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
