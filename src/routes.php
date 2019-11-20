<?php

$app->get('/detail/{id}', function ($request, $response, $args) {
    
    $post = $this->post->getPost($args['id']);
    $comments = $this->comment->getCommentsByPostId($args['id']);
    $tags = $this->post->getTagsByPostId($args['id']);

    // Render index view
    return $this->view->render($response, 'detail.twig', [
        'post' => $post,
        'comments' => $comments,
        'tags' => $tags
    ]);
});

$app->get('/new', function ($request, $response, $args) {
    return $this->view->render($response, 'new.twig', $args);
});

$app->post('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Post");
    $data = $request->getParsedBody();
    $data['date'] = date("Y-m-d H:i");
    $newPost = $this->post->createPost($data);
    if (!empty($data['tags'])) {
        $this->tag->addTags($data['tags'], $newPost['id']);
    }
    // Render index view
    return $response->withRedirect('/detail/'.$newPost['id'], 301);
});

$app->get('/edit/{id}', function ($request, $response, $args) {
    $post = $this->post->getPost($args['id']);
    $tags = array_map(function($t){return $t['name'];}, $this->tag->getTagsByPostId($args['id']));
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

    $data['update_date'] = date("Y-m-d H:i");
    $this->post->updatePost($data);

    $this->tag->addTags($data['tags'], $data['id']);

    // Render index view
    return $response->withRedirect("/detail/".$data['id'], 301);
});

$app->post('/delete', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Delete Post");
    $id = $request->getParsedBody()['id'];
    $this->post->deletePost($id);
    return $response->withRedirect('/', 301);
});

$app->get('/', function ($request, $response, $args) {
    $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
    $limit = 5; // Number of posts on one page
    $skip = ($page - 1) * $limit;
    $count = $this->post->countPosts(); // Count of all available posts

    $posts = $this->post->getPosts($limit, $skip);

    // Render index view
    return $this->view->render($response, 'index.twig', [
        'posts' => $posts,
        'pagination' => [
            'needed' => $count > $limit,
            'count' => $count,
            'page' => $page,
            'lastpage' => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
            'limit' => $limit
        ]
    ]);
});

$app->get('/tag', function ($request, $response, $args) {
    $tagsList = $this->tag->getTags();
    if (empty($request->getParam('tag'))) {
        return $this->view->render($response, 'tags.twig', ['tagsList' => $tagsList]);
    }
    $tag_id = $request->getParam('tag');
    $tagName = $this->tag->getTag($tag_id)['name'];

    $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
    $limit = 5; // Number of posts on one page
    $skip = ($page - 1) * $limit;
    $postsList = $this->post->getPostsPerTag($tag_id, $limit, $skip);
    $count = count($this->post->getPostsPerTag($tag_id));

    return $this->view->render($response, 'tags.twig', [
        'tagsList' => $tagsList,
        'posts' => $postsList,
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
    $data = $request->getParsedBody();
    $tagName = $data['tag'];
    $tag_id = $this->tag->getTagId($tagName)['id'];
    switch ($data['action']) {
        case 'List Entries':
            $tagsList = $this->tag->getTags();

            $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
            $limit = 5; // Number of posts on one page
            $skip = ($page - 1) * $limit;
            $postsList = $this->post->getPostsPerTag($tag_id, $limit, $skip);
            $count = count($this->post->getPostsPerTag($tag_id));

            return $this->view->render($response, 'tags.twig', [
                'tagsList' => $tagsList,
                'posts' => $postsList,
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
            return $this->view->render($response, 'tagUpdate.twig', ['tagName' => $tagName]);
        case 'Delete':
            $tag->deleteTag($tag_id);
        default:
            return $response->withRedirect('/tags', 301);
    }
});

$app->post('/tag/update', function ($request, $response, $args) {
    $newTag = $request->getParsedBody()['new-name'];
    $tag_id = $this->tag->getTagId($request->getParsedBody()['current-name'])['id'];
    $this->tag->addSingleTag($newTag, $tag_id);
    return $response->withRedirect('/tags', 301);
});

$app->post('/comment/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Comment");
    $data = $request->getParsedBody();
    $data['date'] = date("Y-m-d H:i");
    $this->comment->createComment($data);
    // Render index view
    return $response->withRedirect('/detail/'.$data['post_id'], 301);
});

$app->post('/comment/delete', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Create new Comment");
    $data = $request->getParsedBody();
    $msg = $this->comment->deleteComment($data['comment_id']);
    // Render index view
    return $response->withRedirect('/detail/'.$data['post_id'], 301);
});
