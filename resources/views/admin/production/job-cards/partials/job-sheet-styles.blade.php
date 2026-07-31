<style>
    @page { margin: 10mm; size: A4 portrait; }
    * { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #2e3192;
        margin: 0;
        padding: 16px;
        font-size: 11px;
        line-height: 1.35;
        background: #fff;
    }
    .sheet { max-width: 210mm; margin: 0 auto; border: 2px solid #2e3192; }
    .sheet__header {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px 16px;
        padding: 10px 14px 6px;
        border-bottom: 2px solid #2e3192;
        align-items: start;
    }
    .brand-wrap { display: flex; align-items: center; gap: 10px; }
    .brand-logo { height: 52px; width: auto; object-fit: contain; }
    .brand { font-size: 26px; font-weight: 800; color: #e91e8c; line-height: 1.1; letter-spacing: -0.02em; }
    .brand small {
        display: block;
        font-size: 9px;
        font-weight: 700;
        color: #2e3192;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .title {
        font-size: 30px;
        font-weight: 800;
        color: #e91e8c;
        text-align: right;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        align-self: center;
    }
    .contact {
        grid-column: 1 / -1;
        font-size: 9px;
        color: #2e3192;
        border-top: 1px solid #cbd5e1;
        padding-top: 6px;
        line-height: 1.5;
    }
    .meta {
        display: grid;
        grid-template-columns: 120px 1fr 1fr;
        gap: 8px 16px;
        padding: 10px 14px;
        border-bottom: 2px solid #2e3192;
        align-items: end;
    }
    .meta__label { font-weight: 700; color: #2e3192; font-size: 10px; text-transform: uppercase; }
    .meta__value {
        border-bottom: 1px solid #64748b;
        min-height: 20px;
        margin-top: 2px;
        font-size: 12px;
        color: #0f172a;
        padding-bottom: 2px;
    }
    .meta__customer { grid-column: 1 / -1; }
    .section-title {
        color: #e91e8c;
        font-weight: 800;
        text-align: center;
        padding: 6px 10px;
        font-size: 11px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        border-top: 2px solid #2e3192;
        border-bottom: 2px solid #2e3192;
        background: #fff;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td {
        border: 1px solid #2e3192;
        padding: 5px 6px;
        vertical-align: top;
        color: #0f172a;
    }
    th {
        background: #fff;
        color: #2e3192;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom-width: 2px;
    }
    .ncr-cell { padding: 0 !important; }
    .ncr-inner { width: 100%; border-collapse: collapse; }
    .ncr-inner td {
        border: none;
        border-right: 1px dotted #2e3192;
        padding: 4px 5px;
        min-height: 28px;
        font-size: 10px;
    }
    .ncr-inner td:last-child { border-right: none; }
    .ncr-inner small {
        display: block;
        font-size: 8px;
        font-weight: 700;
        color: #2e3192;
        margin-bottom: 2px;
    }
    .notes {
        min-height: 56px;
        padding: 8px 14px;
        border-bottom: 2px solid #2e3192;
        white-space: pre-wrap;
        color: #0f172a;
    }
    .notes__label {
        color: #e91e8c;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 4px;
        letter-spacing: 0.06em;
    }
    .signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 12px 14px;
        border-bottom: 2px solid #2e3192;
    }
    .signatures strong { color: #e91e8c; font-size: 10px; text-transform: uppercase; }
    .sign-line { border-bottom: 1px solid #64748b; min-height: 22px; margin-top: 18px; color: #0f172a; }
    .checks {
        display: flex;
        justify-content: space-around;
        padding: 10px 14px 14px;
        font-size: 11px;
        font-weight: 700;
        color: #2e3192;
    }
    .check { display: inline-flex; align-items: center; gap: 6px; }
    .box {
        width: 14px;
        height: 14px;
        border: 2px solid #2e3192;
        display: inline-block;
        text-align: center;
        line-height: 10px;
        font-size: 10px;
        color: #2e3192;
    }
    .job-sheet-toolbar {
        text-align: center;
        margin-bottom: 12px;
    }
    .job-sheet-toolbar button {
        padding: 8px 18px;
        cursor: pointer;
        border: 2px solid #2e3192;
        background: #fff;
        color: #2e3192;
        font-weight: 700;
        border-radius: 4px;
    }
    .job-sheet-toolbar button:hover { background: #f8fafc; }
    @media print {
        body { padding: 0; }
        .no-print { display: none !important; }
    }
</style>
