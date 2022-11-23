# Tips

* In each downloaded folder, this plugin will create a small metadata folder (`.composer-downloads`) to track the origin of the current code. If you modify the `composer.json` to use a different URL, then it will re-download the file.

* Download each extra file to a distinct `path`. Don't try to download into overlapping paths. (*This has not been tested, but I expect downloads are not well-ordered, and you may find that updates require re-downloading.*)

* What should you do if you *normally* download the extra-file as `*.tar.gz` but sometimes (for local dev) need to grab bleeding edge content from somewhere else?  Simply delete the autodownloaded folder and replace it with your own.  `composer-downloads-plugin` will detect that conflict (by virtue of the absent `.composer-downloads`) and leave your code in place (until you choose to get rid of it). To switch back, you can simply delete the code and run `composer install` again.

* The handler for `archive` type will extract files into `path` directory
* The handlers for `file` and `phar` types will put the file at `path` (not into `path` directory)
* The handler for `gzip` type will decompress the file and put the decompressed file at `path` (not into `path` directory)

* The different between using `archive` type and `gzip` type for the file `exactly-this-file-name.gz` is:
  * The handler for `archive` type will put the decompressed file at `/path/to/exactly-this-file-name`
  * The handler for `gzip` type will put the decompressed file at `/path/to/any-thing-you-want`
