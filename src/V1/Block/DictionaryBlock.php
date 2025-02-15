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
use Opctim\BrunoLang\V1\Block\Entry\DictionaryBlockEntry;
use Opctim\BrunoLang\V1\Tag\DictionaryBlockTag;

/**
 * @method DictionaryBlockEntry getBlockEntries()
 * @method DictionaryBlock setBlockEntries(DictionaryBlockEntry[] $blockEntries)
 * @method DictionaryBlock addBlockEntry(DictionaryBlockEntry $blockEntry)
 */
abstract class DictionaryBlock extends Block
{
    /**
     * @param string $name
     * @param DictionaryBlockEntry[] $blockEntries
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
        $entries = array_map(fn(string $line) => DictionaryBlockEntry::fromParsedLine($line), $parsedBlockData->getEntries());

        if (in_array(DictionaryBlockTag::class, class_parents(static::class), true)) {
            return new static($entries);
        }

        return new static($parsedBlockData->getName(), $entries);
    }

    /**
     * Will return the first occurrence of a block entry with the given name.
     *
     * @param string $name
     * @return DictionaryBlockEntry | null
     */
    public function findOneBlockEntryByName(string $name): ?DictionaryBlockEntry
    {
        foreach ($this->getBlockEntries() as $blockEntry) {
            if ($blockEntry->getName() === $name) {
                return $blockEntry;
            }
        }

        return null;
    }

    /**
     * Will return all block entries with the given name.
     *
     * @param string $name
     * @return DictionaryBlockEntry[]
     */
    public function findBlockEntryByName(string $name): array
    {
        $result = [];

        foreach ($this->getBlockEntries() as $blockEntry) {
            if ($blockEntry->getName() === $name) {
                $result[] = $blockEntry;
            }
        }

        return $result;
    }

    /**
     * Removes all block entries with the given name.
     *
     * @param string $name
     * @return static
     */
    public function removeBlockEntriesByName(string $name): static
    {
        $result = [];

        foreach ($this->getBlockEntries() as $block) {
            if ($block->getName() !== $name) {
                $result[] = $block;
            }
        }

        $this->setBlockEntries($result);

        return $this;
    }

    protected function getStartBracket(): string
    {
        return '{';
    }

    protected function getEndBracket(): string
    {
        return '}';
    }
}
