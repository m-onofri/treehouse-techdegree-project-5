<?php
use Psr\Http\Message\{
    ServerRequestInterface as Request,
    ResponseInterface as Response
};
use App\Models\{
    Post as Post,
    Comment as Comment
};

$app->get('/detail/{id}', function ($request, $response, $args) {
    // Sample log message [{id}]
    $db = new Post($this->db);
    $comment = new Comment($this->db);
    $post = $db->getPost($args['id']);
    $comments = $comment->getCommentsByPostId($args['id']);

    // Render index view
    return $this->renderer->render($response, 'detail.phtml', [
        'post' => $post,
        'comments' => $comments
    ]);
});

$app->get('/new', function ($request, $response, $args) {
    
    // Render index view
    return $this->renderer->render($response, 'new.phtml', $args);
});

$app->post('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Post");
    $post = new Post($this->db);
    $data = $request->getParsedBody();
    $data['date'] = date("Y-m-d H:i");
    $newPost = $post->createPost($data);
    // Render index view
    return $response->withRedirect('/detail/'.$newPost['id'], 301);
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
    $data['update_date'] = date("Y-m-d H:i");
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


$app->post('/comment/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Comment");
    $comment = new Comment($this->db);
    $data = $request->getParsedBody();
    $data['date'] = date("Y-m-d H:i");
    $comment->createComment($data);
    // Render index view
    return $response->withRedirect('/detail/'.$data['post_id'], 301);
});
