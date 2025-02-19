# Changelog

## 1.1.0

- Introduces folder support in collections
- Now using symfony/filesystem for writing in BruFile
- Adds validation for metadata: Now throwing an InvalidRequestsStructureException if invalid (on Collection::__construct & Collection::setMeta)
- Extends tests

## 1.0.0

- Initial implementation