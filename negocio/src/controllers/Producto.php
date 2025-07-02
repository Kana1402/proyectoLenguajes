<?php
    namespace App\controllers;

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Container\ContainerInterface;

    use PDO;

    class Producto{
        protected $container;

        public function __construct(ContainerInterface $c){
            $this->container = $c;
        }

        public function read(Request $request, Response $response, $args){


            $res=['datos'=>'leido'];
            $status= 200;

            $response->getbody()->write(json_encode($res));


            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }

        public function create(Request $request, Response $response, $args){
            
            $body= json_decode($request->getBody());

           /* $sql = "INSERT INTO productos (id, codigo_producto, precio_compra_producto,
                precio_venta_producto, utilidad, fecha_creacion_producto) 
                VALUES($body['id'], $body['codigo_producto'], $body['precio_compra_producto'],
                $body['precio_venta_producto'], $body['utilidad'], '$body['fecha_creacion_producto']')";*/

            $res = $body;

            $response->getBody()->write(json_encode($res));

            $status= 200;

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }
    }
