-- ============================================================================
-- Cómo está cada envío en Wepoint: la foto que manda Wepoint (wepoint_entrada.php,
-- accion 'trazabilidad'). La muestran Pendientes, Hoja de Ruta y Seguimiento.
-- wepoint_lib.php también crea la tabla si no existe. Idempotente.
-- ============================================================================
CREATE TABLE IF NOT EXISTS wepoint_trazabilidad (
    id_tc INT NOT NULL PRIMARY KEY,
    codigo VARCHAR(100) NOT NULL,
    estado VARCHAR(30) NULL,
    piezas INT NOT NULL DEFAULT 0,
    en_deposito INT NOT NULL DEFAULT 0,
    oib VARCHAR(50) NULL,
    orb VARCHAR(255) NULL,
    oeb VARCHAR(255) NULL,
    envio VARCHAR(255) NULL,
    transportista VARCHAR(255) NULL,
    posiciones VARCHAR(255) NULL,
    ingreso_at DATETIME NULL,
    salida_at DATETIME NULL,
    actualizado_en DATETIME NOT NULL
);
