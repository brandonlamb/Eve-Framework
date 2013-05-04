<?php
namespace Eve\Curl\Parser;

interface ParserInterface
{
    /**
     * Parse data and return it
     *
     * @param  string $data
     * @return string
     */
    public function parse($data);
}
