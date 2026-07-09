# Jana Prints ERP — Production Queue & Scheduler Runbook

This runbook documents how to run background workers and the Laravel scheduler for Jana Prints ERP in production.

## Queue driver

The application supports **database** (default) and **Redis** queue drivers. Redis is recommended at higher volume, but database queues are fully supported.

Set in `.env`:

```env
QUEUE_CONNECTION=database
```

For Redis:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Required queues

| Queue | Purpose |
|-------|---------|
| `default` | General platform jobs |
| `emails` | Outbound email delivery |
| `notifications` | In-app / ERP notifications |
| `exports` | Commercial and listing exports |
| `sms` | SMS campaigns and transactional SMS |
| `whatsapp` | WhatsApp message delivery |

## Supervisor example (Linux)

```ini
[program:jana-prints-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/janaprints/artisan queue:work database --queue=default,notifications,exports,emails,sms,whatsapp --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/janaprints/storage/logs/queue-worker.log
stopwaitsecs=3600
```

After deploy or config changes:

```bash
php artisan queue:restart
```

## cPanel / WHM terminal

```bash
cd ~/public_html/janaprints
php artisan queue:work database --queue=default,notifications,exports,emails,sms,whatsapp --sleep=3 --tries=3 --max-time=3600
```

Run under `screen`, `tmux`, or a process manager so the worker stays alive.

## Scheduler cron entry

Add one cron job for the application user:

```cron
* * * * * cd /var/www/janaprints && php artisan schedule:run >> /dev/null 2>&1
```

## Currently configured scheduled tasks

| Command | Schedule |
|---------|----------|
| `commercial:expire-report-exports` | Daily |
| `governance:process-escalations` | Every 15 minutes |
| `inventory:velocity:snapshot --all-windows` | Daily at 02:30 |

## Recommended additional schedules

Consider adding these commands to `bootstrap/app.php` when the related modules go live:

- `communications:dispatch-scheduled-events`
- `communications:dispatch-payment-reminders`
- `printing-intelligence:generate-profitability-snapshots`

## Failed job maintenance

List failed jobs:

```bash
php artisan queue:failed
```

Retry all failed jobs:

```bash
php artisan queue:retry all
```

Retry one failed job:

```bash
php artisan queue:retry {id}
```

Flush failed jobs (destructive):

```bash
php artisan queue:flush
```

## Health checks

1. Open **Administration → System Operations → Background Jobs**.
2. Confirm queue connection is not `sync`.
3. Review queue backlog counts and failed job totals.
4. Ensure cron is running `schedule:run` every minute.

## Security notes

- Do not expose `.env`, Redis, or database credentials in worker logs.
- Restrict server SSH and cron access to trusted operators.
- Use HTTPS for all admin and portal traffic.
