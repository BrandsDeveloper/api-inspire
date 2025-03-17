<?php

namespace App\Application\Models;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
date_default_timezone_set('America/Sao_Paulo');

Class Curso extends Model{

    protected $table = 'cursos';


    protected $fillable = [
        "nome", "slug", "resumo", "descricao", "url_capa", "url_destaque", "nivel", "responsavel",  "created_at", "updated_at"
    ];

    public function getCurso(Request $req, Response $res){

        $curso = self::leftJoin('cursos_categorias as c_categorias', 'c_categorias.curso_id', '=', 'cursos.id')
        ->leftJoin('categorias as categorias', 'c_categorias.categoria_id', '=', 'categorias.id')
        ->select('cursos.*', 'categorias.nome as Categoria')
        ->get();

        $cursoComCategorias = $curso->groupBy('id')->map(function ($item) {
            
            $categorias = $item->pluck('Categoria')->toArray();
            
            $curso = $item->first();
            $curso->Categoria = $categorias;

            return $curso;
        });

        $res->getBody()->write(json_encode( $cursoComCategorias ));
        return $res->withHeader('Content-Type', 'application/json');

    }

    public function getCursoId(Request $req, Response $res, $args){

        $curso = Curso::findOrFail($args['id']);
        $res->getBody()->write(json_encode( $curso ));
        return $res->withHeader('Content-Type', 'application/json');

    }

    public function getCursoAll(Request $req, Response $res, $args){

        $curso = self::join('modulos', 'modulos.curso_id', '=', 'cursos.id')
            ->leftJoin('cursos_categorias', 'cursos_categorias.curso_id', '=', 'cursos.id')
            ->leftJoin('categorias as categorias', 'cursos_categorias.categoria_id', '=', 'categorias.id')
            ->leftJoin('aulas', 'aulas.modulo_id', '=', 'modulos.id')
            ->select(
                'cursos.id as curso_id',
                'cursos.nome as curso_nome',
                'modulos.id as modulo_id',
                'modulos.nome as modulo_nome',
                'aulas.id as aula_id',
                'aulas.nome as aula_nome',
                'aulas.descricao as aula_descricao',
                'aulas.url_video as aula_url_video',
                'aulas.duracao as aula_duracao',
                'cursos_categorias.curso_categoria_id as cursos_categoria_id', // Alterado para usar o alias correto
                'categorias.id as categoria_id',
                'categorias.nome as categoria_nome',
                'categorias.descricao as categoria_descricao'
            )
            ->orderBy('cursos.id')
            ->orderBy('modulos.id')
            ->orderBy('aulas.id')
            ->get();

        $cursosAgrupados = $curso->groupBy('curso_id')->map(function ($cursoItems) {

            $modulos = $cursoItems->groupBy('modulo_id')->map(function ($moduloItems) {

                $aulas = $moduloItems->unique('aula_id')->map(function ($aula) {
                    return [
                        'aula_id' => $aula->aula_id,
                        'aula_nome' => $aula->aula_nome,
                        'aula_descricao' => $aula->aula_descricao,
                        'aula_url_video' => $aula->aula_url_video,
                        'aula_duracao' => $aula->aula_duracao,
                    ];
                });

                $modulo = $moduloItems->first();
                return [
                    'modulo_id' => $modulo->modulo_id,
                    'modulo_nome' => $modulo->modulo_nome,
                    'aulas' => $aulas,
                ];
            });

            $categorias = $cursoItems->groupBy('cursos_categoria_id')->map(function ($categoriaItems) {
                $categoria = $categoriaItems->first();
                return [
                    'categoria_id' => $categoria->categoria_id,
                    'categoria_nome' => $categoria->categoria_nome,
                ];
            });

            $curso = $cursoItems->first();
            return [
                'curso_id' => $curso->curso_id,
                'curso_nome' => $curso->curso_nome,
                'curso_slug' => $curso->curso_slug,
                'curso_resumo' => $curso->curso_resumo,
                'curso_descricao' => $curso->curso_descricao,
                'curso_url_capa' => $curso->curso_url_capa,
                'curso_url_destaque' => $curso->curso_url_destaque,
                'curso_url_nivel' => $curso->curso_url_nivel,
                'curso_url_responsavel' => $curso->curso_url_responsavel,
                'modulos' => $modulos->values(),
                'categorias' => $categorias->values(),
            ];
        });

        $res->getBody()->write(json_encode($cursosAgrupados->values(), JSON_PRETTY_PRINT));
        return $res->withHeader('Content-Type', 'application/json');

    }

    public function getCursoIdAll(Request $req, Response $res, $args){

        $curso = self::join('modulos', 'modulos.curso_id', '=', 'cursos.id')
            ->leftJoin('cursos_categorias', 'cursos_categorias.curso_id', '=', 'cursos.id')
            ->leftJoin('categorias as categorias', 'cursos_categorias.categoria_id', '=', 'categorias.id')
            ->leftJoin('aulas', 'aulas.modulo_id', '=', 'modulos.id')
            ->select(
                'cursos.id as curso_id',
                'cursos.nome as curso_nome',
                'modulos.id as modulo_id',
                'modulos.nome as modulo_nome',
                'aulas.id as aula_id',
                'aulas.nome as aula_nome',
                'aulas.descricao as aula_descricao',
                'aulas.url_video as aula_url_video',
                'aulas.duracao as aula_duracao',
                'cursos_categorias.curso_categoria_id as cursos_categoria_id', // Alterado para usar o alias correto
                'categorias.id as categoria_id',
                'categorias.nome as categoria_nome',
                'categorias.descricao as categoria_descricao'
            )
            ->where('cursos.id', '=', $args['id'])
            ->orderBy('cursos.id')
            ->orderBy('modulos.id')
            ->orderBy('aulas.id')
            ->get();

        $cursosAgrupados = $curso->groupBy('curso_id')->map(function ($cursoItems) {

            $modulos = $cursoItems->groupBy('modulo_id')->map(function ($moduloItems) {

                $aulas = $moduloItems->unique('aula_id')->map(function ($aula) {
                    return [
                        'aula_id' => $aula->aula_id,
                        'aula_nome' => $aula->aula_nome,
                        'aula_descricao' => $aula->aula_descricao,
                        'aula_url_video' => $aula->aula_url_video,
                        'aula_duracao' => $aula->aula_duracao,
                    ];
                });

                $modulo = $moduloItems->first();
                return [
                    'modulo_id' => $modulo->modulo_id,
                    'modulo_nome' => $modulo->modulo_nome,
                    'aulas' => $aulas,
                ];
            });

            $categorias = $cursoItems->groupBy('cursos_categoria_id')->map(function ($categoriaItems) {
                $categoria = $categoriaItems->first();
                return [
                    'categoria_id' => $categoria->categoria_id,
                    'categoria_nome' => $categoria->categoria_nome,
                ];
            });

            $curso = $cursoItems->first();
            return [
                'curso_id' => $curso->curso_id,
                'curso_nome' => $curso->curso_nome,
                'modulos' => $modulos->values(),
                'categorias' => $categorias->values(),
            ];
        });

        $res->getBody()->write(json_encode($cursosAgrupados->values(), JSON_PRETTY_PRINT));
        return $res->withHeader('Content-Type', 'application/json');

    }

    public function addCurso(Request $req, Response $res){

        $dados = $req->getParsedBody();
        $file = $req->getUploadedFiles();
        
        $capa = $file['url_capa'];
        $destaque = $file['url_destaque'];
        $nome_capa = rand(1000000000,10000000000). '-' . $file['url_capa']->getClientFilename();
        $nome_destaque = rand(1000000000,10000000000). '-' . $file['url_destaque']->getClientFilename();

        $caminhoCapa = __DIR__ . '/../../../uploads/' . $nome_capa;
        $capa->moveTo($caminhoCapa);

        $caminhoDestaque = __DIR__ . '/../../../uploads/' . $nome_destaque;
        $capa->moveTo($caminhoDestaque);

        $insert = array(
            'nome' => $dados["nome"],
            'slug' => $dados["slug"],
            'resumo' => $dados["resumo"],
            'descricao' => $dados["descricao"],
            'nivel' => $dados["nivel"],
            'responsavel' => $dados["responsavel"],
            'url_capa' => 'https://api-inspire.brandsdev.com.br/uploads/'.$nome_capa,
            'url_destaque' => 'https://api-inspire.brandsdev.com.br/uploads/'.$nome_destaque
        );

        $curso = Curso::create( $insert );
        $res->getBody()->write(json_encode([
            'message' => 'Curso adicionada com sucesso!',
            'curso' => $curso
        ]));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function updateCurso(Request $req, Response $res, $args){
        $dados = $req->getParsedBody();
        $capa = $req->getUploadedFiles()['url_capa'] ?? null;
        $destaque = $req->getUploadedFiles()['url_destaque'] ?? null;
        
        $insert = array(
            'nome' => $dados["nome"],
            'slug' => $dados["slug"],
            'resumo' => $dados["resumo"],
            'descricao' => $dados["descricao"],
            'nivel' => $dados["nivel"],
            'responsavel' => $dados["responsavel"],
            'updated_at' => date('m/d/Y h:i:s a', time())
        );

        if (isset($capa) && $capa->getError() === UPLOAD_ERR_OK) {

            $nome_arquivo = rand(1000000000, 10000000000) . '-' . $capa->getClientFilename();
            $caminhoArquivo = __DIR__ . '/../../uploads/' . $nome_arquivo;
            
            $capa->moveTo($caminhoArquivo);
            
            $insert['url_capa'] = 'https://api-inspire.brandsdev.com.br/uploads/' . $nome_arquivo;
        }

        if (isset($destaque) && $destaque->getError() === UPLOAD_ERR_OK) {

            $nome_arquivo = rand(1000000000, 10000000000) . '-' . $destaque->getClientFilename();
            $caminhoArquivo = __DIR__ . '/../../uploads/' . $nome_arquivo;
            
            $destaque->moveTo($caminhoArquivo);
            
            $insert['url_destaque'] = 'https://api-inspire.brandsdev.com.br/uploads/' . $nome_arquivo;
        }
    
        $curso = Curso::findOrFail($args['id']);
    
        if (!isset($insert['url_capa'])) {
            $insert['url_capa'] = $curso->url_capa; // Mantém o valor antigo da url_capa
        }
    
        if (!isset($insert['url_destaque'])) {
            $insert['url_destaque'] = $curso->url_capa; // Mantém o valor antigo da url_destaque
        }

        $curso->update( $insert );

        $res->getBody()->write(json_encode([
            'message' => 'Curso atualizado com sucesso!',
            'curso' => $curso
        ]));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function deleteCurso(Request $req, Response $res, $args){
        $curso = Curso::where( 'id', $args['id'] );
        if (!empty($curso)) {

            $res->getBody()->write(json_encode([
                'error' => 'O módulo não pertence ao curso especificado ou não existe.'
            ]));
            return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        $curso->delete();
        $res->getBody()->write(json_encode([
            'message' => 'Curso excluído com sucesso.',
            'modulo' => $curso
        ]));
        return $res->withHeader('Content-Type', 'application/json');
    }
    
}