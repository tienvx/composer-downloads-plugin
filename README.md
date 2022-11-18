Composer Downloads Plugin
===========================

This plugin allows you to download extra files and extract them within your package.

This is an updated version of [civicrm/composer-downloads-plugin](https://github.com/civicrm/composer-downloads-plugin).
It adds support for more archive files and allow custom variables.

## Example

Suppose your PHP package `foo/bar` relies on an external archive file (`examplelib-1.1.0-windows-amd64.zip` on Windows, or `examplelib-1.1.0-linux-x86_64.tar.gz` on Linux, or `examplelib-1.1.0-darwin-x86_64.tar.gz` on MacOS):

```json
{
  "name": "foo/bar",
  "require": {
    "tienvx/composer-downloads-plugin": "^1.0"
  },
  "extra": {
    "downloads": {
      "examplelib": {
        "url": "https://example.com/examplelib-{$version}-{$os}-{$architecture}.{$extension}",
        "path": "extern/{$id}",
        "version": "1.1.0",
        "variables": {
            "{$os}": "strtolower(PHP_OS_FAMILY)",
            "{$architecture}": "strtolower(php_uname('m'))",
            "{$extension}": "PHP_OS_FAMILY === 'Windows' ? 'zip' : 'tar.gz'",
        },
        "ignore": ["tests", "doc", ".*"]
      }
    }
  }
}
```

When a downstream user of `foo/bar` runs `composer require foo/bar`, it will download and extract the archive file to `vendor/foo/bar/extern/examplelib`. 

## Configuration:

* `url`: The URL to the extra file.

* `path`: The releative path where content will be extracted.

* `type`: (*Optional*) Determines how the download is handled. If omit, the extension in `url` will be used to detect.
    * `archive`: The archive file `url` will be downloaded and extracted to `path`.
    * `file`: The file `url` will be downloaded and placed at `path`.
    * `phar`: The PHP executable file `url` will be downloaded and placed at `path`.

* `ignore`: (*Optional*) A list of a files that should be omited from the extracted folder. (This supports a subset of `.gitignore` notation.)

* `version`: (*Optional*) A version number for the downloaded artifact. This has no functional impact on the lifecycle of the artifact, but
   it can affect the console output, and it can be used as a variable.

* `variables`: (*Optional*) List of custom variables.

## Variables

### Supported Configuration

Only following configuration support variables:

* `url`
* `path`

### Default Variables

* `{$id}`: The identifier of the download. (In the example, it would be `examplelib`.)
* `{$version}`: Just a text defined in the `version` configuration, if not defined, the value will be empty string (`""`).

### Custom Variables

* The format will be `"{$variable-name}": "EXPRESSION-SYNTAX-EVALUATED-TO-STRING"`
* More about the syntax at [Expression Syntax](https://github.com/leongrdic/php-smplang#expression-syntax).
* The syntax must be evaluated into a `string`.

## Defaults Configuration

You may set default properties for all downloads. Place them under `*`, as in:

```json
{
  "extra": {
    "downloads": {
      "*": {
        "path": "bower_components/{$id}",
        "ignore": ["test", "tests", "doc", "docs"],
        "variables": {
          "{$extension}": "zip"
        }
      },
      "jquery": {
        "url": "https://github.com/jquery/jquery-dist/archive/1.12.4.{$extension}"
      },
      "jquery-ui": {
        "url": "https://github.com/components/jqueryui/archive/1.12.1.{$extension}"
      }
    }
  }
}
```

## Document

See more at [Doc](./doc/)

## Contributing

Pull requests are welcome, please [send pull requests](https://github.com/tienvx/composer-downloads-plugin/pulls).

If you found any bug, please [report issues](https://github.com/tienvx/composer-downloads-plugin/issues).

## Authors

* **Rob Bayliss** - [Composer Extra Files](https://github.com/LastCallMedia/ComposerExtraFiles/graphs/contributors)
* **Tim Otten** and contributors - [Composer Download Plugin](https://github.com/civicrm/composer-downloads-plugin/graphs/contributors)
* **Tien Vo** and contributors - [this project](https://github.com/tienvx/composer-downloads-plugin/graphs/contributors)

## License

This package is available under the [MIT license](LICENSE).
