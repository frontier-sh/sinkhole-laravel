# Sinkhole for Laravel

A Laravel mail transport that sends all outgoing email to a [Sinkhole](https://github.com/frontier-sh/sinkhole) instance — a Cloudflare Worker email trap with a web UI.

Use it in staging or local development to capture emails without delivering them to real inboxes.

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

## Installation

```sh
composer require frontier-sh/sinkhole
```

The service provider is auto-registered via Laravel's package discovery.

## Configuration

Add the transport to `config/mail.php`:

```php
'mailers' => [
    // ...

    'sinkhole' => [
        'transport' => 'sinkhole',
        'endpoint'  => env('SINKHOLE_ENDPOINT'),
        'api_key'   => env('SINKHOLE_API_KEY'),
        'channel'   => env('SINKHOLE_CHANNEL', 'default'),
    ],
],
```

Set your environment variables:

```env
MAIL_MAILER=sinkhole
SINKHOLE_ENDPOINT=https://your-worker.workers.dev
SINKHOLE_API_KEY=your-api-key
SINKHOLE_CHANNEL=staging
```

| Variable | Description |
|---|---|
| `SINKHOLE_ENDPOINT` | The URL of your Sinkhole Worker |
| `SINKHOLE_API_KEY` | An API key created from the Sinkhole web UI |
| `SINKHOLE_CHANNEL` | Optional label to group emails (defaults to `default`) |

## What gets captured

- To, From, Subject
- HTML and plain text bodies
- All email headers
- Attachments (base64-encoded, stored in R2)
- Custom headers like `X-Tag` and `X-Metadata-*` are displayed in the Sinkhole UI

## License

MIT
