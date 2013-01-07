<?php
namespace Eve\Util;

class Arrays
{
    /**
     * Get an item from an array.
     *
     * If the specified key is null, the entire array will be returned. The array may
     * also be accessed using JavaScript "dot" style notation. Retrieving items nested
     * in multiple arrays is also supported.
     *
     * @param  array  $array
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (null === $key) { return $array; }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return is_callable($default) ? call_user_func($default) : $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set an array item to a given value.
     *
     * This method is primarly helpful for setting the value in an array with
     * a variable depth, such as configuration arrays.
     *
     * Like the Array::get method, JavaScript "dot" syntax is supported.
     *
     * @param  array  $array
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public static function set(&$array, $key, $value)
    {
        if (null === $key) {
            $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;
    }

    /**
     * Return array of states or selected state if 2 char param passed
     * @param string $state
     */
    public static function states($state = null)
    {
        // If a state is passed, make sure it is all uppercase
        if (null !== $state) { $state = strtoupper($state); }

        $states = array(
            'AL'	=>	'Alabama',
            'AK'	=>	'Alaska',
            'AZ'	=>	'Arizona',
            'AR'	=>	'Arkansas',
            'CA'	=>	'California',
            'CO'	=>	'Colorado',
            'CT'	=>	'Connecticut',
            'DE'	=>	'Delaware',
            'FL'	=>	'Florida',
            'GA'	=>	'Georgia',
            'HI'	=>	'Hawaii',
            'ID'	=>	'Idaho',
            'IL'	=>	'Illinois',
            'IN'	=>	'Indiana',
            'IA'	=>	'Iowa',
            'KS'	=>	'Kansas',
            'KY'	=>	'Kentucky',
            'LA'	=>	'Louisiana',
            'ME'	=>	'Maine',
            'MD'	=>	'Maryland',
            'MA'	=>	'Massachusetts',
            'MI'	=>	'Michigan',
            'MN'	=>	'Minnesota',
            'MS'	=>	'Mississippi',
            'MO'	=>	'Missouri',
            'MT'	=>	'Montana',
            'NE'	=>	'Nebraska',
            'NV'	=>	'Nevada',
            'NH'	=>	'New Hampshire',
            'NJ'	=>	'New Jersey',
            'NM'	=>	'New Mexico',
            'NY'	=>	'New York',
            'NC'	=>	'North Calorina',
            'ND'	=>	'North Dakota',
            'OH'	=>	'Ohio',
            'OK'	=>	'Oklahoma',
            'OR'	=>	'Oregon',
            'PA'	=>	'Pennsylvania',
            'RI'	=>	'Rhode Island',
            'SC'	=>	'South Carolina',
            'SD'	=>	'South Dakota',
            'TN'	=>	'Tennessee',
            'TX'	=>	'Texas',
            'UT'	=>	'Utah',
            'VT'	=>	'Vermont',
            'VA'	=>	'Virginia',
            'WA'	=>	'Washington',
            'WV'	=>	'West Virginia',
            'WI'	=>	'Wisconsin',
            'WY'	=>	'Wyoming',
        );

        // Return either the full array of states or the selected state
        return (null !== $state && strlen($state) == 2) ? $states[$state] : $states;
    }
}
