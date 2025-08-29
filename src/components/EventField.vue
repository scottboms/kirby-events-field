<template>
	<k-field
    class="k-event-field"
		v-bind="$props"
	>
		<k-button slot="options" icon="edit" size="xs" variant="filled" :disabled="disabled" @click="openDrawer">Edit...</k-button>

		<!--<k-code>{{ value }}</k-code>-->

		<div class="k-event-field__summary">
			<table v-if="hasContent" style="table-layout: auto" class="k-table" @click="openDrawer">
				<tbody>
					<tr v-for="row in summaryRows" :key="row.key">
						<th data-mobile="true">{{ row.label }}</th>
						<td data-mobile="true">
							<div class="clip">
								<template v-if="row.key === 'url' && row.value">
								<a :href="row.value" target="_blank" rel="noopener noreferrer">{{ row.value }}</a>
								</template>
								<template v-else-if="row.key === 'details' && row.value">
								<div class="k-prose" v-html="row.value"></div>
								</template>
								<template v-else>
								{{ row.value || '—' }}
								</template>
							</div>
						</td>
					</tr>
				</tbody>
			</table>

			<k-empty v-else icon="calendar">{{ emptyComputed }}</k-empty>
		</div>
	</k-field>
</template>

<script>
export default {
	extends: 'k-field',
	props: {
		value: { type: [String, Object, null], default: null },

		// labels
		empty: { type: String, default: 'No event added yet' },
		showEmpty: { type: Boolean, default: false },

		// field visibility controls (configurable per blueprint)
		eventName: { type: Boolean, default: true },
		endDate: { type: Boolean, default: true },
		hoursStart: { type: Boolean, default: false },
		hoursEnd: { type: Boolean, default: false },
		city: { type: Boolean, default: true },
		state: { type: Boolean, default: true },
		country: { type: Boolean, default: true },
		venue: { type: Boolean, default: true },
		url: { type: Boolean, default: true },
		details: { type: Boolean, default: true },

		// labels for sub-fields
		labels: {
			type: Object,
			default: () => ({
				eventName: 'Name',
				startDate: 'Start Date',
				endDate: 'End Date',
				hoursStart: 'From',
				hoursEnd: 'Until',
				eventDates: 'Dates',
				eventHours: 'Hours',
				city: 'City',
				state: 'State/Region',
				country: 'Country',
				location: 'Location',
				venue: 'Venue',
				url: 'URL',
				details: 'Details',
			}),
		},

		// controls summary table visibility
		preview: {
			type: Array,
			default: () => []
		},

	},

	data() {
		return {
			drawer: false,
			saving: false,
			form: {
				eventName: '',
				startDate: '',
				endDate: '',
				hoursStart: '',
				hoursEnd: '',
				city: '',
				state: '',
				country: '',
				venue: '',
				url: '',
				details: '',
			},
		};
	},

	created() {
		// keep `form` synced with whatever Kirby passes in `value`
		this.$watch(
			() => this.value,
			(v) => {
				const base = {
					eventName: '',
					startDate: '',
					endDate: '',
					hoursStart: '',
					hoursEnd: '',
					city: '',
					state: '',
					country: '',
					venue: '',
					url: '',
					details: '',
				};
				this.form = (v && typeof v === 'object') ? { ...base, ...v } : { ...base };
			},
			{ immediate: true }
		);
	},

	computed: {
		emptyComputed() {
			return this.empty || 'No event';
		},

		hasContent() {
			const v = (this.value && typeof this.value === 'object') ? this.value : {};
			const fields = [
				this.eventName ? v.eventName : '',
				v.startDate,
				this.endDate ? v.endDate : '',
				this.hoursStart ? v.hoursStart : '',
				this.hoursEnd ? v.hoursEnd : '',
				this.city ? v.city : '',
				this.state ? v.state : '',
				this.country ? v.country : '',
				this.venue ? v.venue : '',
				this.url ? v.url : '',
				this.details ? v.details : '',
			];
			return fields.some(val => !!val && String(val).trim() !== '');
		},

		// handle preview keys
		allowedKeys() {
			return [
				'eventName', 'startDate','endDate','hoursStart','hoursEnd','eventDates',
				'eventHours','city','state','country','location','venue','url','details'];
		},

		// canonical order; optionals auto-removed by flags below
		summaryOrder() {
			if (Array.isArray(this.preview) && this.preview.length) {
				// take only recognized keys, keep given order
				return this.preview.filter(k => this.allowedKeys.includes(k));
			}
			// fallback (original behavior)
			return [
				...(this.eventName  ? ['eventName'] : []),
				'startDate',
				...(this.endDate    ? ['endDate'] : []),
				...(this.hoursStart ? ['hoursStart'] : []),
				...(this.hoursEnd   ? ['hoursEnd']   : []),
				...(this.city       ? ['city'] : []),
				...(this.state      ? ['state'] : []),
				...(this.country    ? ['country'] : []),
				...(this.venue      ? ['venue']   : []),
				...(this.url        ? ['url']     : []),
				...(this.details    ? ['details'] : []),
			];
		},

		summaryRows() {
			// prefer the prop from kirby
			const src = (this.value && typeof this.value === 'object') ? this.value : {};
			const rows = [];

			for (const key of this.summaryOrder) {
				const raw = src[key] ?? '';
				let val = '';

				if (key === 'eventDates') {
					const start = this.formatPrettyDate(src.startDate || '');
					const end   = this.formatPrettyDate(src.endDate   || '');
					val = start && end ? `${start} – ${end}` : (start || end || '');
				} else if (key === 'eventHours') {
					const left  = this.formatTime(src.hoursStart || '');
					const right = this.formatTime(src.hoursEnd   || '');
					val = left && right ? `${left} – ${right}` : (left || right || '');
				} else if (key === 'location') {
					val = this.formatLocation(src.city, src.state, src.country);
				} else if (key === 'startDate' || key === 'endDate') {
					val = this.formatPrettyDate(raw);
				} else if (key === 'hoursStart' || key === 'hoursEnd') {
					val = this.formatTime(raw);
				} else {
					val = raw;
				}

				const isEmpty = !val || String(val).trim() === '';
				if (!this.showEmpty && isEmpty) continue;

				rows.push({
					key,
					label: this.labels[key] || key,
					value: val,
				});
	    }
			return rows;
		},

	},

	methods: {
		openDrawer() {
			const list = [];

			// helper: map number of visible fields to kirby grid widths
			const span = (n) => (n <= 1 ? '1/1' : n === 2 ? '1/2' : n === 3 ? '1/3' : '1/4');

			// how many 'date/hour' fields are enabled?
			const slots =
				1 + // startDate is always present
				(this.endDate ? 1 : 0) +
				(this.hoursStart ? 1 : 0) +
				(this.hoursEnd ? 1 : 0);

			const dateHourWidth = span(slots);

			// eventName
			if (this.eventName) {
				list.push(['eventName', {
					type: 'text',
					label: this.labels.eventName,
					width: '1/1',
				}]);
			}

			// dates
			list.push(['startDate', {
				type: 'date',
				label: this.endDate ? this.labels.startDate : 'Date',
				display: 'YYYY-MM-DD',
				time: false,
				required: true,
				width: dateHourWidth,
			}]);

			if (this.endDate) {
				list.push(['endDate', {
					type: 'date',
					label: this.labels.endDate,
					display: 'YYYY-MM-DD',
					time: false,
					width: dateHourWidth,
				}]);
			}

			// hours (if separate from start/end times)
			if (this.hoursStart) {
				list.push(['hoursStart', {
					type: 'time',
					label: this.labels.hoursStart,
					display: 'hh:mm a',
					notation: 24,
					width: dateHourWidth,
					step: 15 // 15-min steps
				}]);
			}
			if (this.hoursEnd) {
				list.push(['hoursEnd', {
					type: 'time',
					label: this.labels.hoursEnd,
					display: 'hh:mm a',
					notation: 24,
					width: dateHourWidth,
					step: 15 // 15-min steps
				}]);
			}

			// location
			if (this.city) {
				list.push(['city', {
					type: 'text',
					label: this.labels.city,
					width: this.country ? '1/3' : '1/2', // half if no country
				}]);
			}
			if (this.state) {
				list.push(['state', {
					type: 'text',
					label: this.labels.state,
					width: this.country ? '1/3' : '1/2', // half if no country
				}]);
			}
			if (this.country) {
				list.push(['country', {
					type: 'text',
					label: this.labels.country,
					width: '1/3',
				}]);
			}
		  if (this.venue) {
				list.push(['venue', {
					type: 'text',
					label: this.labels.venue,
				}]);
			}

			// miscellaneous
			if (this.url) {
				list.push(['url', {
					type: 'url',
					label: this.labels.url,
				}]);
			}
			if (this.details) {
				list.push(['details', {
					type: 'textarea',
					label: this.labels.details,
					buttons: false,
					size: 'small',
				}]);
			}

			// convert to object with stable insertion order
			const fields = Object.fromEntries(list);

			this.$panel.drawer.open({
				component: 'k-form-drawer',
				props: {
					icon: 'calendar',
					title: 'Event Details',
					fields,
					value: { ...this.form },
				},
				on: {
					submit: this.handleSubmit.bind(this),
				},
			});
		},

		closeDrawer() {
			this.$panel.drawer.close();
		},

		handleSubmit(formData) {
			Promise.resolve()
			.then(() => {
				this.form = { ...this.form, ...(formData || {}) };
				this.$emit("input", this.form);
				this.$emit("change", this.form);
				this.closeDrawer();

				this.$panel.notification.success({
					message: 'Ok',
					timeout: 4000
				});
			})
			.catch(() => {
				this.$panel.notification.error({
					message: 'An error occurred',
					timeout: 4000
				});
			});
		},

		// reformat date value if time is not present
		// strips time value for preview only
		formatDate(v) {
			if (!v) return '';
			const s = String(v).trim();
			return s.split(/[ T]/)[0]; // trim time off
		},

		formatPrettyDate(v) {
			if (!v) return '';
			// expect "YYYY-MM-DD HH:MM:SS" or "YYYY-MM-DD"
			const s = String(v).trim();
			const [datePart] = s.split(' ');
			const m = datePart.match(/^(\d{4})-(\d{2})-(\d{2})$/);
			if (!m) return datePart || s;

			const y = parseInt(m[1], 10);
			const mo = parseInt(m[2], 10);
			const d = parseInt(m[3], 10);

			// use utc so we don't get off-by-one from local time zones
			const dt = new Date(Date.UTC(y, mo - 1, d));

			// short month, numeric day, 4-digit year
			const fmt = new Intl.DateTimeFormat(undefined, {
				timeZone: 'UTC',
				month: 'short',
				day: '2-digit', // numeric, 2-digit
				year: 'numeric',
			});
			return fmt.format(dt); // e.g. "Aug 1, 2025"
		},

		formatTime(v) {
			if (!v) return '';
			const s = String(v).trim();
			const m = s.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
			if (!m) return s; // unknown format -> show as-is
			let h = parseInt(m[1], 10);
			const min = m[2];
			const mer = h >= 12 ? 'pm' : 'am';
			h = h % 12;
			if (h === 0) h = 12;
			return `${h}:${min} ${mer}`; // e.g. 9:00 am
		},

		formatLocation(city, state, country) {
			const parts = [city, state, country]
				.map(v => (v == null ? '' : String(v).trim()))
				.filter(Boolean);
			return parts.join(', ');
		},

	}
};
</script>

<style>
.k-event-field__summary table {
  table-layout: fixed;
  width: 100%;
}

.k-event-field__summary .k-table th {
	/* don't wrap labels and
	 * force to minimum intrinsic size
	 */
	white-space: nowrap;
	width: 1%!important;
}

.k-event-field__summary td {
	/* allow the field to shrink */
	max-width: 0;
	width: 99%;
}

.k-event-field__summary .clip {
	display: block;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.k-event-field__summary .clip a {
	display: inline-block;
	max-width: 100%;
	overflow: hidden;
	text-overflow: ellipsis;
	vertical-align: bottom;
}

.k-event-field__summary .k-prose {
	padding: .5rem 0;
}
</style>
