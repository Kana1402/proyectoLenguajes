<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class Usuario extends ServicioCURL
{
    protected $container;
    private const ENDPOINT = '/usr';

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    /**
     * Reenvía la petición para buscar un usuario.
     */
    public function buscar(Request $request, Response $response, $args)
    {
        $uri = '/' . $args['id'];
        $respA = $this->ejecutarCURL(self::ENDPOINT . $uri, 'GET');

        $response->getBody()->write($respA['resp']);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($respA['status']);
    }

    /**
     * Reenvía la petición para resetear la contraseña de un usuario.
     */
    public function resetPassw(Request $request, Response $response, $args)
    {
        $uri = '/reset/' . $args['idUsuario'];
        $respA = $this->ejecutarCURL(self::ENDPOINT . $uri, 'PATCH');
        return $response->withStatus($respA['status']);
    }

    /**
     * Reenvía la petición para que un usuario cambie su propia contraseña.
     */
    public function changePassw(Request $request, Response $response, $args)
    {
        $uri = '/change/' . $args['idUsuario'];
        $body = $request->getBody();
        $respA = $this->ejecutarCURL(self::ENDPOINT . $uri, 'PATCH', $body);
        return $response->withStatus($respA['status']);
    }

    /**
     * Reenvía la petición para cambiar el rol de un usuario.
     */
    public function changeRol(Request $request, Response $response, $args)
    {
        $uri = '/rol/' . $args['idUsuario'];
        $body = $request->getBody();
        $respA = $this->ejecutarCURL(self::ENDPOINT . $uri, 'PATCH', $body);
        return $response->withStatus($respA['status']);
    }
}