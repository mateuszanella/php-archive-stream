<?php

namespace PhpArchiveStream\Writers\Zip\Records\Fields;

class Version
{
    /**
     * 1.0 (10)
     */
    public const BASE = 0x000A;

    /**
     * 2.0 (20)
     */
    public const DEFLATE = 0x0014;

    /**
     * 4.5 (45)
     */
    public const ZIP64 = 0x002D;
}
