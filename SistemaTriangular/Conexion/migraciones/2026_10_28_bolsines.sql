-- Bolsines de facturas de Dinter (Logistica/Bolsines.php + app de reparto, Warehouse). Tarea #13 de
-- Micaela: que los recorridos de Dinter no salgan sin el bolsín con las facturas.
-- Cada bolsín tiene un QR fijo (BOL-0001...) y se reutiliza: Disponible -> Asignado (depósito lo
-- asigna a un recorrido) -> En viaje (el chofer lo escanea en Warehouse) -> Disponible (vuelve al
-- depósito). Correr ANTES de subir el código de reparto (GetLista y PuedeSalir leen estas tablas).
-- Se puede correr dos veces.
CREATE TABLE IF NOT EXISTS Bolsines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Codigo VARCHAR(20) NOT NULL,
    Descripcion VARCHAR(100) NULL,
    Estado VARCHAR(15) NOT NULL DEFAULT 'Disponible' COMMENT 'Disponible | Asignado | En viaje',
    Recorrido VARCHAR(10) NULL COMMENT 'Recorridos.Numero mientras está Asignado / En viaje',
    NumerodeOrden INT NULL COMMENT 'Logistica.NumerodeOrden de la salida (En viaje)',
    idUsuarioChofer INT NULL,
    FechaEstado DATETIME NULL,
    Eliminado TINYINT(1) NOT NULL DEFAULT 0,
    FechaAlta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_codigo (Codigo),
    KEY idx_recorrido_estado (Recorrido, Estado)
);

CREATE TABLE IF NOT EXISTS Bolsines_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idBolsin INT NOT NULL,
    Accion VARCHAR(15) NOT NULL COMMENT 'alta | asignado | desasignado | cargado | devuelto | baja',
    Recorrido VARCHAR(10) NULL,
    NumerodeOrden INT NULL,
    Usuario VARCHAR(50) NOT NULL,
    Fecha DATETIME NOT NULL,
    Observaciones VARCHAR(200) NULL,
    KEY idx_bolsin_fecha (idBolsin, Fecha)
);

-- Menú Logistica > Bolsines: mismos roles que ya ven Recorridos.
INSERT INTO usuarios_permisos (nombre, slug, seccion, es_sistema, Eliminado)
SELECT 'Bolsines', 'logistica_bolsines', 'Logistica', 0, 0
WHERE NOT EXISTS (SELECT 1 FROM usuarios_permisos WHERE slug = 'logistica_bolsines');
UPDATE usuarios_permisos SET Eliminado = 0 WHERE slug = 'logistica_bolsines';
INSERT INTO usuarios_rol_permiso (rol_id, permiso_id)
SELECT DISTINCT rp.rol_id, b.id
FROM usuarios_rol_permiso rp
JOIN usuarios_permisos r ON r.id = rp.permiso_id AND r.slug = 'logistica_recorridos'
JOIN usuarios_permisos b ON b.slug = 'logistica_bolsines'
WHERE NOT EXISTS (SELECT 1 FROM usuarios_rol_permiso x WHERE x.rol_id = rp.rol_id AND x.permiso_id = b.id);

-- Control: roles con el permiso nuevo.
SELECT rp.rol_id FROM usuarios_rol_permiso rp JOIN usuarios_permisos p ON p.id = rp.permiso_id WHERE p.slug = 'logistica_bolsines';
