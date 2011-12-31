# Imagefly

This module allows you to quickly create resized / cropped images directly through url parameters. Modified images are cached after the initial request and served up thereafter to help reduce server strain.

## Getting started

In your `application/bootstrap.php` file modify the call to Kohana::modules and include the image and imagefly modules.

    Kohana::modules(array(
        ...
        'image'    => MODPATH.'image',
        'imagefly' => MODPATH.'imagefly',
        ...
    ));

[!!] The image module is requried for the Imagefly module to work.

## Notes

* Imagefly will not process images when the width and height prams are the same as the source
* Images are scaled up if the supplied width or height params are lager then the source width or height 
* Don't forget to make your cache directory writable.
* Inspired by the [smart-lencioni-image-resizer](http://code.google.com/p/smart-lencioni-image-resizer/) by Joe Lencioni

## Compatibility

Imagefly currently works with Kohana 3.2 only.

For Kohana 3.0.x and 3.1.x users, a no longer maintained version can be found [here](http://code.google.com/p/kohana-3-imagefly/)

## Configuration

TODO...

## Usage

TODO...
