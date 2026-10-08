<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

URL::forceRootUrl((string) config('app.url'));

$applicationPath = trim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

$serveFile = static function (string $file) {
    $realPath = realpath($file);

    abort_if($realPath === false || !is_file($realPath), 404);

    $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $mimeType = match ($extension) {
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'svg' => 'image/svg+xml',
        default => mime_content_type($realPath) ?: 'application/octet-stream',
    };

    return response()->file($realPath, ['Content-Type' => $mimeType]);
};

$legacyRoutes = static function () use ($serveFile): void {
Route::get('/download-app', static function () {
    $apkPath = (string) config('mobile_app.apk_path');
    $version = (string) config('mobile_app.version');

    abort_if(
        $apkPath === ''
        || strtolower(pathinfo($apkPath, PATHINFO_EXTENSION)) !== 'apk'
        || !is_file($apkPath)
        || !is_readable($apkPath),
        404,
        'APK Lost & Found IFSU belum tersedia.'
    );

    $safeVersion = preg_replace('/[^A-Za-z0-9._-]+/', '-', $version);
    $fileName = 'lost-and-found-ifsu'
        . ($safeVersion !== '' ? '-v' . $safeVersion : '')
        . '.apk';

    return response()->download($apkPath, $fileName, [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Cache-Control' => 'private, no-store',
    ]);
});

Route::get('/public/assets/{path}', static function (string $path) use ($serveFile) {
    $assetRoot = realpath(public_path('assets'));
    $file = realpath(public_path('assets/' . $path));

    abort_if(
        $assetRoot === false
        || $file === false
        || !str_starts_with($file, $assetRoot . DIRECTORY_SEPARATOR),
        404
    );

    return $serveFile($file);
})->where('path', '.*');

Route::get('/{type}/{path}', static function (string $type, string $path) use ($serveFile) {
    abort_unless(in_array($type, ['css', 'js', 'images'], true), 404);

    $assetRoot = realpath(public_path('assets/' . $type));
    $file = realpath(public_path('assets/' . $type . '/' . $path));

    abort_if(
        $assetRoot === false
        || $file === false
        || !str_starts_with($file, $assetRoot . DIRECTORY_SEPARATOR),
        404
    );

    return $serveFile($file);
})->where([
    'type' => 'css|js|images',
    'path' => '.*',
]);

Route::get('/uploads/{path}', static function (string $path) use ($serveFile) {
    $uploadRoot = realpath(base_path('uploads'));
    $file = realpath(base_path('uploads/' . $path));

    abort_if(
        $uploadRoot === false
        || $file === false
        || !str_starts_with($file, $uploadRoot . DIRECTORY_SEPARATOR),
        404
    );

    return $serveFile($file);
})->where('path', '.*');

Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], '/api/{endpoint}.php', static function (string $endpoint) {
    $allowedEndpoints = [
        'aktivitas',
        'auth',
        'barang',
        'foto',
        'klaim',
        'laporan',
        'users',
    ];

    abort_unless(in_array($endpoint, $allowedEndpoints, true), 404);

    ob_start();
    require base_path('api/' . $endpoint . '.php');
    $content = ob_get_clean();

    $response = response($content, http_response_code() ?: 200);

    foreach (headers_list() as $header) {
        if (preg_match('/^(Content-Type|Access-Control-Allow-[^:]+):\s*(.*)$/i', $header, $matches)) {
            $response->headers->set($matches[1], $matches[2]);
        }
    }

    return $response;
})->where('endpoint', '[a-z-]+');

foreach (['forgot-password', 'reset-password', 'set-password'] as $script) {
    Route::any('/' . $script . '.php', static function () use ($script) {
        $bufferLevel = ob_get_level();
        ob_start();

        try {
            require base_path($script . '.php');
            $content = ob_get_clean();
        } catch (LegacyRedirectException $exception) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            return redirect()->to($exception->getMessage());
        }

        return response($content, http_response_code() ?: 200);
    });
}

Route::any('/{path?}', static function (?string $path = null) {
    $path = trim((string) $path, '/');
    $_GET['url'] = $path === '' ? 'home' : $path;
    $_SERVER['REQUEST_URI'] = '/' . $path;
    $_SERVER['REQUEST_METHOD'] = request()->method();

    $bufferLevel = ob_get_level();
    ob_start();

    try {
        require base_path('legacy/front-controller.php');
        $content = ob_get_clean();
    } catch (LegacyRedirectException $exception) {
        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }

        return redirect()->to($exception->getMessage());
    }

    return response($content, http_response_code() ?: 200);
})->where('path', '.*');
};

$legacyRoutes();

if ($applicationPath !== '') {
    Route::prefix($applicationPath)->group($legacyRoutes);
}
