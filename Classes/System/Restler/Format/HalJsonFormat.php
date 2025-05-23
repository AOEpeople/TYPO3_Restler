<?php

namespace Aoe\Restler\System\Restler\Format;

use Luracast\Restler\Data\Obj;
use Luracast\Restler\Format\Format;
use Luracast\Restler\RestException;

/**
 * Javascript Object Notation Format
 *
 * @subpackage format
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 * @version    3.0.0rc6
 */
class HalJsonFormat extends Format
{
    /**
     * @var string
     */
    public const MIME = 'application/hal+json';

    /**
     * @var string
     */
    public const EXTENSION = 'json';

    /**
     * @var boolean|null  shim for json_encode option JSON_PRETTY_PRINT set
     * it to null to use smart defaults
     */
    public static ?bool $prettyPrint = null;

    /**
     * shim for json_encode option JSON_UNESCAPED_SLASHES
     * set it to null to use smart defaults
     */
    public static bool $unEscapedSlashes = false;

    /**
     * @var boolean|null  shim for json_encode JSON_UNESCAPED_UNICODE set it
     * to null to use smart defaults
     */
    public static ?bool $unEscapedUnicode = null;

    /**
     * @var boolean|null  shim for json_decode JSON_BIGINT_AS_STRING set it to
     * null to
     * use smart defaults
     */
    public static ?bool $bigIntAsString = null;

    /**
     * @var boolean|null  shim for json_decode JSON_NUMERIC_CHECK set it to
     * null to
     * use smart defaults
     */
    public static ?bool $numbersAsNumbers = null;

    public function encode($data, $humanReadable = false)
    {
        if (self::$prettyPrint !== null) {
            $humanReadable = self::$prettyPrint;
        }

        if (self::$unEscapedSlashes === null) {
            self::$unEscapedSlashes = $humanReadable;
        }

        if (self::$unEscapedUnicode === null) {
            self::$unEscapedUnicode = $this->charset == 'utf-8';
        }

        $options = 0;

        if ((PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION >= 4) // PHP >= 5.4
            || PHP_MAJOR_VERSION > 5 // PHP >= 6.0
        ) {
            if ($humanReadable) {
                $options |= JSON_PRETTY_PRINT;
            }

            if (self::$unEscapedSlashes) {
                $options |= JSON_UNESCAPED_SLASHES;
            }

            if (self::$bigIntAsString === true) {
                $options |= JSON_BIGINT_AS_STRING;
            }

            if (self::$unEscapedUnicode) {
                $options |= JSON_UNESCAPED_UNICODE;
            }

            if (self::$numbersAsNumbers === true) {
                $options |= JSON_NUMERIC_CHECK;
            }

            $result = json_encode(Obj::toArray($data, true), $options);
            $this->handleJsonError();

            return $result;
        }

        $result = json_encode(Obj::toArray($data, true), JSON_THROW_ON_ERROR);
        $this->handleJsonError();

        if ($humanReadable) {
            $result = $this->formatJson($result);
        }

        if (self::$unEscapedUnicode === true) {
            $result = preg_replace_callback(
                '/\\\u(\w\w\w\w)/',
                static function (array $matches): string|false {
                    if (function_exists('mb_convert_encoding')) {
                        return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16BE');
                    }

                    return iconv('UTF-16BE', 'UTF-8', pack('H*', $matches[1]));
                },
                $result
            );
        }

        if (self::$unEscapedSlashes) {
            return str_replace('\/', '/', $result);
        }

        return $result;
    }

    public function decode($data): ?array
    {
        if (empty($data)) {
            return null;
        }

        $options = 0;
        if (self::$bigIntAsString === true) {
            if ((PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION >= 4) // PHP >= 5.4
                || PHP_MAJOR_VERSION > 5 // PHP >= 6.0
            ) {
                $options |= JSON_BIGINT_AS_STRING;
            } else {
                $data = preg_replace(
                    '/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/',
                    ': "$1"',
                    $data
                );
            }
        }

        try {
            $decoded = json_decode((string) $data, false, 512, $options);
            $this->handleJsonError();
        } catch (\RuntimeException $runtimeException) {
            throw new RestException('400', $runtimeException->getMessage());
        }

        if (strlen((string) $data) && $decoded === null || $decoded === $data) {
            throw new RestException('400', 'Error parsing JSON');
        }

        return Obj::toArray($decoded);
    }

    /**
     * Throws an exception if an error occurred during the last JSON encoding/decoding
     */
    protected function handleJsonError(): void
    {
        if (function_exists('json_last_error_msg') && json_last_error() !== JSON_ERROR_NONE) {
            // PHP >= 5.5.0
            $message = json_last_error_msg();
        } elseif (function_exists('json_last_error')) {
            // PHP >= 5.3.0
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
                    $message = 'maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $message = 'underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $message = 'unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $message = 'malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $message = 'malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $message = 'unknown error';
                    break;
            }
        }

        if (isset($message)) {
            throw new \RuntimeException('Error encoding/decoding JSON: ' . $message);
        }
    }

    /**
     * Pretty print JSON string
     */
    private function formatJson(string $json): string
    {
        $tab = '  ';
        $newJson = '';
        $indentLevel = 0;
        $inString = false;
        $len = strlen($json);
        for ($c = 0; $c < $len; ++$c) {
            $char = $json[$c];
            switch ($char) {
                case '{':
                case '[':
                    if (!$inString) {
                        $newJson .= $char . "\n" .
                            str_repeat($tab, $indentLevel + 1);
                        ++$indentLevel;
                    } else {
                        $newJson .= $char;
                    }

                    break;
                case '}':
                case ']':
                    if (!$inString) {
                        --$indentLevel;
                        $newJson .= "\n" .
                            str_repeat($tab, $indentLevel) . $char;
                    } else {
                        $newJson .= $char;
                    }

                    break;
                case ',':
                    if (!$inString) {
                        $newJson .= ",\n" .
                            str_repeat($tab, $indentLevel);
                    } else {
                        $newJson .= $char;
                    }

                    break;
                case ':':
                    if (!$inString) {
                        $newJson .= ': ';
                    } else {
                        $newJson .= $char;
                    }

                    break;
                case '"':
                    if ($c == 0) {
                        $inString = true;
                    } elseif ($c > 0 && $json[$c - 1] !== '\\') {
                        $inString = !$inString;
                    }
                    // fall-through
                    // no break
                default:
                    $newJson .= $char;
                    break;
            }
        }

        return $newJson;
    }
}
