-- Gastos Extras (Admin/GastosExtras.php): gastos cargados fuera de Tesorería. No pasan por
-- Mayor de Cuentas ni Libro de IVA; solo se suman al Cuadro de Resultados. Correr ANTES de
-- subir el código (Empleados > Usuarios lee la columna nueva). Se puede correr dos veces.
CREATE TABLE IF NOT EXISTS GastosExtras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Fecha DATE NOT NULL,
    Categoria VARCHAR(20) NOT NULL COMMENT 'Personal | Logistica | Generales | Financieros',
    Descripcion VARCHAR(200) NOT NULL,
    Importe DECIMAL(12,2) NOT NULL,
    Observaciones VARCHAR(200) NULL,
    Usuario VARCHAR(50) NOT NULL,
    Eliminado TINYINT(1) NOT NULL DEFAULT 0,
    InfoABM VARCHAR(200) NULL,
    TimeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_fecha (Fecha),
    KEY idx_categoria (Categoria)
);

-- Permiso por usuario, independiente del rol (mismo patrón que PuedeEliminarPagos).
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS PuedeGestionarGastosExtras TINYINT(1) NOT NULL DEFAULT 0;

-- Agustina Oviedo (aoviedo) y Cintia Carranza (ccarranza). OJO: coviedo es Carolina, no Cintia.
UPDATE usuarios SET PuedeGestionarGastosExtras = 1 WHERE id IN (450, 8) AND Usuario IN ('aoviedo', 'ccarranza');

-- La pantalla está en el menú (Admin > Contabilidad), así que el rol también tiene que tenerla:
-- se habilita al rol de Agustina y Cintia (Administracion). Los demás de ese rol igual no
-- entran sin el permiso por usuario de arriba.
INSERT INTO usuarios_permisos (nombre, slug, seccion, es_sistema, Eliminado)
SELECT 'Gastos Extras', 'admin_gastos_extras', 'Admin', 0, 0
WHERE NOT EXISTS (SELECT 1 FROM usuarios_permisos WHERE slug = 'admin_gastos_extras');
UPDATE usuarios_permisos SET Eliminado = 0 WHERE slug = 'admin_gastos_extras';
INSERT INTO usuarios_rol_permiso (rol_id, permiso_id)
SELECT DISTINCT u.rol_id, p.id FROM usuarios u JOIN usuarios_permisos p ON p.slug = 'admin_gastos_extras'
WHERE u.id IN (450, 8) AND u.rol_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM usuarios_rol_permiso rp WHERE rp.rol_id = u.rol_id AND rp.permiso_id = p.id);

-- Control: tienen que salir las 2 con PuedeGestionarGastosExtras = 1.
SELECT id, Usuario, rol_id, PuedeGestionarGastosExtras FROM usuarios WHERE id IN (450, 8);
