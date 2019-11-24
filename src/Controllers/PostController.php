<?php
namespace App\Controllers;

class PostController
{
    protected $postModel;
    protected $commentModel;
    protected $tagModel;
    protected $view;
    protected $flash;
    protected $logger;
    protected $slugify;
    protected $limit = 5;

    public function __construct($container) {
        $this->postModel = $container->get('post');
        $this->commentModel = $container->get('comment');
        $this->tagModel = $container->get('tag');
        $this->view = $container->get('view');
        $this->flash = $container->get('flash');
        $this->logger = $container->get('logger');
        $this->slugify = $container->get('slugify');
    }

    public function home($request, $response, $args) 
    { 
        // Render index page
        return $this->view->render($response, 'index.twig', $this->pagination($request));
    }

    public function singlePost($request, $response, $args) {
        //Get the post data and the relative comments and tags
        //$postId = $this->postModel->getPostIdBySlug($args['slug']);
        $post = $this->postModel->getPost($args['slug']);
        $comments = $this->commentModel->getCommentsByPostId($post['id']);
        $tags = $this->postModel->getTagsByPostId($post['id']);

        // Render single post
        return $this->view->render($response, 'detail.twig', [
            'post' => $post,
            'comments' => $comments,
            'tags' => $tags,
            'msg' => $this->flash->getFirstMessage('NoComment')
        ]);
    }

    public function newPost($request, $response, $args) {
        // Render form to insert new post
        return $this->view->render($response, 'new.twig', ['msg' => $this->flash->getFirstMessage('NoNew')]);
    }

    public function createNewPost($request, $response, $args) {
        //Get the data from the form
        $data = $request->getParsedBody();
        //Check if title and entry are not empty
        if (empty($data['title']) || empty($data['entry'])) {
            $this->flash->addMessage('NoNew', 'Title and Entry in the post cannot be empty!');
            return $response->withRedirect('/new', 301);
        }
        //Add the creation date of the post and the slug
        $data['date'] = date("Y-m-d H:i");
        $slug = $this->slugify->slugify($data['title']);
        //Check if the new slug is already in the db
        if ($this->isSlugInPosts($slug)) {
            $data['slug'] = $slug . "-" . ($this->postModel->getIdLastPost() + 1);
        } else {
            $data['slug'] = $slug;
        }
        //Store all the data in the db
        $newPost = $this->postModel->createPost($data);
        //If there are tags, store them in the db
        if (!empty($data['tags'])) {
            $this->tagModel->addTags($data['tags'], $newPost['id']);
        }
        // Log message
        $this->logger->info("Create new Post");
        // Redirect to the new single post page
        return $response->withRedirect('/detail/'.$newPost['slug'], 301);
    }

    public function editPostForm($request, $response, $args) {
        //Get post data and relative tags name
        $post = $this->postModel->getPost($args['slug']);
        $tags = array_map(function($t){return $t['name'];}, $this->tagModel->getTagsByPostId($post['id']));
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
        //Check if title and entry are not empty
        if (empty($data['title']) || empty($data['entry'])) {
            $this->flash->addMessage('NoEdit', 'Title and Entry in the post cannot be empty');
            return $response->withRedirect("/edit/".$data['slug'], 301);
        }
        //Add the edit date of the post and create slug
        $data['update_date'] = date("Y-m-d H:i");
        $slug = $this->slugify->slugify($data['title']);
        //Check if the new slug is already in the db
        if ($this->isSlugInPosts($slug)) {
            $data['slug'] = $slug . "-" . ($this->postModel->getIdLastPost() + 1);
        } else {
            $data['slug'] = $slug;
        }
        //Update post data and relative tags name
        $this->postModel->updatePost($data);
        $this->tagModel->addTags($data['tags'], $data['id']);
        // Log message
        $this->logger->info("Update Post");
        // Redirect to the updated single post page
        return $response->withRedirect("/detail/".$data['slug'], 301);
    }

    public function deletePost($request, $response, $args) {
        //Get id of the post to delete and delete it and the associated comments
        $id = $request->getParsedBody()['id'];
        $this->postModel->deletePost($id);
        $this->commentModel->deleteComments($id);
        // Log message
        $this->logger->info("Delete Post");
        //Redirect to the index page
        return $response->withRedirect('/', 301);
    }

    private function isSlugInPosts($slug)
    {
        //Check if the new slug is already in the db
        $posts = $this->postModel->getPosts();
        foreach ($posts as $post) {
            if ($post['slug'] == $slug) {
                return true;
            }
        }
        return false;
    }

    protected function pagination($request)
    {
        /** code adapted from https://github.com/romanzipp/PHP-Slim-Pagination **/
        //Set parameters for pagination
        $page = $request->getParam('page', 0) > 0 ? $request->getParam('page') : 1;
        $skip = ($page - 1) * $this->limit;
        $count = $this->postModel->countPosts();
        //Return parameters for pagination 
        return [
            'posts' => $this->postModel->getPosts($this->limit, $skip),
            'pagination' => [
                'needed' => $count > $this->limit,
                'count' => $count,
                'page' => $page,
                'lastpage' => (ceil($count / $this->limit) == 0 ? 1 : ceil($count / $this->limit)),
                'limit' => $this->limit
            ]
        ];
    }
}
