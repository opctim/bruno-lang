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

namespace Opctim\BrunoLang\V1;

use Opctim\BrunoLang\V1\Interface\TagInterface;
use Opctim\BrunoLang\V1\Utils\ParsedBlockData;
use Opctim\BrunoLang\V1\Block\Block;
use Opctim\BrunoLang\V1\Interface\BuilderInterface;
use Opctim\BrunoLang\V1\Interface\WriterInterface;
use Opctim\BrunoLang\V1\Tag\TagFactory;
use Opctim\BrunoLang\V1\Utils\Utils;

class BruFile implements BuilderInterface, WriterInterface
{
    /**
     * @param string $name
     * @param Block[] $blocks
     */
    public function __construct(
        private string $name,
        private array $blocks = []
    )
    {}

    public static function parse(string $name, string $bruFileContents): static
    {
        $lines = preg_split('/\R/', $bruFileContents);

        $blocks = [];

        do {
            do {
                $line = array_shift($lines);
            } while ($line !== null && !preg_match('/^[^{[]+[{\[]$/', trim($line)));

            if ($line !== null) {
                // $line now contains the beginning of a block
                // find the block name
                preg_match('/^(?<NAME>[^{[]+)/', $line, $matches);
                $blockName = $matches['NAME'] ?? null;

                if ($blockName) {
                    $blockName = trim($blockName);

                    $entries = [];

                    while (!preg_match('/^([}\]])$/', $line = array_shift($lines))) {
                        if (trim($line) !== '') {
                            $entries[] = Utils::stripIndentation($line);
                        }
                    }

                    $blockData = new ParsedBlockData(
                        $blockName,
                        $entries
                    );

                    $tag = TagFactory::createFromBlockData($blockData);

                    if ($tag) {
                        $blocks[] = $tag;
                    } else {
                        error_log('opctim/bruno-lang: Unknown tag "' . $blockData->getName() . '" encountered. Feel free to contribute! Remember to run composer dump-autoload after adding the tag to Opctim\\BrunoLang\\V1\\Tag\\Schema.');
                    }
                }
            }
        } while ($line !== null);

        return new static($name, $blocks);
    }

    public function build(): string
    {
        $builtNodes = array_map(fn(Block $block) => $block->build(), $this->blocks);

        return implode(PHP_EOL, $builtNodes);
    }

    public function write(string $filePath): static
    {
        file_put_contents($filePath, $this->build());

        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    public function addBlock(Block & TagInterface $block): static
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     * Will return the first occurrence of a block with the given name.
     *
     * @param string $name
     * @return (Block & TagInterface) | null
     */
    public function findOneBlockByName(string $name): ?Block
    {
        foreach ($this->getBlocks() as $block) {
            if ($block->getName() === $name) {
                return $block;
            }
        }

        return null;
    }

    /**
     * Will return all blocks with the given name.
     *
     * @param string $name
     * @return (Block & TagInterface)[]
     */
    public function findBlocksByName(string $name): array
    {
        $result = [];

        foreach ($this->getBlocks() as $block) {
            if ($block->getName() === $name) {
                $result[] = $block;
            }
        }

        return $result;
    }

    /**
     * Removes all blocks with the given name.
     *
     * @param string $name
     * @return static
     */
    public function removeBlocksByName(string $name): static
    {
        $result = [];

        foreach ($this->getBlocks() as $block) {
            if ($block->getName() !== $name) {
                $result[] = $block;
            }
        }

        $this->setBlocks($result);

        return $this;
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
}