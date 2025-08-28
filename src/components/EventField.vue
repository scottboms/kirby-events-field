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
		venue: { type: Boolean, default: true },
		url: { type: Boolean, default: true },
		details: { type: Boolean, default: true },

		// control whether the time picker is shown on k-date-field
		time: { type: Boolean, default: true },

		// labels for sub-fields
		labels: {
			type: Object,
			default: () => ({
				eventName: 'Name',
				startDate: 'Start Date',
				endDate: 'End Date',
				city: 'City',
				state: 'State/Region',
				country: 'Country',
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
					eventName: "",
					startDate: "",
					endDate: "",
					city: "",
					state: "",
					country: "",
					venue: "",
					url: "",
					details: "",
				};
				this.form = (v && typeof v === "object") ? { ...base, ...v } : { ...base };
			},
			{ immediate: true }
		);
	},

	computed: {
		emptyComputed() {
			return this.empty || 'No event';
		},

		hasContent() {
			const v = (this.value && typeof this.value === "object") ? this.value : {};
			const fields = [
				this.eventName ? v.eventName : "",
				v.startDate,
				this.endDate ? v.endDate : "",
				v.city,
				v.state,
				v.country,
				this.venue ? v.venue : "",
				this.url ? v.url : "",
				this.details ? v.details : "",
			];
			return fields.some(val => !!val && String(val).trim() !== "");
		},

		// handle preview keys
		allowedKeys() {
			return ['eventName', 'startDate','endDate','city','state','country','venue','url','details'];
		},

		// canonical order; optionals auto-removed by flags below
		summaryOrder() {
			if (Array.isArray(this.preview) && this.preview.length) {
				// take only recognized keys, keep given order
				return this.preview.filter(k => this.allowedKeys.includes(k));
			}
			// fallback (original behavior)
			return [
				...(this.eventName ? ['eventName'] : []),
				'startDate',
				...(this.endDate ? ['endDate'] : []),
				'city',
				'state',
				'country',
				...(this.venue   ? ['venue']   : []),
				...(this.url     ? ['url']     : []),
				...(this.details ? ['details'] : []),
			];
		},

		summaryRows() {
			// prefer the prop from kirby
			const src = (this.value && typeof this.value === 'object') ? this.value : {};
			const rows = [];

			for (const key of this.summaryOrder) {
				let val = src[key] ?? '';

				// per-key formatting
				if (key === 'startDate' || key === 'endDate') {
					val = this.formatDate(val);
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
				label: this.labels.startDate,
				display: 'YYYY-MM-DD',
				time: this.time,
				width: this.endDate ? '1/2' : '1/1', // full width if no endDate
			}]);

			if (this.endDate) {
				list.push(['endDate', {
					type: 'date',
					label: this.labels.endDate,
					display: 'YYYY-MM-DD',
					time: this.time,
					width: '1/2'
				}]);
			}

			// location
			list.push(['city', {
				type: 'text',
				label: this.labels.city,
				width: '1/3',
			}]);
			list.push(['state', {
				type: 'text',
				label: this.labels.state,
				width: '1/3',
			}]);
			list.push(['country', {
				type: 'text',
				label: this.labels.country,
				width: '1/3',
			}]);

			// configurable fields
		  if (this.venue) {
				list.push(['venue', {
					type: 'text',
					label: this.labels.venue,
				}]);
			}
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
					size: 'medium',
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
			const s = String(v);
			return this.time ? s : s.split(/[ T]/)[0]; // drop time if disabled
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
