<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
use PDOException;

class Administrador {
    protected $container;
    private const ROL = 1;

    public function __construct(ContainerInterface $c) {
        $this->container = $c;
    }

    public function read(Request $request, Response $response, $args) {
        $sql = "SELECT * FROM administrador ";
        if (isset($args['id'])) {
            $sql .= "WHERE id = :id ";
        }
        $sql .= "LIMIT 0,5;";
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);

        if (isset($args['id'])) {
            $query->execute(['id' => $args['id']]);
        } else {
            $query->execute();
        }

        $res = $query->fetchAll();
        $status = $query->rowCount() > 0 ? 200 : 204;

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public function create(Request $request, Response $response, $args) {
        $body = json_decode($request->getBody());

        $con = $this->container->get('base_datos');
        $con->beginTransaction();

        try {
            // --- Crear Administrador usando CALL ---
            $sql_admin = "CALL nuevoAdministrador(:idAdministrador, :nombre, :apellido1, :apellido2, :telefono, :celular, :direccion, :correo, @cant)";
            $query_admin = $con->prepare($sql_admin);

            $query_admin->bindValue(':idAdministrador', $body->idAdministrador);
            $query_admin->bindValue(':nombre', $body->nombre);
            $query_admin->bindValue(':apellido1', $body->apellido1);
            $query_admin->bindValue(':apellido2', $body->apellido2);
            $query_admin->bindValue(':telefono', $body->telefono);
            $query_admin->bindValue(':celular', $body->celular);
            $query_admin->bindValue(':direccion', $body->direccion);
            $query_admin->bindValue(':correo', $body->correo);

            $query_admin->execute();

            // Recuperar el valor de @cant
            $countResult = $con->query("SELECT @cant as resultado")->fetch(PDO::FETCH_ASSOC);
            $res = $countResult['resultado'];
            $status = ($res == 0) ? 201 : 409;

            if ($status == 201) {
                // --- Crear Usuario ---
                $sql_user = "SELECT nuevoUsuario(:idUsuario, :correo, :rol, :passw);";
                $query_user = $con->prepare($sql_user);

                $id = $body->idAdministrador;
                $correo = $body->correo;
                $passw = password_hash($id, PASSWORD_DEFAULT);

                $query_user->bindValue(':idUsuario', $id);
                $query_user->bindValue(':correo', $correo);
                $query_user->bindValue(':rol', self::ROL, PDO::PARAM_INT);
                $query_user->bindValue(':passw', $passw);

                $query_user->execute();
            } else {
                $con->rollBack();
                $con = null;
                return $response->withStatus(409);
            }

            $con->commit();

        } catch (PDOException $e) {
            $status = 500;
            $con->rollBack();
        }

        $con = null;
        return $response->withStatus($status);
    }

    public function update(Request $request, Response $response, $args) {
        $body = json_decode($request->getBody());
        $con = $this->container->get('base_datos');
        $con->beginTransaction();

        try {
            // --- Update usando CALL ---
            $sql = "CALL editarAdministrador(:id, :idAdministrador, :nombre, :apellido1, :apellido2, :telefono, :celular, :direccion, :correo, @cant)";
            $query = $con->prepare($sql);

            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->bindValue(':idAdministrador', $body->idAdministrador);
            $query->bindValue(':nombre', $body->nombre);
            $query->bindValue(':apellido1', $body->apellido1);
            $query->bindValue(':apellido2', $body->apellido2);
            $query->bindValue(':telefono', $body->telefono);
            $query->bindValue(':celular', $body->celular);
            $query->bindValue(':direccion', $body->direccion);
            $query->bindValue(':correo', $body->correo);

            $query->execute();

            $countResult = $con->query("SELECT @cant as resultado")->fetch(PDO::FETCH_ASSOC);
            $res = $countResult['resultado'];
            $status = ($res == 0) ? 404 : 200;

            $con->commit();

        } catch (PDOException $e) {
            $status = 500;
            $con->rollBack();
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    public function delete(Request $request, Response $response, $args) {
        $con = $this->container->get('base_datos');
        $con->beginTransaction();

        try {
            $sql = "CALL eliminarAdministrador(:id, @resp)";
            $query = $con->prepare($sql);
            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);

            $query->execute();

            $countResult = $con->query("SELECT @resp as resultado")->fetch(PDO::FETCH_ASSOC);
            $resp = $countResult['resultado'];
            $status = ($resp > 0) ? 200 : 404;

            $con->commit();

        } catch (PDOException $e) {
            $status = 500;
            $con->rollBack();
        }

        $query = null;
        $con = null;

        return $response->withStatus($status);
    }

    public function filtrar(Request $request, Response $response, $args) {
        $datos = $request->getQueryParams();
        $filtro = "%";
        foreach ($datos as $key => $value) {
            $filtro .= "$value%&%";
        }
        $filtro = substr($filtro, 0, -1);

        $sql = "CALL filtrarAdministrador('$filtro', {$args['pag']}, {$args['lim']});";
        $con = $this->container->get('base_datos');
        $query = $con->prepare($sql);

        $query->execute();
        $res = $query->fetchAll();
        $status = $query->rowCount() > 0 ? 200 : 204;

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
