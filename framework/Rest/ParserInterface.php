<?php
/**
 * Eve Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package Eve
 * @version 0.2.0
 */
namespace Eve\Rest;

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
