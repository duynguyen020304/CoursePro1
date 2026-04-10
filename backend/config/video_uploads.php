<?php

return [
    'disk' => env('AWS_VIDEO_DISK', 's3'),
    'prefix' => trim(env('AWS_VIDEO_PREFIX', 'videos'), '/'),
    'signed_url_ttl_seconds' => (int) env('AWS_VIDEO_SIGNED_URL_TTL_SECONDS', 900),
    'multipart_threshold_bytes' => (int) env('AWS_VIDEO_MULTIPART_THRESHOLD_BYTES', 52_428_800),
    'part_size_bytes' => (int) env('AWS_VIDEO_PART_SIZE_BYTES', 10_485_760),
    'max_file_size_bytes' => 524_288_000,
];
