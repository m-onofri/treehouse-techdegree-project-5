<?php
use App\Models\{
    Post as Post,
    Comment as Comment,
    Tag as Tag
};

$app->get('/detail/{id}', function ($request, $response, $args) {
    // Sample log message [{id}]
    $db = new Post($this->db);
    $comment = new Comment($this->db);
    $post = $db->getPost($args['id']);
    $comments = $comment->getCommentsByPostId($args['id']);
    $tag = new Tag($this->db);
    $tags = $tag->getTagsByPostId($args['id']);

    // Render index view
    return $this->view->render($response, 'detail.twig', [
        'post' => $post,
        'comments' => $comments,
        'tags' => $tags
    ]);
});

$app->get('/new', function ($request, $response, $args) {
    
    // Render index view
    return $this->view->render($response, 'new.twig', $args);
});

$app->post('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Post");
    $post = new Post($this->db);
    $data = $request->getParsedBody();
    $data['date'] = date("Y-m-d H:i");
    $newPost = $post->createPost($data);
    if (!empty($data['tags'])) {
        $tag = new Tag($this->db);
        $tag->addTags($data['tags'], $newPost['id']);
    }
    // Render index view
    return $response->withRedirect('/detail/'.$newPost['id'], 301);
});

$app->get('/edit/{id}', function ($request, $response, $args) {
    $db = new Post($this->db);
    $post = $db->getPost($args['id']);
    $tag = new Tag($this->db);
    $tags = array_map(function($t){return $t['name'];}, $tag->getTagsByPostId($args['id']));
    // Render index view
    return $this->view->render($response, 'edit.twig', [
        'post' => $post,
        'tags' => $tags
    ]);
});

$app->post('/edit', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Update Post");
    $data = $request->getParsedBody();

    $post = new Post($this->db);
    $data['update_date'] = date("Y-m-d H:i");
    $post->updatePost($data);

    $tag = new Tag($this->db);
    $tag->addTags($data['tags'], $data['id']);
    // $tags = $tag->getTagsByPostId($data['id']);

    // Render index view
    return $response->withRedirect("/detail/".$data['id'], 301);
    // return $this->view->render($response, 'detail.twig', [
    //     'post' => $updatedPost,
    //     'tags' => $tags
    // ]);
});

$app->post('/delete', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Delete Post");
    $post = new Post($this->db);
    $id = $request->getParsedBody()['id'];
    $post->deletePost($id);
    return $response->withRedirect('/', 301);
});

$app->get('/', function ($request, $response, $args) {
    $post = new Post($this->db);

    $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
    $limit = 5; // Number of posts on one page
    $skip = ($page - 1) * $limit;
    $count = $post->countPosts(); // Count of all available posts

    $posts = $post->getPosts($limit, $skip);
    $p= array_map(function($t) {
        $tags = new Tag($this->db);
        $t['tags'] = $tags->getTagsByPostId($t['id']);
        return $t;
    }, $posts);

    // Render index view
    return $this->view->render($response, 'index.twig', [
        'posts' => $p,
        'pagination' => [
            'needed' => $count > $limit,
            'count' => $count,
            'page' => $page,
            'lastpage' => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
            'limit' => $limit
        ]
    ]);
});

$app->get('/tags', function ($request, $response, $args) {
    $tag = new Tag($this->db);
    $tagsList = $tag->getTags();
    // Render index view
    return $this->view->render($response, 'tags.twig', [
        'tagsList' => $tagsList
    ]);
});

$app->get('/tag', function ($request, $response, $args) {
    $tag = new Tag($this->db);
    $post = new Post($this->db);
    $tag_id = $request->getParam('tag');
    $tagName = $tag->getTag($tag_id)['name'];
    $tagsList = $tag->getTags();

    $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
    $limit = 5; // Number of posts on one page
    $skip = ($page - 1) * $limit;
    $postsList = $post->getPostsPerTag($tag_id, $limit, $skip);
    $count = count($post->getPostsPerTag($tag_id));

    $p= array_map(function($t) {
        $tags = new Tag($this->db);
        $t['tags'] = $tags->getTagsByPostId($t['id']);
        return $t;
    }, $postsList);

    return $this->view->render($response, 'tags.twig', [
        'tagsList' => $tagsList,
        'posts' => $p,
        'tagName' => $tagName,
        'tagId' => $tag_id,
        'pagination' => [
            'needed' => $count > $limit,
            'count' => $count,
            'page' => $page,
            'lastpage' => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
            'limit' => $limit
        ]
    ]);
});

$app->post('/tag', function ($request, $response, $args) {
    $tag = new Tag($this->db);
    $post = new Post($this->db);
    $data = $request->getParsedBody();
    $tagName = $data['tag'];
    $tag_id = $tag->getTagId($tagName)['id'];
    switch ($data['action']) {
        case 'List Entries':
            $tagsList = $tag->getTags();

            $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
            $limit = 5; // Number of posts on one page
            $skip = ($page - 1) * $limit;
            $postsList = $post->getPostsPerTag($tag_id, $limit, $skip);
            $count = count($post->getPostsPerTag($tag_id));
            //print_r($postsList); die;

            $p= array_map(function($t) {
                $tags = new Tag($this->db);
                $t['tags'] = $tags->getTagsByPostId($t['id']);
                return $t;
            }, $postsList);

            return $this->view->render($response, 'tags.twig', [
                'tagsList' => $tagsList,
                'posts' => $p,
                'tagName' => $tagName,
                'tagId' => $tag_id,
                'pagination' => [
                    'needed' => $count > $limit,
                    'count' => $count,
                    'page' => $page,
                    'lastpage' => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
                    'limit' => $limit
                ]
            ]);
        case 'Update':
            return $this->view->render($response, 'tagUpdate.twig', [
                'tagName' => $tagName
            ]);
        case 'Delete':
            $tag->deleteTag($tag_id);
        default:
            return $response->withRedirect('/tags', 301);
    }
});

$app->post('/tag/update', function ($request, $response, $args) {
    $tag = new Tag($this->db);
    $newTag = $request->getParsedBody()['new-name'];
    $tag_id = $tag->getTagId($request->getParsedBody()['current-name'])['id'];
    $tag->addSingleTag($newTag, $tag_id);
    return $response->withRedirect('/tags', 301);
});

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

$app->post('/comment/delete', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Comment");
    $comment = new Comment($this->db);
    $data = $request->getParsedBody();
    $msg = $comment->deleteComment($data['comment_id']);
    // Render index view
    return $response->withRedirect('/detail/'.$data['post_id'], 301);
});
