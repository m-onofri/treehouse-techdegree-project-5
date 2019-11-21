<?php
namespace App\Controllers;
use Psr\Container\ContainerInterface;

class TagController
{
    protected $postModel;
    protected $tagModel;
    protected $view;
    protected $flash;
    protected $logger;

    public function __construct($container) {
        $this->postModel = $container->get('post');
        $this->tagModel = $container->get('tag');
        $this->view = $container->get('view');
    }

    public function tagsList($request, $response, $args) {
      //Get all the tag available
        $tagsList = $this->tagModel->getTags();
        $tag_id = $request->getParam('tag');
        if (empty($tag_id)) {
            //Render only the tag form
            return $this->view->render($response, 'tags.twig', ['tagsList' => $tagsList]);
        }
        //Get name of the selected tag
        $tagName = $this->tagModel->getTag($tag_id)['name'];
        //Set parameters for pagination
        $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
        $limit = 5; // Number of posts on one page
        $skip = ($page - 1) * $limit;
        $count = count($this->postModel->getPostsPerTag($tag_id));
        //Get all the posts with the selected tag
        $postsList = $this->postModel->getPostsPerTag($tag_id, $limit, $skip);
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
    }

    public function handleTag($request, $response, $args) {
        //Get the data from the form
        $data = $request->getParsedBody();
        $tagName = $data['tag'];
        if (empty($tagName)) {
            //Redirect to the index page if no tag is selected
            return $response->withRedirect('/tag', 301);
        }
        //Get the id of the selected tag
        $tag_id = $this->tagModel->getTagId($tagName)['id'];
        switch ($data['action']) {
            //if the user clicked the 'List Post' button
            case 'List Entries':
                //Get all the tag available
                $tagsList = $this->tagModel->getTags();
                //Set parameters for pagination
                $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
                $limit = 5; // Number of posts on one page
                $skip = ($page - 1) * $limit;
                $count = count($this->postModel->getPostsPerTag($tag_id));
                //Get all the posts with the selected tag
                $postsList = $this->postModel->getPostsPerTag($tag_id, $limit, $skip);
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
                $tagModel->deleteTag($tag_id);
            default:
                //Redirect to the tag starting page
                return $response->withRedirect('/tag', 301);
        }
    }
    
    public function updateTag($request, $response, $args) {
        //Get the new tag name
        $newTag = $request->getParsedBody()['new-name'];
        if (empty($newTag)) {
            //If there is no new tag name, send a message
            return $this->view->render($response, 'tagUpdate.twig', ['msg' => 'The new Tag name is required']);
        }
        //Get the id of the tag you want update
        $tag_id = $this->tagModel->getTagId($request->getParsedBody()['current-name'])['id'];
        //Update tag
        $this->tagModel->addSingleTag($newTag, $tag_id);
        //Redirect to the tag starting page
        return $response->withRedirect('/tag', 301);
    }
}
