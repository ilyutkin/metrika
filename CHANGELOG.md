# Rovereto Metrika Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).

## [v1.2.0](https://github.com/ilyutkin/metrika/releases/tag/1.2.0) - 2022-07-01

### Changed

- Changed field name `division_code` to `subdivision_code` for `geoips` table

### Added

- Added fields with extended information to the geo model
- Added IP2Location support for geoips

## [v1.1.0](https://github.com/ilyutkin/metrika/releases/tag/1.1.0) - 2022-06-01

### Added

- Added the definition of robots by User-Agent in the `visitors` table

### Fixed

- In job `\Rovereto\Metrika\Jobs\CrunchStatistics`, if no route found, store null

## [v1.0.0](https://github.com/ilyutkin/metrika/releases/tag/1.0.0) - 2022-05-01

- Tag first release
