<?php

namespace TraderInteractiveTest\Memoize;

class MemcacheMockable extends \Memcache
{
    public function get($name, &$flags, &$cas)
    {
    }
}
