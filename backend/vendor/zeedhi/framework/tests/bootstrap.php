<?php
require __DIR__."/../vendor/autoload.php";
require __DIR__."/../bootstrap.php";

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->add("tests", realpath(__DIR__."/../"));
$classLoader->add("HumanRelation", realpath(__DIR__."/mocks/src/"));
$classLoader->register();