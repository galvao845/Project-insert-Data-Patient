<?php  
require "autoloader.php"; 
require __DIR__."/../vendor/autoload.php";
require __DIR__."/../vendor/zeedhi/framework/bootstrap.php"; 
date_default_timezone_set('America/Sao_Paulo'); 
$instanceManager = \Zeedhi\Framework\DependencyInjection\InstanceManager::getInstance();  
$instanceManager->loadFromFile(__DIR__.'/../environment.xml'); 
$instanceManager->loadFromFile(__DIR__.'/../services.xml'); 
$instanceManager->compile();  