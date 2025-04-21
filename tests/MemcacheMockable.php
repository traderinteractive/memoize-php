<?php

namespace TraderInteractive\Memoize;

class MemcacheMockable extends \Memcache
{
    public function get($name, &$flags, &$cas)
    {
    }
}
