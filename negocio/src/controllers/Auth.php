<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class Auth extends ServicioCURL
{
    protected $container;
    private const ENDPOINT = '/auth';

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    /**
     * Reenvía la petición de inicio de sesión a la capa de datos.
     */
    public function iniciar(Request $request, Response $response, $args)
    {
        $body = $request->getBody();
        $respA = $this->ejecutarCURL(self::ENDPOINT, 'PATCH', $body);

        $response->getBody()->write($respA['resp']);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($respA['status']);
    }

    /**
     * Reenvía la petición de cierre de sesión.
     */
    public function cerrar(Request $request, Response $response, $args)
    {
        $uri = '/' . $args['idUsuario'];
        $respA = $this->ejecutarCURL(self::ENDPOINT . $uri, 'DELETE');
        return $response->withStatus($respA['status']);
    }

    /**
     * Reenvía la petición para refrescar el token.
     */
    public function refrescar(Request $request, Response $response, $args)
    {
        $uri = '/refresh';
        $body = $request->getBody();
        $respA = $this->ejecutarCURL(self::ENDPOINT . $uri, 'PATCH', $body);
        
        $response->getBody()->write($respA['resp']);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($respA['status']);
    }
}