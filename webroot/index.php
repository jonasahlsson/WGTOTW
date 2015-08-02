<?php

require __DIR__.'/config_with_app.php'; 

// länka in theme
$app->theme->configure(ANAX_APP_PATH . 'config/theme_wgtotw.php');

// Visa grid om jag vill
if( $app->request->getGet('show-grid') !== NULL) {
    $app->theme->addStylesheet('css/show-grid.css');
}

// Använd clean länkar
$app->url->setUrlType(\Anax\Url\CUrl::URL_CLEAN);

// Länka in navbar
$app->navbar->configure(ANAX_APP_PATH . 'config/navbar_wgtotw.php');

// create database connection
$di->setShared('db', function() {
    $db = new \Mos\Database\CDatabaseBasic();
    $db->setOptions(require ANAX_APP_PATH . 'config/database_sqlite.php');
    $db->connect();
    return $db;
});

// create a user service
$di->set('users', function () use ($di) {
    $users = new \Anax\Users\User($di);
    $users->setDI($di);
    return $users;
});


// forum
// Create forumcontroller
$di->set('ForumController', function() use ($di) {
    $controller = new \Joah\Forum\ForumController();
    $controller->setDI($di);
    return $controller;
});

// users
// Create usercontroller
$di->set('UsersController', function() use ($di) {
    $controller = new \Anax\Users\UsersController();
    $controller->setDI($di);
    return $controller;
});



// Frontpage 
$app->router->add('', function() use ($app) {
    $app->theme->setTitle("Hem och trädgård");

    // main
    $content = $app->fileContent->get('forum/content.md');
    $content = $app->textFilter->doFilter($content, 'shortcode, markdown');
    
    $app->views->add('forum/content', [
        'content' => $content,
    ]);
    
    // sidebar
    $content = $app->fileContent->get('forum/sidebar.md');
    $content = $app->textFilter->doFilter($content, 'shortcode, markdown');
    
    $app->views->add('forum/content', [
        'content' => $content,
    ], 'sidebar');
    
});

// about 
$app->router->add('about', function() use ($app) {
    $app->theme->setTitle("Om oss");

    // main
    $content = $app->fileContent->get('forum/about.md');
    $content = $app->textFilter->doFilter($content, 'shortcode, markdown');
    
    $app->views->add('forum/content', [
        'content' => $content,
    ]);
    
    // sidebar
    $content = $app->fileContent->get('forum/about_me.md');
    $content = $app->textFilter->doFilter($content, 'shortcode, markdown');
    
    $app->views->add('forum/content', [
        'content' => $content,
    ], 'sidebar');
    
});






$app->router->handle();
$app->theme->render();
