<?php

/**
 * Convert hexdec color string to rgb(a) string
 *
 * @param string $color   The hex value for color. Can be either 3 or 6 digits long
 * @param mixed  $opacity Set to false if you want to ignore transparency, or set to a decimal number to add that amount of transparency (eg. 0.3)
 *
 * @return string RGB or RGBA code
 */
function hex2rgba($color, $opacity = false) {

    $default = 'rgb(0,0,0)';

    //Return default if no color provided
    if (empty($color))
        return $default;

    //Sanitize $color if "#" is provided
    if ($color[ 0 ] == '#') {
        $color = substr($color, 1);
    }

    //Check if color has 6 or 3 characters and get values
    if (strlen($color) == 6) {
        $hex = [ $color[ 0 ] . $color[ 1 ], $color[ 2 ] . $color[ 3 ], $color[ 4 ] . $color[ 5 ] ];
    } elseif (strlen($color) == 3) {
        $hex = [ $color[ 0 ] . $color[ 0 ], $color[ 1 ] . $color[ 1 ], $color[ 2 ] . $color[ 2 ] ];
    } else {
        return $default;
    }

    //Convert hexadec to rgb
    $rgb = array_map('hexdec', $hex);

    //Check if opacity is set(rgba or rgb)
    if ($opacity) {
        if (abs($opacity) > 1)
            $opacity = 1.0;
        $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode(",", $rgb) . ')';
    }

    //Return rgb(a) color string
    return $output;
}

/**
 * Swap the values of two variables
 *
 * @param mixed $x First value
 * @param mixed $y Second value
 */
function swapVariables(&$x, &$y) {
    $tmp = $x;
    $x = $y;
    $y = $tmp;
}

/**
 * Adjust the brightness of a hex code for css
 *
 * @param string $hex   The hexadecimal value
 * @param int    $steps How much brighten/darken the value element of [-255, 255] n Z
 *
 * @return string the adjusted hex value
 */
function adjustBrightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color = hexdec($color); // Convert to decimal
        $color = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

/**
 * Returns a random remark pondering the result (mostly for errors)
 *
 * @param string $modifier A modifier value
 * @param mixed  $value    A value for the modifier
 *
 * @return mixed The fascinating remark(s)
 */
function randomErrorRemark($modifier = '', $value = null) {
    $errorRemarks = [
        'Oops',
        'That\'s strange',
        'Interesting',
        'Well',
        'Peculiar',
        'Fascinating',
    ];

    $return = '';
    switch ($modifier) {
        case 'array':
            if (empty($return))
                $return = [];
            for ($i = 0; $i < $value; $i++) {
                try {
                    if ($value >= count($errorRemarks) || count(array_unique($return)) < count($errorRemarks)) {
                        $return[] = randomErrorRemark('not', $return);
                    } else {

                        if ($i === 0) {
                            $return[] = randomErrorRemark();
                        } else {
                            $return[] = randomErrorRemark('not', $return[ $i - 1 ]);
                        }

                    }
                } catch (Exception $e) {
                    dd($e, $return);
                }
            }
            break;
        case 'multiple':
            $return = randomErrorRemark('array', $value);
            break;
        default:
            $return = $errorRemarks[ array_rand($errorRemarks, 1) ] . '...';
            break;
    }

    switch ($modifier) {
        case 'not':
            if (is_array($value)) {
                $return = randomErrorRemark();
                while (in_array($return, $value)) {
                    $return = randomErrorRemark();
                }
            } else
                while ($return == $value)
                    $return = randomErrorRemark();
            break;
        case 'seed':
            if (is_array($value)) {
                $value = array_values($value);
                $remarks = randomErrorRemark('array', count($value));
                foreach ($value as $i => $val) {
                    $remark = $remarks[ $i ];
                    $value[ $i ] = "<b class=\"error-remark\">{$remark}</b> {$val}";
                }
                $return = $value;
            } else {
                $remark = randomErrorRemark();
                $return = "<b class=\"error-remark\">{$remark}</b> {$value}";
            }
            break;
    }

    return $return;
}


/**
 * Trims text to a space then adds ellipses if desired.
 *
 * @param string $input      text to trim
 * @param int    $length     in characters to trim to
 * @param bool   $ellipses   if ellipses (...) are to be added
 * @param bool   $strip_html if html tags are to be stripped
 *
 * @return string Text trimmed to the last word.
 */
