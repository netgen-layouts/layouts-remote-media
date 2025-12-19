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

## Configure the bundle

### Root folder

This integration also contains the integration for content browser, and the left tree is used to browse
through folders on the selected provider (eg. Clodinary).

If you don't want to expose all folders available, this parameter allows you to configure the root folder,
so that you can see only folders that are below that one.

**NOTE:** this only limits the access to folders in the left tree, but it doesn't prevent you to access
resources outside of this configured folder - you can still find it via search.

```yaml
netgen_layouts_remote_media:
    root_folder: "images/layouts"
```

By default, this parameter is empty (null) so it will fetch all folders.

**IMPORTANT:** when using Cloudinary provider, content browser's folder tree is doing lots of heavy queries to 
the API in the background. If you have lots of folders in the root, using this parameter might be
mandatory, to prevent timeouts or breaking API limits.
