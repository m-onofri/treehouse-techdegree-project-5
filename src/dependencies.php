<?php
// DIC configuration
use App\Models\{
    Post as Post,
    Comment as Comment,
    Tag as Tag
};
use App\Controllers\PostController as PostController;

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

$container['db'] = function ($c) {
    $db = $c->get('settings')['db'];
    $pdo = new PDO("sqlite:".$db['path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
};

// Register component on container
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

$container['post'] = function ($c) {
    $post = new Post($c->db);
    return $post;
};

$container['comment'] = function ($c) {
    $comment = new Comment($c->db);
    return $comment;
};

$container['tag'] = function ($c) {
    $tag = new Tag($c->db);
    return $tag;
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// $container['PostController'] = function($c) {
//     $view = $c->get("view"); // retrieve the 'view' from the container
//     $comment = $c->get("comment"); // retrieve the Comment Model from the container
//     $tag = $c->get("tag"); // retrieve the Tag Model from the container
//     $post = $c->get("post"); // retrieve the Post Model from the container
//     $flash = $c->get("flash");
//     return new PostController($container);
// };

