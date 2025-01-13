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
        "ignore" => ["/v1/token", "/v1/auth", "/v1/user/create"],
        "secret" => $app->getContainer()->get(SettingsInterface::class)->get('secretKey'),
        "algorithm" => ["HS256"],
        "error" => function ($res, $args) {
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
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', 'https://api-inspire.brandsdev.com.br')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });
    
};