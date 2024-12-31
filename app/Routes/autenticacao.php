<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Models\User;
use Firebase\JWT\JWT;
use App\Application\Settings\SettingsInterface;


return function (App $app) {

    $app->post('/v1/token', function (Request $req, Response $res) use ($app) {

        $dados = $req->getParsedBody();

        $cpf = $dados['cpf'] ?? null;
        $email = $dados['email'] ?? null;
        $senha = $dados['senha'] ?? null;

        if (!$cpf && !$email) {
            $res->getBody()->write(json_encode([
                'status' => 'erro',
                'message' => 'Informe CPF ou Email para autenticação.',
            ]));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = User::where('email', $email)
            ->orWhere('cpf', $cpf)
            ->first();

        if ($user && md5($senha) === $user->senha) {

            $secretKey = $app->getContainer()->get(SettingsInterface::class)->get('secretKey');
            $token = JWT::encode($user->email, $secretKey, 'HS256');

            $user->update( [
                'token' => $token
            ]);

            $res->getBody()->write(json_encode([
                'status' => 'sucesso',
                'key' => $token,
            ]));
            return $res->withHeader('Content-Type', 'application/json');
        }

        $res->getBody()->write(json_encode([
            'status' => 'erro',
            'message' => 'Credenciais inválidas.',
        ]));
        return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
    });
};