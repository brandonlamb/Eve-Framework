<?php

namespace Eve;

interface ResourceInterface
{
	const RES_CACHE		= 'cache';
	const RES_CONFIG	= 'config';
	const RES_DISPATCH	= 'dispatcher';
	const RES_FILE		= 'Cache\File';
	const RES_HTML		= 'View\HTML';
	const RES_JSON		= 'View\JSON';
	const RES_LOADER	= 'autoloader';
	const RES_REQUEST	= 'request';
	const RES_RESPONSE	= 'response';
}
