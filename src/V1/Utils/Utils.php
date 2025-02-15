<?php
declare(strict_types=1);

/*
 * This file is part of opctim/bruno-lang
 *
 * (c) Tim Nelles (opctim) <kontakt@timnelles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * information in the project composer.json or on GitHub.
 */

namespace Opctim\BrunoLang\V1\Utils;

class Utils
{
    private const INDENTATION_SPACES = 2;


    public static function indentString(string $text): string
    {
        $indent = str_repeat(' ', self::INDENTATION_SPACES);

        return implode(PHP_EOL, array_map(fn($line) => $indent . $line, explode(PHP_EOL, $text)));
    }

    public static function stripIndentation(string $text): string
    {
        return preg_replace('/^\s{' . self::INDENTATION_SPACES . '}/', '', $text);
    }

    public static function buildEntryName(bool $enabled, bool $local, string $name): string
    {
        $result = '';

        if (!$enabled) {
            $result .= '~';
        }

        if ($local) {
            $result .= '@';
        }

        $result .= $name;

        return $result;
    }

    public static function jsonEncodePretty2Spaces(mixed $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return preg_replace_callback(
            '/^( *)(?=\S)/m',
            fn($matches) => str_repeat("  ", strlen($matches[1]) / 4), // Convert 4 spaces to 2 spaces per level
            $json
        );
    }

    public static function isLocal(string $modifierString): bool
    {
        $modifiers = array_unique(str_split($modifierString));
        $local = false;

        if (in_array('@', $modifiers)) {
            $local = true;
        }

        return $local;
    }

    public static function isEnabled(string $modifierString): bool
    {
        $modifiers = array_unique(str_split($modifierString));
        $enabled = true;

        if (in_array('~', $modifiers)) {
            $enabled = false;
        }

        return $enabled;
    }
}