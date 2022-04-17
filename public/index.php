<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$router = new Bramus\Router\Router();

$router->before('GET|POST', '/admin', function() {
    if (!isset($_SESSION['user'])) {
        header('location: /login');
        exit;
    }
});
$router->before('GET|POST', '/login', function() {
    if (isset($_SESSION['user'])) {
        header('location: /');
        exit;
    }
});
$router->before('GET|POST', '/register', function() {
    if (isset($_SESSION['user'])) {
        header('location: /');
        exit;
    }
});

$router->get('/', 'Mvc\Controllers\TournamentController@listTournament');
$router->get('/register', 'Mvc\Controllers\UserController@register');
$router->post('/register', 'Mvc\Controllers\UserController@register');
$router->get('/login', 'Mvc\Controllers\UserController@login');
$router->post('/login', 'Mvc\Controllers\UserController@login');
$router->get('/logout', 'Mvc\Controllers\UserController@logout');
$router->get('/addTournament', 'Mvc\Controllers\TournamentController@addTournament');
$router->post('/addTournament', 'Mvc\Controllers\TournamentController@addTournament');
$router->get('/tournament', 'Mvc\Controllers\TournamentController@tournament');
$router->post('/tournament', 'Mvc\Controllers\TournamentController@tournament');
$router->get('/joinTeam', 'Mvc\Controllers\TournamentController@joinTeam');
$router->get('/activeMatch', 'Mvc\Controllers\TournamentController@activeMatch');
$router->get('/match', 'Mvc\Controllers\TournamentController@match');



$router->run();