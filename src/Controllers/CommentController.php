<?php
namespace App\Controllers;
use Psr\Container\ContainerInterface;

class CommentController
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

    public function newComment($request, $response, $args) {
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
        $this->commentModel->createComment($data);
        // Sample log message
        $this->logger->info("Create new Comment");
        // Render index view
        return $response->withRedirect('/detail/'.$data['post_id'], 301);
    }

    public function deleteComment($request, $response, $args) {
        //Get data from the form
        $data = $request->getParsedBody();
        //Delete the comment
        $this->commentModel->deleteComment($data['comment_id']);
        // Sample log message
        $this->logger->info("Delete Comment");
        // Render index view
        return $response->withRedirect('/detail/'.$data['post_id'], 301);
    }
}