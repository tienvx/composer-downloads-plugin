Composer Extra Files Plugin
===========================

This Composer plugin allows you to request additional files to be downloaded with your composer package.

It can be used in the root-package *and* in any transitive dependencies.

## When should I use this?

The most common use case is if you have compiled front-end code, where the compiled version is never committed to a git repository, and therefore isn't registered on packagist.org.  For example, if you want your distributed package to depend on an NPM/Bower package.

Note: You probably shouldn't use this if you have the ability to [add repositories](https://getcomposer.org/doc/05-repositories.md) to your project's root composer.json.  There are better alternatives for this.  See [Composer Asset Plugin](https://github.com/fxpio/composer-asset-plugin) and [Asset Packagist](https://asset-packagist.org/).  This plugin is most useful for packages that are required by other packages, since Composer doesn't allow nested repositories.

## Usage: Define the list of files

In your package's composer.json, require this plugin, and specify the extra files in the "extra" section:
```json
{
  ... 
  "require": {
    "lastcall/composer-extra-files": "~1.0"
  }
  "extra": {
    "extra-files": {
      "ui": {
        "url": "https://registry.npmjs.org/lastcall-mannequin-ui/-/lastcall-mannequin-ui-1.0.0-rc2.tgz",
        "path": "ui"
      }
    }
  }
  ...
}
```

The `ui` identifier here is an arbitrary ID for each dependency.

The `url` key specifies the URL to fetch the content from.  If it points to a tarball or zip file, it will be unpacked on downloading.

The `path` key specifies the folder (relative to where your package is installed in `/vendor`) that the content is installed into.

## Usage: Download the files

Simply run `composer install` or `composer update`.

## Tips

In each downloaded folder, this plugin will create a small metadata file (`.composer-extra-files.json`) to track the origin of the
current code. If you modify the `composer.json` to use a different URL, then it will re-download the file.

Download each extra file to a distinct `path`. Don't try to download into overlapping paths. (This has not been tested, and the result is probably unpleasant.)

What should you do if you *normally* download the extra-file as `*.tgz` but sometimes (for local dev) need to grab bleeding edge content from
somewhere else?  Simply delete the autodownloaded folder and replace it with your own.  `composer-extra-files` will detect that conflict (by virtue
of the absent `.composer-extra-files.json`) and leave your code in place (until you choose to get rid of it). To switch back, you can
simply delete the code and run `composer install` again.

## Known Limitations

If you use `extra-files` in a root-project (or in symlinked dev repo), it will create+update extra-files, but it will not remove orphaned items
automatically.  This could be addressed by doing a file-scan for `.composer-extra-files.json` (and deleting any orphan folders).  Since the edge-case
is not particularly common right now, and since a file-scan could be time-consuming, it might make sense as a separate subcommand.

I believe the limitation does *not* affect downstream consumers of a dependency. In that case, the regular `composer` install/update/removal mechanics should take care of any nested extra-files. However, this is a little tricky to test right now.
