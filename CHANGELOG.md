# Rovereto Metrika Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).

## [v1.8.0](https://github.com/ilyutkin/metrika/releases/tag/v1.8.0) - 2023-07-01

### Added

- Added doctrine/dbal 3.0

## [v1.7.0](https://github.com/ilyutkin/metrika/releases/tag/v1.7.0) - 2023-06-01

### Added

- Added data analytics methods and usage example

## [v1.6.0](https://github.com/ilyutkin/metrika/releases/tag/v1.6.0) - 2023-02-01

### Fixed

- Added the proxy to model geoip
- If there is no cookie ID, write session ID

## [v1.5.0](https://github.com/ilyutkin/metrika/releases/tag/v1.5.0) - 2022-12-01

### Added

- Added to determine the proxy by ip address

## [v1.4.0](https://github.com/ilyutkin/metrika/releases/tag/v1.4.0) - 2022-10-01

### Fixed

- If no User-Agent, store agent_id, device_id, platform_id null

## [v1.3.0](https://github.com/ilyutkin/metrika/releases/tag/v1.3.0) - 2022-08-01

### Added

- Added `query` table

### Deleted

- Deleted field `parameters`, `method` for `paths` table

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
