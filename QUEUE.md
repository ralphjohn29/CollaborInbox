# Email Queue System

CollaborInbox uses Laravel's queue system with Redis to handle asynchronous email processing. This document covers how to set up and run the queue workers for production.

## Queue Configuration

The system uses two main queues:

1. **emails** - For mailbox fetching operations. This queue processes the initial email fetching from IMAP servers.
2. **email-processing** - For individual email processing. This queue handles parsing and storing each individual email.

## Running Queue Workers

For production environments, we recommend using Supervisor to ensure queue workers stay running. Here's a sample Supervisor configuration:

```ini
[program:collaborinbox-worker-emails]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/collaborinbox/artisan queue:work redis --queue=emails --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/collaborinbox/storage/logs/worker-emails.log
stopwaitsecs=3600

[program:collaborinbox-worker-email-processing]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/collaborinbox/artisan queue:work redis --queue=email-processing --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/collaborinbox/storage/logs/worker-email-processing.log
stopwaitsecs=3600
```

For the email processing queue, we recommend running multiple workers (2-4 depending on server capacity) to handle concurrent email processing.

## For Development

For local development, you can run the queue workers manually:

```bash
# Run the worker for the emails queue (fetching emails from IMAP)
php artisan queue:work redis --queue=emails

# Run the worker for the email-processing queue (processing individual emails)
php artisan queue:work redis --queue=email-processing
```

Or to run all queues:

```bash
php artisan queue:work redis
```

## Monitoring

The system includes built-in queue monitoring tools:

```bash
# View queue performance metrics
php artisan queue:monitor

# Check for queue performance issues and alert if needed
php artisan queue:monitor --alert

# Retry failed jobs for a specific queue
php artisan queue:retry-batch --queue=email-processing
```

The scheduler is configured to automatically:
- Check for queue performance issues every 15 minutes
- Retry failed jobs from the email-processing queue once per hour

## Rate Limiting

The system includes rate limiting to prevent overwhelming with large email volumes. Current limits:
- Maximum of 100 emails processed per minute per tenant

## Troubleshooting

If you're experiencing issues with the queue system:

1. Check the worker logs for errors:
   - `/path/to/collaborinbox/storage/logs/worker-emails.log`
   - `/path/to/collaborinbox/storage/logs/worker-email-processing.log`

2. View failed jobs in the dashboard or database:
   ```bash
   php artisan queue:failed
   ```

3. Retry specific failed jobs:
   ```bash
   php artisan queue:retry <job_id>
   ```

4. Clear failed jobs if necessary:
   ```bash
   php artisan queue:flush
   ``` 