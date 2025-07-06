<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
use PDOException;
use Slim\Psr7\Stream;

class Oficinista {
    protected $container;
    private const ROL = 2; // ID del rol para Oficinista

    public function __construct(ContainerInterface $c){
        $this->container = $c;
    }

    public function read(Request $request, Response $response, $args){
        $sql = "SELECT * FROM vista_oficinistas ";
        if(isset($args['id'])){
            $sql .= "WHERE id = :id";
        }

        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);

        if(isset($args['id'])){
            $query->execute(['id' => $args['id']]);
        } else {
            $query->execute();
        }
        
        $res = $query->fetchAll();
        $status = $query->rowCount() > 0 ? 200 : 204;

        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public function create(Request $request, Response $response, $args){
        $body = json_decode($request->getBody()->getContents());

        if (!$body || !isset(
            $body->idOficinista, $body->nombre, $body->apellido1,
            $body->apellido2, $body->telefono, $body->celular,
            $body->direccion, $body->correo
        )) {
            $stream = new Stream(fopen('php://temp', 'r+'));
            $stream->write(json_encode(['error' => 'Datos incompletos o mal formateados.']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400)
                ->withBody($stream);
        }

        $sql = "CALL nuevoOficinista(:idOficinista, :nombre, :apellido1, :apellido2, :telefono, :celular, :direccion, :correo, :passw);";
        
        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);

            $passw = password_hash($body->idOficinista, PASSWORD_DEFAULT);

            $query->bindValue(':idOficinista', $body->idOficinista);
            $query->bindValue(':nombre', $body->nombre);
            $query->bindValue(':apellido1', $body->apellido1);
            $query->bindValue(':apellido2', $body->apellido2);
            $query->bindValue(':telefono', $body->telefono);
            $query->bindValue(':celular', $body->celular);
            $query->bindValue(':direccion', $body->direccion);
            $query->bindValue(':correo', $body->correo);
            $query->bindValue(':passw', $passw);

            $query->execute();
            $status = 201;

        } catch(PDOException $e) {
            $status = 500;
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    public function update(Request $request, Response $response, $args) {
        $body = json_decode($request->getBody()->getContents());

        if (!$body || !isset(
            $body->idOficinista, $body->nombre, $body->apellido1,
            $body->apellido2, $body->telefono, $body->celular,
            $body->direccion, $body->correo
        )) {
            $stream = new Stream(fopen('php://temp', 'r+'));
            $stream->write(json_encode(['error' => 'Datos incompletos o mal formateados.']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400)
                ->withBody($stream);
        }

        $sql = "CALL editarOficinista(:id, :idOficinista, :nombre, :apellido1, :apellido2, :telefono, :celular, :direccion, :correo);";
        
        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);

            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->bindValue(':idOficinista', $body->idOficinista);
            $query->bindValue(':nombre', $body->nombre);
            $query->bindValue(':apellido1', $body->apellido1);
            $query->bindValue(':apellido2', $body->apellido2);
            $query->bindValue(':telefono', $body->telefono);
            $query->bindValue(':celular', $body->celular);
            $query->bindValue(':direccion', $body->direccion);
            $query->bindValue(':correo', $body->correo);

            $query->execute();
            $status = 200;

        } catch (PDOException $e) {
            $status = 500;
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    public function delete(Request $request, Response $response, $args){
        $sql = "CALL eliminarOficinista(:id);";

        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->execute();

            $status = 200;

        } catch (PDOException $e) {
            $status = 500;
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    public function filtrar(Request $request, Response $response, $args){
        $sql = "CALL filtrarOficinistas(?, ?, ?);";
        
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);

        $filtro = "%" . ($request->getQueryParams()['filtro'] ?? '') . "%";
        
        $query->bindValue(1, $filtro, PDO::PARAM_STR);
        $query->bindValue(2, $args['pag'], PDO::PARAM_INT);
        $query->bindValue(3, $args['lim'], PDO::PARAM_INT);
        $query->execute();
        
        $res = $query->fetchAll();
        $status = $query->rowCount() > 0 ? 200 : 204;

        $response->getBody()->write(json_encode($res));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
