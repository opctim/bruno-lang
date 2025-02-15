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

namespace Opctim\BrunoLang\Tests\V1\Utils;

use Opctim\BrunoLang\V1\Utils\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testIndentString(): void
    {
        self::assertEquals('  text', Utils::indentString('text'));
    }

    public function testStripIndentation(): void
    {
        self::assertEquals('  text', Utils::stripIndentation('    text'));
    }

    public function testJsonEncodePretty2Space(): void
    {
        $test = [
            'hi' => [
                'bläääa' => 1234,
                'some' => [
                    '~' => 'hello',
                    'hello',
                    '12345' => [
                        'yoo'
                    ],
                ]
            ]
        ];

        $expected =
'{
  "hi": {
    "bläääa": 1234,
    "some": {
      "~": "hello",
      "0": "hello",
      "12345": [
        "yoo"
      ]
    }
  }
}';
        self::assertEquals($expected, Utils::jsonEncodePretty2Spaces($test));
    }

    public function testBuildEntryName(): void
    {
        self::assertEquals('text', Utils::buildEntryName(true, false, 'text'));
        self::assertEquals('~@text', Utils::buildEntryName(false, true, 'text'));
        self::assertEquals('~text', Utils::buildEntryName(false, false, 'text'));
        self::assertEquals('@text', Utils::buildEntryName(true, true, 'text'));
    }

    public function testIsLocal(): void
    {
        self::assertTrue(Utils::isLocal('@'));

        self::assertFalse(Utils::isLocal(''));
    }

    public function testIsEnabled(): void
    {
        self::assertTrue(Utils::isEnabled(''));

        self::assertFalse(Utils::isLocal('~'));
    }
}