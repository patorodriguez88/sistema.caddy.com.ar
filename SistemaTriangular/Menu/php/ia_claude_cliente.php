<?php
// Cliente HTTP mínimo para la API de Mensajes de Claude (Anthropic), + el loop de
// tool-use: Claude solo puede "hacer algo" llamando a una de las herramientas definidas
// en ia_herramientas.php — no ejecuta código, no navega internet, no corre SQL propio.

define('IA_ANTHROPIC_ENDPOINT', 'https://api.anthropic.com/v1/messages');
define('IA_ANTHROPIC_VERSION', '2023-06-01');
define('IA_MAX_ITERACIONES_TOOLS', 6);
define('IA_MAX_TOKENS', 1024);

// USD por millón de tokens [entrada, salida]. La escritura de cache cuesta 1,25× la entrada y la
// lectura 0,1×. Solo para registrar el costo estimado de cada consulta en ia_logs.
const IA_PRECIOS_USD = [
    'claude-haiku-5-5'  => [0.10, 0.50],
    'claude-sonnet-5'   => [2.00, 10.00],
    'claude-sonnet-5-5' => [2.00, 10.00],
    'claude-opus-5-5'   => [4.00, 20.00],
];

class IAClaudeException extends Exception {}

function iaLlamarClaude(array $body): array
{
    if (!defined('ANTHROPIC_API_KEY') || ANTHROPIC_API_KEY === '' || ANTHROPIC_API_KEY === 'TU_API_KEY_DE_ANTHROPIC_AQUI') {
        throw new IAClaudeException('Falta configurar la API key de Claude (Conexion/claude_config.php).');
    }

    $ch = curl_init(IA_ANTHROPIC_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'content-type: application/json',
            'x-api-key: ' . ANTHROPIC_API_KEY,
            'anthropic-version: ' . IA_ANTHROPIC_VERSION,
        ],
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_TIMEOUT => 30,
    ]);

    $respuesta = curl_exec($ch);
    $errorCurl = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false) {
        throw new IAClaudeException('No se pudo conectar con Claude: ' . $errorCurl);
    }

    $decodificado = json_decode($respuesta, true);

    if ($status >= 400) {
        $msg = $decodificado['error']['message'] ?? "Error HTTP $status de la API de Claude.";
        throw new IAClaudeException($msg);
    }

    if (!is_array($decodificado)) {
        throw new IAClaudeException('Respuesta inválida de la API de Claude.');
    }

    return $decodificado;
}

function iaModelo(): string
{
    return defined('ANTHROPIC_MODEL') ? ANTHROPIC_MODEL : 'claude-sonnet-5';
}

// Suma el usage de cada llamada (una consulta hace varias por las vueltas de tool-use).
function iaSumarUso(array &$uso, array $respuesta): void
{
    $u = $respuesta['usage'] ?? [];
    $uso['llamadas']++;
    $uso['entrada'] += (int) ($u['input_tokens'] ?? 0);
    $uso['cache_escritura'] += (int) ($u['cache_creation_input_tokens'] ?? 0);
    $uso['cache_lectura'] += (int) ($u['cache_read_input_tokens'] ?? 0);
    $uso['salida'] += (int) ($u['output_tokens'] ?? 0);
    [$pIn, $pOut] = IA_PRECIOS_USD[$uso['modelo']] ?? IA_PRECIOS_USD['claude-sonnet-5'];
    $uso['costo_usd'] = round(($uso['entrada'] * $pIn + $uso['cache_escritura'] * $pIn * 1.25
        + $uso['cache_lectura'] * $pIn * 0.1 + $uso['salida'] * $pOut) / 1000000, 6);
}

// Corre el intercambio completo (incluyendo las vueltas de tool-use) y devuelve:
// ['texto' => respuesta final en texto, 'herramientas_usadas' => [...nombres...]]. Tokens y costo se
// acumulan en $uso (por referencia, así quedan aunque una vuelta falle).
//
// Cache de prompt: $systemFijo (igual para todos) lleva un breakpoint propio, así herramientas +
// instrucciones se leen de cache (0,1× el precio) en cada vuelta y entre consultas de cualquier
// usuario dentro de los 5 minutos. $systemVariable (fecha, usuario) va después del breakpoint para
// no romperlo. El cache_control de nivel superior va moviendo un segundo breakpoint al final de la
// conversación, así cada vuelta de tool-use relee lo de la anterior.
function iaConversarConClaude(mysqli $mysqli, string $systemFijo, string $systemVariable, array $messages, int $nivel, array &$uso): array
{
    $tools = iaDefinirHerramientas();
    $herramientasUsadas = [];
    $system = [
        ['type' => 'text', 'text' => $systemFijo, 'cache_control' => ['type' => 'ephemeral']],
        ['type' => 'text', 'text' => $systemVariable],
    ];

    for ($i = 0; $i < IA_MAX_ITERACIONES_TOOLS; $i++) {
        $respuesta = iaLlamarClaude([
            'model' => $uso['modelo'],
            'max_tokens' => IA_MAX_TOKENS,
            'cache_control' => ['type' => 'ephemeral'],
            'system' => $system,
            'messages' => $messages,
            'tools' => $tools,
        ]);
        iaSumarUso($uso, $respuesta);

        $contenido = $respuesta['content'] ?? [];
        $stopReason = $respuesta['stop_reason'] ?? '';

        if ($stopReason !== 'tool_use') {
            $texto = '';
            foreach ($contenido as $bloque) {
                if (($bloque['type'] ?? '') === 'text') {
                    $texto .= $bloque['text'];
                }
            }
            return ['texto' => trim($texto) ?: 'No tengo una respuesta para eso.', 'herramientas_usadas' => $herramientasUsadas];
        }

        // PHP decodifica tanto "{}" como "[]" como un array vacío, así que un tool_use
        // sin parámetros (ej. resumen_logistica_mes) se reenviaría como [] en vez de {}
        // y la API lo rechaza ("Input should be an object"). Lo normalizamos acá.
        foreach ($contenido as &$bloqueRef) {
            if (($bloqueRef['type'] ?? '') === 'tool_use' && empty($bloqueRef['input'])) {
                $bloqueRef['input'] = new stdClass();
            }
        }
        unset($bloqueRef);

        // Guardamos el turno del asistente (con sus tool_use) y ejecutamos cada herramienta.
        $messages[] = ['role' => 'assistant', 'content' => $contenido];

        $resultados = [];
        foreach ($contenido as $bloque) {
            if (($bloque['type'] ?? '') !== 'tool_use') continue;

            $nombreHerramienta = $bloque['name'];
            $herramientasUsadas[] = $nombreHerramienta;

            try {
                $salida = iaEjecutarHerramienta($mysqli, $nombreHerramienta, (array)($bloque['input'] ?? []), $nivel);
            } catch (Throwable $e) {
                $salida = ['error' => 'Error interno ejecutando la herramienta.'];
            }

            $resultados[] = [
                'type' => 'tool_result',
                'tool_use_id' => $bloque['id'],
                'content' => json_encode($salida, JSON_UNESCAPED_UNICODE),
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $resultados];
    }

    return [
        'texto' => 'La consulta se volvió demasiado compleja y no llegué a una respuesta. ¿Podés reformularla en una pregunta más simple?',
        'herramientas_usadas' => $herramientasUsadas,
    ];
}
