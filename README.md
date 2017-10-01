Composer Extra Files Plugin
===========================

This Composer plugin allows you to request additional files to be downloaded with your composer package.

### When should I use this?
The most common use case is if you have compiled front-end code, where the compiled version is never committed to a git repository, and therefore isn't registered on packagist.org.  For example, if you want your distributed package to depend on an NPM/Bower package.

Note: You probably shouldn't use this if you have the ability to [add repositories](https://getcomposer.org/doc/05-repositories.md) to your project's root composer.json.  There are better alternatives for this.  See [Composer Asset Plugin](https://github.com/fxpio/composer-asset-plugin) and [Asset Packagist](https://asset-packagist.org/).  This plugin is most useful for packages that are required by other packages, since Composer doesn't allow nested repositories.

## Usage
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
The `url` key specifies the URL to fetch the content from.  If it points to a tarball or zip file, it will be unpacked on downloading.

The `path` key specifies the folder (relative to where your package is installed in /vendor) that the content is installed into.