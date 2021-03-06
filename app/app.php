<?php

$app = new \Silex\Application();
$app->register(new \Silex\Provider\ServiceControllerServiceProvider());
$app->register(new \Silex\Provider\AssetServiceProvider());
$app->register(new \Silex\Provider\HttpFragmentServiceProvider());

$app->register(new SilexGuzzle\GuzzleServiceProvider(),array(
    'guzzle.timeout' => 100.14,
    'guzzle.request_options' =>
        ['auth' => ['admin', 'admin']]
));

$app->register(new \Silex\Provider\TwigServiceProvider(),
    array(
        'twig.path' => __DIR__ . '/../views',
        'twig.options' => array(
            'cache' => __DIR__ . '/../var/cache/twig',
            'strict_variables' => true,
        ),
    )
);

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...
    return $twig;
});

$app->register(new \Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => __DIR__.'/../var/cache/http/'
));

$app->register(new \Silex\Provider\RoutingServiceProvider());

require __DIR__ . '/../app/service.php';
require __DIR__ . '/../app/middlewares.php';
require __DIR__ . '/../app/controllers.php';

return $app;