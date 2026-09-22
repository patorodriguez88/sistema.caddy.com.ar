<?php
// ============================================================
// CÓDIGO DADO DE BAJA — auditoría 2026-09-22 (a pedido de Patricio)
//
// Motivo: Backend del sistema de compras viejo (ver Compras.php); usa mysql_query().
//
// Se apaga en vez de borrar directamente, por seguridad: si nadie lo
// reclama en unos días, se elimina del repo. El código original queda
// disponible en el historial de git ("git log -- Proveedores/Procesos/php/compras.php").
// ============================================================
http_response_code(410);
die('Esta pantalla fue dada de baja el 2026-09-22 (código sin uso, confirmado en auditoría). Si la necesitás, avisá a sistemas.');
