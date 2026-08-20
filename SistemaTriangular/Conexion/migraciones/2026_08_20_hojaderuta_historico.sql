-- Manifiesto histórico de Hoja de Ruta (liquidación de choferes).
-- Ver plan en la conversación del 2026-08-20. Correr a mano contra producción
-- (no forma parte del deploy automático por FTP).

CREATE TABLE HojaDeRuta_Historico (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idHojaDeRuta INT NOT NULL,              -- HojaDeRuta.id de la fila viva que se sobreescribió
  Fecha DATE,
  Hora TIME,
  Recorrido DOUBLE NOT NULL,
  Localizacion TEXT,
  Ciudad TEXT,
  Provincia TEXT,
  Pais TEXT,
  Cliente CHAR(50),
  Titulo TEXT,
  Observaciones TEXT,
  Usuario TEXT,
  Asignado TEXT,
  Estado TEXT,
  NumerodeOrden INT,
  Posicion MEDIUMINT DEFAULT 0,
  Seguimiento VARCHAR(10),
  idCliente INT DEFAULT 0,
  Celular VARCHAR(20),
  TramoMapa INT,
  Eliminado TINYINT(1) DEFAULT 0,
  Avisado TINYINT(1) DEFAULT 0,
  ImporteCobranza DOUBLE(10,2),
  NumeroRepo INT,
  KmO DOUBLE(10,2),
  Tiempo DOUBLE,
  idTransClientes INT DEFAULT 0,
  Devuelto TINYINT(1) DEFAULT 0,
  Servicio CHAR(10),
  Posicion_retiro MEDIUMINT DEFAULT 0,
  Hora_retiro TIME,
  -- auditoría del cambio que disparó el snapshot
  RecorridoNuevo DOUBLE NOT NULL,
  NumerodeOrdenNuevo INT,
  FechaCambio DATE NOT NULL,
  HoraCambio TIME NOT NULL,
  UsuarioCambio VARCHAR(50),
  MotivoCambio TEXT,
  TimeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_Seguimiento (Seguimiento),
  KEY idx_NumerodeOrden (NumerodeOrden),
  KEY idx_NumerodeOrdenNuevo (NumerodeOrdenNuevo)
);
