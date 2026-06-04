# Dashboard-360 — Executive Command Center

## Widget inventory

| # | Section | Widgets | Max height / density |
|---|---------|---------|----------------------|
| 1 | Executive KPI strip | Today Sales, MTD Sales, Open Quotes, Active Jobs, Completed Jobs, Receivables, Payables, Inventory Value | 90px cells |
| 2 | Production pipeline | Quotes → Approved → Artwork → Printing → Finishing → Dispatch → Delivered (counts + bar) | 1 row |
| 3 | Attention center | Overdue jobs, artwork approvals, pending quotes, stock alerts, complaints, invoices | Red badges |
| 4 | Today's operations | Jobs today, machine %, deliveries, collections, PR approvals | Metric grid |
| 5 | Branch performance | Sales, jobs, receivables, profit by branch | Compact table |
| 6 | Top customers | Top 10 by MTD order revenue | Table |
| 7 | Sales performance | 30-day bar chart, quotes/orders/conversion | Panel |
| 8 | Production performance | Completed, turnaround, delayed, in progress, utilization | Metric grid |
| 9 | Inventory health | Low/out stock lists, fast movers, value, alerts | Panel |
| 10 | Finance snapshot | Revenue/expense/profit/receivables/payables/cash | Definition list |
| 11 | CRM pulse | Leads, new customers, quotes sent, conversion, lost | Metrics |
| 12 | HR pulse | Present, on leave, contract expiry, alerts | Metrics |
| 13 | Activity feed | Last 20 `activity_logs` entries | Scroll ~256px |
| 14 | Quick actions | 7 compact action buttons | Wrap row |
| 15 | Smart insights | Auto-generated bullets from live metrics | List |

## Data sources

| Metric | Source | Notes |
|--------|--------|-------|
| Today / MTD sales | `sales_orders.total_amount` | Excludes draft/cancelled |
| Open quotes | `quotations` statuses | Draft through Viewed |
| Active / completed jobs | `production_job_cards.status` | Tenant scoped |
| Receivables / payables / cash | — | Finance module not built; shows `—` |
| Inventory value | `InventoryStockService` + `inventory_items.standard_cost` | Requires branch context |
| Pipeline stages | `quotations`, `artwork_requests`, `production_job_cards`, `sales_orders` | See `ExecutiveDashboardPresenter::pipeline()` |
| Attention counts | Jobs, artwork, quotes, `inventory_reorder_alerts` | Complaints N/A |
| Branch table | `branches` + aggregated `sales_orders` / `production_job_cards` | Company-wide branches |
| Top customers | `sales_orders` grouped by `customer_id` | MTD |
| Sales chart | Daily `sales_orders` sum, 30 days | CSS bars (no Chart.js) |
| Production stats | `production_job_cards`, `production_queues` | Turnaround from actual dates |
| Inventory lists | Balances + movements | Low/out when branch set |
| CRM / HR | `leads`, `customers`, `quotations`, `employees` | |
| Activity | `activity_logs` | Humanized `message` |
| Insights | Derived comparisons | Month-over-month revenue proxy |

Implementation: `app/Support/Dashboard/ExecutiveDashboardPresenter.php`  
Cache: `PlatformCacheService` key `dashboard:{company}:{branch}` in `AppServiceProvider` view composer.

## Layout wireframe (ASCII)

```
+----------------------------------------------------------------------------------+
| Executive Command Center                                    LIVE OPERATIONS      |
+----------------------------------------------------------------------------------+
| [Today Sales][MTD Sales][Open Quotes][Active Jobs][Done][Recv][Pay][Inv Value]   |
+----------------------------------------------------------------------------------+
| PRODUCTION PIPELINE: Quotes → Approved → Artwork → Print → Finish → Disp → Deliv |
+----------------------------------------------------------------------------------+
| ATTENTION CENTER (red)  overdue | artwork | quotes | stock | complaints | AR    |
+------------------------------------------+---------------------------------------+
| TODAY OPS | BRANCH TABLE | SALES CHART    | CRM + HR | INSIGHTS | QUICK ACTS    |
| TOP CUSTOMERS | PROD METRICS | INV/FIN    | ACTIVITY FEED (20)                  |
+----------------------------------------------------------------------------------+
```

## Before / after

| Before | After |
|--------|-------|
| 5 KPI cards (large) | 8 compact KPI cells (≤90px) |
| Static pipeline (zeros) | Live pipeline with real counts + links |
| No attention layer | Red attention center |
| 3-column cards (CRM + workspace only) | 15 sections, dense grid |
| 8 activity rows | 20 activity rows with messages |
| No insights / quick ops / branches | Full command center |

Screenshots: capture `/admin` (or `/admin/dashboard`) before/after deploy in browser at 1440px width.

## Permissions

Quick actions respect `quotations.create`, `crm.customers.create`, `production.create`, `inventory.receive`, `procurement.orders.create`. Sections render for all authenticated tenant users; links use existing module routes.
