-- Habilita el permiso puntual "Modificar Fecha de Envío" (Servicios > Seguimiento,
-- modal "Modificar Fecha de Envío"). Antes el chequeo de password estaba atado a
-- NIVEL=1 (Administrador) a fuego en el codigo, sin importar el rol/permiso.
-- es_sistema=1 lo deja exento del sincronizador de permisos del menu (ese
-- sincronizador solo reconcilia es_sistema=0), asi no se borra solo por no
-- venir de un link de topnav.html - es un permiso de ACCION, no de navegacion.

INSERT INTO usuarios_permisos (nombre, slug, seccion, es_sistema, Eliminado)
VALUES ('Modificar Fecha de Envío', 'accion_modificar_fecha_envio', 'Acciones Especiales', 1, 0);
