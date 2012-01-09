<?php

require_once('UserHandler.php');

$session->start();

if ($session->id && $session->ip && ($session->ip != client_ip()) )
{
    // Logged in: Check if client ip has changed since last request, if so - log user out to avoid session hijacking
    $msg = 'ERROR: Client IP changed for '.$session->username.', Old: '.$session->ip.', current: '.client_ip();
    $error->add($msg);
    dp($msg);
    $session->end();

//    $session->errorPage();
}
else if ($session->id && $session->getLastActive() < (time() - $session->timeout))
{
    // Check user activity - log out inactive user
    $msg = 'Session timed out for '.$session->username.' after '.(time() - $session->getLastActive()).'s (timeout is '.($session->timeout).'s)';
    $error->add($msg);
    dp($msg);
    $session->end();

    throw new exception ( $msg );

    //$session->showErrorPage();
}
/*
else if (!$session->id && !empty($_POST['login_usr']) && isset($_POST['login_pwd']))
{
    // Check for login request, POST to any page with 'login_usr' & 'login_pwd' variables set to log in
    $session->login($_POST['login_usr'], $_POST['login_pwd']);
}*/
else if (!$session->id && $session->facebook_app_id)
{
    // Handle facebook login
    $session->handleFacebookLogin();
}
else if ($session->id)
{
    $session->setLastActive();
}

?>