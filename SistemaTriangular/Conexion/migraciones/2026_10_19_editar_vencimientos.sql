-- Acción administrable desde Roles y Permisos. es_sistema evita que el sincronizador
-- de enlaces del menú dé de baja este permiso de acción.
INSERT INTO usuarios_permisos (nombre,slug,seccion,es_sistema,Eliminado)
SELECT 'Modificar vencimientos de facturas','__editar_vencimientos__','Admin',1,0
WHERE NOT EXISTS (SELECT 1 FROM usuarios_permisos WHERE slug='__editar_vencimientos__');
UPDATE usuarios_permisos SET Eliminado=0 WHERE slug='__editar_vencimientos__';
-- Habilitar el rol administrativo actual de Agustina Oviedo.
INSERT INTO usuarios_rol_permiso (rol_id,permiso_id)
SELECT DISTINCT u.rol_id,p.id FROM usuarios u JOIN usuarios_permisos p ON p.slug='__editar_vencimientos__'
WHERE u.Usuario='aoviedo' AND u.ACTIVO=1 AND u.rol_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM usuarios_rol_permiso rp WHERE rp.rol_id=u.rol_id AND rp.permiso_id=p.id);
