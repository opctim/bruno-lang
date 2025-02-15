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

namespace Opctim\BrunoLang\Tests\V1\Tag;

use Opctim\BrunoLang\V1\Tag\Schema\AuthTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyTag;
use Opctim\BrunoLang\V1\Tag\TagFactory;
use Opctim\BrunoLang\V1\Utils\ParsedBlockData;
use PHPUnit\Framework\TestCase;

class TagFactoryTest extends TestCase
{
    public function testClassMapping(): void
    {
        $tagClasses = TagFactory::getAvailableTags();
        $tagClasses = array_map(fn(string $item) => str_replace('Opctim\\BrunoLang\\V1\\Tag\\Schema\\', '', $item), $tagClasses);
        $files = glob(__DIR__ . '/../../../../src/V1/Tag/Schema/*.php');

        foreach ($files as $file) {
            $name = str_replace('.php', '', basename($file));

            self::assertContains($name, $tagClasses);
        }
    }

    public function testUniqueTagNames(): void
    {
        $tagClasses = TagFactory::getAvailableTags();
        $tagNames = [];

        foreach ($tagClasses as $tagClass) {
            $tagNames[] = call_user_func($tagClass . '::getTagName');
        }

        $hasDuplicates = count($tagNames) !== count(array_unique($tagNames));

        $this->assertFalse($hasDuplicates, 'Tag names should not contain duplicates: ' . implode(', ', array_diff_assoc($tagNames, array_unique($tagNames))));
    }

    public function testFindByName(): void
    {
        $tagClass = TagFactory::findByTagName('auth');

        self::assertEquals(AuthTag::class, $tagClass);
    }

    public function testCreateFromBlockData(): void
    {
        $tag = TagFactory::createFromBlockData(new ParsedBlockData('body', []));

        self::assertInstanceOf(BodyTag::class, $tag);
    }
}