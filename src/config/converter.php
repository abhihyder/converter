<?php

return [
    'pdf_to_image' => [
        'storage_path' => 'public_path', // Set where the converted images should be stored. Options are 'public_path' or 'storage_path'.
        'default_dir' => 'images', // Set the default directory where the converted images should be stored.
        'format' => 'jpg', // Set the image format of the converted images. Supported formats are jpg, jpeg, and png. Default is jpg.
        'resolution' => 144, // Set the resolution of the converted images. Default is 144.
        'max_limit' => null, // Set the maximum number of pages that can be converted. Set to null for no limit. Default is null.
    ],
];
