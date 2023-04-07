<?php

namespace Hyder\Converter\Facades;

use Illuminate\Support\Facades\Facade;

class PdfToImage extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'pdf-to-image-service';
    }
}
