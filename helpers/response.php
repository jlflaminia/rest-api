<?php

/**
 * Helpers: HTTP Response
 * Sends a JSON response and terminates execution.
 */

function respond(int $code, mixed $data = null, ?string $error = null): never
{
    http_response_code($code);

    if ($error !== null) {
        echo json_encode([
            'status'  => 'error',
            'message' => $error,
            'code'    => $code,
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'status' => 'success',
            'data'   => $data,
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}

function respondPaginated(int $code, array $data, int $total, int $page, int $perPage): never
{
    http_response_code($code);
    echo json_encode([
        'status' => 'success',
        'data'   => $data,
        'meta'   => [
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / $perPage),
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
