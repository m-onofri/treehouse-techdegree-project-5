<?php
use App\Controllers\PostController;
use App\Controllers\TagController;
use App\Controllers\CommentController;

$app->get('/', PostController::class . ':home');
$app->get('/detail/{slug}', PostController::class . ':singlePost');
$app->get('/new', PostController::class . ':newPost');
$app->post('/new', PostController::class . ':createNewPost');
$app->get('/edit/{slug}', PostController::class . ':editPostForm');
$app->post('/edit', PostController::class . ':editPost');
$app->post('/delete', PostController::class . ':deletePost');

$app->get('/tag', TagController::class . ':tagsList');
$app->post('/tag', TagController::class . ':handleTag');
$app->post('/tag/update', TagController::class . ':updateTag');

$app->post('/comment/new', CommentController::class . ':newComment');
$app->post('/comment/delete', CommentController::class . ':deleteComment');
