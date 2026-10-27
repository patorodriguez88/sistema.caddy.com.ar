-- Asistente Caddy (Menu/php/ia_consultas.php): tokens y costo estimado de cada consulta, para ver
-- cuánto cuesta la API de Claude por usuario y por mes. Sin estas columnas el asistente igual
-- funciona y registra como antes. Se puede correr dos veces.
ALTER TABLE ia_logs
    ADD COLUMN IF NOT EXISTS modelo VARCHAR(40) NULL,
    ADD COLUMN IF NOT EXISTS llamadas INT NOT NULL DEFAULT 0 COMMENT 'llamadas a la API (vueltas de tool-use)',
    ADD COLUMN IF NOT EXISTS tokens_entrada INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS tokens_cache_escritura INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS tokens_cache_lectura INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS tokens_salida INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS costo_usd DECIMAL(10,6) NOT NULL DEFAULT 0;

-- Control: costo por mes y usuario.
-- SELECT DATE_FORMAT(fecha,'%Y-%m') mes, usuario, COUNT(*) consultas, SUM(costo_usd) usd
-- FROM ia_logs WHERE modelo IS NOT NULL GROUP BY mes, usuario;
