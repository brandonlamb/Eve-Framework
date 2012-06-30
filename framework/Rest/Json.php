<?php
/**
 * Eve Application Framework
 *
 * @author Brandon Lamb
 * @copyright 2012
 * @package Eve
 * @version 0.1.0
 */
namespace Eve\Rest;

class Json implements ParserInterface
{
	/**
	 * Parse data as JSON encoded
	 *
	 * @param string $data
	 * @return array
	 */
	public function parse($data)
	{
		return json_decode($data, true);
	}
}
