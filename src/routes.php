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
    $data = $request->getParsedBody();
    $data['date'] = date("Y-m-d");
    $post->createPost($data);
    // Render index view
    return $this->renderer->render($response, 'new.html', $args);
});

$app->get('/edit/{id}', function ($request, $response, $args) {
    $db = new Post($this->db);
    $post = $db->getPost($args['id']);
    // Render index view
    return $this->renderer->render($response, 'edit.phtml', [
        'post' => $post
    ]);
});

$app->post('/edit', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Update Post");
    $post = new Post($this->db);
    $data = $request->getParsedBody();
    //$data['date'] = date("Y-m-d");
    $updatedPost = $post->updatePost($data);
    // Render index view
    return $this->renderer->render($response, 'detail.phtml', [
        'post' => $updatedPost
    ]);
});

$app->post('/delete', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Delete Post");
    $post = new Post($this->db);
    $id = $request->getParsedBody()['id'];
    $message = $post->deletePost($id);
    $posts = $post->getPosts();

    // Render index view
    return $this->renderer->render($response, 'index.phtml', [
        'posts' => $posts,
        'msg' => $message
    ]);
});

$app->get('/', function ($request, $response, $args) {
    $post = new Post($this->db);
    $posts = $post->getPosts();

    // Render index view
    return $this->renderer->render($response, 'index.phtml', [
        'posts' => $posts
    ]);
})->setName("root");
