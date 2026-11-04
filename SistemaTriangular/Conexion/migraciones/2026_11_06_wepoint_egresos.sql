-- ============================================================================
-- Egreso de Wepoint solicitado desde Hoja de Ruta.
-- Generado 2026-11-06.
--
-- Botón "Solicitar egreso a Wepoint" en cada recorrido de Hoja de Ruta: Wepoint
-- recibe la orden de egreso y la acepta o la rechaza. Mientras está abierta, los
-- paquetes del recorrido que están en Wepoint no se mueven de recorrido: el
-- cambio se le pide a Wepoint y se aplica cuando lo acepta.
-- (Logistica/Proceso/php/wepoint_egresos.php, que también crea estas tablas si
-- no existen.) Idempotente.
-- ============================================================================

CREATE TABLE IF NOT EXISTS wepoint_egresos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recorrido INT NOT NULL,
    orden VARCHAR(30) NULL,
    estado VARCHAR(15) NOT NULL DEFAULT 'solicitado',
    id_wepoint INT NULL,
    nro_oeb VARCHAR(30) NULL,
    motivo VARCHAR(255) NULL,
    solicitado_por VARCHAR(100) NULL,
    solicitado_en DATETIME NOT NULL,
    actualizado_en DATETIME NULL,
    cerrado_en DATETIME NULL,
    KEY ix_wepoint_egresos_recorrido (recorrido, estado)
);

CREATE TABLE IF NOT EXISTS wepoint_egreso_items (
    id_egreso INT NOT NULL,
    id_tc INT NOT NULL,
    codigo VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_egreso, id_tc),
    KEY ix_wepoint_egreso_items_tc (id_tc)
);

CREATE TABLE IF NOT EXISTS wepoint_egreso_cambios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tc INT NOT NULL,
    codigo VARCHAR(100) NOT NULL,
    recorrido_origen INT NULL,
    recorrido_destino INT NULL,
    estado_id INT NOT NULL DEFAULT 10,
    egreso_origen INT NULL,
    egreso_destino INT NULL,
    estado VARCHAR(12) NOT NULL DEFAULT 'pendiente',
    motivo VARCHAR(255) NULL,
    usuario VARCHAR(100) NULL,
    creado_en DATETIME NOT NULL,
    resuelto_en DATETIME NULL,
    KEY ix_wepoint_egreso_cambios_tc (id_tc, estado),
    KEY ix_wepoint_egreso_cambios_estado (estado)
);
