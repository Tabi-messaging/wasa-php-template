# Wasa — PHP Template

A beautiful, ready-to-run boilerplate that showcases every feature of the
[**tabi/sdk**](https://packagist.org/packages/tabi/sdk) for WhatsApp messaging.

Clone it, add your credentials, and start sending messages in under 2 minutes.

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB3)
![tabi/sdk](https://img.shields.io/badge/tabi%2Fsdk-latest-indigo)
![License](https://img.shields.io/badge/license-MIT-green)

---

## Features

| Feature | Description |
|---------|-------------|
| **Send Text** | Send text messages to any WhatsApp number |
| **Send Media** | Send images, videos, audio, and documents with captions |
| **Send Poll** | Create single/multi-select polls |
| **Send Location** | Share location pins |
| **Send Contact** | Share contact cards |
| **Contacts** | List & create contacts in your workspace |
| **Conversations** | Browse active conversations |
| **Channels** | View channel list & live status |

---

## Quick Start

### 1. Clone

```bash
git clone https://github.com/Tabi-messaging/wasa-php-template.git
cd wasa-php-template
```

### 2. Install

```bash
composer install
```

### 3. Configure

```bash
cp .env.example .env
```

Open `.env` and fill in your credentials:

```dotenv
TABI_API_KEY=tk_your_api_key_here
TABI_BASE_URL=https://api.c36.online/api/v1
TABI_CHANNEL_ID=your-channel-id-here
```

**Where to get these values:**

| Variable | Where to find it |
|----------|-----------------|
| `TABI_API_KEY` | Dashboard → Developer → API Keys → Create token |
| `TABI_BASE_URL` | Your Tabi API endpoint (default: `https://api.c36.online/api/v1`) |
| `TABI_CHANNEL_ID` | Dashboard → Channels → click a channel → copy the ID from the URL |

### 4. Run

```bash
php -S localhost:4000 -t public
```

Open [http://localhost:4000](http://localhost:4000) and you're ready to go.

---

## Project Structure

```
wasa-php-template/
├── public/
│   └── index.php          # Entry point (front controller)
├── routes/
│   └── api.php            # Server-side SDK handler
├── src/
│   ├── Env.php            # .env file parser
│   └── Router.php         # Simple request router
├── templates/
│   └── app.php            # Beautiful single-page UI (Tailwind CSS)
├── .env.example
├── composer.json
└── README.md
```

Your API key lives server-side in `.env` — it's never exposed to the browser.

---

## How It Works

1. The browser UI (`templates/app.php`) sends AJAX requests to `/api` with an
   `action` field.
2. The route handler (`routes/api.php`) creates a `TabiClient` from `tabi/sdk`
   using your `.env` credentials.
3. The matching SDK method is called and the JSON response is returned to the UI.

### Example — Sending a text message

```php
use Tabi\TabiClient;

$tabi = new TabiClient([
    'apiKey'  => 'tk_your_key',
    'baseUrl' => 'https://api.c36.online/api/v1',
]);

$result = $tabi->messages->send('channel-id', [
    'to'      => '2348012345678',
    'content' => 'Hello from Wasa!',
]);

print_r($result);
```

---

## Phone Number Format

Always use full international format **without** the `+` sign:

| Country | Format | Example |
|---------|--------|---------|
| Nigeria | `234XXXXXXXXXX` | `2348012345678` |
| US / Canada | `1XXXXXXXXXX` | `12025551234` |
| UK | `44XXXXXXXXXX` | `447911123456` |

---

## Requirements

- PHP 8.1+
- cURL extension (enabled by default on most systems)
- Composer

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| "TABI_API_KEY not set" | Make sure `.env` exists and contains your key, then restart the PHP server |
| 403 errors | Verify your API key is valid and the channel belongs to your workspace |
| Messages not delivered | Check that the channel is connected and review the risk engine status in your dashboard |
| `Class 'Tabi\TabiClient' not found` | Run `composer install` first |

---

## Learn More

- [tabi/sdk on Packagist](https://packagist.org/packages/tabi/sdk)
- [tabi-sdk on npm (JavaScript)](https://www.npmjs.com/package/tabi-sdk)

---

## License

MIT
