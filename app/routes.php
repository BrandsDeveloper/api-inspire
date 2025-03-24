<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/uploads/{filename}', function (Request $request, Response $response, $args) {
        $filename = $args['filename'];
        $filePath = __DIR__ . '/../uploads/' . $filename;
    
        if (!file_exists($filePath)) {
            return $response->withStatus(404)->write('Arquivo não encontrado');
        }
    
        // Obtém o tipo MIME do arquivo
        $mimeType = mime_content_type($filePath);
        
        // Configura os cabeçalhos para permitir cache e exibir corretamente no navegador
        $response = $response
            ->withHeader('Cache-Control', 'public, max-age=31536000') // Cache de longo prazo
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Length', filesize($filePath));
    
        // **IMPORTANTE**: Retornar um stream do arquivo para evitar caracteres quebrados
        $stream = fopen($filePath, 'rb'); // 'rb' = read binary (leitura binária)
        return $response->withBody(new \Slim\Http\Stream($stream));
    });
    

    require __DIR__ . '/Routes/inspire.php';

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });
};
