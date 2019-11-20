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
    public function countPosts()
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM posts'
        );
        $statement->execute();
        $posts = $statement->fetch()[0];
        if (empty($posts)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND, 404);
        }
        return $posts;
    }
    public function getPosts($limit, $skip)
    {
        $statement = $this->database->prepare(
            'SELECT * FROM posts ORDER BY date DESC LIMIT :limit OFFSET :skip'
        );
        $statement->bindParam('limit', $limit);
        $statement->bindParam('skip', $skip);
        $statement->execute();
        $posts = $statement->fetchAll();
        if (empty($posts)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND, 404);
        }
        return $posts;
    }
    public function getPostsPerTag($tag_id, $limit = null, $skip = 0) {
        try {
            $query = "SELECT posts.* FROM posts JOIN posts_tags
                        ON posts.id = posts_tags.posts_id
                        WHERE posts_tags.tags_id = :tag_id
                        ORDER BY posts.date DESC";
            if (!empty($limit)) {
                $query .= " LIMIT :limit OFFSET :skip";
            }
            $results =  $this->database->prepare($query);
            $results->bindParam('tag_id', $tag_id);
            if (!empty($limit)) {
                $results->bindParam('limit', $limit);
                $results->bindParam('skip', $skip);
            }
            $results->execute();
        } catch (Exception $e) {
           $e->getMessage();
        }
    
        $entries = $results->fetchAll();
    
        return $entries;
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
        if (empty($data['id']) || empty($data['title']) || empty($data['entry'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }

        $query = 'UPDATE posts SET title=:title, body=:body,';
        if(!empty($data['update_date'])) {
            $query .= ' update_date=:update_date';
        }
        $query .= ' WHERE id=:id';

        $statement = $this->database->prepare($query);
        $statement->bindParam('title', $data['title']);
        $statement->bindParam('body', $data['entry']);
        $statement->bindParam('id', $data['id']);
        if(!empty($data['update_date'])) {
            $statement->bindParam('update_date', $data['update_date']);
        }
        $statement->execute();
        if ($statement->rowCount()<1) {
            throw new ApiException(ApiException::COURSE_UPDATE_FAILED);
        }
        return $this->getPost($data['id']);
    }
    public function deletePost($post_id)
    {
        try {
            $result = $this->database->prepare('DELETE FROM posts WHERE id = :post_id');
            $result->bindParam('post_id', $post_id);
    
            $result1 = $this->database->prepare('DELETE FROM posts_tags WHERE posts_id = :post_id');
            $result1->bindParam('post_id', $post_id);
    
           if ($result->execute() && $result1->execute()) {
                return true;
            }
        } catch (Exception $e) {
           $e->getMessage();
        }
    
        return false;
    }
}
