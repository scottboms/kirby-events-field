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
							<template v-if="row.key === 'url' && row.value">
							<a :href="row.value" target="_blank" rel="noopener noreferrer">{{ row.value }}</a>
							</template>
							<template v-else-if="row.key === 'details' && row.value">
							<div class="k-prose" v-html="row.value"></div>
							</template>
							<template v-else>
							{{ row.value || '—' }}
							</template>
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
		drawerTitle: { type: String, default: 'Event Details' },

		// field visibility controls (configurable per blueprint)
		venue: { type: Boolean, default: true },
		url: { type: Boolean, default: true },
		details: { type: Boolean, default: true },

		// control whether the time picker is shown on k-date-field
		time: { type: Boolean, default: true },

		// optional custom labels for sub-fields
		labels: {
			type: Object,
			default: () => ({
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
				v.startDate,
				v.endDate,
				v.city,
				v.state,
				v.country,
				this.venue ? v.venue : "",
				this.url ? v.url : "",
				this.details ? v.details : "",
			];
			return fields.some(val => !!val && String(val).trim() !== "");
		},

		// canonical order; optionals auto-removed by flags below
		summaryOrder() {
			if (Array.isArray(this.preview) && this.preview.length) {
				// take only recognized keys, keep given order
				return this.preview.filter(k => this.allowedKeys.includes(k));
			}
			// fallback (original behavior)
			return [
				'startDate',
				'endDate',
				'city',
				'state',
				'country',
				...(this.venue   ? ['venue']   : []),
				...(this.url     ? ['url']     : []),
				...(this.details ? ['details'] : []),
			];
		},

		summaryRows() {
			// prefer the prop kirby gives us
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
				const row = {
					key,
					label: this.labels[key] || key,
					value: val,
				};
				rows.push(row);
	    }
			return rows;
		},

	},

	methods: {
		openDrawer() {
			const fields = {
				// dates
				startDate: {
					type: 'date',
					label: this.labels.startDate,
					time: this.time,
					width: '1/2'
				},
				endDate: {
					type: 'date',
					label: this.labels.endDate,
					time: this.time,
					width: '1/2'
				},

				// location
				city: {
					type: 'text',
					label: this.labels.city,
					width: '1/2',
				},
				state: {
		      type: 'text',
		      label: this.labels.state,
		      width: '1/2',
		    },
				country: {
					type: 'text',
					label: this.labels.country,
				},
			};

			// optional fields
			// checks if true in blueprint (e.g. venue: true)
			if (this.venue) {
				fields.venue = {
					type: 'text',
					label: this.labels.venue,
				};
			}
			if (this.url) {
				fields.url = {
					type: 'url',
					label: this.labels.url,
				};
			}
			if (this.details) {
				fields.details = {
					type: 'textarea',
					label: this.labels.details,
					buttons: false,
					size: 'medium'
				};
			}

			this.$panel.drawer.open({
				component: 'k-form-drawer',
				props: {
					icon: 'calendar',
					title: this.drawerTitle,
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

		// handle reformating date value if time is not present
		// strip time value for preview only
		formatDate(v) {
			if (!v) return '';
			const s = String(v);
			return this.time ? s : s.split(/[ T]/)[0]; // drop time if disabled
		},

	}
};
</script>

<style>
.k-event-field__summary .k-prose {
	padding: .5rem 0;
}
</style>
