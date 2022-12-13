<?php

namespace LastCall\DownloadsPlugin;

use Composer\Installer\BinaryInstaller;
use Composer\IO\IOInterface;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;

class BinariesInstaller
{
    public function install(Subpackage $subpackage, IOInterface $io): void
    {
        $baseDir = $subpackage->getParentPath();
        foreach ($subpackage->getExecutable() as $bin) {
            $path = $baseDir.\DIRECTORY_SEPARATOR.$bin;
            if (Platform::isWindows() || (method_exists(Platform::class, 'isWindowsSubsystemForLinux') ? Platform::isWindowsSubsystemForLinux() : false)) {
                $proxy = $path.'.bat';
                if (file_exists($proxy)) {
                    $io->writeError('    Skipped installation of bin '.$bin.'.bat proxy for package '.$subpackage->getName().': a .bat proxy was already installed');
                } else {
                    $caller = BinaryInstaller::determineBinaryCaller($path);
                    file_put_contents($proxy, '@'.$caller.' "%~dp0'.ProcessExecutor::escape(basename($proxy, '.bat')).'" %*');
                }
            } else {
                chmod($path, 0777 ^ umask());
            }
        }
    }
}
