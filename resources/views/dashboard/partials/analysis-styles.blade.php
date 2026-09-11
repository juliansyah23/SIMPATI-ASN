<style>
    .analysis-layout{display:grid;grid-template-columns:290px minmax(0,1fr);gap:14px;color:#172b4d}
    .analysis-card{border:1px solid #e2e8f0;border-radius:16px;background:#fff;padding:18px;min-width:0;box-shadow:0 4px 14px #0f172a06}
    .criteria-card{align-self:start;background:linear-gradient(135deg,#fff 55%,#eff6ff 100%)}
    .distribution-card{background:linear-gradient(135deg,#fff 60%,#ecfdf5 100%)}
    .analysis-card h3{display:flex;align-items:center;gap:9px;font-size:15px;font-weight:600;color:#0f172a;margin:0 0 18px;letter-spacing:-.2px}
    .analysis-card h3::before{content:"";width:4px;height:18px;border-radius:4px;background:#93c5fd;flex-shrink:0}
    .distribution-card h3::before{background:#6ee7b7}
    .analysis-card h4{font-size:12px;font-weight:600;color:#475569;margin-bottom:14px}
    .analysis-layout aside h4{color:#185496;font-size:15px;margin-top:14px}
    .scale-list{list-style:disc;padding-left:18px;font-size:13px;line-height:2}
    .criteria-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#ffffffd9}
    .criteria-table th,.criteria-table td{border:0;border-bottom:1px solid #edf2f7;padding:12px 5px;text-align:left}
    .criteria-table th{background:#f1f5f9;color:#475569;font-weight:600;font-size:11px}
    .criteria-table tbody tr:last-child td{border-bottom:0}
    .criteria-table th:first-child,.criteria-table td:first-child{text-align:center}
    .criteria-table td:last-child{white-space:nowrap;text-align:center}
    .criteria-label{display:flex;align-items:center;gap:8px;padding:6px 8px;text-align:left;color:#172b4d;font-weight:600;line-height:1.5}
    .criteria-marker{width:8px;height:8px;border-radius:50%;flex-shrink:0}
    .scale-note{font-size:11px;line-height:1.8;margin-top:14px;padding:10px 12px;border-radius:10px;background:#eff6ff;color:#475569}
    .analysis-main{display:flex;flex-direction:column;gap:14px;min-width:0}
    .analysis-summary{min-width:0;padding:16px 20px;border:1px solid #e2e8f0;border-radius:14px;background:linear-gradient(115deg,#fff 35%,#eff6ff 75%,#ecfdf5 100%);box-shadow:0 4px 14px #0f172a06}
    .summary-line{display:flex;align-items:center;flex-wrap:wrap;gap:8px 12px;margin:0;font-size:13px;line-height:1.8;color:#64748b;font-variant-numeric:tabular-nums}
    .summary-line strong{color:#172b4d;font-weight:600;overflow-wrap:anywhere}
    .summary-average{font-size:15px}
    .summary-separator{color:#94a3b8}
    .summary-class{display:inline-flex;align-items:center;gap:7px;padding:4px 10px;border-radius:999px;background:#fff;color:#334155;font-size:11px;font-weight:600;line-height:1.6}
    .summary-class i{width:6px;height:6px;flex-shrink:0;border-radius:50%}
    .summary-empty{font-weight:500;color:#64748b}
    @media(max-width:700px){.analysis-summary{padding:14px}.summary-line{flex-direction:column;align-items:flex-start;gap:6px}.summary-separator{display:none}}
    .frequency-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .diagram-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr))}
    .chart-cell{padding:14px 10px;min-width:0;border:1px solid #e8edf2;border-radius:12px;background:#ffffffed}
    .bar-chart{width:100%;height:240px;fill:#172b4d}
    .pie-layout{display:flex;align-items:center;gap:8px;min-height:240px}
    .pie-layout svg{width:55%;min-width:0}
    .chart-legend{flex:1;font-size:10px;display:flex;flex-direction:column;gap:12px}
    .chart-legend li{display:flex;align-items:center;gap:7px}
    .chart-legend i{width:11px;height:11px;border-radius:3px;flex-shrink:0}
    .diagram-grid .pie-layout{flex-wrap:wrap;justify-content:center}
    .diagram-grid .pie-layout svg{width:150px;max-width:100%}
    .diagram-grid .chart-legend{flex-basis:100%;gap:5px}
    .tree-chart{display:flex;flex-direction:column;height:220px;overflow:hidden;border-radius:5px;margin-top:16px}
    .tree-row{display:flex;min-height:0}
    .tree-cell{display:flex;flex-direction:column;justify-content:center;align-items:center;gap:5px;min-width:0;overflow:hidden;border:1px solid #ffffffaa;text-align:center;font-size:11px;padding:3px;overflow-wrap:anywhere}
    .chart-empty{text-align:center;font-size:12px;color:#64748b;margin-top:8px}
    #category-panel[aria-busy="true"]{opacity:.5}
    @media(max-width:1100px){.analysis-layout{grid-template-columns:1fr}.diagram-grid .pie-layout svg{width:55%}.diagram-grid .chart-legend{flex-basis:auto}}
    @media(max-width:700px){.frequency-grid,.diagram-grid{grid-template-columns:1fr}.pie-layout{min-height:220px}.pie-layout svg{max-width:220px}.chart-legend{font-size:12px}.analysis-card{padding:14px}}
</style>