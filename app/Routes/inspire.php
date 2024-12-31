<?php

use Slim\App as App;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


$app->group('/v1', function( Group $group){

    $group->post('/token', '\App\Application\Models\User:token' );

    // Rotas Curso
    $group->get('/cursos', '\App\Application\Models\Curso:getCurso');
    $group->get('/cursos/all', '\App\Application\Models\Curso:getCursoAll');
    $group->get('/cursos/{id}', '\App\Application\Models\Curso:getCursoId');
    $group->get('/cursos/{id}/all', '\App\Application\Models\Curso:getCursoIdAll');
    $group->post('/cursos/create', '\App\Application\Models\Curso:addCurso');
    $group->post('/cursos/{id}', '\App\Application\Models\Curso:updateCurso');
    $group->delete('/cursos/{id}', '\App\Application\Models\Curso:deleteCurso');

    // Rotas Modulos 
    $group->get('/cursos/{id}/modulos', '\App\Application\Models\Modulo:getModulosByCurso');
    $group->get('/cursos/{curso_id}/modulos/{modulo_id}', '\App\Application\Models\Modulo:getModuloById');
    $group->post('/cursos/{id}/modulos/create', '\App\Application\Models\Modulo:addModuloToCurso');
    $group->put('/cursos/{curso_id}/modulos/{modulo_id}', '\App\Application\Models\Modulo:updateModuloByCurso');
    $group->delete('/cursos/{curso_id}/modulos/{modulo_id}', '\App\Application\Models\Modulo:deleteModuloByCurso');    
    
    // Rotas Aulas
    $group->get('/modulos/{modulo_id}/aulas', '\App\Application\Models\Aula:getAulasByModulo');
    $group->get('/modulos/{modulo_id}/aulas/{aula_id}', '\App\Application\Models\Aula:getAulaById');
    $group->post('/modulos/{id}/aulas/create', '\App\Application\Models\Aula:addAulaToModulo');
    $group->post('/modulos/{modulo_id}/aulas/{aula_id}', '\App\Application\Models\Aula:updateAulaByModulo');
    $group->delete('/modulos/{modulo_id}/aulas/{aula_id}', '\App\Application\Models\Aula:deleteAulaByModulo');

    // Rotas Categoria
    $group->get('/categorias', '\App\Application\Models\Categoria:getCategoria');
    $group->get('/categorias/{id}', '\App\Application\Models\Categoria:getCategoriaId');
    $group->post('/categorias/create', '\App\Application\Models\Categoria:addCategoria');
    $group->post('/categorias/{id}', '\App\Application\Models\Categoria:updateCategoria');
    $group->delete('/categorias/{id}', '\App\Application\Models\Categoria:deleteCategoria');

});

