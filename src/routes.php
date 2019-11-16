<?php
use Psr\Http\Message\{
    ServerRequestInterface as Request,
    ResponseInterface as Response
};
use App\Models\Post;

$app->get('/detail/{id}', function ($request, $response, $args) {
    // Sample log message [{id}]
    $db = new Post($this->db);
    $post = $db->getPost($args['id']);
    // print_r($post);
    // die;

    // Render index view
    return $this->renderer->render($response, 'detail.phtml', [
        'post' => $post
    ]);
});

$app->get('/new', function ($request, $response, $args) {
    
    // Render index view
    return $this->renderer->render($response, 'new.html', $args);
});

$app->post('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Post");
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

    // Render index view
    return $this->renderer->render($response, 'edit.html', $args);
});

$app->get('/', function ($request, $response, $args) {
    $post = new Post($this->db);
    $posts = $post->getPosts();

    // Render index view
    return $this->renderer->render($response, 'index.phtml', [
        'posts' => $posts
    ]);
});
