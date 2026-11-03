-- Permiso por usuario para permitir en Logística > Órdenes que un recorrido arranque sin escanear
-- ("ESCANEO OMITIDO", Logistica.OmitirControlEscaneo). Hasta ahora lo podía prender cualquiera.
-- Se tilda en Empleados > Usuarios, columna "Omitir Escaneo" (mismo patrón que PuedeEliminarPagos).
-- Nivel 1 siempre puede. Volver a exigir el escaneo lo puede hacer cualquiera.
-- Correr ANTES de subir el código: sin la columna la lista de Usuarios no carga.
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS PuedeOmitirEscaneo TINYINT(1) NOT NULL DEFAULT 0;

-- Control: nadie habilitado todavía; se tildan desde la pantalla.
SELECT id, Usuario, NIVEL, PuedeOmitirEscaneo FROM usuarios WHERE Activo = 1 AND NIVEL IN (1, 2, 7) ORDER BY NIVEL, Usuario;
