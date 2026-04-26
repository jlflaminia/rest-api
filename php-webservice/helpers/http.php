<?php

/**
 * Helpers: HTTP Client
 * Reusable cURL wrapper for consuming external REST APIs.
 */

class HttpClient
{
    private string $baseUrl;
    private array  $headers = ['Accept: application/json'];
    private int    $timeout = 10;

    public function __construct(string $baseUrl = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function withToken(string $token): static
    {
        $this->headers[] = "Authorization: Bearer {$token}";
        return $this;
    }

    public function withHeader(string $header): static
    {
        $this->headers[] = $header;
        return $this;
    }

    public function setTimeout(int $seconds): static
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        return $this->request('GET', $url);
    }

    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $this->baseUrl . $path, $data);
    }

    public function put(string $path, array $data = []): array
    {
        return $this->request('PUT', $this->baseUrl . $path, $data);
    }

    public function patch(string $path, array $data = []): array
    {
        return $this->request('PATCH', $this->baseUrl . $path, $data);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $this->baseUrl . $path);
    }

    private function request(string $method, string $url, ?array $data = null): array
    {
        $ch      = curl_init($url);
        $headers = $this->headers;

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        $body       = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error      = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL error: {$error}");
        }

        return [
            'status' => $statusCode,
            'data'   => json_decode($body, true),
            'raw'    => $body,
        ];
    }
}
