<?php

namespace App\Application\Models;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
date_default_timezone_set('America/Sao_Paulo');

Class Aula extends Model{

    protected $table = 'aulas';

    protected $fillable = [
        'modulo_id', 'nome', 'descricao', 'url_capa', 'url_video', 'duracao', 'completed', "created_at", "updated_at"
    ];

    public function getAulasByModulo(Request $req, Response $res, $args){

        $aula = self::leftJoin('modulos', 'aulas.modulo_id', '=', 'modulos.id')
        ->select( 'aulas.*' )
        ->where('modulos.id', '=', $args['modulo_id'])
        ->get();

        if($aula->isEmpty()){
            $res->getBody()->write(json_encode( [
                'status' => '404', 
                'erro' => 'O modulo selecionada não existe!!'
            ]));
            return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
            exit;
        }

        $res->getBody()->write(json_encode( $aula ));
        return $res->withHeader('Content-Type', 'application/json');

    }

    public function getAulaByModulo(Request $req, Response $res, $args){

        $aula = self::leftJoin('modulos', 'aulas.modulo_id', '=', 'modulos.id')
        ->select( 'aulas.*')
        ->where('modulos.slug', '=', $args['modulo_slug'])
        ->where('aulas.id', '=', $args['aula_id'])
        ->get();

        if($aula->isEmpty()){
            $res->getBody()->write(json_encode( [
                'status' => '404', 
                'erro' => 'A aula selecionada não tem nenhum vinculo com o modulo'
            ]));
            return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
            exit;
        }

        $res->getBody()->write(json_encode( $aula ));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function addAulaToModulo(Request $req, Response $res, $args){

        $dados = $req->getParsedBody();
        $file = $req->getUploadedFiles();
        $aula = self::leftJoin('aulas', 'modulos.curso_id', '=', 'aulas.id')
        ->select( 'modulos.*')
        ->where('aulas.id', '=', $args['id'])
        ->first();
        
        $arquivo = $file['url_capa'];
        $nome_arquivo = rand(1000000000,10000000000). '-' . $file['url_capa']->getClientFilename();

        $caminhoArquivo = __DIR__ . '/../../../uploads/' . $nome_arquivo;
        $arquivo->moveTo($caminhoArquivo);

        if( $aula ){
    
            $insert = array(
                'modulo_id' => $args['id'],
                'nome' => $dados["nome"],
                'descricao' => $dados["descricao"],
                'url_capa' => 'https://api-inspire.brandsdev.com.br/uploads/'.$nome_arquivo,
                'url_video' => $dados["url_video"],
                'duracao' => $dados["duracao"],
                'completed' => $dados["completed"],
            );
            
            $aula = Aula::create( $insert );
            $res->getBody()->write(json_encode([
                'message' => 'Aula adicionada com sucesso!',
                'aula' => $aula
            ]));
            return $res->withHeader('Content-Type', 'application/json');
        }
        
        $res->getBody()->write(json_encode( [
            'status' => '404', 
            'erro' => 'O modulo selecionado não existe!! Tente novamente com um modulo válido'
        ]));
        return $res->withStatus(404)->withHeader('Content-Type', 'application/json');

    }

    public function updateAulaByModulo(Request $req, Response $res, $args){
        $dados = $req->getParsedBody();
        $file = $req->getUploadedFiles()['url_capa'] ?? null;


        $aula = self::leftJoin('aulas', 'modulos.curso_id', '=', 'aulas.id')
        ->select( 'modulos.*')
        ->where('modulos.id', '=', $args['modulo_id'])
        ->where('aulas.id', '=', $args['aula_id'])
        ->first();
    
        if( $aula ){

            $insert = array(
                'nome' => $dados["nome"],
                'descricao' => $dados["descricao"],
                'url_capa' => $dados["url_capa"],
                'url_video' => $dados["url_video"],
                'duracao' => $dados["duracao"],
                'completed' => $dados["completed"],
                'updated_at' => date('m/d/Y h:i:s a', time())
            );

            if (isset($file) && $file->getError() === UPLOAD_ERR_OK) {

                $nome_arquivo = rand(1000000000, 10000000000) . '-' . $file->getClientFilename();
                $caminhoArquivo = __DIR__ . '/../../uploads/' . $nome_arquivo;
                
                $file->moveTo($caminhoArquivo);
                
                $insert['url_capa'] = 'https://api-inspire.brandsdev.com.br/uploads/' . $nome_arquivo;
            }

            if (!isset($insert['url_capa'])) {
                $insert['url_capa'] = $aula->url_capa;
            }

            $aula->update( $insert );

            $res->getBody()->write(json_encode([
                'message' => 'Aula atualizada com sucesso!',
                'aula' => $aula
            ]));
            return $res->withHeader('Content-Type', 'application/json');
        }

        $res->getBody()->write(json_encode([
            'status' => '404', 
            'erro' => 'O modelo ou aula selecionado não tem nenhum vinculo ou não existe!! Tente novamente com um modulo ou aula válido'
        ]));
        return $res->withStatus(404)->withHeader('Content-Type', 'application/json');

    }

    public function deleteAulaByModulo(Request $req, Response $res, $args){
        $moduloId = $args['modulo_id'];
        $aulaId = $args['aula_id'];

        $aula = Aula::where('id', $aulaId)
            ->where('modulo_id', $moduloId)
            ->first();

        if (!$aula) {

            $res->getBody()->write(json_encode([
                'status' => '404', 
                'error' => 'A aula não pertence ao módulo especificado ou não existe.'
            ]));
            return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $aula->delete();

        $res->getBody()->write(json_encode([
            'message' => 'Aula excluído com sucesso!',
            'aula' => $aula
        ]));
        return $res->withHeader('Content-Type', 'application/json');
    }
    
}