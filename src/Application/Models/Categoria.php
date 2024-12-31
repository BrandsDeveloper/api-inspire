<?php

namespace App\Application\Models;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

Class Categoria extends Model{

    protected $table = 'categorias';

    protected $fillable = [
        "nome", "descricao"
    ];

    public function getCategoria(Request $req, Response $res){

        $categoria = Categoria::get();
        $res->getBody()->write(json_encode( $categoria ));
        return $res->withHeader('Content-Type', 'application/json');

    }

    public function getCategoriaId(Request $req, Response $res, $args){

        $categoria = Categoria::findOrFail($args['id']);
        $res->getBody()->write(json_encode( $categoria ));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function addCategoria(Request $req, Response $res){

        $dados = $req->getParsedBody();
        $categoria = Categoria::create( $dados );
        $res->getBody()->write(json_encode($categoria));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function updateCategoria(Request $req, Response $res, $args){
        
        $dados = $req->getParsedBody();    
        $categoria = Categoria::findOrFail($args['id']);
        $categoria->update( $dados );
        $res->getBody()->write(json_encode($categoria));
        return $res->withHeader('Content-Type', 'application/json');
        
    }

    public function deleteCategoria(Request $req, Response $res, $args){
        $categoria = Categoria::findOrFail( $args['id'] );
        $categoria->delete();
        $res->getBody()->write(json_encode($categoria));
        return $res->withHeader('Content-Type', 'application/json');
    }
    
}