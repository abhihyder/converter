<?php

namespace Hyder\Converter\Converters;

use Hyder\Converter\Exception\InvalidFormat;
use Hyder\Converter\Exception\PdfDoesNotExist;
use Hyder\Converter\Exception\PageDoesNotExist;
use Illuminate\Support\Facades\Config;
use Imagick;

class PdfToImageConverter
{
    protected $pdfFile;
    
    protected $resolution;
    
    protected $outputFormat = 'jpg';

    protected $page = 1;

    protected $validOutputFormats = ['jpg', 'jpeg', 'png'];


    public function __construct($pdfFile)
    {
        if (!file_exists($pdfFile)) {
            throw new PdfDoesNotExist();
        }

        $this->resolution = Config::get('converter.pdf_to_image.resolution') ?? 144;

        $this->pdfFile = $pdfFile;
    }

    /**
     * Set the raster resolution.
     *
     * @param int $resolution
     *
     * @return $this
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }


    public function getValidOutputFormats()
    {
        return $this->validOutputFormats;
    }

    public function setOutputFormat($outputFormat)
    {
        if (!$this->isValidOutputFormat($outputFormat)) {
            throw new InvalidFormat('Format ' . $outputFormat . ' is not supported');
        }

        $this->outputFormat = $outputFormat;

        return $this;
    }

    /**
     * Determine if the given format is a valid output format.
     *
     * @param $outputFormat
     *
     * @return bool
     */
    public function isValidOutputFormat($outputFormat)
    {
        return in_array($outputFormat, $this->validOutputFormats);
    }

    public function setPage($page)
    {
        if ($page > $this->getNumberOfPages()) {
            throw new PageDoesNotExist('Page ' . $page . ' does not exist');
        }

        $this->page = $page;

        return $this;
    }

    /**
     * Get the number of pages in the pdf file.
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return (new Imagick($this->pdfFile))->getNumberImages();
    }

    /**
     * Save the image to the given path.
     *
     * @param string $pathToImage
     *
     * @return bool
     */
    public function saveImage($pathToImage)
    {
        $imageData = $this->getImageData($pathToImage);
        return file_put_contents($pathToImage, $imageData) === false ? false : true;
    }

    /**
     * Save the file as images to the given directory.
     *
     * @param string $directory
     * @param string $prefix
     *
     * @return array $files the paths to the created images
     */
    public function saveAllPagesAsImages($directory, $prefix = '')
    {
        $numberOfPages = $this->getNumberOfPages();

        if ($numberOfPages === 0) {
            return [];
        }

        return array_map(function ($pageNumber) use ($directory, $prefix) {
            $this->setPage($pageNumber);

            $destination = "{$directory}/{$prefix}{$pageNumber}.{$this->outputFormat}";

            $this->saveImage($destination);

            return $destination;
        }, range(1, $numberOfPages));
    }

    /**
     * Return raw image data.
     *
     * @param string $pathToImage
     *
     * @return Imagick
     */
    public function getImageData($pathToImage)
    {
        $imagick = new Imagick();

        $imagick->setResolution($this->resolution, $this->resolution);

        $imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $this->page - 1));

        $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        $imagick->setFormat($this->determineOutputFormat($pathToImage));

        return $imagick;
    }

    /**
     * Determine in which format the image must be rendered.
     *
     * @param $pathToImage
     *
     * @return string
     */
    protected function determineOutputFormat($pathToImage)
    {
        $outputFormat = pathinfo($pathToImage, PATHINFO_EXTENSION);

        if ($this->outputFormat != '') {
            $outputFormat = $this->outputFormat;
        }

        $outputFormat = strtolower($outputFormat);

        if (!$this->isValidOutputFormat($outputFormat)) {
            $outputFormat = 'jpg';
        }

        return $outputFormat;
    }
}
