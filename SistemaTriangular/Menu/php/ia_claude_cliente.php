<?php
// Cliente HTTP mínimo para la API de Mensajes de Claude (Anthropic), + el loop de
// tool-use: Claude solo puede "hacer algo" llamando a una de las herramientas definidas
// en ia_herramientas.php — no ejecuta código, no navega internet, no corre SQL propio.

define('IA_ANTHROPIC_ENDPOINT', 'https://api.anthropic.com/v1/messages');
define('IA_ANTHROPIC_VERSION', '2023-06-01');
define('IA_MAX_ITERACIONES_TOOLS', 6);
define('IA_MAX_TOKENS', 1024);

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

// Corre el intercambio completo (incluyendo las vueltas de tool-use) y devuelve:
// ['texto' => respuesta final en texto, 'herramientas_usadas' => [...nombres...]]
function iaConversarConClaude(mysqli $mysqli, string $systemPrompt, array $messages, int $nivel): array
{
    $tools = iaDefinirHerramientas();
    $herramientasUsadas = [];

    for ($i = 0; $i < IA_MAX_ITERACIONES_TOOLS; $i++) {
        $respuesta = iaLlamarClaude([
            'model' => defined('ANTHROPIC_MODEL') ? ANTHROPIC_MODEL : 'claude-sonnet-5',
            'max_tokens' => IA_MAX_TOKENS,
            'system' => $systemPrompt,
            'messages' => $messages,
            'tools' => $tools,
        ]);

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
