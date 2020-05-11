<?php

declare(strict_types=1);

/**
 * Рекурсивно итерирует директории по пути $path,
 * возвращает только файлы с именем $filename
 *
 * @return iterable<SplFileInfo>
 */
function iterate(string $path, string $filename): iterable
{
    $directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
    $filter = new RecursiveCallbackFilterIterator($directory, static function (SplFileInfo $current) use ($filename): bool {
        if ($current->isLink()) {
            return false;
        }
        if ($current->isDir()) {
            return true;
        }

        return $current->getFilename() === $filename && $current->isFile() && $current->isReadable();
    });

    return new RecursiveIteratorIterator($filter);
}

/**
 * rmdir() не удаляет не пустые директории
 */
function recursive_rmdir(string $dir): void
{
    if (is_dir($dir) && false !== ($nodes = scandir($dir))) {
        foreach ($nodes as $node) {
            if ($node !== '.' && $node !== '..') {
                $path = $dir . DIRECTORY_SEPARATOR . $node;
                if (is_dir($path) && !is_link($path)) {
                    recursive_rmdir($path);
                } else {
                    unlink($path);
                }
            }
        }
        rmdir($dir);
    }
}
