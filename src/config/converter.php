<?php

return [
    'pdf_to_image' => [
        'storage_path' => storage_path('images'),
        'format' => 'jpg', // Supported formats are jpg, jpeg, and png. Default is jpg.
        'resolution' => 144, // Default.
        'max_limit' => null, // Default. You can set the maximum page can be converted.
    ],
];
