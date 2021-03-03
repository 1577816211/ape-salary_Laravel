<?php


namespace App\Exceptions;

use Exception;
use Throwable;
use App\Library\Common;

abstract class BaseException extends Exception
{
    const CODE_PRE = '1000';

    const CODE_MAP = [];

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param string|array $code
     * @param Throwable|null $previous
     * @throws $this
     */
    public static function T($code, Throwable $previous = null)
    {
        throw self::instance($code, $previous);
    }

    /**
     * @param $code
     * @return BaseException
     */
    public static function E($code)
    {
        return self::instance($code);
    }

    protected static function instance($code, Throwable $previous = null)
    {
        if (is_array($code)) {
            $message = Common::parseTemplate(static::CODE_MAP[$code[0]], $code[1]);
            $code = static::CODE_PRE.$code[0];
        } else {
            $message = static::CODE_MAP[$code];
            $code = static::CODE_PRE.$code;
        }
        return new static($message, $code, $previous);
    }

    /**
     * @param $code
     * @return string
     */
    public static function C($code)
    {
        return static::CODE_PRE.$code;
    }
}