<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditRedirectController extends Controller
{
    private function buildHtml(string $title, string $message, string $deepLink, string $fallbackUrl): string
    {
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $escapedDeepLink = htmlspecialchars($deepLink, ENT_QUOTES, 'UTF-8');
        $escapedFallback = htmlspecialchars($fallbackUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<title>{$escapedTitle}</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 2rem; background: #f9fafb; color: #111827; }
  .card { max-width: 560px; margin: 4rem auto; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); padding: 2rem; text-align: center; }
  h1 { font-size: 1.25rem; margin: 0 0 0.5rem; }
  p { color: #4b5563; }
  .actions { display: flex; gap: 0.75rem; justify-content: center; margin-top: 1.25rem; flex-wrap: wrap; }
  .btn { display: inline-block; padding: 0.75rem 1rem; border-radius: 10px; text-decoration: none; font-weight: 600; }
  .btn-primary { background: #16a34a; color: #fff; }
  .btn-secondary { background: #e5e7eb; color: #111827; }
  .hint { margin-top: 0.75rem; font-size: 0.875rem; color: #6b7280; }
</style>
<script>
  // Try to open the app shortly after load
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function(){ window.location.href = '{$escapedDeepLink}'; }, 100);
  });
</script>
</head>
<body>
  <div class="card">
    <h1>{$escapedTitle}</h1>
    <p>{$escapedMessage}</p>
    <div class="actions">
      <a class="btn btn-primary" href="{$escapedDeepLink}">Open App</a>
      <a class="btn btn-secondary" href="{$escapedFallback}" target="_self">Open on Web</a>
    </div>
    <p class="hint">If nothing happens, tap "Open App". You can also continue on web.</p>
    <noscript>
      <p>JavaScript is disabled. Use one of the buttons above.</p>
    </noscript>
  </div>
</body>
</html>
HTML;
    }

    public function success(Request $request)
    {
        $externalId = (string) $request->query('external_id', '');
        if ($externalId === '') {
            return response('Missing external_id', 400);
        }

        $scheme = env('MOBILE_APP_SCHEME', 'nutrifarm');
        $successPath = trim(env('MOBILE_APP_SUCCESS_PATH', 'orders'), '/');
        $baseWeb = rtrim(env('APP_WEB_REDIRECT_BASE', config('app.url')), '/');

        $deepLink = sprintf('%s://%s?external_id=%s', $scheme, $successPath, rawurlencode($externalId));
        $fallbackUrl = $baseWeb . '/orders';

        $html = $this->buildHtml(
            'Payment Success - Open Nutrifarm',
            'Payment completed. We will open your order history in the app.',
            $deepLink,
            $fallbackUrl
        );
        return response($html)->header('Content-Type', 'text/html');
    }

    public function failure(Request $request)
    {
        $externalId = (string) $request->query('external_id', '');
        $reason = (string) $request->query('reason', 'failed');

        $scheme = env('MOBILE_APP_SCHEME', 'nutrifarm');
        $failurePath = trim(env('MOBILE_APP_FAILURE_PATH', 'cart'), '/');
        $baseWeb = rtrim(env('APP_WEB_REDIRECT_BASE', config('app.url')), '/');

        $query = http_build_query([
            'external_id' => $externalId,
            'reason' => $reason,
        ]);
        $deepLink = sprintf('%s://%s?%s', $scheme, $failurePath, $query);
        $fallbackUrl = $baseWeb . '/cart';

        $html = $this->buildHtml(
            'Payment Failed - Open Nutrifarm',
            'Payment failed or was cancelled. You can retry from cart in the app.',
            $deepLink,
            $fallbackUrl
        );
        return response($html)->header('Content-Type', 'text/html');
    }
}
