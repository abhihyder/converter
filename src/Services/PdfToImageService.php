<?php

namespace Hyder\Converter\Services;

use Hyder\Converter\Converters\PdfToImageConverter;
use Illuminate\Support\Facades\Config;
use SplFileInfo;

class PdfToImageService
{
    protected $outputFormat = 'jpg';

    private $totalPage = 1;

    private $pageNumber = [1];

    private $maxLimit = null;

    private $toDirectory = '';

    private $tempFilePath = '';

    private PdfToImageConverter $pdf;

    public function __construct()
    {
        $this->toDirectory = $this->makeDirectory(Config::get('converter.pdf_to_image.default_dir'));

        $this->maxLimit = Config::get('converter.pdf_to_image.max_limit');
    }

    public function path(string $path)
    {
        try {

            $this->makeTempFile($path);

            $this->pdf = new PdfToImageConverter($this->tempFilePath);

            // Count the number of pages ---------------------
            $this->totalPage = $this->pdf->getNumberOfPages();

            return $this;
        } catch (\Exception $ex) {
            return throw new \Exception($ex->getMessage());
        }
    }

    public function format(string $format)
    {
        if (in_array($format, $this->pdf->getValidOutputFormats())) {
            $this->outputFormat = $format;
        } else {
            return throw new \Exception("Unsupported format!", 400);
        }

        return $this;
    }

    public function maxLimit(int $max)
    {
        $this->maxLimit = $max;

        return $this;
    }

    public function resolution(int $dpi)
    {
        $this->pdf->setResolution($dpi);

        return $this;
    }

    public function setPage($pages)
    {
        try {
            $this->setPageNumber($pages);
            return  $this;
        } catch (\Exception $ex) {
            return throw new \Exception($ex->getMessage());
        }
    }

    public function allPage()
    {
        try {
            $this->pageNumber = range(1, $this->totalPage);
            $this->maxLimit = null;
            return $this;
        } catch (\Exception $ex) {
            return throw new \Exception($ex->getMessage());
        }
    }

    public function toDir(string $storageTo)
    {
        try {
            $this->toDirectory = $this->makeDirectory($storageTo);
            return $this;
        } catch (\Exception $ex) {
            return throw new \Exception($ex->getMessage());
        }
    }

    public function save(string $name = '')
    {
        try {

            $outputDetails = [];
            $folderPath = $this->toDirectory;

            foreach ($this->pageNumber as $key => $index) {

                // If the index is empty --------------------
                if (empty($index)) {
                    continue;
                }

                if (!empty($name)) {
                    $imageName = $name . "." . $this->outputFormat;
                    if (file_exists($folderPath . '/' . $imageName)) {
                        $imageName = $name . "_" . time() . "." . $this->outputFormat;
                    }
                } else {
                    $imageName = "page_" . $index . "_" . time() . "." . $this->outputFormat;
                }

                // Generate image url to store -----------------------------
                $image_path = $folderPath . '/' . $imageName;

                // Generate image --------------------------------
                $this->pdf->setPage($index)->saveImage($image_path);

                // assign image to an array --------------------------------
                $outputDetails[] = [
                    'file_dir' =>  $folderPath,
                    'file_name' => $imageName
                ];

                if ($this->maxLimit && $key === $this->maxLimit) {
                    break;
                }
            }
            unlink($this->tempFilePath);
            // Success response with image details --------------------
            return $outputDetails;
        } catch (\Exception $ex) {
            unlink($this->tempFilePath);
            return throw new \Exception($ex->getMessage());
        }
    }

    private function makeTempFile($path)
    {
        try {

            $file = new SplFileInfo($path);
            $file_type = $file->getExtension();

            $this->tempFilePath = tempnam(sys_get_temp_dir(), $file_type);
            file_put_contents($this->tempFilePath, file_get_contents($path));

            // if the path is invalid ------------------------------
            if (!file_exists($this->tempFilePath)) {
                return throw new \Exception("Invalid file path provided");
            }

            return true;
        } catch (\Exception $ex) {
            return throw new \Exception($ex->getMessage());
        }
    }

    private function setPageNumber($index)
    {
        $preg_match = preg_match("/^[0-9,\-]+$/", $index);
        if ($preg_match) {
            if (str_contains($index, ',') && str_contains($index, '-')) {
                $pages = explode(',', $index);
                $index = [];
                foreach ($pages as $page) {
                    if ($page) {
                        if (str_contains($page, '-')) {
                            $page = explode('-', $page);
                            $range = range($page[0], $page[1]);
                            $index = array_merge($index, $range);
                        } else {
                            $index = array_merge($index, [intVal($page)]);
                        }
                    }
                }
            } else if (str_contains($index, ',')) {
                $pages = explode(',', $index);
                $index = [];
                foreach ($pages as $page) {
                    if ($page) {
                        $index[] = intVal($page);
                    }
                }
            } else if (str_contains($index, '-')) {
                $index = explode('-', $index);
                $index = range($index[0], $index[1]);
            } else {
                $pages = explode(',', $index);
                $index = [];
                foreach ($pages as $page) {
                    $index[] = intVal($page);
                }
            }
            $index = array_unique($index);
            sort($index);

            // If page index greater than total number of pages
            if (max($index) > $this->totalPage) {
                return throw new \Exception("Page number greater than total number of pages ($this->totalPage).", 400);
            }

            $this->pageNumber = $index;
            return true;
        } else {
            return throw new \Exception('Invalid page format! Example: "1" or "1-9" or "1,5,7" or "1,7-9" etc.', 400);
        }
    }

    private function makeDirectory(string $path)
    {
        try {

            $storagePath = Config::get('converter.pdf_to_image.storage_path')();

            $paths = explode('/', $path);

            foreach ($paths as $path) {
                if (empty($path)) {
                    continue;
                }
                $storagePath = $storagePath . '/' . $path;
                if (!is_dir($storagePath) && !mkdir($storagePath, 0775, true)) {
                    return throw new \Exception("Failed to create directory: {0}", $storagePath);
                }
            }

            return $storagePath;
        } catch (\Exception $ex) {
            return throw new \Exception($ex->getMessage(), 500);
        }
    }
}
