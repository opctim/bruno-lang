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

namespace Opctim\BrunoLang\Tests\V1;

use Opctim\BrunoLang\V1\Block\Entry\DictionaryBlockEntry;
use Opctim\BrunoLang\V1\BruFile;
use Opctim\BrunoLang\V1\Tag\Schema\BodyJsonTag;
use Opctim\BrunoLang\V1\Tag\Schema\HeadersTag;
use Opctim\BrunoLang\V1\Tag\Schema\MetaTag;
use Opctim\BrunoLang\V1\Tag\Schema\PostTag;
use Opctim\BrunoLang\V1\Tag\Schema\ScriptPostResponseTag;
use PHPUnit\Framework\TestCase;

class BruFileTest extends TestCase
{
    public function testParseAndBuild(): void
    {
        $testFiles = [
            'file' => __DIR__ . '/../../fixtures/my_collection/file.bru',
            'cities' => __DIR__ . '/../../fixtures/my_collection/cities.bru',
            'login' => __DIR__ . '/../../fixtures/my_collection/login.bru',
        ];

        foreach ($testFiles as $testFileName => $testFile) {
            $bruFile = file_get_contents($testFile);

            $bru = BruFile::parse($testFileName, $bruFile);

            self::assertEquals($bruFile, $bru->build());
        }
    }

    public function testManualBuild(): void
    {
        $bruFile = new BruFile('test', [
            new MetaTag([
                new DictionaryBlockEntry('name', 'Login'),
                new DictionaryBlockEntry('type', 'http'),
                new DictionaryBlockEntry('seq', '3'),
            ]),
            new PostTag([
                new DictionaryBlockEntry('url', '{{baseUrl}}/api/login'),
                new DictionaryBlockEntry('body', 'json'),
                new DictionaryBlockEntry('auth', 'none'),
            ]),
            new HeadersTag([
                new DictionaryBlockEntry('Content-Type', 'application/json'),
                new DictionaryBlockEntry('X-Custom', '1234', false),
            ]),
            new BodyJsonTag('{
  "username": "john@example.com",
  "password": "1234"
}'),
            new ScriptPostResponseTag("bru.setVar('auth_token', res.body.token)")
        ]);

        self::assertEquals(file_get_contents(__DIR__ . '/../../fixtures/my_collection/login.bru'), $bruFile->build());
    }
}