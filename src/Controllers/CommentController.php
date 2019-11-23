<?php
namespace App\Controllers;

class CommentController
{
    protected $commentModel;
    protected $flash;
    protected $logger;

    public function __construct($container) {
        $this->commentModel = $container->get('comment');
        $this->flash = $container->get('flash');
        $this->logger = $container->get('logger');
    }

    public function newComment($request, $response, $args) {
        //Get all the data from the form
        $data = $request->getParsedBody();
        //Check if title and entry are not empty
        if (empty($data['name']) || empty($data['body'])) {
            $this->flash->addMessage('NoComment', 'Name and Body in comments cannot be empty');
            return $response->withRedirect('/detail/'.$data['slug'], 301);
        }
        //Set the data when comment is created
        $data['date'] = date("Y-m-d H:i");
        //Create comment
        $this->commentModel->createComment($data);
        // Sample log message
        $this->logger->info("Create new Comment");
        // Render index view
        return $response->withRedirect('/detail/'.$data['slug'], 301);
    }

    public function deleteComment($request, $response, $args) {
        //Get data from the form
        $data = $request->getParsedBody();
        //Delete the comment
        $this->commentModel->deleteComment($data['comment_id']);
        // Sample log message
        $this->logger->info("Delete Comment");
        // Render index view
        return $response->withRedirect('/detail/'.$data['slug'], 301);
    }
}
