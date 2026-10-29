-- Colecta manual (Ventas > Colecta, 29/10): el operador arma la colecta eligiendo cliente y servicios
-- a retirar, y la edita hasta que el chofer la marca como retirada. La automática de Flex no cambia.
-- Correr ANTES del deploy. Se puede correr dos veces.
ALTER TABLE Colecta ADD COLUMN IF NOT EXISTS Manual TINYINT(1) NOT NULL DEFAULT 0;

-- Paquetes que el chofer encontró en el retiro y no están cargados en el sistema (app de reparto).
CREATE TABLE IF NOT EXISTS ColectaSobrantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idColecta INT NOT NULL,
    Codigo VARCHAR(120) NOT NULL,
    Usuario VARCHAR(50) NOT NULL,
    Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_colecta (idColecta)
);
