<?php
// Toml extension, https://github.com/GiovanniSalmeri/yellow-toml

class YellowToml {
    const VERSION = "0.9.1";

    private static $lineNumber;
    public static $error;

    public static function parse($toml) {
        $var = null;
        $prefix = [];
        $lines = preg_split('/\R/', $toml);
        foreach ($lines as self::$lineNumber=>$line) {
            if (trim($line)!=="" && $line[0]!=="#") {
                if (preg_match('/^\s*([a-z\d_.\-]+?)\s*=\s*(.*?)\s*$/i', $line, $matches)) {
                    $value = json_decode($matches[2], true);
                    if ($value===null || self::isMap($value)) {
                        if (self::validTomlDatetime($matches[2])) {
                            $value = $matches[2];
                        } elseif (preg_match('/^\'.*\'$/', $matches[2])) {
                            $value = substr($matches[2], 1, -1);
                        } else {
                            self::$error = "Invalid value at line ".(self::$lineNumber+1);
                            return null;
                        }
                    }
                    if (self::setDeep($var, array_merge($prefix, explode('.', $matches[1])), $value)===false) return null;
                } elseif (preg_match('/^\s*\[([a-z\d_.\-]+?)\]\s*$/i', $line, $matches)) {
                    $prefix = explode('.', $matches[1]);
                    if (self::setDeep($var, $prefix, [])===false) return null;
                } elseif (preg_match('/^\s*\[\[([a-z\d_.\-]+?)\]\]\s*$/i', $line, $matches)) {
                    $prefix = explode('.', $matches[1]);
                    $index = self::getIndex($var, $prefix);
                    if ($index!==false) { 
                        $prefix[] = (string)$index;
                    } else {
                        return null;
                    }
                    if (self::setDeep($var, $prefix, [])===false) return null;
                } else {
                    self::$error = "Invalid line ".(self::$lineNumber+1);
                    return null;
                }
            }
        }
        return $var;
    }

    private static function isMap($value) {
        if (is_array($value)) {
            if ($value!==array_values($value)) {
                self::$error = "Invalid value at line ".(self::$lineNumber+1);
                return true;
            } else {
                foreach ($value as $item) {
                    if (self::isMap($item)) return true;
                }
            }
        }
        return false;
    }

    private static function validTomlDatetime($string) {
        return (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)(?:[T ](\d\d):(\d\d):(\d\d)(?:\.\d{1,6})?(?:Z|[-+](\d\d):(\d\d))?)?$/', $string, $parts) && 
            checkdate($parts[2], $parts[3], $parts[1]) && 
            (!isset($parts[4]) || $parts[4]<=23 && $parts[5]<=59 && $parts[6]<=59) && 
            (!isset($parts[7]) || $parts[7]<=23 && $parts[8]<=59)) ||
            (preg_match('/^(\d\d):(\d\d):(\d\d)(\.\d{1,6})?$/', $string, $parts) && 
            ($parts[1]<=23 && $parts[2]<=59 && $parts[3]<=59));
    }

    private static function setDeep(&$var, $keysArray, $value) {
        if (array_search("", $keysArray)!==false) {
            self::$error = "Empty key at line ".(self::$lineNumber+1);
            return false;
        }
        if (count($keysArray)==1) {
            if (isset($var[$keysArray[0]])) {
                self::$error = "Invalid re-assignement at line ".(self::$lineNumber+1);
                return false;
            } else {
                $var[$keysArray[0]] = $value;
            }
        } else {
            if (self::setDeep($var[$keysArray[0]], array_slice($keysArray, 1), $value)===false) return false;
        }
        return true;
    }

    private static function getIndex($var, $keysArray) {
        if (array_search("", $keysArray)!==false) {
            self::$error = "Empty key at line ".(self::$lineNumber+1);
            return false;
        }
        if (count($keysArray)==1) {
            if (!isset($var[$keysArray[0]])) {
                return 0;
            } elseif (is_array($var[$keysArray[0]]) && $var[$keysArray[0]]===array_values($var[$keysArray[0]])) {
                return count($var[$keysArray[0]]);
            } else {
                self::$error = "Invalid re-assignement at line ".(self::$lineNumber+1);
                return false;
            }
        } else {
            return self::getIndex(@$var[$keysArray[0]], array_slice($keysArray, 1));
        }
    }
}

function toml_parse($input) {
    return YellowToml::parse($input);
}
function toml_parse_file($filename) {
    $content = @file_get_contents($filename);
    if (substr($content, 0, 3)=="\xef\xbb\xbf") $content = substr($content, 3);
    return $content===false ? false : YellowToml::parse($content);
}
function toml_parse_url($url) {
    return toml_parse_file($url);
}
