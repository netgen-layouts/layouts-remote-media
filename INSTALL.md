# Netgen Content Browser & Cloudinary integration installation instructions

## Use Composer

Run the following command to install Netgen Content Browser & Cloudinary integration:

```
composer require netgen/content-browser-cloudinary
```

## Activate the bundles

Activate the Content Browser in your kernel class with all required bundles:

```
...
...

$bundles[] = new Netgen\Bundle\ContentBrowserBundle\NetgenContentBrowserBundle();
$bundles[] = new Netgen\Bundle\ContentBrowserCloudinaryBundle\NetgenContentBrowserCloudinaryBundle();

return $bundles;
```
