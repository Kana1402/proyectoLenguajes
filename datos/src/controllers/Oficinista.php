¡Claro! Aquí tienes el código completo y corregido para la clase Oficinista.php, lista para que la copies y la pegues en tu archivo datos/src/controllers/Oficinista.php.

He ajustado el ROL a 2, como indicaste.

Clase Oficinista.php
PHP

<?php
    namespace App\controllers;

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Container\ContainerInterface;
    use PDO;

    class Oficinista {
        protected $container;
        private const ROL = 2; // AJUSTADO: ID del rol para Oficinista

        public function __construct(ContainerInterface $c){
            $this->container = $c;
        }

        /**
         * Lee uno o todos los oficinistas.
         */
        public function read(Request $request, Response $response, $args){
            // Asumimos que tienes una vista llamada 'vista_oficinistas' para leer los datos.
            $sql = "SELECT * FROM vista_oficinistas ";

            if(isset($args['id'])){
                $sql .= "WHERE id = :id ";
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
         * Crea un nuevo oficinista y su usuario asociado.
         */
        public function create(Request $request, Response $response, $args){
            $body = json_decode($request->getBody());

            // Asumimos un procedimiento almacenado 'nuevoOficinista'.
            $sql = "SELECT nuevoOficinista(:idOficinista, :nombre, :apellido1, :apellido2, :telefono, :celular, :direccion, :correo);";

            $con = $this->container->get('base_datos');
            $con->beginTransaction();
            $query = $con->prepare($sql);
            
            foreach($body as $key => $value){
                $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
                $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $query->bindValue($key, $value, $TIPO);
            }

            try {
                // 1. Crear los datos personales del oficinista
                $query->execute();
                $res = $query->fetch(PDO::FETCH_NUM)[0];
                $status = ($res == 0) ? 201 : 409;
                
                // 2. Si es exitoso, crear el usuario asociado
                if ($status == 201) {
                    $id = $body->idOficinista;
                    $passw = $id; // Contraseña inicial es la cédula
                    $correo = $body->correo;

                    $sql_user = "SELECT nuevoUsuario(:idUsuario, :correo, :rol, :passw);";
                    $query_user = $con->prepare($sql_user);
                    $query_user->bindValue(':idUsuario', $id, PDO::PARAM_STR);
                    $query_user->bindValue(':correo', $correo, PDO::PARAM_STR);
                    $query_user->bindValue(':rol', self::ROL, PDO::PARAM_INT);
                    $query_user->bindValue(':passw', password_hash($passw, PASSWORD_DEFAULT), PDO::PARAM_STR); // Hashear contraseña
                    $query_user->execute();

                    $con->commit();
                } else {
                    $con->rollBack(); // Si falla la creación, revertir todo
                }
            } catch(PDOException $e) {
                $status = 500;
                $con->rollBack();
            }

            $query = null;
            $con = null;

            return $response->withStatus($status);
        }

        /**
         * Actualiza un oficinista existente.
         */
        public function update(Request $request, Response $response, $args) {
            $body = json_decode($request->getBody());
            // Asumimos un SP 'editarOficinista'
            $sql = "SELECT editarOficinista(:id, :idOficinista, :nombre, :apellido1, :apellido2, :telefono, :celular, :direccion, :correo);";
            
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);

            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);

            foreach ($body as $key => $value) {
                $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
                $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $query->bindValue($key, $value, $TIPO);
            }

            try {
                $query->execute();
                $res = $query->fetch(PDO::FETCH_NUM)[0];
                $status = ($res == 1) ? 200 : 404;
            } catch (PDOException $e) {
                $status = 500;
            }

            $query = null;
            $con = null;

            return $response->withStatus($status);
        }

        /**
         * Elimina un oficinista y su usuario asociado.
         */
        public function delete(Request $request, Response $response, $args){
            // Asumimos una FUNCIÓN 'eliminarOficinista' que maneja la lógica de borrado en cascada o en transacción.
            $sql = "SELECT eliminarOficinista(:id);";
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->bindValue('id', $args['id'], PDO::PARAM_INT);
            $query->execute();
                
            $resp = $query->fetch(PDO::FETCH_NUM)[0];
            $status = $resp > 0 ? 200 : 404;

            $query = null;
            $con = null;

            return $response->withStatus($status);
        }

        /**
         * Filtra los oficinistas con paginación.
         */
        public function filtrar(Request $request, Response $response, $args){
            $datos = $request->getQueryParams();
            $filtro = "%" . implode("%&%", $datos) . "%";

            // Asumimos un SP 'filtrarOficinistas'
            $sql = "CALL filtrarOficinistas('{$filtro}', {$args['pag']}, {$args['lim']});";

            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->execute();
            
            $res = $query->fetchAll();
            $status = $query->rowCount() > 0 ? 200 : 204;

            $response->getBody()->write(json_encode($res));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }
    }