Composer Extra Files Plugin
===========================

This Composer plugin allows you to request additional files to be downloaded with your composer package.  The canonical use case is including compiled code that you don't want committed to your repository.

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