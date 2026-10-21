-- Rec. Humanos > Tareas: módulo propio tipo Asana (reemplaza Tareas / Tareas_comentarios, que dependían
-- de Asana y HubSpot y quedan como archivo). Usuarios por id (usuarios.id), borrado lógico con Eliminado=1:
-- las tareas eliminadas se ven 30 días en "Eliminados" y se pueden recuperar.
-- Empleados/Procesos/php/tareas_api.php crea estas tablas si faltan; este archivo queda como referencia.

CREATE TABLE IF NOT EXISTS Proyectos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  Nombre VARCHAR(120) NOT NULL,
  Descripcion TEXT NULL,
  Color VARCHAR(9) NOT NULL DEFAULT '#727cf5',
  Todos TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = lo ven todos (Visible para todos), 0 = solo participantes',
  idCreador INT NOT NULL,
  FechaAlta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Eliminado TINYINT(1) NOT NULL DEFAULT 0,
  EliminadoFecha DATETIME NULL,
  EliminadoPor INT NULL,
  KEY idx_eliminado (Eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Participantes de cada proyecto: solo ellos lo ven (más los admins, nivel 1, y los proyectos con Todos=1).
-- No hay proyecto por defecto: cada uno arma los suyos, solo o en equipo.
-- Alguien de afuera asignado o mencionado ve solo esa tarea.
CREATE TABLE IF NOT EXISTS ProyectosMiembros (
  idProyecto INT NOT NULL,
  idUsuario INT NOT NULL,
  idAgrego INT NULL,
  Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (idProyecto, idUsuario),
  KEY idx_usuario (idUsuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ProyectosTareas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idProyecto INT NOT NULL,
  Titulo VARCHAR(200) NOT NULL,
  Descripcion TEXT NULL,
  idResponsable INT NOT NULL,
  idCreador INT NOT NULL,
  FechaVencimiento DATE NULL,
  Completada TINYINT(1) NOT NULL DEFAULT 0,
  CompletadaFecha DATETIME NULL,
  CompletadaPor INT NULL,
  FechaAlta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Actualizada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Último cambio o comentario: lo que no vio el responsable',
  Eliminado TINYINT(1) NOT NULL DEFAULT 0,
  EliminadoFecha DATETIME NULL,
  EliminadoPor INT NULL,
  KEY idx_responsable (idResponsable, Eliminado, Completada, FechaVencimiento),
  KEY idx_proyecto (idProyecto, Eliminado),
  KEY idx_eliminado (Eliminado, EliminadoFecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ProyectosTareas_comentarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idTarea INT NOT NULL,
  idUsuario INT NOT NULL,
  Comentario TEXT NOT NULL,
  Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Eliminado TINYINT(1) NOT NULL DEFAULT 0,
  KEY idx_tarea (idTarea, Eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ProyectosTareas_menciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idTarea INT NOT NULL,
  idComentario INT NULL COMMENT 'NULL = mención en la descripción de la tarea',
  idUsuario INT NOT NULL COMMENT 'Mencionado',
  idAutor INT NOT NULL,
  Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  MailEnviado TINYINT(1) NOT NULL DEFAULT 0,
  Vista TINYINT(1) NOT NULL DEFAULT 0,
  KEY idx_usuario (idUsuario, Vista),
  KEY idx_tarea (idTarea)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ProyectosTareas_vistas (
  idTarea INT NOT NULL,
  idUsuario INT NOT NULL,
  UltimaVista DATETIME NOT NULL,
  PRIMARY KEY (idTarea, idUsuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Archivos adjuntos (fotos, PDF, Word, Excel…). Se guardan en Empleados/Tareas/adjuntos/<idTarea>/ con un
-- nombre al azar; la carpeta no se sirve por URL: se bajan por tareas_api.php?accion=descargar con sesión.
CREATE TABLE IF NOT EXISTS ProyectosTareas_adjuntos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idTarea INT NOT NULL,
  idUsuario INT NOT NULL,
  NombreOriginal VARCHAR(255) NOT NULL,
  Archivo VARCHAR(120) NOT NULL COMMENT 'Nombre en disco',
  Mime VARCHAR(120) NOT NULL,
  Tamano INT NOT NULL,
  Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Eliminado TINYINT(1) NOT NULL DEFAULT 0,
  EliminadoFecha DATETIME NULL,
  Purgado TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'El archivo ya se borró del disco (30 días después de quitarlo)',
  KEY idx_tarea (idTarea, Eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
