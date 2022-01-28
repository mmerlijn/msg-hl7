<?php

namespace mmerlijn\msgHl7\validation;

class Validator
{
    public static array $errors = [];

    public static function validate(array $data, array $rules, array $msg = []): bool
    {
        //loop through data input
        foreach ($data as $k => $v) {
            //loop through rules
            foreach ($rules as $field => $ruleSet) {
                //data key in rules
                if ($field == $k) {
                    if (is_string($ruleSet)) {//split
                        $ruleSet = explode("|", $ruleSet);
                    }
                    foreach ($ruleSet as $ruleMethod) {
                        if (!static::check($v, $ruleMethod)) {
                            static::$errors[] = $k . " " . $ruleMethod . " failure " . ($msg[$k] ?? "");
                        }
                    }
                }
            }
        }
        return empty(static::$errors);
    }

    public static function reset(): void
    {
        static::$errors = [];
    }

    public static function getErrors(): array
    {
        return static::$errors;
    }

    public static function firstError(): string
    {
        return static::$errors[0] ?? "";
    }

    public static function fails(): bool
    {
        return !empty(static::$errors);
    }

    //split $rule by :
    // example length:40 => calls length($value,40)
    protected static function check($value, $rule): bool
    {
        $rule_set = explode(":", $rule);
        $method = $rule_set[0];
        return static::$method($value, $rule_set[1] ?? null);
    }

    protected static function length($value, $len): bool
    {
        if (strlen($value) == $len)
            return true;
        return false;
    }

    protected static function max($value, $len): bool
    {
        if (strlen($value) <= $len)
            return true;
        return false;
    }

    protected static function min($value, $len): bool
    {
        if (strlen($value) >= $len)
            return true;
        return false;
    }

    protected static function between($value, $params): bool
    {
        $param = explode(",", $params);
        if (strlen($value) >= ($param[0] ?? 0) and strlen($value) <= ($param[1] ?? 255))
            return true;
        return false;
    }

    public static function required($value, $param): bool
    {
        if ($value) {
            return true;
        }
        return false;
    }
}