<?php

declare(strict_types=1);

use Brick\Math\BigInteger;

require 'vendor/autoload.php';

$basePath = '/tmp/drom-test-task1';
recursive_rmdir($basePath);

$mkdir = static function (string $path): void {
    if (!mkdir($concurrentDirectory = $path, 0777, true) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }

    echo 'Created a dir: ' . $path . PHP_EOL;
};

$count = BigInteger::zero();

$emitCount = static function (string $dirPath) use (&$count): void {
    // 50% to create a `count` file
    if (random_int(0, 100) <= 50) {
        $random = random_int(0, PHP_INT_MAX);
        $path = $dirPath . '/count';
        file_put_contents($path, $random);
        $count = $count->plus($random);

        echo 'Created a file: ' . $path . PHP_EOL;
    }
};

$stepIn = static function (string $dirPath) use ($emitCount, $mkdir, &$stepIn): void {
    $emitCount($dirPath);

    // 50% to create a nested dir
    if (random_int(0, 100) <= 50) {
        $name = uniqid('', true);
        $mkdir($dirPath . '/' . $name);
        $stepIn($dirPath . '/' . $name);
    }
};

try {
    foreach (range(1, 10) as $i) {
        $path = $basePath . '/' . 'dir' . $i;
        $mkdir($path);
        $stepIn($path);
    }

    echo PHP_EOL . sprintf('Expected result:   %s', $count) . PHP_EOL;
    $result = trim(exec('php count.php ' . $basePath));
    echo sprintf('Script run result: %s', $result) . PHP_EOL;
    if (!$count->isEqualTo($result)) {
        throw new RuntimeException('Actual result differs from the excepted one');
    }
} finally {
    recursive_rmdir($basePath);
}
