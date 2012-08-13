#Twootstrap Asset Manager
## About Twootstrap
Twootstrap is an ongoing Kohana module development project that is aimed at creating a module for seemlessly merging Kohana 3 and [Twitter Bootstrap](http://twitter.github.com/bootstrap).

## Asset Manager
The asset manager is a decoupled part of ongoing development of Twootstrap. It helps you effectively manage assets (stylesheets and scripts) on your Kohana application.

## What are the features?
 - Merges seemlessly with Kohana because it follows Kohanas coding practices.
 - Asset tagging, that allows you to queue and call assets in your view files based on tags e.g footer, header, inline etc.
 - Method chaining, helps you create cleaner codes.
 - Asset dependency, allows you queue assets with dependency to other assets.
 - Automatically discerns the asset type (for js and css only)
 - Even more...

## Installation
Copy the **twootstrap** module into your **modules** folder. Go to your applications *bootstrap.php* file and **activate twootstrap** by adding **Twootstrap** to your modules array. Example:

    Kohana::modules(array(
        // ...other modules...

        'twootstrap' => MODPATH.'twootstrap',

        // ..other modules...
    ));

If you want to use **Twootstrap** in your modules, make sure you add it before any module you want to use **Twootstrap** in.

[How To Use Twootstrap Asset Manager &#187;](using)