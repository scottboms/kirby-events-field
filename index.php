<?php

/*
 * A custom Event field type for Kirby
 * @link https://github.com/scottboms/kirby-events-field
 */

use Kirby\Cms\App;
use Kirby\Content\Field;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Obj;

// kirby version compatibility check
$version = App::version() ?? '0.0.0';

if (version_compare($version, '6.0.0-alpha.1', '<') || version_compare($version, '8.0.0', '>=')) {
  throw new Exception('SVG Tag requires Kirby v6');
}

Kirby::plugin('scottboms/events-field', [
	'fields' => [
		'event' => [
			'props' => [
				'value' => function ($value = null) {
					$event = Yaml::decode($value) ?? [];

					// ensure we always have an array
					if (is_object($event)) {
						$event = (array)$event;
					}

					$defaults = [
						'eventName'  => null,
						'startDate'  => null,
						'endDate'    => null,
						'timeStart'  => null,
						'timeEnd'    => null,
						'city'       => null,
						'state'      => null,
						'country'    => null,
						'venue'      => null,
						'url'        => null,
						'details'    => null,
					];

					$event = array_merge($defaults, is_array($event) ? $event : []);

					$normalizeDate = function ($v): ?string {
						if ($v === null || $v === '') {
							return null;
						}

						// if yaml parsed it as a DateTime object
						if ($v instanceof \DateTimeInterface) {
							return $v->format('Y-m-d H:i:s');
						}

						// if it already became a unix timestamp
						if (is_int($v) || (is_string($v) && ctype_digit($v))) {
							return date('Y-m-d H:i:s', (int)$v);
						}

						// if it is a string like "YYYY-MM-DD HH:MM:SS"
						if (is_string($v)) {
							return trim($v);
						}

						return null;
					};

					$event['startDate'] = $normalizeDate($event['startDate']);
					$event['endDate']   = $normalizeDate($event['endDate']);
					return $event;

				},
				'preview' => function ($value = []) {
					return is_array($value) ? $value : [];
				},

				// edit-drawer toggles (independent of preview)
				'eventName'  => fn ($value = true)  => (bool)$value,
				'endDate'    => fn ($value = true)  => (bool)$value,
				'timeStart'  => fn ($value = true)  => (bool)$value,
				'timeEnd'    => fn ($value = true)  => (bool)$value,
				'venue'      => fn ($value = true)  => (bool)$value,
				'url'        => fn ($value = true)  => (bool)$value,
				'details'    => fn ($value = true)  => (bool)$value,
				'empty'      => fn ($value = false) => (bool)$value,
			]
		]
	],

	'fieldMethods' => require __DIR__ . '/lib/fieldMethods.php',

	'info' => [
		'version'  => '6.0.0',
		'homepage' => 'https://github.com/scottboms/kirby-events-field',
		'license'  => 'MIT',
		'authors'  => [[ 'name' => 'Scott Boms', 'url' => 'https://scottboms.com' ]],
	]
]);
