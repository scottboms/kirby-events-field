<?php

namespace Scottboms\Events;

use Kirby\Data\Yaml;
use Kirby\Form\Field\InputField;
use Kirby\Reflection\Attributes\Prop;

class EventField extends InputField
{
	#[Prop]
	protected array $preview = [];

	#[Prop]
	protected bool $eventName = true;

	#[Prop]
	protected bool $endDate = true;

	#[Prop]
	protected bool $timeStart = false;

	#[Prop]
	protected bool $timeEnd = false;

	#[Prop]
	protected bool $city = true;

	#[Prop]
	protected bool $state = true;

	#[Prop]
	protected bool $country = true;

	#[Prop]
	protected bool $venue = true;

	#[Prop]
	protected bool $url = true;

	#[Prop]
	protected bool $details = true;

	#[Prop]
	protected string $empty = 'No event added yet';

	#[Prop]
	protected bool $showEmpty = false;

	protected mixed $value = null;

	protected const EVENT_DEFAULTS = [
		'eventName' => null,
		'startDate' => null,
		'endDate'   => null,
		'timeStart' => null,
		'timeEnd'   => null,
		'city'      => null,
		'state'     => null,
		'country'   => null,
		'venue'     => null,
		'url'       => null,
		'details'   => null,
	];

	public function toFormValue(): mixed
	{
		$value = parent::toFormValue();

		if (is_string($value)) {
			$value = Yaml::decode($value);
		}

		return array_replace(
			self::EVENT_DEFAULTS,
			is_array($value) ? $value : []
		);
	}

	public function toStoredValue(): mixed
	{
		$value = $this->toFormValue();
		return Yaml::encode($value);
	}

	public function props(): array
	{
		return array_merge(parent::props(), [
			'eventName' => $this->eventName,
			'endDate'   => $this->endDate,
			'timeStart' => $this->timeStart,
			'timeEnd'   => $this->timeEnd,
			'city'      => $this->city,
			'state'     => $this->state,
			'country'   => $this->country,
			'venue'     => $this->venue,
			'url'       => $this->url,
			'details'   => $this->details,
			'empty'     => $this->empty,
			'showEmpty' => $this->showEmpty,
			'preview'   => $this->preview,
		]);
	}
}
