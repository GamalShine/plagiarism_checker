# Hostinger deployment

## 1. Folder layout

Recommended layout:

```text
/home/USERNAME/plagiarism_checker/  # the Laravel project, including app/, vendor/, storage/
/home/USERNAME/public_html/          # the contents of the project's public/ folder
```

Do not expose `.env`, `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, or `vendor/` under the public web root.

If Hostinger allows changing the domain document root, point it directly to:

```text
/home/USERNAME/plagiarism_checker/public
```

In that case no `index.php` path edit is needed.

If `public_html` must be the document root, copy the contents of `public/` into it and update `public_html/index.php` so both paths point to the project directory:

```php
require __DIR__.'/../plagiarism_checker/vendor/autoload.php';
$app = require_once __DIR__.'/../plagiarism_checker/bootstrap/app.php';
```

Replace `plagiarism_checker` with the actual folder name and use the correct parent path for the Hostinger account.

## 2. Environment

Copy `.env.hostinger.example` to `.env` on the server and fill in the Hostinger MySQL values and payment credentials. Do not upload the local `.env` unchanged.

Required production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://slategray-penguin-737813.hostingersite.com
```

The URL must not include `/public` when the domain already points to Laravel's public directory.

## 3. Install and initialize

Run from the Laravel project directory:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Make sure `storage/` and `bootstrap/cache/` are writable by PHP.

## 4. Queue processing

The plagiarism job uses the `plagiarism` queue. A shared Hostinger plan needs a cron job because a permanent worker may not be available. Configure a cron job every minute with the real account paths:

```bash
cd /home/USERNAME/plagiarism_checker && php artisan queue:work database --queue=plagiarism --stop-when-empty --timeout=300 --tries=1
```

The command must use the same PHP version selected for the domain. Do not run `queue:work` with the default queue only; plagiarism jobs are explicitly dispatched to `plagiarism`.

## 5. Storage and payment callbacks

Confirm that `https://slategray-penguin-737813.hostingersite.com/storage/...` can load a test uploaded file. Update DOKU/Midtrans callback or notification URLs in the provider dashboard to the HTTPS Hostinger domain. Do not use localhost or ngrok URLs in production.

## 6. Server limitations

PDF/DOCX conversion features that require Python or Microsoft Word may not work on shared Linux hosting. Keep `WORD_COM_ENABLED=false` unless the required server tools are actually installed. Test document upload, queue completion, payment callback, and PDF export after deployment.

## 7. PDF export on shared hosting

The PDF export can use CloudConvert to turn DOCX into a layout-preserving PDF, then overlay highlights on the converted PDF. Add a CloudConvert API key in Hostinger's private `.env` (never commit the key) and use these settings:

```env
PDF_LIGHTWEIGHT=false
WORD_COM_ENABLED=false
LIBREOFFICE_PATH=
CLOUDCONVERT_API_KEY=your-private-cloudconvert-api-key
CLOUDCONVERT_REQUEST_TIMEOUT=30
CLOUDCONVERT_MAX_WAIT_SECONDS=240
CLOUDCONVERT_POLL_INTERVAL_SECONDS=3
```

The uploaded DOCX is sent to CloudConvert for conversion. The application downloads the converted PDF, applies highlight overlays, merges it with the report, and requests immediate deletion of the CloudConvert job when finished. This uses CloudConvert credits and sends document contents to that third party; confirm that this is acceptable for your users/data before enabling it. If no API key is configured or CloudConvert fails, the renderer tries the configured LibreOffice converter and then PHPWord/Dompdf fallback.

For existing PDF uploads, the original PDF is used directly. Keep `PDF_LIGHTWEIGHT=false` so source pages and highlights are included instead of falling back to extracted/reflowed HTML text.

After deployment, clear the Laravel runtime cache:

```bash
php artisan optimize:clear
php artisan storage:link
```

After changing the conversion provider/key, delete cached export PDFs under `storage/app/public/exports/v*/` for checks that need regeneration. If CloudConvert is unavailable, PHP must have `shell_exec` and the host must provide LibreOffice at the configured `LIBREOFFICE_PATH`; otherwise use PDF uploads to retain source layout.
