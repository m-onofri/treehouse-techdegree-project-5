<?php
namespace App\Models;

class Tag
{
    protected $database;
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }
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
    public function getTags()
    {
        try {
            $statement = $this->database->prepare('SELECT name FROM tags ORDER BY name');
            $statement->execute();
            $tags = array_map(function($t) { return $t['name'];}, $statement->fetchAll());
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return $tags;
    }
    public function getTag($tag_id)
    {
        try {
            $statement = $this->database->prepare('SELECT name FROM tags WHERE id=:id');
            $statement->bindParam('id', $tag_id);
            $statement->execute();
            $tag_name = $statement->fetch();
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return $tag_name;
    }
    public function getTagId($tag)
    {
        try {
            $statement = $this->database->prepare('SELECT id FROM tags WHERE name=:name');
            $statement->bindParam('name', $tag);
            $statement->execute();
            $tag_id = $statement->fetch();
        } catch (Exception $e) {
            $e->getMessage();
        }
        
        return $tag_id;
    }
    public function addSingleTag($tag, $id = null)
    {
        try {
            if (!empty($id)) {
                $result = $this->database->prepare('UPDATE tags SET name = :name WHERE id = :id');
            } else {
                $result = $this->database->prepare('INSERT INTO tags (name) VALUES (:name)');
            }
            $result->bindParam('name', $tag);
            if (!empty($id)) {
                $result->bindParam('id', $id);
            }
            if ($result->execute()) {
                if (!empty($id)) {
                    return true;
                } else {
                    $tag_id = $this->database->lastInsertId();
                    return $tag_id;
                }
            }  
        } catch (Exception $e) {
            $e->getMessage();
        }
        return false;
    }
    public function addTags($tags, $post_id)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        $tags_arr = array_map(function($t) {return trim($t);}, explode(',', $tags));
        //Get all the tags in the tags table
        $tags_list = $this->getTags();
        //Get all the tags associated with the entry
        $postTags = array_map(function($t) { return $t['name'];}, $this->getTagsByPostID($post_id));

        foreach ($tags_arr as $tag) {
            //Check if $tag is already in the tags table
            if (!in_array($tag, $tags_list)) {
                //if not, add the tag to the tags table
                $tag_id = $this->addSingleTag($tag);
            } else {
                //otherwise get the id of the tag
            $tag_id = $this->getTagId($tag)['id']; 
            }

            //Check if $tag is already associated to the entry
            if (!in_array($tag, $postTags)) {
                //if not, add the entry id and the tag id to the enries_tags table
                try {
                    $result = $this->database->prepare('INSERT INTO posts_tags (posts_id, tags_id) VALUES (:posts_id, :tags_id)');
                    $result->bindParam("posts_id", $post_id);
                    $result->bindParam("tags_id", $tag_id);
                    $result->execute();
                } catch (Exception $e) {
                    $e->getMessage();
                }
            }
        }

        foreach ($postTags as $tag1) {
            //Check if the user removes a tag for the selected entry
            if (!in_array($tag1, $tags_arr)) {
                //if so get the tag id
                $tag_id = $this->getTagId($tag1)['id'];
                //and remove all rows with the current $entry_id and $tag_id
                $result1 = $this->deletePostTag($post_id, $tag_id);
            }
        }
    
        return true;
    }
    public function deletePostTag($post_id, $tag_id)
    {
        try {
            $result = $this->database->prepare(
                'DELETE FROM posts_tags WHERE posts_id = :post_id AND tags_id = :tag_id');
            $result->bindParam("post_id", $post_id);
            $result->bindParam("tag_id", $tag_id);
            if ($result->execute()) {
                return true;
            }  
        } catch (Exception $e) {
            $e->getMessage();
        }
    
        return false;
    }
    public function deleteTag($tag_id) {
    
        try {
            $result = $this->database->prepare('DELETE FROM tags WHERE id = :tag_id');
            $result->bindParam("tag_id", $tag_id);
    
            $result1 = $this->database->prepare('DELETE FROM posts_tags WHERE tags_id = :tag_id');
            $result1->bindParam("tag_id", $tag_id);
    
           if ($result->execute() && $result1->execute()) {
                return true;
            }
        } catch (Exception $e) {
           $e->getMessage();
        }
    
        return false;
    }
}
