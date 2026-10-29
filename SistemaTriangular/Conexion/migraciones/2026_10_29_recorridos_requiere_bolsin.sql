-- Bolsines (29/10): el bolsín interno es obligatorio en los recorridos que el operador marque con el
-- switch "Bolsín" de Logística > Recorridos (antes se detectaba por pedidos de Dinter). La app de
-- reparto solo frena esos recorridos. Correr ANTES del deploy. Se puede correr dos veces.
ALTER TABLE Recorridos ADD COLUMN IF NOT EXISTS RequiereBolsin TINYINT(1) NOT NULL DEFAULT 0;
