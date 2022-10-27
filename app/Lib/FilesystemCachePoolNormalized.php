<?php

namespace App\Lib;

use Cache\Adapter\Common\PhpCacheItem;
use Cache\Adapter\Filesystem\FilesystemCachePool;

/**
 * A override to make the FS-CacheItem implementation work with `Doctrine\Common\Annotations\PsrCachedReader`
 * @todo as soon as PsrCachedReader saves with PSR-6 valid keys, remove this
 */
class FilesystemCachePoolNormalized extends FilesystemCachePool {
    public function getItem($key): PhpCacheItem {
        return parent::getItem(hash('sha256', $key));
    }
}
