<?php

namespace App\Application\Models;

use Slim\App;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Settings\SettingsInterface;
use Firebase\JWT\JWT;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

Class User extends Model{

    protected $table = 'users';

    protected $fillable = [
        "id", "nome", "descricao", "email", "cpf", "celular", "genero", "senha", "url_foto_perfil", "token", "created_at", "updated_at",
    ];

    private $container;

    public function __construct($container = null) {
        $this->container = $container;
        parent::__construct();
    }
    
    public function token(Request $req, Response $res) {

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

            $secretKey = $this->container->get(SettingsInterface::class)->get('secretKey');
            $payload = [
                'email' => $user->email,
                'iat' => time(), // Data de emissão
                // 'exp' => time() + 3600, 
            ];
            $token = JWT::encode($payload, $secretKey, 'HS256');

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
    }
}