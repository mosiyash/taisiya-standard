<?php

namespace Composer;

use Composer\Script\Event;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ScriptHandler
{
    public static function copySettings(Event $event): void
    {
        defined('ROOT_DIR') || define('ROOT_DIR', dirname(dirname(__DIR__)));

        $finder = new Finder();
        $finder
            ->in([
                ROOT_DIR.'/vendor',
                ROOT_DIR.'/src',
            ])
            ->files()
            ->name('/settings\.default\.php$/')
            ->filter(function (SplFileInfo $file) {
                return (bool) preg_match('/\/app\/config\//', $file->getPathname());
            });

        foreach ($finder as $file) {
            $dest = preg_replace('/^.+?\/vendor\/(.+?)\/(.+?)\/.+?$/', ROOT_DIR.'/app/config/\1.\2.settings.default.php', $file->getPathname());
            if (!copy($file->getPathname(), $dest)) {
                $event->getIO()->writeError('  - <error>Couldn\'t copy '.str_replace(ROOT_DIR.'/', '', $file->getPathname()).' to '.str_replace(ROOT_DIR.'/', '', $dest).'</error>');
            } else {
                $event->getIO()->write('  - <info>'.str_replace(ROOT_DIR.'/', '', $file->getPathname()).' is copied to '.str_replace(ROOT_DIR.'/', '', $dest).'</info>');
            }
        }

        return;
    }

    public static function mergeSettings(Event $event): void
    {
        $uname = php_uname('n');
        $files = [];

        $finder = new Finder();
        $finder
            ->in(ROOT_DIR.'/app/config')
            ->files()
            ->name('/\.settings\.default\.php$/');

        foreach ($finder as $file) {
            $files[] = $file->getPathname();
        }

        $finder = new Finder();
        $finder
            ->in(ROOT_DIR.'/app/config')
            ->files()
            ->name('/\.settings\.local\.php$/');

        foreach ($finder as $file) {
            $files[] = $file->getPathname();
        }

        $finder = new Finder();
        $finder
            ->in(ROOT_DIR.'/app/config')
            ->files()
            ->name('/\.settings\.'.preg_quote($uname, '/').'\.local\.php$/');

        foreach ($finder as $file) {
            $files[] = $file->getPathname();
        }

        $files[] = ROOT_DIR.'/app/config/settings.default.php';
        if (file_exists(ROOT_DIR.'/app/config/settings.local.php')) {
            $files[] = ROOT_DIR.'/app/config/settings.local.php';
        }
        if (file_exists(ROOT_DIR.'/app/config/settings.'.$uname.'.local.php')) {
            $files[] = ROOT_DIR.'/app/config/settings.'.$uname.'.local.php';
        }

        $settings = [];
        foreach ($files as $file) {
            $settings = array_merge_recursive($settings, require $file);
        }

        if (!file_put_contents(ROOT_DIR.'/app/config/settings.php', "<?php\n\nreturn ".var_export($settings, true).";\n")) {
            $event->getIO()->writeError('  - <error>Couldn\'t write app/config/settings.php</error>');
        } else {
            $event->getIO()->write('  - <info>writed to app/config/settings.php</info>');
        }

        return;
    }
}
