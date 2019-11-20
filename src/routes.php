<?php

$app->get('/', function ($request, $response, $args) {
    //Set parameters for pagination
    $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
    $limit = 5; // Number of posts on one page
    $skip = ($page - 1) * $limit;
    $count = $this->post->countPosts(); // Count of all available posts

    //Get all the available posts
    $posts = $this->post->getPosts($limit, $skip);

    // Render index page
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

$app->get('/detail/{id}', function ($request, $response, $args) {
    //Get the post data and the relative comments and tags
    $post = $this->post->getPost($args['id']);
    $comments = $this->comment->getCommentsByPostId($args['id']);
    $tags = $this->post->getTagsByPostId($args['id']);

    // Render single post
    return $this->view->render($response, 'detail.twig', [
        'post' => $post,
        'comments' => $comments,
        'tags' => $tags,
        'msg' => $this->flash->getFirstMessage('NoComment')
    ]);
})->setName('detail');

$app->get('/new', function ($request, $response, $args) {
    // Render form to create a new post
    return $this->view->render($response, 'new.twig', [ 'msg' => $this->flash->getFirstMessage('NoNew') ]);
});

$app->post('/new', function ($request, $response, $args) {
    //Get the data from the form
    $data = $request->getParsedBody();
    if (empty($data['title']) || empty($data['entry'])) {
        //Check if title and entry are not empty
        $this->flash->addMessage('NoNew', 'Title and Entry cannot be empty!');
        return $response->withRedirect('/new', 301);
        //return $this->view->render($response, 'new.twig', ['msg' => 'Title and Entry cannot be empty']);
    }
    //Add the creation date of the post
    $data['date'] = date("Y-m-d H:i");
    //Store all the data in the db
    $newPost = $this->post->createPost($data);
    if (!empty($data['tags'])) {
        //If there are tags, store them in the db
        $this->tag->addTags($data['tags'], $newPost['id']);
    }
    // Sample log message
    $this->logger->info("Create new Post");
    // Redirect to the new single post page
    return $response->withRedirect('/detail/'.$newPost['id'], 301);
});

$app->get('/edit/{id}', function ($request, $response, $args) {
    //Get post data and relative tags name
    $post = $this->post->getPost($args['id']);
    $tags = array_map(function($t){return $t['name'];}, $this->tag->getTagsByPostId($args['id']));
    // Render edit post page
    return $this->view->render($response, 'edit.twig', [
        'post' => $post,
        'tags' => $tags,
        'msg' => $this->flash->getFirstMessage('NoEdit')
    ]);
});

$app->post('/edit', function ($request, $response, $args) {
    //Get the data from the form
    $data = $request->getParsedBody();
    if (empty($data['title']) || empty($data['entry'])) {
        //Check if title and entry are not empty
        $this->flash->addMessage('NoEdit', 'Title and Entry cannot be empty');
        return $response->withRedirect("/edit/".$data['id'], 301);
    }
    //Add the edit date of the post
    $data['update_date'] = date("Y-m-d H:i");
    //Update post data and relative tags name
    $this->post->updatePost($data);
    $this->tag->addTags($data['tags'], $data['id']);
    // Sample log message
    $this->logger->info("Update Post");
    // Redirect to the updated single post page
    return $response->withRedirect("/detail/".$data['id'], 301);
});

$app->post('/delete', function ($request, $response, $args) {
    //Get id of the post to delete and delete it
    $id = $request->getParsedBody()['id'];
    $this->post->deletePost($id);
    // Sample log message
    $this->logger->info("Delete Post");
    //Redirect to the index page
    return $response->withRedirect('/', 301);
});

$app->get('/tag', function ($request, $response, $args) {
    //Get all the tag available
    $tagsList = $this->tag->getTags();
    $tag_id = $request->getParam('tag');
    if (empty($tag_id)) {
        //Render only the tag form
        return $this->view->render($response, 'tags.twig', ['tagsList' => $tagsList]);
    }
    //Get name of the selected tag
    $tagName = $this->tag->getTag($tag_id)['name'];
    //Set parameters for pagination
    $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
    $limit = 5; // Number of posts on one page
    $skip = ($page - 1) * $limit;
    $count = count($this->post->getPostsPerTag($tag_id));
    //Get all the posts with the selected tag
    $postsList = $this->post->getPostsPerTag($tag_id, $limit, $skip);
    //Render the list of the posts with the selected tag
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
    //Get the data from the form
    $data = $request->getParsedBody();
    $tagName = $data['tag'];
    if (empty($tagName)) {
        //Redirect to the index page if no tag is selected
        return $response->withRedirect('/tag', 301);
    }
    //Get the id of the selected tag
    $tag_id = $this->tag->getTagId($tagName)['id'];
    switch ($data['action']) {
        //if the user clicked the 'List Post' button
        case 'List Entries':
            //Get all the tag available
            $tagsList = $this->tag->getTags();
            //Set parameters for pagination
            $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
            $limit = 5; // Number of posts on one page
            $skip = ($page - 1) * $limit;
            $count = count($this->post->getPostsPerTag($tag_id));
            //Get all the posts with the selected tag
            $postsList = $this->post->getPostsPerTag($tag_id, $limit, $skip);
            //Render the list of the posts with the selected tag
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
        //if the user clicked the 'Update' button
        case 'Update':
            //Render the tag update page
            return $this->view->render($response, 'tagUpdate.twig', ['tagName' => $tagName]);
        //if the user clicked the 'Delete' button
        case 'Delete':
            //Delete the selected tag
            $tag->deleteTag($tag_id);
        default:
            //Redirect to the tag starting page
            return $response->withRedirect('/tag', 301);
    }
});

$app->post('/tag/update', function ($request, $response, $args) {
    //Get the new tag name
    $newTag = $request->getParsedBody()['new-name'];
    if (empty($newTag)) {
        //If there is no new tag name, send a message
        return $this->view->render($response, 'tagUpdate.twig', ['msg' => 'The new Tag name is required']);
    }
    //Get the id of the tag you want update
    $tag_id = $this->tag->getTagId($request->getParsedBody()['current-name'])['id'];
    //Update tag
    $this->tag->addSingleTag($newTag, $tag_id);
    //Redirect to the tag starting page
    return $response->withRedirect('/tag', 301);
});

$app->post('/comment/new', function ($request, $response, $args) {
    //Get all the data from the form
    $data = $request->getParsedBody();
    //Check if title and entry are not empty
    if (empty($data['name']) || empty($data['body'])) {
        $this->flash->addMessage('NoComment', 'Name and Body cannot be empty');
        return $response->withRedirect('/detail/'.$data['post_id'], 301);
    }
    //Set the data when comment is created
    $data['date'] = date("Y-m-d H:i");
    //Create comment
    $this->comment->createComment($data);
    // Sample log message
    $this->logger->info("Create new Comment");
    // Render index view
    return $response->withRedirect('/detail/'.$data['post_id'], 301);
});

$app->post('/comment/delete', function ($request, $response, $args) {
    //Get data from the form
    $data = $request->getParsedBody();
    //Delete the comment
    $this->comment->deleteComment($data['comment_id']);
    // Sample log message
    $this->logger->info("Create new Comment");
    // Render index view
    return $response->withRedirect('/detail/'.$data['post_id'], 301);
});
