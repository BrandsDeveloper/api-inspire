<?php

namespace App\Application\Models;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

Class Modulo extends Model{

    protected $table = 'modulos';

    protected $fillable = [
        "curso_id", "nome", "descricao", "created_at", "updated_at"
    ];

    public function getModulosByCurso(Request $req, Response $res, $args){

        $modulo = self::leftJoin('cursos', 'modulos.curso_id', '=', 'cursos.id')
        ->select( 'modulos.*' )
        ->where('cursos.id', '=', $args['id'])
        ->get();

        $res->getBody()->write(json_encode( $modulo ));
        return $res->withHeader('Content-Type', 'application/json');

    }

    public function getModuloById(Request $req, Response $res, $args){

        $modulo = self::leftJoin('cursos', 'modulos.curso_id', '=', 'cursos.id')
        ->select( 'modulos.*')
        ->where('cursos.id', '=', $args['curso_id'])
        ->where('modulos.id', '=', $args['modulo_id'])
        ->get();

        if($modulo->isEmpty()){
            $res->getBody()->write(json_encode( ['status' => '404', 'erro' => 'O modelo selecionado não tem nenhum vinculo com o curso']  ));
            return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
            exit;
        }

        $res->getBody()->write(json_encode( $modulo ));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function addModuloToCurso(Request $req, Response $res, $args){

        $dados = $req->getParsedBody();

        $modulo = self::leftJoin('cursos', 'modulos.curso_id', '=', 'cursos.id')
        ->select( 'modulos.*')
        ->where('cursos.id', '=', $args['id'])
        ->first();

        if( $modulo ){
    
            $insert = array(
                'curso_id' => $args['id'],
                'nome' => $dados["nome"],
                'descricao' => $dados["descricao"],
            );
            
            $modulo = Modulo::create( $insert );
            $res->getBody()->write(json_encode($modulo));
            return $res->withHeader('Content-Type', 'application/json');
        }
        
        $res->getBody()->write(json_encode([
            'status' => '404', 
            'erro' => 'O curso selecionado não existe!! Tente novamente com um curso válido'
        ]));
        return $res->withStatus(404)->withHeader('Content-Type', 'application/json');

    }

    public function updateModuloByCurso(Request $req, Response $res, $args){
        $dados = $req->getParsedBody();

        $modulo = self::leftJoin('cursos', 'modulos.curso_id', '=', 'cursos.id')
        ->select( 'modulos.*')
        ->where('cursos.id', '=', $args['curso_id'])
        ->where('modulos.id', '=', $args['modulo_id'])
        ->first();
    
        if( $modulo ){

            $insert = array(
                'nome' => $dados["nome"],
                'descricao' => $dados["descricao"],
                'updated_at' => date('m/d/Y h:i:s a', time())
            );

            $modulo->update( $insert );

            $res->getBody()->write(json_encode($modulo));
            return $res->withHeader('Content-Type', 'application/json');
        }

        $res->getBody()->write(json_encode([
            'status' => '404', 
            'erro' => 'O curso ou modelo selecionado não tem nenhum vinculo ou não existe!! Tente novamente com um curso ou modulo válido'
        ]));
        return $res->withStatus(404)->withHeader('Content-Type', 'application/json');

    }

    public function deleteModuloByCurso(Request $req, Response $res, $args){
        $moduloId = $args['modulo_id'];
        $cursoId = $args['curso_id'];

        $modulo = Modulo::where('id', $moduloId)
            ->where('curso_id', $cursoId)
            ->first();

        if (!$modulo) {

            $res->getBody()->write(json_encode([
                'error' => 'O módulo não pertence ao curso especificado ou não existe.'
            ]));
            return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $modulo->delete();

        $res->getBody()->write(json_encode([
            'message' => 'Módulo excluído com sucesso.',
            'modulo' => $modulo
        ]));
        return $res->withHeader('Content-Type', 'application/json');
    }
    
}