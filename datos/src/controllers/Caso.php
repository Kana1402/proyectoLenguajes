¡Por supuesto! Siguiendo la lógica de tu controlador Administrador.php, he creado el controlador Caso.php para ti.

Este código asume que tienes procedimientos almacenados en tu base de datos con nombres como nuevoCaso, editarCaso, eliminarCaso y filtrarCasos. Simplemente ajústalos si tienen otros nombres.

Aquí tienes el código para tu archivo controllers/Caso.php:

PHP

<?php
    namespace App\controllers;

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Container\ContainerInterface;
    use PDO;

    class Caso {
        protected $container;

        public function __construct(ContainerInterface $c){
            $this->container = $c;
        }

        /**
         * Lee uno o todos los casos.
         */
        public function read(Request $request, Response $response, $args){
            $sql = "SELECT * FROM vista_casos "; // Usamos una vista para obtener datos relacionados

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

            $query = null;
            $con = null;

            $response->getBody()->write(json_encode($res));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }

        /**
         * Crea un nuevo caso.
         */
        public function create(Request $request, Response $response, $args){
            $body = json_decode($request->getBody());

            // Asumimos un procedimiento almacenado 'nuevoCaso'
            $sql = "SELECT nuevoCaso(:id_cliente, :id_artefacto, :id_tecnico_asignado, :descripcion_problema);";

            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            
            // Sanitizar y enlazar parámetros del body
            foreach($body as $key => $value){
                $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
                $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $query->bindValue(":$key", $value, $TIPO);
            }

            try {
                $query->execute();
                $res = $query->fetch(PDO::FETCH_NUM)[0];
                $status = match($res) {
                    0 => 201, // Creado con éxito
                    default => 409, // Conflicto o error
                };
            } catch(PDOException $e) {
                $status = 500;
                // Opcional: registrar el error $e->getMessage()
            }

            $query = null;
            $con = null;

            return $response->withStatus($status);
        }

        /**
         * Actualiza un caso existente.
         */
        public function update(Request $request, Response $response, $args) {
            $body = json_decode($request->getBody());
            // Asumimos un procedimiento almacenado 'editarCaso'
            $sql = "SELECT editarCaso(:id, :id_cliente, :id_artefacto, :id_tecnico_asignado, :descripcion_problema, :estado);";

            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);

            // Enlazar el ID desde los argumentos de la URL
            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);

            // Enlazar los demás valores desde el body
            foreach ($body as $key => $value) {
                $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
                $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $query->bindValue(":$key", $value, $TIPO);
            }

            try {
                $query->execute();
                $res = $query->fetch(PDO::FETCH_NUM)[0];
                $status = match ($res) {
                    1 => 200, // OK
                    0 => 404, // No encontrado
                    default => 500,
                };
            } catch (PDOException $e) {
                $status = 500;
            }

            $query = null;
            $con = null;

            return $response->withStatus($status);
        }

        /**
         * Elimina un caso.
         */
        public function delete(Request $request, Response $response, $args){
            // Asumimos un procedimiento almacenado 'eliminarCaso'
            $sql = "SELECT eliminarCaso(:id);";
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);

            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->execute();
                
            $resp = $query->fetch(PDO::FETCH_NUM)[0];
            $status = $resp > 0 ? 200 : 404;

            $query = null;
            $con = null;

            return $response->withStatus($status);
        }

        /**
         * Filtra los casos con paginación.
         */
        public function filtrar(Request $request, Response $response, $args){
            $datos = $request->getQueryParams();
            $filtro = "%";
            foreach($datos as $key => $value){
                $filtro .= "$value%&%";
            }
            $filtro = substr($filtro, 0, -1);

            // Asumimos un procedimiento almacenado 'filtrarCasos'
            $sql = "CALL filtrarCasos('{$filtro}', {$args['pag']}, {$args['lim']});";

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

        // --- Métodos Adicionales según tu PDF ---

        public function modificarEstado(Request $request, Response $response, $args) {
            $body = json_decode($request->getBody());
            
            // Asume un SP para cambiar solo el estado
            $sql = "SELECT cambiarEstadoCaso(:id, :nuevo_estado);";
            
            $con = $this->container->get('base_datos');
            $query = $con->prepare($sql);
            
            $query->bindValue(':id', $args['id'], PDO::PARAM_INT);
            $query->bindValue(':nuevo_estado', $body->estado, PDO::PARAM_STR);
            
            try {
                $query->execute();
                $res = $query->fetch(PDO::FETCH_NUM)[0];
                $status = $res > 0 ? 200 : 404;
            } catch(PDOException $e) {
                $status = 500;
            }
            
            $query = null;
            $con = null;
            
            return $response->withStatus($status);
        }
    }