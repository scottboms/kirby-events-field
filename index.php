<?php

/*
 * A custom Event field type for Kirby
 * @link https://github.com/scottboms/kirby-events-field
 */

use Kirby\Cms\App;
use Kirby\Content\Field;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Obj;

if (
	version_compare(App::version() ?? '0.0.0', '4.0.1', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '6.0.0', '>=') === true
) {
	throw new Exception('Events Field requires Kirby v4 or v5');
}

Kirby::plugin('scottboms/events-field', [
	'fields' => [
		'event' => [
			'props' => [
				'value' => function ($value = null) {
					$event = Yaml::decode($value);
					return [
						'eventName'  => null,
						'startDate'  => null,
						'endDate'    => null,
						'hoursStart' => null,
						'hoursEnd'   => null,
						'city'       => null,
						'state'      => null,
						'country'    => null,
						'venue'      => null,
						'url'        => null,
						'details'    => null,
						...$event
					];
				},
				'preview' => function ($value = []) {
					return is_array($value) ? $value : [];
				},

				// edit-drawer toggles (independent of preview)
				'eventName'  => fn ($value = true)  => (bool)$value,
				'endDate'    => fn ($value = true)  => (bool)$value,
				'hoursStart' => fn ($value = true) => (bool)$value,
				'hoursEnd'   => fn ($value = true) => (bool)$value,
				'venue'      => fn ($value = true)  => (bool)$value,
				'url'        => fn ($value = true)  => (bool)$value,
				'details'    => fn ($value = true)  => (bool)$value,
				'empty'      => fn ($value = false) => (bool)$value,
			]
		]
	],

	'fieldMethods' => require __DIR__ . '/lib/fieldMethods.php',

	'info' => [
		'version'  => '1.1.0',
		'homepage' => 'https://github.com/scottboms/kirby-events-field',
		'license'  => 'MIT',
		'authors'  => [[ 'name' => 'Scott Boms', 'url' => 'https://scottboms.com' ]],
	]
]);
