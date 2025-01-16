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
        $filename = $args['filename']; // Obtém o nome do arquivo da URL
    
        $filePath = __DIR__ . '/../uploads/' . $filename;
    
        // if (file_exists($filePath)) {
            
        //     $mimeType = mime_content_type($filePath);
        //     $response = $response->withHeader('Content-Type', $mimeType);
    
        //     $fileContent = file_get_contents($filePath);
        //     $response->getBody()->write($fileContent);
    
        //     return $response;
        // }

        if (file_exists($filePath)) {
            // Usando finfo para obter o MIME type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($filePath);
            
            $response = $response->withHeader('Content-Type', $mimeType);
        
            $fileContent = file_get_contents($filePath);
            $response->getBody()->write($fileContent);
        
            return $response;
        }
    
        return $response->withStatus(404)->write('Arquivo não encontrado');
    });

    require __DIR__ . '/Routes/inspire.php';

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });
};
