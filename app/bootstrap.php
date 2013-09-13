<?php
/*
 * This file is part of Silex Cops. Licensed under WTFPL
 *
 * (c) Mathieu Duplouy <mathieu.duplouy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

ini_set('date.timezone', 'Europe/Paris');
define('BASE_DIR', __DIR__.'/../');
define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

// Define core model
// Load configuration & set service providers
$app['core'] =  new Cops\Model\Core($app, __DIR__.'/cops/config.ini');

$app['image_gd'] = new Cops\Model\ImageProcessor\Gd();
$app['image_imagick'] = new Cops\Model\ImageProcessor\Imagick();

// Set the mount points for the controllers
$app->mount('/', new Cops\Controller\IndexController());
$app->mount('book/', new Cops\Controller\BookController());

// Register url generator service
$app->register(new Cops\Provider\UrlGeneratorServiceProvider());

return $app;