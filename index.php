<?php

/*
 * A custom Event field type for Kirby
 * @link https://github.com/scottboms/kirby-events-field
 */

use Kirby\Cms\App;
use Scottboms\Events\EventField;

load([
	'Scottboms\Events\EventField' => __DIR__ . '/fields/EventField.php',
]);

$version = App::version() ?? '0.0.0';

if (
	version_compare($version, '6.0.0-beta.1', '<') ||
	version_compare($version, '7.0.0', '>=')
) {
	throw new Exception('Events Field requires Kirby 6');
}

App::plugin('scottboms/events-field',
	info: [
		'homepage' => 'https://github.com/scottboms/kirby-events-field',
		'license'  => 'MIT',
		'version'  => '6.0.0',
		'authors'  => [['name' => 'Scott Boms']],
	],
	extends: [
		'fields' => [
			'event' => EventField::class,
		],
		'fieldMethods' => require __DIR__ . '/lib/fieldMethods.php',
	]
);
