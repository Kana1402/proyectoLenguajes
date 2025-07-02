<?php
    namespace App\controllers;

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Container\ContainerInterface;

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    use PDO;

    class Auth Extends Autenticar{
        protected $container;
        public function __construct(ContainerInterface $c){
            $this->container = $c;
        }

        private function accederToken(string $proc, string $idUsuario, string $tkRef= ""){
            $sql= $proc== "modificar" ? "SELECT modificarToken " : "CALL verificarTokenR ";
            $sql.= "(:idUsuario, :tkRef);";
            $con= $this->container->get('base_datos');
            $query = $con->prepare($sql);
            $query->execute([
                'idUsuario' => $idUsuario,
                'tkRef' => $tkRef
            ]);
            $datos = $proc == "modificar" ? $query->fetchAll(PDO::FETCH_OBJ) :$query->fetchColumn();
            $query = null;
            $con = null;
            return $datos;
        }

        private function modificarToken(string $idUsuario, string $tkRef= ""){
           return $this->accederToken("modificar", $idUsuario, $tkRef);
        }

         private function verificarToken(string $idUsuario, string $tkRef){
           return $this->accederToken("verificar", $idUsuario, $tkRef);
        }

         private function generarToken(string $idUsuario, string $rol, string $nombre){
            $key= $this->container->get('key'); // Clave secreta para firmar el token
            $payload= [
                'iss' => $_SERVER['SERVER_NAME'], // Emisor del token
                'iat' => time(),
                'exp' => time() + 3600, // 1 hora de validez
                'sub' => $idUsuario, // Asunto del token
                'rol' => $rol, // Rol del usuario
                'nom' => $nombre // Nombre del usuario
            ];

            $payloadRef = [
                'iss' => $_SERVER['SERVER_NAME'], // Emisor del token
                'iat' => time(),
                'rol' => $rol, // Rol del usuario
                'nom' => $nombre // Nombre del usuario
            ];

            return [
                "token"=>JWT::encode($payload, $key, 'HS256'),
                "tkRef"=>JWT::encode($payloadRef, $key, 'HS256') // Token de referencia
            ];
           
        }

        public function iniciar(Request $request, Response $response, $args){
            $body= json_decode($request->getBody());
            if($datos = $this->autenticar($body->idUsuario, $body->passw)){
                $tokens = $this->generarToken($body->idUsuario, $datos['rol'], $datos['nombre']);
                $this->modificarToken(idUsuario: $body->idUsuario, tkRef: $tokens['tkRef']);
                $response->getBody()->write(json_encode($tokens));
                $status= 200; // OK
            } else{
                $status= 401; // No autorizado
            }
            return $response->withStatus($status)
                            ->withHeader('Content-Type', 'application/json');
        }

        public function cerrar(Request $request, Response $response, $args){
            $this->modificarToken(idUsuario: $args['idUsuario']);
            return $response->withStatus(200); // OK

        }

        public function refrescar(Request $request, Response $response, $args){
            $body = json_decode($request->getBody());
            $rol = $this->verificarToken(idUsuario: $body->idUsuario, tkRef: $body->tkRef);
            $status = 200;
            if($rol){
                $datos = JWT::decode($body->tkRef, new Key($this->container->get('key'), 'HS256'));
                $tokens = $this->generarToken($body->idUsuario, $datos->rol, $datos->nom);
                $this->modificarToken(idUsuario: $body->idUsuario, tkRef: $tokens['tkRef']);
                $response->getBody()->write(json_encode($tokens));
            }else{
                $status = 401; // No autorizado
            }
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus($status);

        }

    }