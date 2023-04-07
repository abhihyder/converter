<?php

namespace Hyder\Converter\Services;

use Hyder\Converter\Converters\PdfToImageConverter;
use Illuminate\Support\Facades\Config;
use SplFileInfo;

class PdfToImageService
{

    private $totalPage = 1;

    private $pageNumber = [1];

    private $format = '';

    private $tempFilePath = '';

    private PdfToImageConverter $pdf;

    public function path(string $path)
    {
        try {

            // $file = new SplFileInfo($path);
            // $file_type = $file->getExtension();

            // $this->tempFilePath = tempnam(sys_get_temp_dir(), $file_type);
            // file_put_contents($this->tempFilePath, file_get_contents($path));
            // $path = $this->tempFilePath;

            // if the path is invalid ------------------------------
            if (!file_exists($path)) {
                return throw new \Exception("Invalid file path provided", 404);
            }
            $this->pdf = new PdfToImageConverter($path);

            // Image format ------------------------------
            $supportedFormats = $this->pdf->getValidOutputFormats();
            $this->format = $supportedFormats[0];
            
            // Count the number of pages ---------------------
            $this->totalPage = $this->pdf->getNumberOfPages();
            return $this->totalPage;

            return $this;
        } catch (\Exception $ex) {
            // unlink($this->tempFilePath);
            return $ex->getMessage();
            return throw new \Exception($ex->getMessage());
        }
    }

    public function format(string $format)
    {
        if (in_array($format, $this->pdf->getValidOutputFormats())) {
            $this->format = $format;
        } else {
            return throw new \Exception("Unsupported format!", 400);
        }

        return $this;
    }

    public function resolution(int $value)
    {
        $this->pdf->setResolution($value);

        return $this;
    }

    public function setPage($pages)
    {
        try {
            return  $this->setPageNumber($pages);
        } catch (\Exception $ex) {
            return throw new \Exception($ex->getMessage());
        }
    }


    public function save(string $storagePath = '')
    {
        try {

            $image_details = [];
            $folderPath = Config::get('converter.pdf_to_image.storage_path');
            $maxLimit = Config::get('converter.pdf_to_image.max_limit', null);
            foreach ($this->pageNumber as $key => $index) {

                // If the index is empty --------------------
                if (empty($index)) {
                    continue;
                }

                $imageName = "page_" . $index . "_" . time() . "." . $this->format;
                // Generate image url to store -----------------------------
                $image_path = $folderPath . $imageName;

                // Generate image --------------------------------
                $this->pdf->setPage($index)->saveImage($image_path);

                // assign image to an array --------------------------------
                $image_details[] = [
                    'file_size_in_kb' => number_format(filesize($image_path) / 1024, 2),
                    'file_dir' =>  $folderPath,
                    'file_name' => $imageName
                ];

                if ($maxLimit && $key === $maxLimit) {
                    break;
                }
            }
            unlink($this->tempFilePath);
            // Success response with image details --------------------
            return $$image_details;
        } catch (\Exception $ex) {
            unlink($this->tempFilePath);
            return throw new \Exception($ex->getMessage(), 500);
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
            return $this;
        } else {
            return throw new \Exception('Invalid page format! Example: "1" or "1-9" or "1,5,7" or "1,7-9" etc.', 400);
        }
    }

    public function getPageNumber()
    {
        var_dump($this->pageNumber);
    }
}

