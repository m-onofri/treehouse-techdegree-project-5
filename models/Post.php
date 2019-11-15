<?php
namespace App\Models;
use App\Exception\ApiException;

class Post
{
    protected $database;
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }
    public function getPosts()
    {
        $statement = $this->database->prepare(
            'SELECT * FROM posts ORDER BY id'
        );
        $statement->execute();
        $posts = $statement->fetchAll();
        if (empty($posts)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND, 404);
        }
        return $posts;
    }
    public function getPost($post_id)
    {
        $statement = $this->database->prepare(
            'SELECT * FROM posts WHERE id=:id'
        );
        $statement->bindParam('id', $post_id);
        $statement->execute();
        $post = $statement->fetch();
        if (empty($post)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND, 404);
        }
        return $post;
    }
    public function createPost($data)
    {
        if (empty($data['title']) || empty($data['entry']) || empty($data['date'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }
        $statement = $this->database->prepare(
            'INSERT INTO posts (title, body, date) VALUES (:title, :body, :date)'
        );
        $statement->bindParam('title', $data['title']);
        $statement->bindParam('body', $data['entry']);
        $statement->bindParam('date', $data['date']);
        $statement->execute();
        if ($statement->rowCount()<1) {
            throw new ApiException(ApiException::COURSE_CREATION_FAILED);
        }
        return $this->getPost($this->database->lastInsertId());
    }
    public function updatePost($data)
    {
        if (empty($data['course_id']) || empty($data['title']) || empty($data['url'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }
        $statement = $this->database->prepare(
            'UPDATE posts SET title=:title, url=:url WHERE id=:id'
        );
        $statement->bindParam('title', $data['title']);
        $statement->bindParam('url', $data['url']);
        $statement->bindParam('id', $data['post_id']);
        $statement->execute();
        if ($statement->rowCount()<1) {
            throw new ApiException(ApiException::COURSE_UPDATE_FAILED);
        }
        return $this->getPost($data['post_id']);
    }
    public function deletePost($post_id)
    {
        $this->getPost($post_id);
        $statement = $this->database->prepare(
            'DELETE FROM posts WHERE id=:id'
        );
        $statement->bindParam('id', $post_id);
        $statement->execute();
        if ($statement->rowCount()<1) {
            throw new ApiException(ApiException::COURSE_DELETE_FAILED);
        }
        return ['message' => 'The post was deleted'];
    }
}
