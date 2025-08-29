# Events Field plugin for Kirby

![Plugin Preview](src/assets/events-field-plugin.jpg)

Adds a customizable Event Field Type for Kirby.

## Installation

### [Kirby CLI](https://github.com/getkirby/cli)
    
```bash
kirby plugin:install scottboms/kirby-events-field
```

### Git submodule

```bash
git submodule add https://github.com/scottboms/kirby-events-field.git site/plugins/kirby-events
```

### Copy and Paste

1. [Download](https://github.com/scottboms/kirby-events-field/archive/master.zip) the contents of this repository as Zip file.
2. Rename the extracted folder to `kirby-events` and copy it into the `site/plugins/` directory in your Kirby project.


## Usage

### Blueprint Configuration

In a Page blueprint, add a new field with the type `event.` Standard field attributes such as `label, required, help`, etc. can also be used to override defaults. Use `empty` to change the text displayed when the field is in an empty state.

### Field Properties

| Name       | Type    | Default | Description                                                     |
|------------|---------|---------|-----------------------------------------------------------------|
| empty      | string  | `null`  | The placeholder text if no information has been added           |
| preview    | array   | `[ ]`   | Optional array of field names to display in the preview         |
| eventName  | boolean | `true`  | If `true`, the field is available in the form                   |
| endDate    | boolean | `true`  | If `true`, the field is available in the form                   |
| timeStart  | boolean | `true`  | If `true`, the field is available in the form                   |
| timeEnd    | boolean | `true`  | If `true`, the field is available in the form                   |
| city       | boolean | `true`  | If `true`, the field is available in the form                   |
| state      | boolean | `true`  | If `true`, the field is available in the form                   |
| country    | boolean | `true`  | If `true`, the field is available in the form                   |
| venue      | boolean | `true`  | If `true`, the field is available in the form                   |
| url        | boolean | `true`  | If `true`, the field is available in the form                   |
| details    | boolean | `true`  | If `true`, the field is available in the form                   |



```yml
  event:
    # standard properties from Kirby
    label: Example Event
    required: false
    width: 1/3

    # field properties
    type: event
    empty: 'Add an event'

    # optional field properties
    eventName: true
    endDate: true
    timeStart: true
    timeEnd: true
    venue: true
    city: true
    state: true
    country: true
    url: true
    details: true

    # preview table display
		# list of available field names
    preview:
      - eventName
      - startDate
      - endDate
      - timeStart
      - timeEnd
      - eventDates # summary of startDate, endDate
      - eventTime # summary of timeStart, timeEnd
      - city
      - state
      - country
      - location # summary of city, state, country
      - venue
      - url
      - details
```

### Templates and Snippets

To access an event field in your templates, you can use the toEvent() method.


```php
<?php if ($event = $page->event()->toEvent()): ?>
<h1><a href="<?= $event->url() ?>"><?= $event->eventName() ?></a></h1>

<div class="dates">
	<span class="start"><?= $event->startDate()->toDate('M d, Y') ?></span> –
	<span class="end"><?= $event->endDate()->toDate('M d, Y') ?></span>, daily from
	<span class="start"><?= $event->timeStart()->toDate('g:i a') ?></span> – 
	<span class="end"><?= $event->timeEnd()->toDate('g:i a') ?></span>
</div>

<div class="location">
	<div class="venue"><?= $event->venue() ?></div>
	<span class="city"><?= $event->city() ?></span>, 
	<span class="state"><?= $event->state() ?></span> 
	<span class="country"><?= $event->country() ?></span>
</div>

<div class="details"><?= $event->details() ?></div>
<?php endif ?>
```

### Field Methods

Special Field Methods have been included to provide additional utility when utilizing the field. These include a `toEvent()` wrapper which behaves similarly to Kirby's native toStructure() method. Additionally, a `daysUntil()` method allows you to provide a simple countdown based on the field's `startDate` value.

```php
<?php if ($days = $page->sxsw()->daysUntil()): ?>
  <?php if ($days > 0): ?>
    <p>Event starts in <?= $days ?> days</p>
  <?php elseif ($days === 0): ?>
    <p>Event starts today!</p>
  <?php else: ?>
    <p>Started <?= abs($days) ?> days ago</p>
  <?php endif ?>
<?php endif ?>
```


## Compatibility

* Kirby 4.x
* Kirby 5.x


## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test before using it in a production environment. If you identify an issue, typo, etc, please [create a new issue](/issues/new) so I can investigate.


## License

[MIT](https://opensource.org/licenses/MIT)
