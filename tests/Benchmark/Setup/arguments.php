<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

/** @var array $setting */

// Config prefix, suffix
$result = [
    ':arg' => [':', ''],
    '{arg}' => ['{', '}'],
    '[*:arg]' => ['[*:', ']'],
];

// Initialize
$variables = $setting['num_variables'] ? array_map(static fn($i) => "arg$i", range(1, $setting['num_variables'])) : [];
$url_variables = implode('/', $variables);

foreach ($result as $key => &$data) {
    $data = [
        'prefix' => $data[0],
        'suffix' => $data[1],
        'variables' => implode('/', array_map(static fn($i) => "$data[0]$i$data[1]", $variables)),
        'routes' => [],
        'match' => [
            'first' => null,
            'last' => null,
            'rand' => []
        ]
    ];
}

$urls = [];
for ($i = 0; $i < $setting['num_routes']; $i++) {
    $url_prefix = md5(uniqid());
    $url_suffix = md5(uniqid());
    $urls[] = $url = "/" . implode("/", array_filter([$url_prefix, $url_variables, $url_suffix]));

    foreach ($result as $key => &$data) {
        $data['routes'][] = [
            "/" . implode("/", array_filter([$url_prefix, $data['variables'], $url_suffix])),
            $url
        ];
    }
}

foreach ($result as $key => &$data) {
    $data['match']['first'] = current($urls);
    $data['match']['last'] = end($urls);
}

do {
    shuffle($urls);
    foreach ($result as $key => &$data) {
        $data['match']['rand'] = [...$data['match']['rand'] ?? [], ...$urls];
    }
} while (count($data['match']['rand']) < $setting['num_random']);
$data['match']['rand'] = array_slice($data['match']['rand'], 0, $setting['num_random']);

return $result;
