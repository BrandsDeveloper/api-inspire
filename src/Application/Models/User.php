<?php

namespace App\Application\Models;

use App\Application\Settings\SettingsInterface;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

Class User extends Model{

    protected $fillable = [
        "id", "nome", "descricao", "email", "cpf", "celular", "genero", "senha", "url_foto_perfil", "token", "created_at", "updated_at",
    ];
    
    public function auth(Request $req, Response $res) {

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

        if ($user && $senha === $user->senha) {

            $res->getBody()->write(json_encode([
                'status' => 'sucesso',
                'message' => 'Login realizado com sucesso',
                'auth' => true,
                'id' => $user->id,
                'token' => $user->token,
            ]));
            return $res->withHeader('Content-Type', 'application/json');
        }

        $res->getBody()->write(json_encode([
            'status' => 'erro',
            'message' => 'Credenciais inválidas, verifique sua senha e tente novamente.',
            'auth' => false,
        ]));
        return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    public function getUserByID(Request $req, Response $res, $args){
        $user = User::findOrFail( $args['id'] );
        $res->getBody()->write(json_encode( $user ));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function addUser(Request $req, Response $res) {
        $dados = $req->getParsedBody();

        global $container;
        $settings = $container->get(SettingsInterface::class);
        $secretKey = $settings->get('secretKey');

        $userFilter = User::where('email', $dados['email'])
        ->orWhere('cpf', $dados['cpf'])
        ->first();

        if($userFilter){
            if($dados['email'] === $userFilter->email){
                $res->getBody()->write(json_encode([
                    'status' => 'erro',
                    'message' => 'O e-mail que você inseriu já está em uso. Tente fazer login.',
                ]));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            if($dados['cpf'] === $userFilter->cpf){
                $res->getBody()->write(json_encode([
                    'status' => 'erro',
                    'message' => 'O CPF que você inseriu já está em uso. Tente fazer login.',
                ]));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
        }

        $payload = [
            'email' => $dados['email'],
            'iat' => time(), // Data de emissão
            // 'exp' => time() + 3600, 
        ];
        $token = JWT::encode($payload, $secretKey, 'HS256');

        $insert = [
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'cpf' => $dados['cpf'],
            'celular' => $dados['celular'],
            'senha' => $dados['senha'],
            'token' => $token
        ];

        $user = User::create($insert);
        $res->getBody()->write($user->toJson());
        // $res->getBody()->write(json_encode($user));
        return $res->withHeader('Content-Type', 'application/json');

    }

}