/**
 * Alpine.js data component: date range picker (presets + dual calendars).
 * First click: shows only preset list.
 * Custom Range: expands to show month/year dropdowns + dual calendars.
 */
function pad2(n) {
    return String(n).padStart(2, '0');
}

function toYMD(d) {
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function parseYMD(s) {
    if (!s || typeof s !== 'string') return null;
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return null;
    const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
    return Number.isNaN(d.getTime()) ? null : d;
}

function formatUS(d) {
    if (!d) return '';
    return `${pad2(d.getMonth() + 1)}/${pad2(d.getDate())}/${d.getFullYear()}`;
}

function startOfDay(d) {
    const x = new Date(d);
    x.setHours(0, 0, 0, 0);
    return x;
}

function today() {
    return startOfDay(new Date());
}

export function purchaseDateRange(initial = {}) {
    return {
        open: false,
        dateFrom: initial.dateFrom || '',
        dateTo: initial.dateTo || '',
        activePreset: null,
        customMode: false,
        tempStart: null,
        tempEnd: null,
        viewYear: new Date().getFullYear(),
        viewMonth: new Date().getMonth(),

        init() {
            if (this.dateFrom && this.dateTo) {
                const a = parseYMD(this.dateFrom);
                const b = parseYMD(this.dateTo);
                if (a && b) {
                    this.viewYear = a.getFullYear();
                    this.viewMonth = a.getMonth();
                }
            }
        },

        displayPlaceholder() {
            if (this.dateFrom && this.dateTo) {
                const a = parseYMD(this.dateFrom);
                const b = parseYMD(this.dateTo);
                if (a && b) return `${formatUS(a)} - ${formatUS(b)}`;
            }
            return 'Start Date - End Date';
        },

        footerLabel() {
            if (this.customMode && this.tempStart) {
                const a = this.tempStart;
                const b = this.tempEnd || this.tempStart;
                return `${formatUS(a)} - ${formatUS(b)}`;
            }
            if (this.dateFrom && this.dateTo) {
                const a = parseYMD(this.dateFrom);
                const b = parseYMD(this.dateTo);
                if (a && b) return `${formatUS(a)} - ${formatUS(b)}`;
            }
            return `${formatUS(today())} - ${formatUS(today())}`;
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                // Reset to preset-only view when opening
                this.customMode = false;
                this.activePreset = null;
                if (this.dateFrom && this.dateTo) {
                    const a = parseYMD(this.dateFrom);
                    if (a) {
                        this.viewYear = a.getFullYear();
                        this.viewMonth = a.getMonth();
                    }
                }
            }
        },

        close() {
            this.open = false;
        },

        yearOptions() {
            const cur = new Date().getFullYear();
            const arr = [];
            for (let y = cur - 10; y <= cur + 5; y++) arr.push(y);
            return arr;
        },

        selectPreset(id) {
            if (id === 'custom') {
                this.activePreset = 'custom';
                this.customMode = true;
                this.tempStart = this.dateFrom ? parseYMD(this.dateFrom) : null;
                this.tempEnd = this.dateTo ? parseYMD(this.dateTo) : null;
                if (this.tempStart) {
                    this.viewYear = this.tempStart.getFullYear();
                    this.viewMonth = this.tempStart.getMonth();
                } else {
                    this.viewYear = new Date().getFullYear();
                    this.viewMonth = new Date().getMonth();
                }
                return;
            }

            this.customMode = false;
            this.tempStart = null;
            this.tempEnd = null;
            this.activePreset = id;

            const t = today();
            let start = t;
            let end = t;

            switch (id) {
                case 'today':
                    start = end = t;
                    break;
                case 'yesterday': {
                    const y = new Date(t);
                    y.setDate(y.getDate() - 1);
                    start = end = startOfDay(y);
                    break;
                }
                case 'last7': {
                    end = t;
                    start = new Date(t);
                    start.setDate(start.getDate() - 6);
                    start = startOfDay(start);
                    break;
                }
                case 'last15': {
                    end = t;
                    start = new Date(t);
                    start.setDate(start.getDate() - 14);
                    start = startOfDay(start);
                    break;
                }
                case 'last30': {
                    end = t;
                    start = new Date(t);
                    start.setDate(start.getDate() - 29);
                    start = startOfDay(start);
                    break;
                }
                case 'thisMonth': {
                    start = startOfDay(new Date(t.getFullYear(), t.getMonth(), 1));
                    end = startOfDay(new Date(t.getFullYear(), t.getMonth() + 1, 0));
                    break;
                }
                case 'lastMonth': {
                    const lm = new Date(t.getFullYear(), t.getMonth() - 1, 1);
                    start = startOfDay(lm);
                    end = startOfDay(new Date(lm.getFullYear(), lm.getMonth() + 1, 0));
                    break;
                }
                case 'last6': {
                    end = t;
                    start = new Date(t);
                    start.setMonth(start.getMonth() - 6);
                    start = startOfDay(start);
                    break;
                }
                case 'thisYear': {
                    start = startOfDay(new Date(t.getFullYear(), 0, 1));
                    end = t;
                    break;
                }
                default:
                    return;
            }

            this.dateFrom = toYMD(start);
            this.dateTo = toYMD(end);

            // Auto-submit when selecting a preset (non-custom)
            this.close();
            this.$nextTick(() => {
                this.$refs.filterForm.submit();
            });
        },

        applyFooter() {
            if (this.customMode) {
                if (this.tempStart) {
                    const a = this.tempStart;
                    const b = this.tempEnd || this.tempStart;
                    const [from, to] = a <= b ? [a, b] : [b, a];
                    this.dateFrom = toYMD(from);
                    this.dateTo = toYMD(to);
                }
            }
            this.close();
            this.$nextTick(() => {
                this.$refs.filterForm.submit();
            });
        },

        clearRange() {
            this.dateFrom = '';
            this.dateTo = '';
            this.activePreset = null;
            this.customMode = false;
            this.tempStart = null;
            this.tempEnd = null;
            this.close();
            this.$nextTick(() => {
                this.$refs.filterForm.submit();
            });
        },

        submitSearch() {
            this.$refs.filterForm.submit();
        },

        monthGrid(year, month) {
            const first = new Date(year, month, 1);
            const last = new Date(year, month + 1, 0);
            const pad = first.getDay();
            const total = last.getDate();
            const cells = [];
            for (let i = 0; i < pad; i++) cells.push(null);
            for (let d = 1; d <= total; d++) {
                cells.push(new Date(year, month, d));
            }
            while (cells.length % 7 !== 0) cells.push(null);
            const rows = [];
            for (let i = 0; i < cells.length; i += 7) {
                rows.push(cells.slice(i, i + 7));
            }
            return rows;
        },

        monthLabel(year, month) {
            return new Date(year, month, 1).toLocaleString('en-US', { month: 'long', year: 'numeric' });
        },

        prevMonths() {
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear -= 1;
            } else {
                this.viewMonth -= 1;
            }
        },

        nextMonths() {
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear += 1;
            } else {
                this.viewMonth += 1;
            }
        },

        rightCalYearMonth() {
            let m = this.viewMonth + 1;
            let y = this.viewYear;
            if (m > 11) {
                m = 0;
                y += 1;
            }
            return { year: y, month: m };
        },

        isInRange(d) {
            if (!d) return false;
            let start;
            let end;
            if (this.customMode) {
                start = this.tempStart;
                end = this.tempEnd || this.tempStart;
            } else {
                start = parseYMD(this.dateFrom);
                end = parseYMD(this.dateTo);
            }
            if (!start) return false;
            const endF = end || start;
            const t = startOfDay(d).getTime();
            return t >= startOfDay(start).getTime() && t <= startOfDay(endF).getTime();
        },

        isSameDay(a, b) {
            if (!a || !b) return false;
            return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
        },

        clickDay(d) {
            if (!d || !this.customMode) return;
            if (!this.tempStart || (this.tempStart && this.tempEnd)) {
                this.tempStart = startOfDay(d);
                this.tempEnd = null;
                return;
            }
            const second = startOfDay(d);
            let a = this.tempStart;
            let b = second;
            if (b < a) [a, b] = [b, a];
            this.tempStart = a;
            this.tempEnd = b;
        },

        isToday(d) {
            if (!d) return false;
            return this.isSameDay(d, today());
        },

        dayClass(d) {
            if (!d) return 'invisible pointer-events-none';
            const base = 'w-7 h-7 flex items-center justify-center rounded text-xs cursor-pointer';
            const inR = this.isInRange(d);
            const sel =
                this.customMode &&
                (this.isSameDay(d, this.tempStart) || (this.tempEnd && this.isSameDay(d, this.tempEnd)));
            if (sel) return `${base} bg-[#5542ff] text-white font-semibold`;
            if (inR) return `${base} bg-[#5542ff]/15 text-[#0a1233]`;
            if (this.customMode && this.isToday(d)) return `${base} text-[#5542ff] font-semibold ring-1 ring-[#5542ff]/40`;
            return `${base} text-gray-700 hover:bg-gray-100`;
        },

        leftGrid() {
            return this.monthGrid(this.viewYear, this.viewMonth);
        },

        rightGrid() {
            const r = this.rightCalYearMonth();
            return this.monthGrid(r.year, r.month);
        },

        escapeClose() {
            if (this.open) {
                this.open = false;
            }
        },
    };
}
