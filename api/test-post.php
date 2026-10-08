<?php

http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => false,
    'message' => 'Endpoint pengujian tidak tersedia.',
], JSON_UNESCAPED_UNICODE);