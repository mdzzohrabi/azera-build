<?php
namespace Azera\Build;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Constants.php';

$app = new Application();

$app->add( new Command\Build );
$app->add( new Command\Projects );
$app->setDefaultCommand( 'projects' );

$app->run();
?>