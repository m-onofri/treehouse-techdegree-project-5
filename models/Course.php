<?php
namespace App\Models;
use App\Exception\ApiException;

class Course
{
    protected $database;
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }
    public function getCourses()
    {
        $statement = $this->database->prepare(
            'SELECT * FROM courses ORDER BY id'
        );
        $statement->execute();
        $courses = $statement->fetchAll();
        if (empty($courses)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND, 404);
        }
        return $courses;
    }
    public function getCourse($course_id)
    {
        $statement = $this->database->prepare(
            'SELECT * FROM courses WHERE id=:id'
        );
        $statement->bindParam('id', $course_id);
        $statement->execute();
        $course = $statement->fetch();
        if (empty($courses)) {
            throw new ApiException(ApiException::COURSE_NOT_FOUND, 404);
        }
        return $course;
    }
    public function createCourse($data)
    {
        if (empty($data['title']) || empty($data['url'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }
        $statement = $this->database->prepare(
            'INSERT INTO courses(title, url) VALUES(:title, :url)'
        );
        $statement->bindParam('title', $data['title']);
        $statement->bindParam('url', $data['url']);
        $statement->execute();
        if ($statement->rowCount()<1) {
            throw new ApiException(ApiException::COURSE_CREATION_FAILED);
        }
        return $this->getCourse($this->database->lastInsertId());
    }
    public function updateCourse($data)
    {
        if (empty($data['course_id']) || empty($data['title']) || empty($data['url'])) {
            throw new ApiException(ApiException::COURSE_INFO_REQUIRED);
        }
        $statement = $this->database->prepare(
            'UPDATE courses SET title=:title, url=:url WHERE id=:id'
        );
        $statement->bindParam('title', $data['title']);
        $statement->bindParam('url', $data['url']);
        $statement->bindParam('id', $data['course_id']);
        $statement->execute();
        if ($statement->rowCount()<1) {
            throw new ApiException(ApiException::COURSE_UPDATE_FAILED);
        }
        return $this->getCourse($data['course_id']);
    }
    public function deleteCourse($course_id)
    {
        $this->getCourse($course_id);
        $statement = $this->database->prepare(
            'DELETE FROM courses WHERE id=:id'
        );
        $statement->bindParam('id', $course_id);
        $statement->execute();
        if ($statement->rowCount()<1) {
            throw new ApiException(ApiException::COURSE_DELETE_FAILED);
        }
        return ['message' => 'The course was deleted'];
    }
}