function trim_text($input, $length, $ellipses = true, $strip_html = true) {
    if ($strip_html)
        $input = strip_tags($input);
    if (strlen($input) <= $length)
        return $input;
    $last_space = strrpos(substr($input, 0, $length), ' ');

    if ($last_space == 0)
        $last_space = $length;

    $trimmed_text = substr($input, 0, $last_space);

    if ($ellipses)
        $trimmed_text .= '...';

    return $trimmed_text;
}

/**
 * Gets a globally unique identifier
 *
 * @return string Globally unique identifier
 */
function GUID() {
    if (function_exists('com_create_guid') === true)
        $return = trim(com_create_guid(), '{}');
    else
        $return = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535),
                          mt_rand(0, 65535));

    return strtolower($return);
}

/**
 * Converts the string to an ASCII encoded slug
 *
 * @param string $str       The string that will be converted to a slug
 * @param array  $replace   List of characters on which to add a delimiter
 * @param string $delimiter The delimiter used to separate the words
 *
 * @return string Returns the slug
 */
function slug($str, $replace = [], $delimiter = '-'): string {
    setlocale(LC_ALL, 'en_US.UTF8');

    if (!empty($replace)) {
        $str = str_replace((array) $replace, ' ', $str);
    }

    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
}

/**
 * Recursively negates all elements of the array
 *
 * @param array $array The array to be negated
 *
 * @return array The negated array
 */
function array_not(array $array): array {
    return array_map(function ($el) {
        if (is_array($el)) {
            return array_not($el);
        }

        return !$el;
    }, $array);
}


/**
 * Fix the formatting of the $_FILES array
 ** Will transform this:
 **
 **  array(1) {
 **      ["upload"]=>array(2) {
 **          ["name"]=>array(2) {
 **              [0]=>string(9)"file0.txt"
 **              [1]=>string(9)"file1.txt"
 **          }
 **          ["type"]=>array(2) {
 **              [0]=>string(10)"text/plain"
 **              [1]=>string(10)"text/html"
 **          }
 **      }
 **  }
 **
 **
 ** Into:
 **
 **  array(1) {
 **      ["upload"]=>array(2) {
 **          [0]=>array(2) {
 **              ["name"]=>string(9)"file0.txt"
 **              ["type"]=>string(10)"text/plain"
 **          },
 **          [1]=>array(2) {
 **              ["name"]=>string(9)"file1.txt"
 **              ["type"]=>string(10)"text/html"
 **          }
 **      }
 **  }
 *
 * @param array $filesArray The $_FILES array with the selected file ( eg. $_FILES['file'] )
 *
 * @return array An array with each element having all values for each file
 */
function fixFilesArray(array $filesArray): array {
    $result = [];
    foreach ($filesArray as $key1 => $value1)
        foreach ($value1 as $key2 => $value2)
            $result[ $key2 ][ $key1 ] = $value2;

    return $result;
}

/**
 * Get the ratio of the levenstein distance between two string and the max length
 *
 * @param string $str1 First string
 * @param string $str2 Second string
 *
 * @return float The ratio
 */
function levenshteinRatio($str1, $str2): float {
    $lev = levenshtein($str1, $str2);
    $max = strlen(max($str1, $str2));

    if ($max == 0)
        return 1;

    return (1 - $lev / $max);
}

/**
 * Generate integer value from string (used for error codes, about 5% chance of duplicates)
 *
 * @param string $error The error string
 *
 * @return int A integer based on the input string
 */
function getErrorCode(string $error): int {

    if (strlen($error) == 1) {
        return ord($error);
    }

    $err = (int) array_reduce(str_split($error), function ($i, $char) {
        $i ^= ord($char);

        return $i;
    }, 500);

    $err *= strlen($error);

    $err ^= ord(substr($error, 0, 1));

    $err ^= ord(substr($error, -1, 1));

    $err ^= ord(substr($error, (int) strlen($error) / 2, 1));

    return $err;
}

/**
 * Convert object to array
 *
 * @param object $object The object to convert
 *
 * @return array The converted array
 */
function toArray($object): array {
    return json_decode(json_encode($object), true);
}

