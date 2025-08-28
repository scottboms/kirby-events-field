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

Kirby::plugin('scottboms/events', [
	'fields' => [
		'event' => [
			'props' => [
				'value' => function ($value = null) {
					$event = Yaml::decode($value);
					return [
						'eventName' => null,
						'startDate' => null,
						'endDate'   => null,
						'city'      => null,
						'state'     => null,
						'country'   => null,
						'venue'     => null,
						'url'       => null,
						'details'   => null,
						...$event
					];
				},
				'preview' => function ($value = []) {
					return is_array($value) ? $value : [];
				},

				// edit-drawer toggles (independent of preview)
				'eventName' => fn ($value = true)  => (bool)$value,
				'endDate'   => fn ($value = true)  => (bool)$value,
				'venue'     => fn ($value = true)  => (bool)$value,
				'url'       => fn ($value = true)  => (bool)$value,
				'details'   => fn ($value = true)  => (bool)$value,
				'time'      => fn ($value = true)  => (bool)$value,
				'empty'     => fn ($value = false) => (bool)$value,
			]
		]
	],

	'fieldMethods' => [
		/**
		 * $page->event()->toEvent()
		 * returns an obj with field instances: startDate(), endDate(), city(), etc.
		 */
		'toEvent' => function (Field $field) {
			$raw = $field->value();
			$data = is_array($raw) ? $raw : (is_string($raw) ? (Yaml::decode($raw) ?? []) : []);

			$defaults = [
				'eventName' => null,
				'startDate' => null,
				'endDate'   => null,
				'city'      => null,
				'state'     => null,
				'country'   => null,
				'venue'     => null,
				'url'       => null,
				'details'   => null,
			];
			$data = array_merge($defaults, $data);

			foreach ($data as $k => $v) {
				if ($v === '' || $v === []) {
					$data[$k] = null;
				}
			}

			// wrap everything as field instances so you can call ->toDate(), ->isNotEmpty(), etc.
			$parent = $field->parent() ?? site();
			$wrapped = [];
			foreach ($data as $key => $value) {
				$wrapped[$key] = new Field($parent, $key, $value);
			}
			//return as obj, so $event->startDate() returns a field
			return new Obj($wrapped);
		}
	],

	'info' => [
		'version'  => '1.0.0',
		'homepage' => 'https://github.com/scottboms/kirby-events-field',
		'license'  => 'MIT',
		'authors'  => [[ 'name' => 'Scott Boms', 'url' => 'https://scottboms.com' ]],
	]
]);
