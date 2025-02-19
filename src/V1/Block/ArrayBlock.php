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

namespace Opctim\BrunoLang\V1\Block;

use Opctim\BrunoLang\V1\Utils\ParsedBlockData;
use Opctim\BrunoLang\V1\Block\Entry\ArrayBlockEntry;
use Opctim\BrunoLang\V1\Interface\BuilderInterface;
use Opctim\BrunoLang\V1\Utils\Utils;
use Opctim\BrunoLang\V1\Tag\ArrayBlockTag;

/**
 * @method ArrayBlockEntry[] getBlockEntries()
 * @method ArrayBlock setBlockEntries(ArrayBlockEntry[] $blockEntries)
 * @method ArrayBlock addBlockEntry(ArrayBlockEntry $blockEntry)
 */
abstract class ArrayBlock extends Block
{
    /**
     * @param string $name
     * @param ArrayBlockEntry[] $blockEntries
     */
    public function __construct(
        string $name,
        array $blockEntries = []
    )
    {
        parent::__construct($name, $blockEntries);
    }

    public static function fromParsedBlockData(ParsedBlockData $parsedBlockData): static
    {
       $entries = array_map(fn(string $line) => ArrayBlockEntry::fromParsedLine($line), $parsedBlockData->getEntries());

        if (in_array(ArrayBlockTag::class, class_parents(static::class), true)) {
            return new static($entries);
        }

       return new static($parsedBlockData->getName(), $entries);
    }

    protected function buildEntries(): string
    {
        $builtEntries = array_map(
            fn(BuilderInterface $blockEntry): string => Utils::indentString($blockEntry->build()) . ',',
            $this->blockEntries
        );

        $result = implode(PHP_EOL, $builtEntries);

        return preg_replace('/,$/', '', $result);
    }

    protected function getStartBracket(): string
    {
        return '[';
    }

    protected function getEndBracket(): string
    {
        return ']';
    }
}
