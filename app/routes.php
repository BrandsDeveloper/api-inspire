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
        
        // Adiciona cabeçalhos de cache
        $response = $response
            ->withHeader('Cache-Control', 'public, max-age=31536000') // Cache por 1 ano
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Length', filesize($filePath));
    
        // Usa `readfile()` em vez de `file_get_contents()` para melhor performance
        readfile($filePath);
        
        return $response;
    });
    

    require __DIR__ . '/Routes/inspire.php';

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });
};
