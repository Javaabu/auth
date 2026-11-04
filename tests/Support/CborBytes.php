<?php

namespace Javaabu\Auth\Tests\Support;

/**
 * A CBOR byte string, as opposed to a text string
 */
class CborBytes
{
    public function __construct(
        public string $bytes,
    ) {}
}
