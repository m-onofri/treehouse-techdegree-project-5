<?php
namespace App\Controllers;
use Psr\Container\ContainerInterface;

class PostController
{
    protected $container;
    protected $postModel;
    protected $commentModel;
    protected $tagModel;
    protected $view;
    protected $flash;
    protected $logger;

    public function __construct($container) {
        $this->container = $container;
        $this->postModel = $container->get('post');
        $this->commentModel = $container->get('comment');
        $this->tagModel = $container->get('tag');
        $this->view = $container->get('view');
        $this->flash = $container->get('flash');
        $this->logger = $container->get('logger');
    }

    public function home($request, $response, $args) {
      //Set parameters for pagination
        $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
        $limit = 5; // Number of posts on one page
        $skip = ($page - 1) * $limit;
        $count = $this->postModel->countPosts(); // Count of all available posts

        //Get all the available posts
        $posts = $this->postModel->getPosts($limit, $skip);

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
    }

    public function singlePost($request, $response, $args) {
        //Get the post data and the relative comments and tags
        $post = $this->postModel->getPost($args['id']);
        $comments = $this->commentModel->getCommentsByPostId($args['id']);
        $tags = $this->postModel->getTagsByPostId($args['id']);

        // Render single post
        return $this->view->render($response, 'detail.twig', [
            'post' => $post,
            'comments' => $comments,
            'tags' => $tags,
            'msg' => $this->flash->getFirstMessage('NoComment')
        ]);
    }

    public function newPost($request, $response, $args) {
        return $this->view->render($response, 'new.twig', ['msg' => $this->flash->getFirstMessage('NoNew')]);
    }

    public function createNewPost($request, $response, $args) {
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
        $newPost = $this->postModel->createPost($data);
        if (!empty($data['tags'])) {
            //If there are tags, store them in the db
            $this->tagModel->addTags($data['tags'], $newPost['id']);
        }
        // Sample log message
        $this->logger->info("Create new Post");
        // Redirect to the new single post page
        return $response->withRedirect('/detail/'.$newPost['id'], 301);
    }

    public function editPostForm($request, $response, $args) {
        //Get post data and relative tags name
        $post = $this->postModel->getPost($args['id']);
        $tags = array_map(function($t){return $t['name'];}, $this->tagModel->getTagsByPostId($args['id']));
        // Render edit post page
        return $this->view->render($response, 'edit.twig', [
            'post' => $post,
            'tags' => $tags,
            'msg' => $this->flash->getFirstMessage('NoEdit')
        ]);
    }

    public function editPost($request, $response, $args) {
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
        $this->postModel->updatePost($data);
        $this->tagModel->addTags($data['tags'], $data['id']);
        // Sample log message
        $this->logger->info("Update Post");
        // Redirect to the updated single post page
        return $response->withRedirect("/detail/".$data['id'], 301);
    }

    public function deletePost($request, $response, $args) {
        //Get id of the post to delete and delete it
        $id = $request->getParsedBody()['id'];
        $this->postModel->deletePost($id);
        // Sample log message
        $this->logger->info("Delete Post");
        //Redirect to the index page
        return $response->withRedirect('/', 301);
    }
}
