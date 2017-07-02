# SuluStorageBundle

This unofficial bundle for [Sulu CMF](https://github.com/sulu/sulu), provides possibility to use remote storage bundle (like AWS S3) for Sulu media files.

SuluMediaBundle use [Flysystem](https://github.com/thephpleague/flysystem) filesystem abstraction in compination with [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle).

**Features**
- Use local or external service as storage for media files.
- Possibility to define replica storage which will be a mirror for master storage.
- Possibility to use Flysystem cache adapter separately for master and replica storage.
- Possibility to develop own missing adapters which are supported by Flysystem.

## Attention

Current version is not stable. Some errors may occur. You are using this bundle at your own risk. If you would like to report error or suggest some improvements, please send an issue on GitHub.

## Note

This bundle bases on standard [SuluMediaBundle](https://github.com/sulu/sulu/tree/develop/src/Sulu/Bundle/MediaBundle) and it overrides some elements of SuluMediaBundle.

## Requirements

SuluMediaBundle was tested for Sulu version **1.5.x** and **1.6.x**. For more information check composer.json file.

## Installation

The installation description you will find [here](Resources/doc/index.md).

## Supported adapters

- [Local adapter](Resources/doc/local_adapter.md)
- [AwsS3v3 adapter](Resources/doc/awss3v3_adapter.md)
- [Custom adapter](Resources/doc/custom_adapter.md)
