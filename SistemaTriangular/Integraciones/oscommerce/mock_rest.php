<?php
// mock_rest.php  (ejecutá con Apache/PHP normal)
header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$sampleOrder = [
    'order_id' => 12345,
    'created_at' => '2025-09-17 10:20:00',
    'customer' => ['email' => 'cliente@test.com', 'phone' => '+5493511234567'],
    'shipping' => [
        'firstname' => 'Juan',
        'lastname' => 'Pérez',
        'address1' => 'Av. Siempre Viva 742',
        'address2' => '',
        'city' => 'Córdoba',
        'state' => 'CBA',
        'postcode' => '5000'
    ],
    'total' => ['grand_total' => 107690.00],
    'payment' => ['method' => 'cod', 'cod_amount' => 107690.00],
    'items' => [
        ['sku' => 'SKU-001', 'name' => 'Producto A', 'qty' => 1, 'price' => 107690.00]
    ]
];

if ($method === 'GET' && preg_match('#/orders/(\d+)$#', $path, $m)) {
    $sampleOrder['order_id'] = (int)$m[1];
    echo json_encode($sampleOrder, JSON_UNESCAPED_UNICODE);
    exit;
}
if ($method === 'GET' && preg_match('#/orders$#', $path)) {
    echo json_encode([$sampleOrder], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($method === 'POST' && preg_match('#/orders$#', $path)) {
    // simula crear/recibir pedido
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $in['order_id'] = $in['order_id'] ?? random_int(10000, 99999);
    echo json_encode(['ok' => true, 'stored' => $in], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'not found']);
