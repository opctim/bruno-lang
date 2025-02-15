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

use Opctim\BrunoLang\V1\Exception\InvalidCollectionMetaException;
use Opctim\BrunoLang\V1\Interface\WriterInterface;
use Opctim\BrunoLang\V1\Utils\Utils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Collection implements WriterInterface
{
    public function __construct(
        /** @var array{
         *     version: string,
         *     name: string,
         *     type: "collection",
         *     ignore: string[],
         *     presets: array<string, mixed>
         * } $meta
         */
        private array $meta = [],
        /** @var BruFile[] $requests */
        private array $requests = [],
        /** @var BruFile[] $environments */
        private array $environments = [],
    )
    {}

    /**
     * @throws InvalidCollectionMetaException
     */
    public static function parse(string $filePath): self
    {
        $finder = Finder::create()->files()->in($filePath);

        $meta = [];
        $requests = [];
        $environments = [];

        foreach ($finder as $collectionFile) {
            if ($collectionFile->getRelativePath() === '') {
                if ($collectionFile->getBasename() === 'bruno.json') {
                    $meta = json_decode($collectionFile->getContents(), true);

                    if (!isset($meta['name'])) {
                        throw new InvalidCollectionMetaException("The collection bruno.json doesn't have a name.");
                    }

                    if (!isset($meta['version'])) {
                        throw new InvalidCollectionMetaException("The collection bruno.json doesn't have a version.");
                    }

                    if ($meta['version'] !== '1') {
                        throw new InvalidCollectionMetaException(Collection::class . ' only supports version 1.');
                    }
                }

                if ($collectionFile->getExtension() === 'bru') {
                    $requests[] = BruFile::parse($collectionFile->getBasename('.bru'), $collectionFile->getContents());
                }
            }

            if ($collectionFile->getRelativePath() === 'environments') {
                $environments[] = BruFile::parse($collectionFile->getBasename('.bru'), $collectionFile->getContents());
            }
        }

        return new self($meta, $requests, $environments);
    }

    public function write(string $filePath): static
    {
        // Replace trailing slash
        $filePath = preg_replace('/\/$/', '', $filePath);

        $filesystem = new Filesystem();

        $filesystem->mkdir($filePath);

        foreach ($this->getRequests() as $bruFile) {
            $filesystem->dumpFile($filePath . '/' . $bruFile->getName() . '.bru', $bruFile->build());
        }

        if (count($this->getEnvironments()) > 0) {
            $filesystem->mkdir($filePath . '/environments');
        }

        foreach ($this->getEnvironments() as $bruFile) {
            $filesystem->dumpFile($filePath . '/environments/' . $bruFile->getName() . '.bru', $bruFile->build());
        }

        $filesystem->dumpFile($filePath . '/bruno.json', Utils::jsonEncodePretty2Spaces($this->getMeta()));

        return $this;
    }

    public function getName(): string
    {
        return $this->getMeta()['name'];
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function setRequests(array $requests): static
    {
        $this->requests = $requests;

        return $this;
    }

    public function getEnvironments(): array
    {
        return $this->environments;
    }

    public function setEnvironments(array $environments): static
    {
        $this->environments = $environments;

        return $this;
    }
}