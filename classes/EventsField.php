<?php

namespace Scottboms\Events;

use Kirby\Cms\Field;
use Kirby\Toolkit\Arr;
use Kirby\Toolkit\Date;

class EventField extends Field
{
	/**
	 * rhe default field structure
	* @var array
	*/
	public function default(): array
	{
		return [
			'name'      => null,
			'startDate' => null,
			'endDate'   => null,
			'city'      => null,
			'state'     => null,
			'country'   => null,
			'venue'     => null,
			'url'       => null,
			'details'   => null,
		];
	}


	/**
	* return the field type name
	* @return string
	*/
	public function type(): string
	{
		return 'event';
	}


	/**
	 * Field validation – e.g. end should be after start
	 */
	public function validate(): array
	{
		$errors = [];
		if ($this->startDate()->isEmpty() || $this->endDate()->isEmpty()) {
			return $errors; // date fields already validate themselves
		}
		$startDate = Date::from($this->startDate()->value());
		$endDate   = Date::from($this->endDate()->value());

		if ($endDate < $startDate) {
			$errors['end'] = 'The end date/time must be after the start date/time.';
		}
		return $errors;
	}


}
