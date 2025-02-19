# Changelog

## 1.2.0

- Introduces splat operator for Block::addBlockEntry() & BruFile::addBlock()

## 1.1.1

- Fixes wrong return types for getBlockEntries() method on ArrayBlock & DictionaryBlock

## 1.1.0

- Introduces folder support in collections
- Now using symfony/filesystem for writing in BruFile
- Adds validation for metadata: Now throwing an InvalidRequestsStructureException if invalid (on Collection::__construct & Collection::setMeta)
- Extends tests

## 1.0.0

- Initial implementation