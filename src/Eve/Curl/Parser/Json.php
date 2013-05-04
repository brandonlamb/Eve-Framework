<?php
namespace Eve\Curl\Parser;

class Json implements ParserInterface
{
    /**
     * Parse data as JSON encoded
     *
     * @param  string $data
     * @return array
     */
    public function parse($data)
    {
        return json_decode($data, true);
    }
}
