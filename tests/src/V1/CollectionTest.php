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
use Opctim\BrunoLang\V1\Collection;
use Opctim\BrunoLang\V1\Tag\Schema\GetTag;
use Opctim\BrunoLang\V1\Tag\Schema\MetaTag;
use Opctim\BrunoLang\V1\Tag\Schema\PostTag;
use Opctim\BrunoLang\V1\Tag\Schema\VarsTag;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class CollectionTest extends TestCase
{
    public function testFlattenRequests(): void
    {
        $c = new Collection([
            'name' => 'test',
            'version' => '1'
        ], [
            'test' => [
                new BruFile('test', []),
                'oneLevelDeeper' => [
                    new BruFile('test', []),
                ]
            ],
            new BruFile('test', []),
            new BruFile('test', [])
        ]);

        self::assertCount(3, $c->getRequests());
        self::assertCount(4, $c->getFlatRequests());
        self::assertInstanceOf(BruFile::class, $c->getFlatRequests()[1]);
    }

    public function testCollectionReadAndWrite(): void
    {
        $base = __DIR__ . '/../../fixtures';

        $expected = $base . '/my_collection';
        $actual = $base . '/write_test';

        $c = Collection::parse($expected);
        $c->write($actual);

        $this->assertDirectoriesAreEqual($expected, $actual);
    }

    public function testErroneousCollectionReadAndWrite(): void
    {
        $base = __DIR__ . '/../../fixtures';

        $expected = $base . '/my_collection';
        $actual = $base . '/write_test';

        $c = Collection::parse($expected);

        foreach ($c->getRequests() as $request) {
            // adding data to each request...
            $request->addBlock(new GetTag([
                new DictionaryBlockEntry('test', '1234')
            ]));
        }

        $c->write($actual);

        $this->assertDirectoriesAreNotEqual($expected, $actual);
    }

    public function testCollectionReadAndWrite2(): void
    {
        $base = __DIR__ . '/../../fixtures';

        $expected = $base . '/my_collection_2';
        $actual = $base . '/write_test';

        $c = Collection::parse($expected);
        $c->write($actual);

        $this->assertDirectoriesAreEqual($expected, $actual);
    }

    public function testAddCollectionProgrammatically(): void
    {
        $meta = [
            'version' => '1',
            'name' => 'my_collection_2',
            'type' => 'collection',
            'ignore' => [
                'node_modules',
                '.git'
            ]
        ];

        $requests = [
            new BruFile('test', [
                new MetaTag([
                    new DictionaryBlockEntry('name', 'test'),
                    new DictionaryBlockEntry('type', 'graphql'),
                    new DictionaryBlockEntry('seq', '2')
                ]),
                new PostTag([
                    new DictionaryBlockEntry('url', 'https://google.com'),
                    new DictionaryBlockEntry('body', 'none'),
                    new DictionaryBlockEntry('auth', 'none')
                ])
            ]),
            new BruFile('test2', [
                new MetaTag([
                    new DictionaryBlockEntry('name', 'test2'),
                    new DictionaryBlockEntry('type', 'http'),
                    new DictionaryBlockEntry('seq', '3')
                ]),
                new GetTag([
                    new DictionaryBlockEntry('url', 'https://google.com/abc'),
                    new DictionaryBlockEntry('body', 'none'),
                    new DictionaryBlockEntry('auth', 'none')
                ])
            ]),
            'test' => [
                'test2' => [
                    new BruFile('nested', [
                        new MetaTag([
                            new DictionaryBlockEntry('name', 'nested'),
                            new DictionaryBlockEntry('type', 'http'),
                            new DictionaryBlockEntry('seq', '1')
                        ]),
                        new GetTag([
                            new DictionaryBlockEntry('url', '/nested'),
                            new DictionaryBlockEntry('body', 'none'),
                            new DictionaryBlockEntry('auth', 'none')
                        ])
                    ]),
                    new BruFile('nested2', [
                        new MetaTag([
                            new DictionaryBlockEntry('name', 'nested2'),
                            new DictionaryBlockEntry('type', 'http'),
                            new DictionaryBlockEntry('seq', '1')
                        ]),
                        new GetTag([
                            new DictionaryBlockEntry('url', '/nested'),
                            new DictionaryBlockEntry('body', 'none'),
                            new DictionaryBlockEntry('auth', 'none')
                        ])
                    ]),
                ],
                new BruFile('first_nested', [
                    new MetaTag([
                        new DictionaryBlockEntry('name', 'first_nested'),
                        new DictionaryBlockEntry('type', 'http'),
                        new DictionaryBlockEntry('seq', '1')
                    ]),
                    new GetTag([
                        new DictionaryBlockEntry('url', '/nested'),
                        new DictionaryBlockEntry('body', 'none'),
                        new DictionaryBlockEntry('auth', 'none')
                    ])
                ]),
            ]
        ];

        $environments = [
            new BruFile('test', [
                new VarsTag([
                    new DictionaryBlockEntry('my_var', '12345')
                ])
            ])
        ];

        $base = __DIR__ . '/../../fixtures';

        $expected = $base . '/my_collection_2';
        $actual = $base . '/write_test';

        $c = new Collection($meta, $requests, $environments);
        $c->write($actual);

        $this->assertDirectoriesAreEqual($expected, $actual);
    }

    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../../fixtures/write_test');
    }

    private function assertDirectoriesAreEqual(string $expectedDir, string $actualDir): void
    {
        $expectedFiles = $this->getAllFiles($expectedDir);
        $actualFiles = $this->getAllFiles($actualDir);

        // Compare directory structure
        $this->assertSame(
            $expectedFiles,
            $actualFiles,
            "Directory structure mismatch! Expected: " . print_r($expectedFiles, true) .
            " but found: " . print_r($actualFiles, true)
        );

        // Compare file contents STRICTLY
        foreach ($expectedFiles as $file) {
            $expectedFilePath = $expectedDir . DIRECTORY_SEPARATOR . $file;
            $actualFilePath = $actualDir . DIRECTORY_SEPARATOR . $file;

            $this->assertFileExists($actualFilePath, "File is missing: $file");

            // Read raw contents and normalize line endings (cross-platform consistency)
            $expectedContent = str_replace("\r\n", "\n", file_get_contents($expectedFilePath));
            $actualContent = str_replace("\r\n", "\n", file_get_contents($actualFilePath));

            $this->assertSame(
                $expectedContent,
                $actualContent,
                "File content mismatch in: $file"
            );
        }
    }

    private function assertDirectoriesAreNotEqual(string $expectedDir, string $actualDir): void
    {
        $expectedFiles = $this->getAllFiles($expectedDir);
        $actualFiles = $this->getAllFiles($actualDir);

        // Check if directory structures differ
        if ($expectedFiles !== $actualFiles) {
            $this->assertNotSame(
                $expectedFiles,
                $actualFiles,
                "Expected directory structure to be different, but they match."
            );
            return; // Stop checking contents if structure already differs
        }

        // Check for content differences
        foreach ($expectedFiles as $file) {
            $expectedFilePath = $expectedDir . DIRECTORY_SEPARATOR . $file;
            $actualFilePath = $actualDir . DIRECTORY_SEPARATOR . $file;

            if (!file_exists($actualFilePath)) {
                continue; // Missing files are already handled in the structure check
            }

            $expectedContent = str_replace("\r\n", "\n", file_get_contents($expectedFilePath));
            $actualContent = str_replace("\r\n", "\n", file_get_contents($actualFilePath));

            if ($expectedContent !== $actualContent) {
                $this->assertNotSame(
                    $expectedContent,
                    $actualContent,
                    "Expected file '$file' to have different content, but it matches."
                );
                return; // Stop checking further files after finding a difference
            }
        }

        // If no differences were found, explicitly fail the test
        $this->fail("Expected directories to be different, but they are identical.");
    }

    private function getAllFiles(string $dir, string $basePath = ''): array
    {
        $files = [];
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $relativePath = ltrim($basePath . DIRECTORY_SEPARATOR . $item, DIRECTORY_SEPARATOR);
            $fullPath = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                $files = array_merge($files, $this->getAllFiles($fullPath, $relativePath));
            } else {
                $files[] = $relativePath;
            }
        }

        sort($files); // Ensure consistent ordering

        return $files;
    }
}