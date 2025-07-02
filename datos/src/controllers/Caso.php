<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
use PDOException;

class Caso {
    protected $container;

    public function __construct(ContainerInterface $c){
        $this->container = $c;
    }

    public function read(Request $request, Response $response, $args){
        $sql = "SELECT * FROM vista_casos "; // La vista ya hace el trabajo complejo
        if(isset($args['id'])){
            $sql .= "WHERE id = :id";
        }
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);
        if(isset($args['id'])) $query->execute(['id' => $args['id']]);
        else $query->execute();
        
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $status = $query->rowCount() > 0 ? 200 : 204;
        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public function create(Request $request, Response $response, $args){
        $body = json_decode($request->getBody());
        $sql = "CALL nuevoCaso(:idTecnico, :idCreador, :idArtefacto, :descripcion);";
        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->bindValue(':idTecnico', $body->idTecnico);
            $query->bindValue(':idCreador', $body->idCreador);
            $query->bindValue(':idArtefacto', $body->idArtefacto, PDO::PARAM_INT);
            $query->bindValue(':descripcion', $body->descripcion);
            $query->execute();
            $status = 201;
        } catch(PDOException $e) {
            $status = 500;
        }
        return $response->withStatus($status);
    }

    public function update(Request $request, Response $response, $args) {
        $body = json_decode($request->getBody());
        $sql = "CALL editarCaso(:id, :idTecnico, :descripcion);";
        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->bindValue(':idTecnico', $body->idTecnico);
            $query->bindValue(':descripcion', $body->descripcion);
            $query->execute();
            $status = $query->rowCount() > 0 ? 200 : 404;
        } catch (PDOException $e) {
            $status = 500;
        }
        return $response->withStatus($status);
    }

    public function delete(Request $request, Response $response, $args){
        $sql = "CALL eliminarCaso(:id);";
        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->execute();
            $status = $query->rowCount() > 0 ? 200 : 404;
        } catch(PDOException $e){
            $status = 500;
        }
        return $response->withStatus($status);
    }

    public function filtrar(Request $request, Response $response, $args){
        $sql = "CALL filtrarCasos(?, ?, ?);";
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);
        $filtro = "%" . ($request->getQueryParams()['filtro'] ?? '') . "%";
        $query->bindValue(1, $filtro, PDO::PARAM_STR);
        $query->bindValue(2, $args['pag'], PDO::PARAM_INT);
        $query->bindValue(3, $args['lim'], PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $status = $query->rowCount() > 0 ? 200 : 204;
        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public function modificarEstado(Request $request, Response $response, $args) {
        $body = json_decode($request->getBody());
        $sql = "CALL cambiarEstadoCaso(:idCaso, :idResponsable, :nuevo_estado, :descripcion_cambio);";
        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->bindValue(':idCaso', $args['id'], PDO::PARAM_INT);
            $query->bindValue(':idResponsable', $body->idResponsable, PDO::PARAM_STR);
            $query->bindValue(':nuevo_estado', $body->estado, PDO::PARAM_INT);
            $query->bindValue(':descripcion_cambio', $body->descripcion);
            $query->execute();
            $status = $query->rowCount() > 0 ? 200 : 404;
        } catch(PDOException $e) {
            $status = 500;
        }
        return $response->withStatus($status);
    }
}