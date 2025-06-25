# Changelog

All notable changes to this project will be documented in this file.

## [1.3.1] - 2025-06-25
### Fixed
- Dispatched `MessageSent` on forward messages
- Fixed pagination on messages reactions and views

## [1.3.0] - 2025-06-23
### Added
- Added universal chat type support

## [1.2.1] - 2025-06-19
### Added
- Added rate limiting for messages and chat creation routes with configuration

### Fixed
- Dispatch `MessageSent` event to only those users with unmuted chat

## [1.2.0] - 2025-06-18
### Added
- Event: `MessageSent` to allow custom hooks after sending messages

## [1.1.0] - 2025-06-17
### Changed
- Updated service class function for more user friendy usage

## [1.0.5] - 2025-06-16
### Added
- Support for forwarding multiple messages to multiple chats

## [1.0.4] - 2025-06-13
### Added
- Added an artisan command to install chat

## [1.0.3] - 2025-06-12
### Changed
- Improved chat ordering logic based on latest message or chat creation

## [1.0.2] - 2025-06-11
### Fixed
- Prevent actions on deleted messages

### Changed
- Added configuration for chat group users

## [1.0.1] - 2025-06-10
### Changed
- Updated resource class structure
- Improved chat ordering logic based on type message

## [1.0.0] - 2025-06-06
- Initial release
- Messaging system (groups, private chats)
- Attachment support
- Configurable file types
