<?php
   
    use Slim\Factory\AppFactory;
    use DI\Container; 
    /*
    use JimTools\JwtAuth\Middleware\JwtAuthentication;
    use JimTools\JwtAuth\Options;
    use JimTools\JwtAuth\Rules\RequestMethodRule;
    use JimTools\JwtAuth\Rules\RequestPathRule;
    use JimTools\JwtAuth\Decoder\FirebaseDecoder;
    use JimTools\JwtAuth\Secret;
    */
    require __DIR__ . '/../../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable('/var/www/html'); 
    $dotenv->load();

    $container = new Container();

    AppFactory::SetContainer($container);
    
    $app = AppFactory::create();

    require "config.php";

    $app->add(new Tuupola\Middleware\JwtAuthentication([
        "secure" => false, // Set to true in production
        "path" => ["/api"],
        "ignore" => ["/api/auth", "/api/cliente"],// or ["/api", "/admin"]
        "secret" => ["acme" => $container->get('key')],
        "algorithm" => ["acme" => "HS256"],
    ]));

   /* 
   $rules = [
        new RequestMethodRule(ignore: ['OPTIONS']),
        new RequestPathRule(
            paths: ['/api'], 
            ignore: ['/api/auth']
        )
        ];
    $decoder = new FirebaseDecoder(new Secret($container->get('key'), 'HS256'));
    $authentication = new JwtAuthentication(new Options(), $decoder, $rules);
    $app->addMiddleware($authentication);
    */
    require "conexion.php";

    require "routes.php";
    

    $app->run();