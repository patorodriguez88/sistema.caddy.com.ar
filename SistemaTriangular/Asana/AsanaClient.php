<?php

declare(strict_types=1);

class AsanaClient
{
    private mysqli $mysqli;

    // 🔒 Ideal: mover client_id/secret/redirect a tabla o config fuera del repo
    private string $clientId = '1204867479928301';
    private string $clientSecret = '84f466e023db6f9958ddebad539b4df6';
    private string $redirectUri = 'https://www.sistemacaddy.com.ar/Api/Asana/recepcion.php';

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    private function getApiRow(): array
    {
        $res = $this->mysqli->query("SELECT token, refresh_token FROM Api WHERE id=2 LIMIT 1");
        if (!$res) throw new RuntimeException("SQL Api: " . $this->mysqli->error);
        $row = $res->fetch_assoc();
        if (!$row) throw new RuntimeException("No existe Api id=2");
        return $row;
    }

    private function saveAccessToken(string $token): void
    {
        $tokenEsc = $this->mysqli->real_escape_string($token);
        $ok = $this->mysqli->query("UPDATE Api SET token='$tokenEsc' WHERE id=2");
        if (!$ok) throw new RuntimeException("No se pudo actualizar token: " . $this->mysqli->error);
    }

    private function refreshToken(string $refreshToken): bool
    {
        $postFields = http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'refresh_token' => $refreshToken
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://app.asana.com/-/oauth_token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $resp = curl_exec($curl);
        $http = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err  = curl_error($curl);
        curl_close($curl);

        if ($resp === false || $http !== 200) {
            error_log("ASANA refresh fail http=$http err=$err resp=$resp");
            return false;
        }

        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data['access_token'])) {
            error_log("ASANA refresh invalid resp=$resp");
            return false;
        }

        $this->saveAccessToken($data['access_token']);
        return true;
    }

    /**
     * request() hace:
     * - usa token actual
     * - si recibe 401 intenta refresh 1 vez
     * - reintenta
     */
    public function request(string $method, string $url, ?array $body = null): array
    {
        $api = $this->getApiRow();

        $attempt = 0;
        while ($attempt < 2) {
            $attempt++;

            $token = trim((string)($api['token'] ?? ''));
            if ($token === '') {
                return ['ok' => false, 'http_code' => 401, 'error' => 'Token Asana vacío'];
            }

            $curl = curl_init();

            $headers = [
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ];

            $opts = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
            ];

            if ($body !== null) {
                $payload = json_encode(['data' => $body], JSON_UNESCAPED_UNICODE);
                $headers[] = 'Content-Type: application/json';
                $opts[CURLOPT_HTTPHEADER] = $headers;
                $opts[CURLOPT_POSTFIELDS] = $payload;
            }

            curl_setopt_array($curl, $opts);

            $resp = curl_exec($curl);
            $http = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $err  = curl_error($curl);
            curl_close($curl);

            if ($resp === false) {
                return ['ok' => false, 'http_code' => $http, 'error' => $err ?: 'cURL error'];
            }

            $decoded = json_decode($resp, true);

            // Si 401, refrescar y reintentar 1 vez
            if ($http === 401 && $attempt === 1 && !empty($api['refresh_token'])) {
                $refreshed = $this->refreshToken((string)$api['refresh_token']);
                if ($refreshed) {
                    $api = $this->getApiRow(); // recargar token nuevo
                    continue;
                }
            }

            return [
                'ok' => ($http >= 200 && $http < 300),
                'http_code' => $http,
                'decoded' => $decoded,
                'raw' => $resp
            ];
        }

        return ['ok' => false, 'http_code' => 401, 'error' => 'No autorizado'];
    }
}
