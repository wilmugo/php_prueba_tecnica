<?php

require_once '../vendor/autoload.php';
use EasyProjects\SimpleRouter\Router as Router;
use App\Controller\AuthController;
use App\Controller\ProductController;

$auth = new AuthController();
$producto = new ProductController();

$router = new Router();


$router->get('/login', fn() => $auth->login());
$router->post('/login', fn() => $auth->login());

$router->get('/registro', fn() => $auth->registro());
$router->post('/registro', fn() => $auth->registro());

$router->get('/dashboard', fn() => $producto->index());
$router->get('/productos/crear', fn() => $producto->crear());
$router->post('/productos/crear', fn() => $producto->crear());


$router->start();