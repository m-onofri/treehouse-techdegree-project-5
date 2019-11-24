<?php
namespace App\Controllers;

class TagController
{
    protected $postModel;
    protected $tagModel;
    protected $view;
    protected $limit = 5;

    public function __construct($container) {
        $this->postModel = $container->get('post');
        $this->tagModel = $container->get('tag');
        $this->view = $container->get('view');
    }

    protected function pagination($request, $tag_id)
    {
        //Set parameters for pagination
        $page = $request->getParam('page', 0) > 0 ? $request->getParam('page') : 1;
        $skip = ($page - 1) * $this->limit;
        $count = count($this->postModel->getPostsPerTag($tag_id));
        //Return parameters for pagination 
        return [
        'posts' => $$this->postModel->getPostsPerTag($tag_id, $this->limit, $skip), 
        'pagination' => [
                'needed' => $count > $this->limit,
                'count' => $count,
                'page' => $page,
                'lastpage' => (ceil($count / $this->limit) == 0 ? 1 : ceil($count / $this->limit)),
                'limit' => $this->limit
            ]
        ];
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
        //Render the list of the posts with the selected tag
        return $this->view->render($response, 'tags.twig', array_merge([
                'tagsList' => $tagsList,
                'tagName' => $tagName,
                'tagId' => $tag_id
            ], $this->pagination($request, $tag_id)
        ));
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
                return $this->view->render($response, 'tags.twig', array_merge([
                        'tagsList' => $tagsList,
                        'tagName' => $tagName,
                        'tagId' => $tag_id
                    ],$this->pagination($request, $tag_id)
                ));
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
