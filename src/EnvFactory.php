<?php

namespace Elazar\Dibby;

use League\Flysystem\FilesystemOperator;
use M1\Env\Parser as EnvParser;
use Psr\SimpleCache\CacheInterface;

class EnvFactory
{
    public function __construct(
        private CacheInterface $cache,
        private string $cacheKey,
        private FilesystemOperator $filesystem,
        private string $file,
    ) { }

    public function get(): array
    {
        $cached = $this->cache->get($this->cacheKey);
        if ($cached === null) {
            $contents = $this->filesystem->read($this->file);
            $cached = EnvParser::parse($contents);
            $this->cache->set($this->cacheKey, $cached);
        }
        return $cached;
    }
}
