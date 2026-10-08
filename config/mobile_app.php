<?php

return [
    'apk_path' => env('MOBILE_APP_APK_PATH') ?: storage_path('app/mobile/app-release.apk'),
    'version' => env('MOBILE_APP_VERSION', ''),
];
