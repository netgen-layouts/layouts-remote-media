# Netgen Layouts & Remote Media integration installation instructions

## Use Composer to install the integration

Run the following command to install Netgen Layouts & Remote Media integration:

```
composer require netgen/layouts-remote-media
```

## Activate the bundle

Activate the bundle in your kernel class. Make sure it is activated after all
other Netgen Layouts and Content Browser bundles:

```
...
...

$bundles[] = new Netgen\Bundle\LayoutsRemoteMediaBundle\LayoutsRemoteMediaBundle();

return $bundles;
```
