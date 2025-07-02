<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class Oficinista extends ServicioCURL
{
    protected $container;
    private const ENDPOINT = '/oficinista';

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function read(Request $request, Response $response, $args)
    {
        $url = $this::ENDPOINT . '/read';
        if (isset($args['id'])) {
            $url .= '/' . $args['id'];
        }
        $respA = $this->ejecutarCURL($url, 'GET');
        $response->getBody()->write($respA['resp']);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($respA['status']);
    }

    public function create(Request $request, Response $response, $args)
    {
        $body = $request->getBody();
        $respA = $this->ejecutarCURL($this::ENDPOINT, 'POST', $body);
        return $response->withStatus($respA['status']);
    }

    public function update(Request $request, Response $response, $args)
    {
        $uri = '/' . $args['id'];
        $body = $request->getBody();
        $respA = $this->ejecutarCURL($this::ENDPOINT . $uri, 'PUT', $body);
        return $response->withStatus($respA['status']);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $uri = '/' . $args['id'];
        $respA = $this->ejecutarCURL($this::ENDPOINT . $uri, 'DELETE');
        return $response->withStatus($respA['status']);
    }

    public function filtrar(Request $request, Response $response, $args)
    {
        $uri = "/filtrar/{$args['pag']}/{$args['lim']}?" . http_build_query($request->getQueryParams());
        $respA = $this->ejecutarCURL($this::ENDPOINT . $uri, 'GET');
        $response->getBody()->write($respA['resp']);
        return $response->withStatus($respA['status']);
    }
}