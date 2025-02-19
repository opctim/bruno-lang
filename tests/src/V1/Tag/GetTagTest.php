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

namespace V1\Tag;

use Opctim\BrunoLang\V1\Block\Entry\DictionaryBlockEntry;
use Opctim\BrunoLang\V1\Tag\Schema\GetTag;
use PHPUnit\Framework\TestCase;

class GetTagTest extends TestCase
{
    public function testSplatOperator(): void
    {
        $bruFile = new GetTag([]);

        $bruFile->addBlockEntry(new DictionaryBlockEntry('test', 'test'));
        $bruFile->addBlockEntry(new DictionaryBlockEntry('test', 'test'), new DictionaryBlockEntry('test', 'test'), new DictionaryBlockEntry('test', 'test'));

        self::assertCount(4, $bruFile->getBlockEntries());
    }
}