<?php

/*
 * A custom Event field type for Kirby
 * @link https://github.com/scottboms/kirby-events
 */

use Kirby\Cms\App;

if (
	version_compare(App::version() ?? '0.0.0', '4.0.1', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '6.0.0', '>=') === true
) {
	throw new Exception('Events Field requires Kirby v4 or v5');
}

Kirby::plugin('scottboms/events', [
	'fields' => [
		'event' => [
			'props' => [
				'value' => function ($value = null) {
					$event = Yaml::decode($value);
					return [
						'startDate' => null,
						'endDate' => null,
						'city' => null,
						'state' => null,
						'country' => null,
						'venue' => null,
						'url' => null,
						'details' => null,
						...$event
					];
				}
			]
		]
	],

	'info' => [
		'version'  => '1.0.0',
		'homepage' => 'https://github.com/scottboms/kirby-events',
		'license'  => 'MIT',
		'authors'  => [[ 'name' => 'Scott Boms', 'url' => 'https://scottboms.com' ]],
	]
]);
