<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/uploads/{filename}', function (Request $request, Response $response, $args) {
        $filename = $args['filename'];
        $filePath = __DIR__ . '/../uploads/' . $filename;
    
        if (!file_exists($filePath)) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/plain')->write('Arquivo não encontrado');
        }
    
        // Obtém o tipo MIME do arquivo
        $mimeType = mime_content_type($filePath);
    
        // Cria um stream para o arquivo
        $stream = Utils::tryFopen($filePath, 'rb'); // Método seguro para abrir o arquivo
    
        // Configura os cabeçalhos da resposta e envia o arquivo
        return $response
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Cache-Control', 'public, max-age=31536000') // Cache por 1 ano
            ->withHeader('Content-Length', filesize($filePath))
            ->withBody(new Stream($stream));
    });
    

    require __DIR__ . '/Routes/inspire.php';

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });
};
