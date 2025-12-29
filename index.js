import { resolveComponent, createBlock, openBlock, mergeProps, withCtx, createVNode, createCommentVNode, createElementVNode, createTextVNode, createElementBlock, Fragment, renderList, toDisplayString } from "vue";
const _export_sfc = (sfc, props) => {
  const target = sfc.__vccOpts || sfc;
  for (const [key, val] of props) {
    target[key] = val;
  }
  return target;
};
const _sfc_main = {
  extends: "k-field",
  props: {
    value: { type: [String, Object, null], default: null },
    // labels
    empty: { type: String, default: "No event added yet" },
    showEmpty: { type: Boolean, default: false },
    // field visibility controls (configurable per blueprint)
    eventName: { type: Boolean, default: true },
    endDate: { type: Boolean, default: true },
    timeStart: { type: Boolean, default: false },
    timeEnd: { type: Boolean, default: false },
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
        eventName: "Name",
        date: "Date",
        startDate: "Start Date",
        endDate: "End Date",
        timeStart: "From",
        timeEnd: "Until",
        eventDates: "Dates",
        eventTime: "Time",
        city: "City",
        state: "State/Region",
        country: "Country",
        location: "Location",
        venue: "Venue",
        url: "URL",
        details: "Details"
      })
    },
    // controls summary table visibility
    preview: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      drawer: false,
      saving: false,
      form: {
        eventName: "",
        startDate: "",
        endDate: "",
        timeStart: "",
        timeEnd: "",
        city: "",
        state: "",
        country: "",
        venue: "",
        url: "",
        details: ""
      }
    };
  },
  created() {
    this.$watch(
      () => this.value,
      (v) => {
        const base = {
          eventName: "",
          startDate: "",
          endDate: "",
          timeStart: "",
          timeEnd: "",
          city: "",
          state: "",
          country: "",
          venue: "",
          url: "",
          details: ""
        };
        const parsed = typeof v === "string" && v.trim() ? this.$helper?.yaml?.decode(v) ?? {} : v;
        const next = parsed && typeof parsed === "object" ? { ...base, ...parsed } : { ...base };
        next.startDate = this.formatDate(next.startDate);
        next.endDate = this.formatDate(next.endDate);
        next.timeStart = next.timeStart || "";
        next.timeEnd = next.timeEnd || "";
        this.form = next;
      },
      { immediate: true }
    );
  },
  computed: {
    emptyComputed() {
      return this.empty || "No event";
    },
    hasContent() {
      const v = this.value && typeof this.value === "object" ? this.value : {};
      const fields = [
        this.eventName ? v.eventName : "",
        v.startDate,
        this.endDate ? v.endDate : "",
        this.timeStart ? v.timeStart : "",
        this.timeEnd ? v.timeEnd : "",
        this.city ? v.city : "",
        this.state ? v.state : "",
        this.country ? v.country : "",
        this.venue ? v.venue : "",
        this.url ? v.url : "",
        this.details ? v.details : ""
      ];
      return fields.some((val) => !!val && String(val).trim() !== "");
    },
    // handle preview keys
    allowedKeys() {
      return [
        "eventName",
        "startDate",
        "endDate",
        "timeStart",
        "timeEnd",
        "eventDates",
        "eventTime",
        "city",
        "state",
        "country",
        "location",
        "venue",
        "url",
        "details"
      ];
    },
    // canonical order; optionals auto-removed by flags below
    summaryOrder() {
      const allowed = this.allowedKeys;
      if (Array.isArray(this.preview) && this.preview.length) {
        let order = this.preview.filter((k) => allowed.includes(k));
        if (!this.endDate) {
          order = order.filter((k) => k !== "endDate");
        }
        const iStart = order.indexOf("startDate");
        const iEnd = order.indexOf("endDate");
        const hasEventDates = order.includes("eventDates");
        if (!hasEventDates && (iStart !== -1 || iEnd !== -1)) {
          const insertAt = Math.min(
            iStart !== -1 ? iStart : Infinity,
            iEnd !== -1 ? iEnd : Infinity
          );
          order = order.filter((k) => k !== "startDate" && k !== "endDate");
          order.splice(insertAt === Infinity ? order.length : insertAt, 0, "eventDates");
        }
        return order;
      }
      return [
        ...this.eventName ? ["eventName"] : [],
        "eventDates",
        ...this.timeStart ? ["timeStart"] : [],
        ...this.timeEnd ? ["timeEnd"] : [],
        ...this.city ? ["city"] : [],
        ...this.state ? ["state"] : [],
        ...this.country ? ["country"] : [],
        ...this.venue ? ["venue"] : [],
        ...this.url ? ["url"] : [],
        ...this.details ? ["details"] : []
      ];
    },
    summaryRows() {
      const src = this.value && typeof this.value === "object" ? this.value : {};
      const rows = [];
      for (const key of this.summaryOrder) {
        const raw = src[key] ?? "";
        let val = "";
        let label = this.labels[key] || key;
        if (key === "eventDates") {
          const start = this.formatPrettyDate(src.startDate || "");
          const end = this.endDate ? this.formatPrettyDate(src.endDate || "") : "";
          val = start && end ? `${start} – ${end}` : start || end || "";
          label = this.endDate ? this.labels.eventDates || "Dates" : this.labels.date || "Date";
        } else if (key === "eventTime") {
          const left = this.formatTime(src.timeStart || "");
          const right = this.timeEnd ? this.formatTime(src.timeEnd || "") : "";
          val = left && right ? `${left} – ${right}` : left || right || "";
        } else if (key === "location") {
          val = this.formatLocation(src.city, src.state, src.country);
        } else if (key === "startDate" || key === "endDate") {
          val = this.formatPrettyDate(raw);
        } else if (key === "timeStart" || key === "timeEnd") {
          val = this.formatTime(raw);
        } else {
          val = raw;
        }
        const isEmpty = !val || String(val).trim() === "";
        if (!this.showEmpty && isEmpty) continue;
        rows.push({
          key,
          label,
          value: val
        });
      }
      return rows;
    }
  },
  methods: {
    openDrawer() {
      const list = [];
      const span = (n) => n <= 1 ? "1/1" : n === 2 ? "1/2" : n === 3 ? "1/3" : "1/4";
      const slots = 1 + // startDate is always present
      (this.endDate ? 1 : 0) + (this.timeStart ? 1 : 0) + (this.timeEnd ? 1 : 0);
      const dateTimeWidth = span(slots);
      if (this.eventName) {
        list.push(["eventName", {
          type: "text",
          label: this.labels.eventName,
          width: "1/1"
        }]);
      }
      list.push(["startDate", {
        type: "date",
        label: this.endDate ? this.labels.startDate : "Date",
        display: "YYYY-MM-DD",
        time: false,
        required: true,
        width: dateTimeWidth
      }]);
      if (this.endDate) {
        list.push(["endDate", {
          type: "date",
          label: this.labels.endDate,
          display: "YYYY-MM-DD",
          time: false,
          width: dateTimeWidth
        }]);
      }
      if (this.timeStart) {
        list.push(["timeStart", {
          type: "time",
          label: this.labels.timeStart,
          display: "hh:mm a",
          notation: 24,
          width: dateTimeWidth,
          step: 15
          // 15-min steps
        }]);
      }
      if (this.timeEnd) {
        list.push(["timeEnd", {
          type: "time",
          label: this.labels.timeEnd,
          display: "hh:mm a",
          notation: 24,
          width: dateTimeWidth,
          step: 15
          // 15-min steps
        }]);
      }
      if (this.city) {
        list.push(["city", {
          type: "text",
          label: this.labels.city,
          width: this.country ? "1/3" : "1/2"
          // half if no country
        }]);
      }
      if (this.state) {
        list.push(["state", {
          type: "text",
          label: this.labels.state,
          width: this.country ? "1/3" : "1/2"
          // half if no country
        }]);
      }
      if (this.country) {
        list.push(["country", {
          type: "text",
          label: this.labels.country,
          width: "1/3"
        }]);
      }
      if (this.venue) {
        list.push(["venue", {
          type: "text",
          label: this.labels.venue
        }]);
      }
      if (this.url) {
        list.push(["url", {
          type: "url",
          label: this.labels.url
        }]);
      }
      if (this.details) {
        list.push(["details", {
          type: "textarea",
          label: this.labels.details,
          buttons: false,
          size: "small"
        }]);
      }
      const fields = Object.fromEntries(list);
      this.$panel.drawer.open({
        component: "k-form-drawer",
        props: {
          icon: "calendar",
          title: "Event Details",
          fields,
          value: { ...this.form }
        },
        on: {
          submit: this.handleSubmit.bind(this)
        }
      });
    },
    closeDrawer() {
      this.$panel.drawer.close();
    },
    handleSubmit(formData) {
      Promise.resolve().then(() => {
        this.form = { ...this.form, ...formData || {} };
        this.$emit("input", this.form);
        this.$emit("change", this.form);
        this.closeDrawer();
        this.$panel.notification.success({
          message: "Ok",
          timeout: 4e3
        });
      }).catch(() => {
        this.$panel.notification.error({
          message: "An error occurred",
          timeout: 4e3
        });
      });
    },
    // reformat date value if time is not present
    // strips time value for preview only
    formatDate(v) {
      if (!v) return "";
      const s = String(v).trim();
      return s.split(/[ T]/)[0];
    },
    formatPrettyDate(v) {
      if (!v) return "";
      const s = String(v).trim();
      const [datePart] = s.split(" ");
      const m = datePart.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (!m) return datePart || s;
      const y = parseInt(m[1], 10);
      const mo = parseInt(m[2], 10);
      const d = parseInt(m[3], 10);
      const dt = new Date(Date.UTC(y, mo - 1, d));
      const fmt = new Intl.DateTimeFormat(void 0, {
        timeZone: "UTC",
        month: "short",
        day: "2-digit",
        // numeric, 2-digit
        year: "numeric"
      });
      return fmt.format(dt);
    },
    formatTime(v) {
      if (!v) return "";
      const s = String(v).trim();
      const m = s.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
      if (!m) return s;
      let h = parseInt(m[1], 10);
      const min = m[2];
      const mer = h >= 12 ? "pm" : "am";
      h = h % 12;
      if (h === 0) h = 12;
      return `${h}:${min} ${mer}`;
    },
    formatLocation(city, state, country) {
      const parts = [city, state, country].map((v) => v == null ? "" : String(v).trim()).filter(Boolean);
      return parts.join(", ");
    }
  }
};
const _hoisted_1 = { class: "k-event-field__summary" };
const _hoisted_2 = { "data-mobile": "true" };
const _hoisted_3 = { "data-mobile": "true" };
const _hoisted_4 = { class: "clip" };
const _hoisted_5 = ["href"];
const _hoisted_6 = ["innerHTML"];
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_k_button = resolveComponent("k-button");
  const _component_k_empty = resolveComponent("k-empty");
  const _component_k_field = resolveComponent("k-field");
  return openBlock(), createBlock(
    _component_k_field,
    mergeProps({ class: "k-event-field" }, _ctx.$props),
    {
      default: withCtx(() => [
        createVNode(_component_k_button, {
          slot: "options",
          icon: "edit",
          size: "xs",
          variant: "filled",
          disabled: _ctx.disabled,
          onClick: $options.openDrawer
        }, {
          default: withCtx(() => [..._cache[1] || (_cache[1] = [
            createTextVNode(
              "Edit...",
              -1
              /* CACHED */
            )
          ])]),
          _: 1
          /* STABLE */
        }, 8, ["disabled", "onClick"]),
        createCommentVNode("<k-code>{{ value }}</k-code>"),
        createElementVNode("div", _hoisted_1, [
          $options.hasContent ? (openBlock(), createElementBlock("table", {
            key: 0,
            style: { "table-layout": "auto" },
            class: "k-table",
            onClick: _cache[0] || (_cache[0] = (...args) => $options.openDrawer && $options.openDrawer(...args))
          }, [
            createElementVNode("tbody", null, [
              (openBlock(true), createElementBlock(
                Fragment,
                null,
                renderList($options.summaryRows, (row) => {
                  return openBlock(), createElementBlock("tr", {
                    key: row.key
                  }, [
                    createElementVNode(
                      "th",
                      _hoisted_2,
                      toDisplayString(row.label),
                      1
                      /* TEXT */
                    ),
                    createElementVNode("td", _hoisted_3, [
                      createElementVNode("div", _hoisted_4, [
                        row.key === "url" && row.value ? (openBlock(), createElementBlock("a", {
                          key: 0,
                          href: row.value,
                          target: "_blank",
                          rel: "noopener noreferrer"
                        }, toDisplayString(row.value), 9, _hoisted_5)) : row.key === "details" && row.value ? (openBlock(), createElementBlock("div", {
                          key: 1,
                          class: "k-prose",
                          innerHTML: row.value
                        }, null, 8, _hoisted_6)) : (openBlock(), createElementBlock(
                          Fragment,
                          { key: 2 },
                          [
                            createTextVNode(
                              toDisplayString(row.value || "—"),
                              1
                              /* TEXT */
                            )
                          ],
                          64
                          /* STABLE_FRAGMENT */
                        ))
                      ])
                    ])
                  ]);
                }),
                128
                /* KEYED_FRAGMENT */
              ))
            ])
          ])) : (openBlock(), createBlock(_component_k_empty, {
            key: 1,
            icon: "calendar"
          }, {
            default: withCtx(() => [
              createTextVNode(
                toDisplayString($options.emptyComputed),
                1
                /* TEXT */
              )
            ]),
            _: 1
            /* STABLE */
          }))
        ])
      ]),
      _: 1
      /* STABLE */
    },
    16
    /* FULL_PROPS */
  );
}
const EventField = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
panel.plugin("scottboms/events", {
  fields: {
    event: EventField
  }
});
