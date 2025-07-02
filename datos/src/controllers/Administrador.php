<?php
    namespace App\controllers;

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Container\ContainerInterface;

    use PDO;

    class Administrador{
        protected $container;
        private const ROL= 1;

        public function __construct(ContainerInterface $c){
            $this->container = $c;
        }

        public function read(Request $request, Response $response, $args){
            $sql= "SELECT * FROM administrador ";

            if(isset($args['id'])){
                $sql.="WHERE id = :id ";
            }

            $sql .=" LIMIT 0,5;";
            $con=  $this->container->get('base_datos');
            $query = $con->prepare($sql);

            if(isset($args['id'])){
                $query->execute(['id' => $args['id']]);
            }else{
                $query->execute();
            }
            
            $res= $query->fetchAll();

            $status= $query->rowCount()> 0 ? 200 : 204;

            $query=null;
            $con=null;

            $response->getbody()->write(json_encode($res));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }

        public function create(Request $request, Response $response, $args)
{
    $body = json_decode($request->getBody());

    // NOTA: Se recomienda refactorizar esto a un PROCEDIMIENTO con transacción,
    // como hicimos con Oficinista y Tecnico, para mayor seguridad y consistencia.
    $sql_admin = "SELECT nuevoAdministrador(:idAdministrador,:nombre,:apellido1,:apellido2,:telefono,:celular,:direccion,:correo);";

    $con = $this->container->get('base_datos');
    $con->beginTransaction();

    try {
        // --- Parte 1: Crear el Administrador ---
        $query_admin = $con->prepare($sql_admin);
        foreach ($body as $key => $value) {
            $query_admin->bindValue($key, $value);
        }
        $query_admin->execute();

        $res = $query_admin->fetch(PDO::FETCH_NUM)[0];
        $status = ($res == 0) ? 201 : 409;

        // Si el administrador se creó bien (o ya existía y queremos continuar)
        if ($status == 201) {
            // --- Parte 2: Crear el Usuario ---

            // CORREGIDO: Se añade el parámetro :correo a la llamada
            $sql_user = "SELECT nuevoUsuario(:idUsuario, :correo, :rol, :passw);";
            $query_user = $con->prepare($sql_user);

            $id = $body->idAdministrador;
            $correo = $body->correo; // Se obtiene el correo del body
            $passw = password_hash($id, PASSWORD_DEFAULT); // Se hashea la contraseña

            $query_user->bindValue(':idUsuario', $id);
            $query_user->bindValue(':correo', $correo); // CORREGIDO: Se bindea el correo
            $query_user->bindValue(':rol', self::ROL, PDO::PARAM_INT);
            $query_user->bindValue(':passw', $passw);

            $query_user->execute();
        }

        // Si algo salió mal antes (ej: admin duplicado), revertir.
        if ($status == 409) {
            $con->rollBack();
        } else {
            $con->commit();
        }

    } catch (PDOException $e) {
        $status = 500;
        $con->rollBack();
    }

    $con = null;
    return $response->withStatus($status);
}

        public function update(Request $request, Response $response, $args) {

            $body = json_decode($request->getBody());
            $sql = "SELECT editarAdministrador(:id,:idAdministrador,:nombre,:apellido1,:apellido2,:telefono,:celular,:direccion,:correo);";

            $con = $this->container->get('base_datos');
            $con->beginTransaction();
            $query = $con->prepare($sql);

            $value = filter_var($args['id'], FILTER_SANITIZE_SPECIAL_CHARS);
            $query->bindValue(':id', $value, PDO::PARAM_INT);

            foreach ($body as $key => $value) {
                $TIPO = gettype($value) == "integer" ? PDO::PARAM_INT : PDO::PARAM_STR;
                $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $query->bindValue($key, $value, $TIPO);
            }

            try {
                $query->execute();
                $con->commit();
                $res = $query->fetch(PDO::FETCH_NUM)[0];
                $status = match ($res) {
                    0 => 404,
                    1 => 200,
                };
            } catch (PDOException $e) {
                $status = 500;
                $con->rollBack();
            }

            $query = null;
            $con = null;

            return $response->withStatus($status);
        }


        public function delete(Request $request, Response $response, $args){
            
            $sql = "SELECT eliminarAdministrador(:id);";
            $con=  $this->container->get('base_datos');

            $query = $con->prepare($sql);
 
            $query->bindValue('id', $args['id'], PDO::PARAM_INT);
            $query->execute();
              
            $resp= $query->fetch(PDO::FETCH_NUM)[0];
            
            $status= $resp > 0 ? 200 : 404;
 
            $query=null;
            $con=null;
 
            return $response ->withStatus($status);
        }

        public function filtrar(Request $request, Response $response, $args){

            // %idAdministrador%&%nombre%&%apellido1%&%apellido2%&
            $datos= $request->getQueryParams();
            $filtro= "%";
            foreach($datos as $key => $value){
                $filtro .= "$value%&%";
            }
            $filtro= substr($filtro, 0, -1);

            $sql="CALL filtrarAdministrador('$filtro', {$args['pag']},{$args['lim']});";

            $con=  $this->container->get('base_datos');
            $query = $con->prepare($sql);

            //die($sql);

            $query->execute();
            
            $res= $query->fetchAll();

            $status= $query->rowCount()> 0 ? 200 : 204;

            $query=null;
            $con=null;


            $response->getbody()->write(json_encode($res));


            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }



    }
