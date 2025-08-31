CREATE DATABASE IF NOT EXISTS n455735_new
USE ln455735_new;

-- Tabla de rutas
CREATE TABLE Rutas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origen_lat DECIMAL(10, 7),
    origen_lng DECIMAL(10, 7),
    destino_lat DECIMAL(10, 7),
    destino_lng DECIMAL(10, 7),
    distancia_km DECIMAL(10, 2),
    duracion_min INT,
    vehiculo_tipo VARCHAR(20),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de paradas intermedias
CREATE TABLE Rutas_paradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT,
    lat DECIMAL(10, 7),
    lng DECIMAL(10, 7),
    tiempo_estadia INT, -- en segundos
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE
);

-- Tabla de choferes
CREATE TABLE Rutas_choferes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    telefono VARCHAR(20),
    vehiculo VARCHAR(50)
);

-- Tabla de asignación de rutas a choferes
CREATE TABLE Rutas_asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT,
    chofer_id INT,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ruta_id) REFERENCES rutas(id),
    FOREIGN KEY (chofer_id) REFERENCES choferes(id)
);