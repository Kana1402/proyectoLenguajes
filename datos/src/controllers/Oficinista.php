<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
use PDOException; // Importar PDOException para el manejo de errores

class Oficinista {
    protected $container;
    private const ROL = 2; // ID del rol para Oficinista

    public function __construct(ContainerInterface $c){
        $this->container = $c;
    }

    /**
     * Lee uno o todos los oficinistas usando la vista.
     */
    public function read(Request $request, Response $response, $args){
        // La vista ya une las tablas, este método es correcto.
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

    /**
     * Crea un nuevo oficinista y su usuario asociado (Lógica corregida).
     */
     public function create(Request $request, Response $response, $args){
        $body = json_decode($request->getBody());

        $sql = "CALL nuevoOficinista(:idOficinista, :nombre, :apellido1, :apellido2, :telefono, :celular, :direccion, :correo, :passw);";
        
        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);

            $passw = $body->idOficinista;

            $query->bindValue(':idOficinista', $body->idOficinista);
            $query->bindValue(':nombre', $body->nombre);
            $query->bindValue(':apellido1', $body->apellido1);
            $query->bindValue(':apellido2', $body->apellido2);
            $query->bindValue(':telefono', $body->telefono);
            $query->bindValue(':celular', $body->celular);
            $query->bindValue(':direccion', $body->direccion);
            $query->bindValue(':correo', $body->correo);
            $query->bindValue(':passw', password_hash($passw, PASSWORD_DEFAULT));

            $query->execute();
            
            // Si no hay excepción, asumimos que fue exitoso.
            $status = 201;

        } catch(PDOException $e) {
            // Cualquier error en la base de datos (ej: ID duplicado) resultará en 500.
            $status = 500;
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    /**
     * Actualiza un oficinista existente (Lógica corregida).
     */
    public function update(Request $request, Response $response, $args) {
        $body = json_decode($request->getBody());

        // CORREGIDO: Se usa CALL para invocar el procedimiento.
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
            $status = $query->rowCount() > 0 ? 200 : 404;

        } catch (PDOException $e) {
            $status = 500;
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    /**
     * Elimina un oficinista y su usuario asociado (Lógica corregida).
     */
    public function delete(Request $request, Response $response, $args){
        // CORREGIDO: Se usa CALL para invocar el procedimiento.
        $sql = "CALL eliminarOficinista(:id);";

        try {
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->execute();

            $status = $query->rowCount() > 0 ? 200 : 404;

        } catch (PDOException $e) {
            $status = 500;
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    /**
     * Filtra los oficinistas con paginación (Lógica corregida y segura).
     */
    public function filtrar(Request $request, Response $response, $args){
        // SEGURO: Se usan placeholders (?) para prevenir inyección SQL.
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