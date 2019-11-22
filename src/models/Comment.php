<?php
namespace App\Models;

class Comment
{
    protected $database;
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }
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
    public function createComment($data)
    {
        try {
            $statement = $this->database->prepare(
                'INSERT INTO comments (post_id, name, body, date) VALUES (:post_id, :name, :body, :date)');
            $statement->bindParam('post_id', $data['post_id']);
            $statement->bindParam('name', $data['name']);
            $statement->bindParam('body', $data['body']);
            $statement->bindParam('date', $data['date']);
            $statement->execute();
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return $this->getComment($this->database->lastInsertId());
    }
    public function deleteComment($comment_id)
    {
        try {
            $this->getComment($comment_id);
            $statement = $this->database->prepare('DELETE FROM comments WHERE id=:id');
            $statement->bindParam('id', $comment_id);
            $statement->execute();
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return ['message' => 'The review was deleted.'];
    }
}
