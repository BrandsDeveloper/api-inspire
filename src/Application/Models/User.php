<?php

namespace App\Application\Models;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

Class User extends Model{

    protected $fillable = [
        "id", "nome", "descricao", "email", "cpf", "celular", "genero", "senha", "url_foto_perfil", "token", "created_at", "updated_at",
    ];
    
    public function login(Request $req, Response $res) {

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
                'message' => 'Usuário Autenticado',
                'auth' => true,
            ]));
            return $res->withHeader('Content-Type', 'application/json');
        }

        $res->getBody()->write(json_encode([
            'status' => 'erro',
            'message' => 'Credenciais inválidas.',
            'auth' => false,
        ]));
        return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    public function getUser(Request $req, Response $res){
        $user = User::get();
        $res->getBody()->write(json_encode( $user ));
        return $res->withHeader('Content-Type', 'application/json');
    }

}