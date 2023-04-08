## PDF to Image Converter

PDF to Image Converter is a PHP package that allows you to convert PDF files to images. The package uses Imagick PHP extension and GhostScript to convert PDF files to images.

## Requirements

- PHP "^7.3|^8.0"
- Imagick PHP extension
  Enable the Imagick extension in your php.ini file by uncommenting or adding the following line:

```text
extension=imagick
```

- Ghostscript
  Verify that Ghostscript is installed by running the following command:

```bash
gs -v
```

This should display the version number of Ghostscript.

## Installation

You can install the package via composer:

```bash
composer require hyder/converter
```

## Configuration

You can publish the configuration file to customize the package settings. To publish the configuration file, run the following command:

```bash
php artisan vendor:publish --provider="Hyder\Converter\ConverterServiceProvider" --tag="config"

```

This will create a config/converter.php file where you can configure the package settings.

## Optional

The service provider will automatically get registered. Or you may manually add the service provider in your config/app.php file:

```php
'providers' => [
    // ...
    Hyder\Converter\ConverterServiceProvider::class,
];
```

## Usage

Here's an example of how to convert a PDF file to an image:

```php
use Hyder\Converter\Facades\PdfToImage;

// Minimalistic
PdfToImage::path('/path/to/file.pdf')
    ->save();

// Customize output
PdfToImage::path('/path/to/file.pdf')
    ->format('png')
    ->resolution(200)
    ->maxLimit(5)
    ->setPage('1-3,5')
    ->toDir('/path/to/directory')
    ->save('output-image-name');

```

## Available Methods

Here are the available methods of the package:

### path(string $path)

This method sets the path of the PDF file you want to convert to an image.

### format(string $format)

This method sets the output format of the image. The supported formats are JPEG, JPG, and PNG.

### resolution($dpi)

This method sets the resolution of the output image in dots per inch (dpi). The higher the resolution, the better the quality of the image, but also the larger the file size.

### maxLimit($max)

This method sets a maximum limit for the number of pages to be converted. If the PDF file has more pages than this limit, only the first $max pages will be converted.

### setPage($pages)

This method sets the pages of the PDF file that you want to convert. The pages can be specified as single page numbers (e.g. 1,2,3), a range of pages (e.g. 1-3), or a combination of both (e.g. 1,2,3-5).

### toDir(string $storageTo = '')

This method sets the directory where the converted images will be saved. If no directory is specified, the default directory will be used.

### save(string $name = '')

This method converts the PDF file to an image and saves it to the specified directory. If no name is specified, the image will be saved with a default name.

## Error Handling

After enable imagick you may get an error in live service. ImageMagick has some security policies disabling some rights for security reasons.
You will have to edit a config file to re-enble the action you need.

Open /etc/ImageMagick-6/policy.xml with your favorite text editor, find the line <policy domain="coder" rights="none" pattern="PDF" /> and replace "none" by "read|write"

### Open the file

```bash
sudo nano /etc/ImageMagick-6/policy.xml
```

### Find and edit the line

```text
<policy domain="coder" rights="none" pattern="PDF" />
```

### To

```text
<policy domain="coder" rights="read|write" pattern="PDF" />
```

You can [check Here](https://askubuntu.com/questions/1127260/imagemagick-convert-not-allowed#:~:text=ImageMagick%20has%20some,write%22%20pattern%3D%22PDF%22%20/%3E) for better understanding.

## Credits

Imagick

## License

The PDF to Image Converter package is open-source software licensed under the MIT license.
