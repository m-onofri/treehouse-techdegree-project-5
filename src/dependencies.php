<?php
// DIC configuration
use App\Models\{
    Post as Post,
    Comment as Comment,
    Tag as Tag
};

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// connect to DB
$container['db'] = function ($c) {
    $db = $c->get('settings')['db'];
    $pdo = new PDO("sqlite:".$db['path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
};

// twig
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig( __DIR__ . '/../templates/', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

//Post Model
$container['post'] = function ($c) {
    $post = new Post($c->db);
    return $post;
};

//Comment Model
$container['comment'] = function ($c) {
    $comment = new Comment($c->db);
    return $comment;
};

//Tag Model
$container['tag'] = function ($c) {
    $tag = new Tag($c->db);
    return $tag;
};

//Flash Messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

//Slugify
$container['slugify'] = function () {
    return new \Cocur\Slugify\Slugify();
};
