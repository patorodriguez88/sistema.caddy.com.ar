<?php
// ============================================================
// CÓDIGO DADO DE BAJA — auditoría 2026-09-22 (a pedido de Patricio)
//
// Motivo: Sin ninguna referencia en el resto del sistema; usa mysql_query().
//
// Se apaga en vez de borrar directamente, por seguridad: si nadie lo
// reclama en unos días, se elimina del repo. El código original queda
// disponible en el historial de git ("git log -- Ventas/Reposiciones_old.php").
// ============================================================
http_response_code(410);
die('Esta pantalla fue dada de baja el 2026-09-22 (código sin uso, confirmado en auditoría). Si la necesitás, avisá a sistemas.');
