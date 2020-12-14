<?php

$container = $app->getContainer();

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../src/View', []);

    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$container['doctrine'] = function ($container){
    $config = new \Doctrine\DBAL\Configuration();
    $connection = \Doctrine\DBAL\DriverManager::getConnection(
        $container->get('settings')['database'], $config
    );
    return $connection;
};
