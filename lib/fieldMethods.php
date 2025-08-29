<?php

use Kirby\Content\Field;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Obj;

return [
	/**
	 * $page->event()->toEvent()
	 * returns an obj with field instances: startDate(), endDate(), city(), etc.
	 */
	'toEvent' => function (Field $field) {
		$raw = $field->value();
		$data = is_array($raw) ? $raw : (is_string($raw) ? (Yaml::decode($raw) ?? []) : []);

		$defaults = [
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
	},

	/**
	 * $page->event()->daysUntil()
	 * compares current date to field startDate
	 * @return Integer | null
	 */
	'daysUntil' => function(Field $field) {
		$event = $field->toEvent();

		$startValue = $event->startDate()->value();
		if (empty($startValue)) {
			return null; // nothing to compare
		}

		try {
			$now   = new DateTime('today');
			$start = new DateTime($startValue);
		} catch (Exception $e) {
			return null; // invalid date string
		}

		// calculate difference
		$interval = $now->diff($start);
		// if startDate is in the past, diff->days is still positive,
		// so check direction with ->invert
		$days = (int)$interval->days;
		return $interval->invert ? -$days : $days;
	},

];
