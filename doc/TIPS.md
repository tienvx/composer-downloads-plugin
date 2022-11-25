# Tips

* In each downloaded folder, this plugin will create a small metadata folder (`.composer-downloads`) to track the origin of the current code. If you modify the `composer.json` to use a different URL, then it will re-download the file.

* Download each extra file to a distinct `path`. Don't try to download into overlapping paths. (*This has not been tested, but I expect downloads are not well-ordered, and you may find that updates require re-downloading.*)

* What should you do if you *normally* download the extra-file as `*.tar.gz` but sometimes (for local dev) need to grab bleeding edge content from somewhere else?  Simply delete the autodownloaded folder and replace it with your own.  `composer-downloads-plugin` will detect that conflict (by virtue of the absent `.composer-downloads`) and leave your code in place (until you choose to get rid of it). To switch back, you can simply delete the code and run `composer install` again.
