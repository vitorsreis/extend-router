<?php
/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

$setting = [
    // Total of class instantiations
    'num_instantiations' => 1,

    // Total of others iterations
    'num_iterations' => 100,

    // Total routes added to test
    'num_routes' => 1000,

    // Total variables added to routes
    'num_variables' => 10,

    // Libraries to compare
    'library' => [
        'd5whub/extend-benchmark' => true,
        'nikic/fast-route' => true,
        'symfony/routing' => true,
//        'symfony/routing:optimized' => true,
        'aura/router' => true,
        'corneltek/pux' => true,
        'klein/klein' => true
    ],

    // Tests status
    'tests' => [
//        'instantiation' => true,
//        'first' => true,
//        'last' => true,
//        'not-found' => true,
//        'first-not-allowed' => true,
//        'last-not-allowed' => true,
        'rand' => true,
//        'show_end_results' => true,
    ],

    // Use one instance for all tests except instantiation tests, *not recommended for more real testing*
    'one_instance' => false,
];

// Same routes/url for all instances by argument types
$setting['arguments'] = require __DIR__ . '/Setup/arguments.php';

require_once __DIR__ . '/vendor/autoload.php';

$agent = new D5WHUB\Extend\Benchmark\Benchmark(
    'Router libraries benchmark',
    sprintf(
        'The purpose of this benchmark is to compare various php router libraries in different scenarios with %s '
        . 'route%s with %s variable%s. There will be %s instantiation%s and %s interaction%s in the other scenarios.',
        $setting['num_routes'],
        $setting['num_routes'] > 1 ? 's' : '',
        $setting['num_variables'],
        $setting['num_variables'] > 1 ? 's' : '',
        $setting['num_instantiations'],
        $setting['num_instantiations'] > 1 ? 's' : '',
        $setting['num_iterations'],
        $setting['num_iterations'] > 1 ? 's' : ''
    )
);

!empty($setting['tests']['instantiation']) && $benchmark['instantiation'] = $agent->createBenchmark(
    "Create instances",
    sprintf(
        "Benchmark of creating instances of libraries adding %s route%s with %s variable%s",
        $setting['num_routes'],
        $setting['num_routes'] > 1 ? 's' : '',
        $setting['num_variables'],
        $setting['num_variables'] > 1 ? 's' : '',
    ),
    $setting['num_instantiations']
);

!empty($setting['tests']['first']) && $benchmark['first'] = $agent->createBenchmark(
    "Matching first route",
    "Benchmark of matching with first added route"
);

!empty($setting['tests']['last']) && $benchmark['last'] = $agent->createBenchmark(
    "Matching last route",
    "Benchmark of matching with last added route"
);

!empty($setting['tests']['not-found']) && $benchmark['not-found'] = $agent->createBenchmark(
    "Matching not found route",
    "Benchmark of matching with not found route"
);

!empty($setting['tests']['first-not-allowed']) && $benchmark['first-not-allowed'] = $agent->createBenchmark(
    "Matching first route with method not allowed",
    "Benchmark of matching with first added route and method not allowed"
);

!empty($setting['tests']['last-not-allowed']) && $benchmark['last-not-allowed'] = $agent->createBenchmark(
    "Matching last route with method not allowed",
    "Benchmark of matching with last added route and method not allowed"
);

!empty($setting['tests']['rand']) && $benchmark['rand'] = $agent->createBenchmark(
    "Matching random routes",
    "Benchmark of matching with random added route"
);

if (!empty($setting['library']['d5whub/extend-benchmark'])) {
    require_once __DIR__ . "/Setup/me.php";
}

if (!empty($setting['library']['nikic/fast-route'])) {
    require_once __DIR__ . "/Setup/fastroute.php";
}

if (!empty($setting['library']['corneltek/pux'])) {
    require_once __DIR__ . "/Setup/pux.php";
}

if (!empty($setting['library']['aura/router'])) {
    require_once __DIR__ . "/Setup/aura.php";
}

if (!empty($setting['library']['klein/klein'])) {
    require_once __DIR__ . "/Setup/klein.php";
}

if (!empty($setting['library']['symfony/routing'])) {
    require_once __DIR__ . "/Setup/symfony.php";
}

if (!empty($setting['library']['symfony/routing:optimized'])) {
    require_once __DIR__ . "/Setup/symfony-optimized.php";
}

$agent->execute($setting['num_iterations'], empty($setting['tests']['show_end_results']));
