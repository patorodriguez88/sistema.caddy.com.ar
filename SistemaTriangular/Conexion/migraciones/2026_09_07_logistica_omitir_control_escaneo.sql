-- Override del control de escaneo en Warehouse para la app de reparto.
-- Logistica.OmitirControlEscaneo = 1  => ese recorrido puede arrancar en la app
-- de reparto sin escanear los bultos (escáner roto, paquetes sin etiqueta, etc.).
--
-- El toggle vive en sistema.caddy.com.ar > Logística > Órdenes de Salida
-- (botón "ESCANEO OBLIGATORIO / OMITIDO" en la columna Acción).
-- La app de reparto lo lee en SistemaReparto/Funciones/control_escaneo.php
-- (overrideEscaneo), por la orden en Estado='Cargada' del chofer, y cada bypass
-- del chofer queda en SistemaReparto/logs/control_escaneo_bypass.log.
--
-- _Por / _Fecha: trazabilidad del operador de sistema que autorizó el bypass.
-- Espejo de SistemaReparto/Funciones/migrations/2026-09-03_logistica_omitir_control_escaneo.sql
-- + 2026_09_06_omitir_control_escaneo_auditoria.sql (mismas columnas, misma base).
--
-- Correr a mano contra producción (no forma parte del deploy automático por FTP).
-- Idempotente-ish: si las columnas ya existen, MySQL tira error 1060 y listo.

ALTER TABLE Logistica
  ADD COLUMN OmitirControlEscaneo TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'App reparto: 1 = permite entregar sin escaneo previo (warehouse/retiro/colecta)',
  ADD COLUMN OmitirControlEscaneo_Por VARCHAR(80) NULL
    COMMENT 'App reparto: operador que cambió OmitirControlEscaneo por última vez',
  ADD COLUMN OmitirControlEscaneo_Fecha DATETIME NULL
    COMMENT 'App reparto: fecha/hora del último cambio de OmitirControlEscaneo';
