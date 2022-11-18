# Known Limitations

If you use `downloads` in a root-project (or in symlinked dev repo), it will create+update downloads, but it will not remove orphaned items automatically.  This could be addressed by doing a file-scan for `.composer-downloads` (and deleting any orphan folders).  Since the edge-case is not particularly common right now, and since a file-scan could be time-consuming, it might make sense as a separate subcommand.

I believe the limitation does *not* affect downstream consumers of a dependency. In that case, the regular `composer` install/update/removal mechanics should take care of any nested downloads.
