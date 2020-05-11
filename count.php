<?php

declare(strict_types=1);

use Brick\Math\BigInteger;
use Brick\Math\Exception\NumberFormatException;

require 'vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    echo 'This script must be run as a cli script' . PHP_EOL;
    exit(1);
}

$arguments = $argv;
array_shift($arguments);

if (count($paths = array_unique($arguments)) < 1) {
    fwrite(STDERR, 'You must provide at least one directory path' . PHP_EOL);
    exit(1);
}

$count = BigInteger::zero();

foreach ($paths as $path) {
    if (!is_dir($path)) {
        fwrite(STDERR, sprintf('%s is not a directory, skipping...', $path) . PHP_EOL);
        continue;
    }

    $iterator = iterate($path, 'count');
    foreach ($iterator as $info) {
        assert($info instanceof SplFileInfo);

        $number = file_get_contents($info->getPathname(), false, null);
        if ($number === false) {
            $message = sprintf('File %s is skipped because it can\'t be read', $info->getPathname());
            fwrite(STDERR, $message . PHP_EOL);
            continue;
        }

        try {
            $count = $count->plus(trim($number));
        } catch (NumberFormatException $exception) {
            $number !== '' ?: $number = '(empty)';
            $message = sprintf('File %s is skipped because it\'s contents can\'t be parsed as a number: %s',
                $info->getPathname(),
                strlen($number) < 32 ? $number : substr($number, 0, 32) . '...'
            );
            fwrite(STDERR, $message . PHP_EOL);
        }
    }

    echo $count . PHP_EOL;
}
