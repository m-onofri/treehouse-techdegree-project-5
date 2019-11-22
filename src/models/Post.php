<?php
namespace App\Models;

class Post
{
    protected $database;
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }
    //Return the total number of available posts
    public function countPosts()
    {
        try {
            $statement = $this->database->prepare('SELECT COUNT(*) FROM posts');
            $statement->execute();
            $postNumber = $statement->fetch()[0];
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $postNumber;
    }
    /**Return all the available posts
     * 2 required arguments: $limit (integer), $skip (integer)*/
    public function getPosts($limit, $skip)
    {
        try {
            $statement = $this->database->prepare(
                'SELECT * FROM posts ORDER BY date DESC LIMIT :limit OFFSET :skip'
            );
            $statement->bindParam('limit', $limit);
            $statement->bindParam('skip', $skip);
            $statement->execute();
            $posts = $statement->fetchAll();
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return $this->implementTags($posts);
    }
    /**Return all the available posts with a specific tag
     * 1 required argument: $tag_id (integer)
     * 2 optional arguments: $limit (integer), $skip (integer)*/
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
            $entries = $results->fetchAll();
        } catch (Exception $e) {
           $e->getMessage();
        }
    
        return $this->implementTags($entries);
    }
    /**Return a specific post
     * 1 required argument: $post_id (integer)*/
    public function getPost($post_id)
    {
        try {
            $statement = $this->database->prepare('SELECT * FROM posts WHERE id=:id');
            $statement->bindParam('id', $post_id);
            $statement->execute();
            $singlePost = $statement->fetch();
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $singlePost;
    }
    /**Add a new post to the database
     * 1 required argument: $data (array)
     * Return the new added post*/
    public function createPost($data)
    {
        try {
            $statement = $this->database->prepare(
                'INSERT INTO posts (title, body, date) VALUES (:title, :body, :date)'
            );
            $statement->bindParam('title', $data['title']);
            $statement->bindParam('body', $data['entry']);
            $statement->bindParam('date', $data['date']);
            $statement->execute();
        } catch (Exception $e) {
            $e->getMessage();
        }
  
        return $this->getPost($this->database->lastInsertId());
    }
    /**Update an existing post
     * 1 required argument: $data (array)
     * Return the updated post*/
    public function updatePost($data)
    {
        try {
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
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $this->getPost($data['id']);
    }
    /**Delete a specific post
     * 1 required argument: $post_id (integer)
     * Return true if the post was deleted, otherwise false*/
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
    /**Return all the tags associated to a specific post
     * 1 required argument: $post_id (integer)*/
    public function getTagsByPostId($post_id)
    {
        try {
            $statement = $this->database->prepare(
                'SELECT tags.name, tags.id FROM tags 
                    JOIN posts_tags ON tags.id = posts_tags.tags_id 
                    WHERE posts_tags.posts_id = :post_id');
            $statement->bindParam('post_id', $post_id);
            $statement->execute();
            $tags = $statement->fetchAll();
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $tags;
    }
    /**Add the tags to the post data array
     * 1 required argument: $post_id (integer)*/
    protected function implementTags($posts)
    {
        return array_map(function($t) {
                $t['tags'] = $this->getTagsByPostId($t['id']);
                return $t;
            }, $posts);
    }
}

