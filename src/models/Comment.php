<?php
namespace App\Models;

class Comment
{
    protected $database;
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }
    /**Return all the comments associated to a specific post
     * 1 required argument: $post_id (integer)*/
    public function getCommentsByPostId($post_id)
    {
        try {
            $statement = $this->database->prepare('SELECT * FROM comments WHERE post_id=:post_id');
            $statement->bindParam('post_id', $post_id);
            $statement->execute();
            $comments = $statement->fetchAll();
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return $comments;
    }
    /**Return a specific comment
     * 1 required argument: $comment_id (integer)*/
    public function getComment($comment_id)
    {
        try {
            $statement = $this->database->prepare('SELECT * FROM comments WHERE id=:id');
            $statement->bindParam('id', $comment_id);
            $statement->execute();
            $comment = $statement->fetch();
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return $comment;
    }
    /**Create a new comment
     * 1 required argument: $data (array)
     * Return true if the comment was created, otherwise false*/
    public function createComment($data)
    {
        try {
            $statement = $this->database->prepare(
                'INSERT INTO comments (post_id, name, body, date) VALUES (:post_id, :name, :body, :date)');
            $statement->bindParam('post_id', $data['post_id']);
            $statement->bindParam('name', $data['name']);
            $statement->bindParam('body', $data['body']);
            $statement->bindParam('date', $data['date']);
            if ($statement->execute()) {
                return true;
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return false;
    }
    /**Delete a specific comment
     * 1 required argument: $comment_id (integer)
     * Return true if the comment was created, otherwise false*/
    public function deleteComment($comment_id)
    {
        try {
            $statement = $this->database->prepare('DELETE FROM comments WHERE id=:id');
            $statement->bindParam('id', $comment_id);
            if ($statement->execute()) {
                return true;
            }
            
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return false;
    }
    /**Delete all the comments associated to specific post
     * 1 required argument: $post_id (integer)
     * Return true if the comments were deleted, otherwise false*/
    public function deleteComments($post_id)
    {
        try {
            $statement = $this->database->prepare('DELETE FROM comments WHERE post_id=:post_id');
            $statement->bindParam('post_id', $post_id);
            if ($statement->execute()) {
                return true;
            }
            
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return false;
    }
}
