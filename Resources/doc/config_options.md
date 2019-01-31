# SuluStorageBundle

## Config options

Configuration example:

```
pb_sulu_storage:
    provider: flysystem
    flysystem:
        filesystem:
            storage: custom_alias_to_media_storage
            format_cache: custom_alias_to_format_storage
    segments: 10
    logger: logger
```

- **provider** - This option determine what type of filesystem storage should be used. Currently only `flysystem` provider is available
- **flysystem** - Required for `flysystem` provider
    - **filesystem** - storage filesystem configuration
        - **storage** - `oneup_flysystem` filesystem service id or alias
        - **format_cache** - `oneup_flysystem` filesystem service id or alias (can be the same as `storage`)
- **segments** (default: 10) - Define maximum number of segments for media files (see [SuluMediaBundle](https://github.com/sulu/sulu/tree/develop/src/Sulu/Bundle/MediaBundle)).
- **logger** (default: logger) - Id of logger service which should be used to log storage errors 

