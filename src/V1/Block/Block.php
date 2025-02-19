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

use Opctim\BrunoLang\V1\Interface\BuilderInterface;
use Opctim\BrunoLang\V1\Interface\BlockEntryInterface;
use Opctim\BrunoLang\V1\Utils\ParsedBlockData;
use Opctim\BrunoLang\V1\Utils\Utils;

abstract class Block implements BuilderInterface
{
    /**
     * @param string $name
     * @param BlockEntryInterface[] $blockEntries
     */
    public function __construct(
        private string $name,
        protected array $blockEntries = []
    )
    {}

    abstract public static function fromParsedBlockData(ParsedBlockData $parsedBlockData): static;

    public function build(): string
    {
        $result = $this->name . ' ' . $this->getStartBracket() . PHP_EOL;

        $result .= $this->buildEntries() . PHP_EOL;

        $result .= $this->getEndBracket() . PHP_EOL;

        return $result;
    }

    abstract protected function getStartBracket(): string;

    abstract protected function getEndBracket(): string;

    protected function buildEntries(): string
    {
        $builtEntries = array_map(
            fn(BlockEntryInterface $blockEntry): string => Utils::indentString($blockEntry->build()),
            $this->blockEntries
        );

        return implode(PHP_EOL, $builtEntries);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return BlockEntryInterface[]
     */
    public function getBlockEntries(): array
    {
        return $this->blockEntries;
    }

    /**
     * @param BlockEntryInterface[] $blockEntries
     * @return $this
     */
    public function setBlockEntries(array $blockEntries): static
    {
        $this->blockEntries = $blockEntries;

        return $this;
    }

    public function addBlockEntry(BlockEntryInterface ...$blockEntries): static
    {
        $this->blockEntries =  array_merge($this->blockEntries, $blockEntries);

        return $this;
    }
}