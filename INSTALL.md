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

### Cache configuration

This bundle uses cache to store next cursor when fetching remote media, due to incompatibility between cursor-based pagination in Remote Media and limit/offset based pagination in Netgen Layouts.

You can manually configure cache pool as well as desired TTL:


```yaml
netgen_layouts_remote_media:
    cache:
        pool: cache.app
        ttl: 7200
```

Above shown are the default used parameters. For more information about creating and configuring cache pools, see https://symfony.com/doc/current/cache.html.

### Root folder configuration

This integration also contains the integration for content browser, and the left tree is used to browse
through folders on the selected provider (eg. Cloudinary).

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

## Import database tables

This bundle stores used resources in a separate table in the database. Use the following command to update your database:

```
php bin/console doctrine:schema:update
```

**Note:** Use the command with `--dump-sql` first to check that it will do only modifications related to this bundle, and then use it with parameter `--force` to do the actual changes.
