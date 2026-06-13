<style>
    .jp-doc {
        color: #1E293B;
        font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
        font-size: 9pt;
        line-height: 1.45;
    }

    .jp-doc * {
        box-sizing: border-box;
    }

    /* ── Header ── */
    .jp-doc__header {
        width: 100%;
        margin-bottom: 6mm;
    }

    .jp-doc__header-top td {
        vertical-align: middle;
        padding-bottom: 3mm;
    }

    .jp-doc__logo-cell {
        width: 55%;
        padding-right: 4mm;
    }

    .jp-doc__title-cell {
        width: 45%;
        text-align: right;
        vertical-align: middle;
    }

    .jp-doc__logo {
        display: block;
        width: 62mm;
        height: auto;
        max-width: 100%;
    }

    .jp-doc__title {
        color: #1E3A6E;
        font-size: 22pt;
        font-weight: bold;
        letter-spacing: 0.04em;
        margin: 0;
        text-transform: uppercase;
    }

    .jp-doc__number {
        color: #334155;
        font-size: 10pt;
        margin: 1.5mm 0 0;
    }

    .jp-doc__header-highlight-label {
        color: #64748B;
        font-size: 8pt;
        margin: 3mm 0 0.5mm;
    }

    .jp-doc__header-highlight-value {
        color: #0F172A;
        font-size: 13pt;
        font-weight: bold;
        margin: 0;
    }

    .jp-doc__header-status {
        margin-top: 2mm;
        text-align: right;
    }

    .jp-doc__header-address td {
        padding-top: 1mm;
        padding-bottom: 4mm;
        border-bottom: 1px solid #E2E8F0;
    }

    .jp-doc__company-lines {
        margin: 0;
    }

    .jp-doc__company-line {
        color: #475569;
        font-size: 8.5pt;
        line-height: 1.5;
        margin: 0 0 0.5mm;
    }

    .jp-doc__company-line--name {
        color: #1E293B;
        font-size: 9pt;
        font-weight: bold;
    }

    @media screen {
        .jp-doc__logo {
            width: 240px;
        }
    }

    /* ── Parties / dates ── */
    .jp-doc__parties {
        width: 100%;
        margin: 5mm 0;
    }

    .jp-doc__parties-bill {
        width: 50%;
        vertical-align: top;
        padding-right: 4mm;
    }

    .jp-doc__parties-dates {
        width: 50%;
        vertical-align: top;
        padding-left: 4mm;
    }

    .jp-doc__section-title {
        color: #64748B;
        font-size: 8pt;
        font-weight: normal;
        margin: 0 0 2mm;
    }

    .jp-doc__party-name {
        color: #0F172A;
        font-size: 11pt;
        font-weight: bold;
        margin: 0 0 1mm;
    }

    .jp-doc__party-line {
        color: #475569;
        font-size: 8.5pt;
        margin: 0 0 1mm;
    }

    .jp-doc__dates-table {
        width: 100%;
        margin-left: auto;
    }

    .jp-doc__dates-table td {
        font-size: 8.5pt;
        padding: 0.8mm 0;
        vertical-align: top;
    }

    .jp-doc__dates-label {
        color: #64748B;
        text-align: right;
        padding-right: 3mm;
        white-space: nowrap;
        width: 42%;
    }

    .jp-doc__dates-value {
        color: #1E293B;
        text-align: right;
        font-weight: 500;
        width: 58%;
    }

    /* ── Commercial meta ── */
    .jp-doc__commercial-meta {
        width: 100%;
        margin-bottom: 4mm;
        border-bottom: 1px solid #F1F5F9;
        padding-bottom: 3mm;
    }

    .jp-doc__commercial-meta-col {
        width: 50%;
        vertical-align: top;
        padding-right: 3mm;
    }

    .jp-doc__commercial-meta-label {
        color: #94A3B8;
        font-size: 7.5pt;
        padding: 0.5mm 2mm 0.5mm 0;
        white-space: nowrap;
        width: 38%;
    }

    .jp-doc__commercial-meta-value {
        color: #475569;
        font-size: 7.5pt;
        padding: 0.5mm 0;
    }

    /* ── Status badge ── */
    .jp-doc__badge {
        border-radius: 10px;
        display: inline-block;
        font-size: 7pt;
        font-weight: bold;
        letter-spacing: 0.04em;
        padding: 1.2mm 2.5mm;
        text-transform: uppercase;
    }

    .jp-doc__badge--neutral { background: #F1F5F9; color: #64748B; }
    .jp-doc__badge--info { background: #EEF2FF; color: #6C4BFF; }
    .jp-doc__badge--success { background: #ECFDF5; color: #16A34A; }
    .jp-doc__badge--warning { background: #FFFBEB; color: #D97706; }
    .jp-doc__badge--danger { background: #FEF2F2; color: #DC2626; }

    /* ── Items table ── */
    .jp-doc__items {
        border-collapse: collapse;
        margin-top: 2mm;
        width: 100%;
    }

    .jp-doc__items th {
        background: #7B4FD1;
        color: #FFFFFF;
        font-size: 8pt;
        font-weight: bold;
        padding: 2.8mm 2.5mm;
    }

    .jp-doc__items td {
        border-bottom: 1px solid #E2E8F0;
        color: #1E293B;
        font-size: 9pt;
        padding: 2.8mm 2.5mm;
        vertical-align: top;
    }

    .jp-doc__items .is-right {
        font-family: DejaVu Sans Mono, monospace;
        text-align: right;
        white-space: nowrap;
    }

    .jp-doc__empty {
        color: #94A3B8;
        font-style: italic;
        padding: 6mm 2mm;
        text-align: center;
    }

    /* ── Totals + notes row ── */
    .jp-doc__bottom-row {
        width: 100%;
        margin-top: 2mm;
        border-top: 1px solid #E2E8F0;
    }

    .jp-doc__bottom-notes {
        width: 52%;
        vertical-align: top;
        padding: 4mm 4mm 0 0;
    }

    .jp-doc__bottom-totals {
        width: 48%;
        vertical-align: top;
        padding: 4mm 0 0 0;
    }

    .jp-doc__notes-title {
        color: #64748B;
        font-size: 8pt;
        margin: 0 0 2mm;
    }

    .jp-doc__notes-body {
        color: #475569;
        font-size: 8.5pt;
        line-height: 1.5;
        margin: 0;
        white-space: pre-line;
    }

    .jp-doc__totals {
        width: 100%;
    }

    .jp-doc__totals td {
        font-size: 9pt;
        padding: 1.5mm 0;
    }

    .jp-doc__totals .label {
        color: #64748B;
        text-align: right;
        padding-right: 4mm;
        width: 62%;
    }

    .jp-doc__totals .value {
        font-family: DejaVu Sans Mono, monospace;
        text-align: right;
        width: 38%;
    }

    .jp-doc__totals .is-highlight .label,
    .jp-doc__totals .is-highlight .value {
        color: #0F172A;
        font-size: 10pt;
        font-weight: bold;
        padding-top: 2mm;
    }

    .jp-doc__totals .is-balance-bar .label,
    .jp-doc__totals .is-balance-bar .value {
        background: #F1F5F9;
        font-weight: bold;
        padding: 2mm 2mm;
    }

    .jp-doc__totals .is-balance-bar .label {
        border-radius: 2px 0 0 2px;
    }

    .jp-doc__totals .is-balance-bar .value {
        border-radius: 0 2px 2px 0;
    }

    /* ── Summary blocks (receipt allocations etc.) ── */
    .jp-doc__summary {
        background: #F8FAFF;
        border: 1px solid #E2E8F0;
        margin: 4mm 0;
        padding: 3mm 4mm;
    }

    .jp-doc__summary-title {
        color: #1E293B;
        font-size: 8.5pt;
        font-weight: bold;
        margin: 0 0 2mm;
        text-transform: uppercase;
    }

    .jp-doc__summary-table {
        width: 100%;
    }

    .jp-doc__summary-table td {
        font-size: 8.5pt;
        padding: 1mm 0;
    }

    .jp-doc__summary-table .label {
        color: #64748B;
        width: 42%;
    }

    .jp-doc__summary-table .value {
        font-weight: 600;
        text-align: right;
        width: 58%;
    }

    /* ── Payment footer bar ── */
    .jp-doc__payment-footer {
        background: #7B4FD1;
        color: #FFFFFF;
        margin-top: 8mm;
        padding: 4mm 5mm;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .jp-doc__payment-footer-title {
        font-size: 8.5pt;
        font-weight: bold;
        margin: 0 0 2mm;
        text-decoration: underline;
    }

    .jp-doc__payment-footer-line {
        font-size: 8pt;
        line-height: 1.55;
        margin: 0 0 1.5mm;
    }

    .jp-doc__payment-footer-line:last-child {
        margin-bottom: 0;
    }

    /* ── Document footer ── */
    .jp-doc__footer {
        color: #94A3B8;
        font-size: 8pt;
        margin-top: 6mm;
        text-align: center;
    }

    .jp-doc__footer-thanks {
        color: #64748B;
        font-style: italic;
        margin: 0 0 1.5mm;
    }

    .jp-doc__footer-system {
        color: #CBD5E1;
        font-size: 7pt;
        margin: 0;
    }

    .jp-doc__summary,
    .jp-doc__totals,
    .jp-doc__payment-footer,
    .jp-doc__footer,
    .jp-doc__bottom-row {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    @media print {
        .jp-doc-actions {
            display: none !important;
        }
    }
</style>
