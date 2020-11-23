# Netgen Layouts & Remote media integration installation instructions

## Use Composer

Run the following command to install Netgen Layouts & Remote media integration:

```
composer require netgen/layouts-remote-media
```

## Activate the bundles

Activate the Content Browser in your kernel class with all required bundles:

```
...
...

$bundles[] = new Netgen\Bundle\LayoutsRemoteMediaBundle\LayoutsRemoteMediaBundle();

return $bundles;
```
