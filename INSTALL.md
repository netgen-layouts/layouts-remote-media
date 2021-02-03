# Netgen Layouts & Remote Media integration installation instructions

## Use Composer

Run the following command to install Netgen Layouts & Remote Media integration:

```
composer require netgen/layouts-remote-media
```

## Activate the bundles

Activate the bundle in your kernel class:

```
...
...

$bundles[] = new Netgen\Bundle\LayoutsRemoteMediaBundle\LayoutsRemoteMediaBundle();

return $bundles;
```
