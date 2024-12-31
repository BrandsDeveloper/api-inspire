<?php

declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Application\Settings\SettingsInterface;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    
    $app->add(new Tuupola\Middleware\JwtAuthentication([
        "header" => "Authorization",
        "regexp" => "/^Bearer\s(\S+)/",
        "path" => "/v1",
        "ignore" => ["/v1/token"],
        "secret" => $app->getContainer()->get(SettingsInterface::class)->get('secretKey'),
        "algorithm" => ["HS256"],
        "error" => function ($res, $args) use ($app) {
            error_log('Token JWT: ' . json_encode($args));
            error_log('Secret Key: ' . $app->getContainer()->get(SettingsInterface::class)->get('secretKey'));
            $data = [
                "status" => "Error",
                "message" => $args["message"] . ' Defina um token para acessar a API'
            ];
            $res->getBody()->write( json_encode( $data ) );
            return $res
                ->withHeader("Content-Type", "application/json");
        },
    ]));
    
    $app->add(function (Request $request, RequestHandler $handler): Response {
        if ($request->getMethod() === 'OPTIONS') {
            return $handler->handle($request);
        }
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization, X-Requested-With, Content-Type, Accept, Origin')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });
    
};