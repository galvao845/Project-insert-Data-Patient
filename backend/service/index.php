<?php
require_once 'bootstrap.php';
/** @var \Zeedhi\Framework\Application $application */
$application = $instanceManager->getService('application');
$application->run();