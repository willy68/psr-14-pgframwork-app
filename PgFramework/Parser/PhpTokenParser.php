<?php

declare(strict_types=1);

namespace PgFramework\Parser;

class PhpTokenParser
{
    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     * @return string|false Full class name if found, false otherwise
     * @throws \LogicException
     */
    public static function findClass($file)
    {
        if (!\function_exists('token_get_all')) {
            throw new \LogicException("Function token_get_all don't exists in this system");
        }

        $class = false;
        $namespace = false;
        $semiColon = false;
        $tokens = token_get_all(file_get_contents($file));

        $nsToken = [\T_NS_SEPARATOR, \T_STRING];
        if (PHP_VERSION_ID >= 80000) {
            if (\defined('T_NAME_QUALIFIED')) {
                $nsToken[] = T_NAME_QUALIFIED;
            }
            if (\defined('T_NAME_FULLY_QUALIFIED')) {
                $nsToken[] = T_NAME_FULLY_QUALIFIED;
            }
        }

        for ($i = 0, $count = \count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if (!\is_array($token)) {
                continue;
            }

            if (\T_DOUBLE_COLON === $token[0]) {
                $nextToken = $tokens[$i+1];
                if (\is_array($nextToken) && $nextToken[0] === \T_CLASS) {
                    dd($nextToken, $file);
                    $semiColon = true;
                }
                continue;
            }

            if (true === $class && \T_STRING === $token[0]) {
                return $namespace . '\\' . $token[1];
            }

            if (true === $namespace && \in_array($token[0], $nsToken)) {
                $namespace = '';
                do {
                    $namespace .= $token[1];
                    $token = $tokens[++$i];
                } while ($i < $count && \is_array($token) && \in_array($token[0], $nsToken));
            }
            if (\T_CLASS === $token[0]) {
                if($semiColon === false) {
                    $class = true;
                }
                $semiColon = false;
            }
            if (\T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }
        return false;
    }
    /*
    public function findClasses(string $path)
    {
        $code = file_get_contents($path);
        $tokens = @token_get_all($code);
        $namespace = $class = $classLevel = $level = NULL;
        $classes = [];
        while (list(, $token) = each($tokens)) {
            switch (is_array($token) ? $token[0] : $token) {
                case T_NAMESPACE:
                    $namespace = ltrim($this->fetch($tokens, [T_STRING, T_NS_SEPARATOR]) . '\\', '\\');
                    break;
                case T_CLASS:
                case T_INTERFACE:
                    if ($name = $this->fetch($tokens, T_STRING)) {
                        $classes[] = $namespace . $name;
                    }
                    break;
            }
        }
        return $classes;
    }

    private function fetch(&$tokens, $take)
    {
        $res = NULL;
        while ($token = current($tokens)) {
            list($token, $s) = is_array($token) ? $token : [$token, $token];
            if (in_array($token, (array) $take, TRUE)) {
                $res .= $s;
            } elseif (!in_array($token, [T_DOC_COMMENT, T_WHITESPACE, T_COMMENT], TRUE)) {
                break;
            }
            next($tokens);
        }
        return $res;
    }*/
}
