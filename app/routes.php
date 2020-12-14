<?php

use PwExam\Controller\HomeController;
use PwExam\Controller\LoginController;
use PwExam\Controller\RegisterController;
use PwExam\Controller\ItemController;
use PwExam\Controller\ProfileController;

$app->get('/', HomeController::class . ':indexAction');

$app
    ->post('/', HomeController::class . ':searchAction')
    ->setName('search');

$app->get('/login', LogInController::class . ':formAction');

$app
    ->post('/login', LogInController::class . ':loginAction')
    ->setName('login');

$app->get('/register', RegisterController::class . ':formAction');


$app
    ->post('/register', RegisterController::class . ':registerAction')
    ->setName('register');

$app->get('/addItem', ItemController::class . ':formAction');


$app
    ->post('/addItem', ItemController::class . ':addItemAction')
    ->setName('addItem');

$app->get('/myProfile', ProfileController::class . ':formAction');

$app->get('/delete/account/{id}', ProfileController::class . ':deleteAccountAction');

$app->get('/delete/item/{id}', ItemController::class . ':itemDeleteAction');




$app->get('/edit/item/{id}', ItemController::class . ':updateItemForm');

$app
    ->post('/edit/item', ItemController::class . ':updateItem')
    ->setName('updateItem');




$app
    ->post('/myProfile', RegisterController::class . ':updateUser')
    ->setName('updateUser');

$app->get('/item/{id}', ItemController::class . ':itemAction');

$app->get('/myItems', HomeController::class . ':myItemsAction');

$app->get('/buy/{id}', ItemController::class . ':buyItemAction');




$app->get('/logout', ProfileController::class . ':logout');

