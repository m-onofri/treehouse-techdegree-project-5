<?php
use Psr\Http\Message\{
    ServerRequestInterface as Request,
    ResponseInterface as Response
};
use App\Models\Post;

$app->get('/detail', function ($request, $response, $args) {
    // Sample log message [{id}]
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'detail.html', $args);
});

$app->get('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'new.html', $args);
});

$app->post('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    $post = new Post($this->db);
    // print_r($post);
    // die;
    $data = $request->getParsedBody();
    $data['date'] = date("Y-m-d");
    $post->createPost($data);
    // Render index view
    return $this->renderer->render($response, 'new.html', $args);
});

$app->get('/edit', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'edit.html', $args);
});

$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.html', $args);
});
