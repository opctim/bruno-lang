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
use Opctim\BrunoLang\V1\Exception\InvalidRequestsStructureException;
use Opctim\BrunoLang\V1\Interface\WriterInterface;
use Opctim\BrunoLang\V1\Utils\Utils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @phpstan-type RequestsType array<int|string, BruFile|RequestsType>
 * @phpstan-type MetaType array{
 *      version: string,
 *      name: string,
 *      type: "collection",
 *      ignore: string[],
 *      presets: array<string, mixed>
 *  }
 */
class Collection implements WriterInterface
{
    /**
     * @throws InvalidCollectionMetaException
     */
    public function __construct(
        /** @var MetaType $meta */
        private array $meta = [],
        /** @var RequestsType $requests */
        private array $requests = [],
        /** @var BruFile[] $environments */
        private array $environments = [],
    )
    {
        self::validateMeta($meta);
    }

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
            if ($collectionFile->getRelativePath() === 'environments') {
                // Parse environments
                $environments[] = BruFile::parse($collectionFile->getBasename('.bru'), $collectionFile->getContents());
            } else if ($collectionFile->getRelativePath() === '') {
                // Parse metadata
                if ($collectionFile->getBasename() === 'bruno.json') {
                    $meta = json_decode($collectionFile->getContents(), true);
                }

                // Parse bru files in directory root
                if ($collectionFile->getExtension() === 'bru') {
                    $requests[] = BruFile::parse($collectionFile->getBasename('.bru'), $collectionFile->getContents());
                }
            } else {
                // Parse nested bru files
                $folders = explode('/', $collectionFile->getRelativePath());

                $nested = &$requests; // Reference to the root

                foreach ($folders as $folder) {
                    $nested = &$nested[$folder]; // Going deeper
                }

                $nested[] = BruFile::parse($collectionFile->getBasename('.bru'), $collectionFile->getContents());
            }
        }

        return new self($meta, $requests, $environments);
    }

    /**
     * @throws InvalidRequestsStructureException
     */
    public function write(string $filePath): static
    {
        $filePath = rtrim($filePath, '/');

        // Replace trailing slash
        $filePath = preg_replace('/\/$/', '', $filePath);

        $filesystem = new Filesystem();

        $filesystem->mkdir($filePath);

        // Recursively write requests
        $this->writeRequests($filePath, $this->getRequests());

        foreach ($this->getEnvironments() as $bruFile) {
            $bruFile->write($filePath . '/environments');
        }

        $filesystem->dumpFile($filePath . '/bruno.json', Utils::jsonEncodePretty2Spaces($this->getMeta()));

        return $this;
    }

    /**
     * @param string $basePath
     * @param RequestsType $requests
     *
     * @throws InvalidRequestsStructureException
     */
    protected function writeRequests(string $basePath, array $requests): void
    {
        foreach ($requests as $key => $item) {
            if (is_string($key) && is_array($item)) {
                $this->writeRequests($basePath . '/' . $key, $item);
            } else if (is_int($key) && $item instanceof BruFile) {
                $item->write($basePath);
            } else {
                throw new InvalidRequestsStructureException('Invalid requests structure. Key "' . $key . '" and value "' . gettype($item) . '" are not supported.');
            }
        }
    }

    /**
     * @param MetaType $meta
     *
     * @throws InvalidCollectionMetaException
     */
    protected static function validateMeta(array $meta): void
    {
        if (!isset($meta['name'])) {
            throw new InvalidCollectionMetaException("The collection meta doesn't have a name.");
        }

        if (!isset($meta['version'])) {
            throw new InvalidCollectionMetaException("The collection meta doesn't have a version.");
        }

        if ($meta['version'] !== '1') {
            throw new InvalidCollectionMetaException(Collection::class . ' only supports version 1.');
        }
    }

    public function getName(): string
    {
        return $this->getMeta()['name'];
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @throws InvalidCollectionMetaException
     */
    public function setMeta(array $meta): static
    {
        self::validateMeta($meta);

        $this->meta = $meta;

        return $this;
    }

    /**
     * @return RequestsType
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @param RequestsType $requests
     *
     * @return $this
     */
    public function setRequests(array $requests): static
    {
        $this->requests = $requests;

        return $this;
    }

    /**
     * @return BruFile[]
     */
    public function getFlatRequests(): array
    {
        return $this->flattenRequests($this->getRequests());
    }

    protected function flattenRequests(array $input): array
    {
        $flattened = [];

        foreach ($input as $value) {
            if ($value instanceof BruFile) {
                $flattened[] = $value;
            } elseif (is_array($value)) {
                $flattened = array_merge($flattened, $this->flattenRequests($value));
            }
        }

        return $flattened;
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