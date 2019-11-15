<?php
use App\Models\Course;

$app->get('/detail', function ($request, $response, $args) {
    // Sample log message [{id}]
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'detail.html', $args);
});

$app->get('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    $course = new Course($this->db);
    echo var_dump($course);
    die;

    // Render index view
    return $this->renderer->render($response, 'new.html', $args);
});

$app->get('/edit', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'edit.html', $args);
});

$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.html', $args);
});