/**
 * Announces an error and stops execution
 *
 * @param string $reason  A name or reason for throwing an error
 * @param array  $data    Data to be supplied with alongside the message
 * @param string $action  An additional action
 * @param array  $actions Possible additional actions
 * @param int    $status  The status code for the error
 *
 * @return void
 */
function err(string $reason, array $data = [], string $action = '', array $actions = [], int $status = 400): void {
    global $app;
    $app->status($status);
    res(true, $reason, $data, $action, $actions);
}

/**
 * Announces a success message and stops execution
 *
 * @param string $reason  A success message or name for the success
 * @param array  $data    Data to be supplied with alongside the message
 * @param string $action  An additional action
 * @param array  $actions Possible additional actions
 *
 * @return void
 */
function say(string $reason, array $data = [], string $action = '', array $actions = []): void {
    res(false, $reason, $data, $action, $actions);
}

/**
 * Announces a response message and stops execution
 *
 * @param bool   $isError Whether the response is an error message
 * @param string $reason  A success message or name for the response
 * @param array  $data    Data to be supplied with alongside the message
 * @param string $action  An additional action
 * @param array  $actions Possible additional actions
 *
 * @return void
 */
function res(bool $isError, string $reason, array $data = [], string $action = '', array $actions = []): void {
    $return = [ 'error' => $isError, 'responseCode' => getErrorCode($reason), 'reason' => $reason, 'data' => [], 'messages' => [] ];

    if (isset($data[ 'messages' ])) {
        $return[ 'messages' ] = $data[ 'messages' ];
        unset($data[ 'messages' ]);
    }

    if ($isError) {
        $continue = false;

        if (isset($data[ 'data' ])) {
            $return[ 'data' ] = $data[ 'data' ];
            unset($data[ 'data' ]);
        }

        if (isset($data[ 'errors' ])) {
            $return[ 'errors' ] = $data[ 'errors' ];
            unset($data[ 'errors' ]);
            $return[ 'data' ] = array_merge($return[ 'data' ], $data);

            $continue = true;
        }

        if (isset($data[ 'error' ])) {
            $data[ 'message' ] = $data[ 'error' ];
            unset($data[ 'error' ]);
        }

        if (isset($data[ 'message' ])) {
            $return[ 'messages' ][] = $data[ 'message' ];
            unset($data[ 'message' ]);
            $return[ 'data' ] = $data;
        }

        if (!$continue) {
            $return[ 'errors' ] = $data;
            $return[ 'data' ] = [];
        }
    } else {
        $return[ 'data' ] = $data;
    }

    if (isset($return[ 'message' ])) {
        $return[ 'messages' ] = (array) $return[ 'message' ];
        unset($return[ 'message' ]);
    }

    $return[ 'action' ] = $action;

    if (!empty($actions)) {
        $return[ 'actions' ] = $actions;
        if ($action !== '*') {
            $return[ 'actions' ][] = $action;
            $return[ 'action' ] = '*';
        }
    }

    $return[ 'timestamp' ] = time();

    sdj($return);
}

/**
 * Generate a UUID (random or time based)
 *
 * @param string $randomOrTime Whether to generate a random or time based UUID (fallback to random)
 *
 * @return string The UUID
 */
function uuid(string $randomOrTime = 'random'): string {

    $randomKeys = [
        'random',
        'rand',
    ];

    try {
        if (in_array($randomOrTime, $randomKeys))
            $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        else
            $uuid = \Ramsey\Uuid\Uuid::uuid1()->toString();
    } catch (\Throwable $e) {
        $uuid = GUID();
    }

    return $uuid;
}

/**
 * Check whether a string starts with a needle
 *
 * @param string $haystack The string to check against
 * @param string $needle   The string to check for
 *
 * @return bool
 */
