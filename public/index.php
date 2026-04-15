<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Controller\MovementController;
use App\Http\Request;
use App\Http\Response;

$request  = new Request();
$response = new Response();

$method = $request->getMethod();
$path   = $request->getPath();

if ($method === 'GET' && preg_match('#^/movements/([^/]+)/ranking$#i', $path, $matches)) {
    $identifier = urldecode($matches[1]);

    $controller = new MovementController($response);
    $controller->ranking($identifier);
    return;
}

$response->notFound('Route not found. Use: GET /movements/{id_or_name}/ranking');
