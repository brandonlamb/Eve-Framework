<?php
namespace Eve;

// Namespace aliases
use Eve\Util as Util;
use Eve\Filter as Filter;

class Filter
{
    /**
     * @var HtmlPurifier
     */
    protected static $_purifier;

    /**
     * Validator that filters integers
     *
     * @param  int $value
     * @return int
     */
    public static function integer($value)
    {
        return (is_numeric($value) && $value >= 0) ? (int) $value : 0;
    }

    /**
     * Validator that filters integers
     *
     * @param  int    $value
     * @return string
     */
    public static function notNull($value)
    {
        return (null !== $value) ? $value : '';
    }

    /**
     * Filter Datetime field
     *
     * @param  string $value
     * @return string
     */
    public static function datetime($value)
    {
        try {
            $time = new DateTime($value);

            return $time->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Get current Datetime in mysql datetime format (0000-00-00 00:00:00)
            $time = new DateTime(null);

            return $time->format('Y-m-d H:i:s');
        }
    }

    /**
     * Filter Time field
     *
     * @param  string $value
     * @return string
     */
    public static function time($value)
    {
        return (preg_match('/[0-9]{1,2}:[0-9]{2}(:[0-9]{2})?/', $value)) ? $value : '00:00:00';
    }

    /**
     * Filter Date field
     *
     * @param  string $value
     * @return string
     */
    public static function date($value)
    {
        try {
            $time = new DateTime($value);

            return $time->format('Y-m-d');
        } catch (\Exception $e) {
            // Get current Datetime in mysql datetime format (0000-00-00 00:00:00)
            $time = new DateTime(null);

            return $time->format('Y-m-d');
        }
    }

    /**
     * Filter email address
     *
     * @param  string $value
     * @return string
     */
    public static function email($value)
    {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Filter URL
     *
     * @param  string $value
     * @return string
     */
    public static function url($value)
    {
        return filter_var($value, FILTER_SANITIZE_URL);
    }

    /**
     * Filter text and remove any html tags
     *
     * @param  string $value
     * @return string
     */
    public static function plaintext($value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }

    /**
     * Filter to plain text, encode special characters and convert breaks
     *
     * @param  string $value
     * @return string
     */
    public static function plaintextBr($value)
    {
        return nl2br(htmlspecialchars(self::plaintext($value)));
    }

    /**
     * Filter out Malicious HTML (XSS), but don't strip all tags
     *
     * @param  string $value
     * @return string
     */
    public static function html($value)
    {
        require_once 'Eve/Filter/HtmlPurifier/HtmlPurifier.php';
        if (null === static::$_purifier) {
            static::$_purifier = new \HTMLPurifier();
        }

           return static::$_purifier->purify($value);
    }

    /**
     * Filter decimals. The separator must be a period
     *
     * @param  decimal $value
     * @return decimal
     */
    public static function decimal($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT, array('options' => array('decimal' => '.')));
    }

    /**
     * Pass to utility class
     *
     * @param  string $value
     * @return string
     */
    public static function xss($value)
    {
        return Util\Strings::xss($value);
    }
}