function startsWith(string $haystack, string $needle): bool {
    // Search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

/**
 * Check whether a string ends with a needle
 *
 * @param string $haystack The string to check against
 * @param string $needle   The string to check for
 *
 * @return bool
 */
function endsWith(string $haystack, string $needle): bool {
    // Search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}


##########################

/**
 * Easy image resize function
 *
 * @param  $file               - file name to resize
 * @param  $string             - The image data, as a string
 * @param  $width              - new image width
 * @param  $height             - new image height
 * @param  $proportional       - keep image proportional, default is no
 * @param  $output             - name of the new file (include path if needed)
 * @param  $delete_original    - if true the original image will be deleted
 * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
 * @param  $quality            - enter 1-100 (100 is best quality) default is 100
 *
 * @return boolean|resource
 */
function smart_resize_image($file, $string = null, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false, $quality = 100) {

    if ($height <= 0 && $width <= 0)
        return false;
    if ($file === null && $string === null)
        return false;

    # Setting defaults and meta
    $info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
    list($width_old, $height_old) = $info;
    $cropHeight = $cropWidth = 0;

    # Calculating proportionality
    if ($proportional) {
        if ($width == 0)
            $factor = $height / $height_old;
        elseif ($height == 0)
            $factor = $width / $width_old;
        else
            $factor = min($width / $width_old, $height / $height_old);

        $final_width = round($width_old * $factor);
        $final_height = round($height_old * $factor);
    } else {
        $final_width = ($width <= 0) ? $width_old : $width;
        $final_height = ($height <= 0) ? $height_old : $height;
        $widthX = $width_old / $width;
        $heightX = $height_old / $height;

        $x = min($widthX, $heightX);
        $cropWidth = ($width_old - $width * $x) / 2;
        $cropHeight = ($height_old - $height * $x) / 2;
    }

    # Loading image to memory according to type
    switch ($info[ 2 ]) {
        case IMAGETYPE_JPEG:
            $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);
            break;
        case IMAGETYPE_GIF:
            $file !== null ? $image = imagecreatefromgif($file) : $image = imagecreatefromstring($string);
            break;
        case IMAGETYPE_PNG:
            $file !== null ? $image = imagecreatefrompng($file) : $image = imagecreatefromstring($string);
            break;
        default:
            return false;
    }


    # This is the resizing/resampling/transparency-preserving magic
    $image_resized = imagecreatetruecolor($final_width, $final_height);
    if (($info[ 2 ] == IMAGETYPE_GIF) || ($info[ 2 ] == IMAGETYPE_PNG)) {
        $transparency = imagecolortransparent($image);
        $palletsize = imagecolorstotal($image);

        if ($transparency >= 0 && $transparency < $palletsize) {
            $transparent_color = imagecolorsforindex($image, $transparency);
            $transparency = imagecolorallocate($image_resized, $transparent_color[ 'red' ], $transparent_color[ 'green' ], $transparent_color[ 'blue' ]);
            imagefill($image_resized, 0, 0, $transparency);
            imagecolortransparent($image_resized, $transparency);
        } elseif ($info[ 2 ] == IMAGETYPE_PNG) {
            imagealphablending($image_resized, false);
            $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
            imagefill($image_resized, 0, 0, $color);
            imagesavealpha($image_resized, true);
        }
    }
    imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);


    # Taking care of original, if needed
    if ($delete_original) {
        if ($use_linux_commands)
            exec('rm ' . $file);
        else
            @unlink($file);
    }

    # Preparing a method of providing result
    switch (strtolower($output)) {
        case 'browser':
            $mime = image_type_to_mime_type($info[ 2 ]);
            header("Content-type: $mime");
            $output = null;
            break;
        case 'file':
            $output = $file;
            break;
        case 'return':
            return $image_resized;
            break;
        default:
            break;
    }

    # Writing image according to type to the output destination and image quality
    switch ($info[ 2 ]) {
        case IMAGETYPE_GIF:
            imagegif($image_resized, $output);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($image_resized, $output, $quality);
            break;
        case IMAGETYPE_PNG:
            $quality = 9 - (int) ((0.9 * $quality) / 10.0);
            imagepng($image_resized, $output, $quality);
            break;
        default:
            return false;
    }

    return true;
}

/**
 * (sdj => Stop Dump Json)
 *
 * Halt the $app and dump input as json
 *
 * @param array|object $array
 */
function sdj($array) {
    $app = \Slim\Slim::getInstance();

    $app->contentType('application/json');
    $app->halt($app->response->getStatus(), json_encode($array));
}

/**
 * Prints out the array in json, sets the correct headers and exits the script
 *
 * @param array|object $array
 */
function ddj($array) {
    header('Content-Type: application/json');
    exit(json_encode($array));
}
