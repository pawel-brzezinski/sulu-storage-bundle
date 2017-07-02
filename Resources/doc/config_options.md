# SuluStorageBundle

## Config options

Configuration example:

```
pb_sulu_storage:
    master:
        type: awss3v3
        filesystem: master
        segments: 10
    replica:
        type: local
        filesystem: replica
    format_cache:
        type: awss3v3
        filesystem: format_cache
```

You can define 3 different storages:
- **master** - main storage
- **replica** - replica storage
- **format_cache** - sulu media format cache storage

**master** and **format_cache** are required, **replica** is optional.

Each storage has the following options:
- **type** - unique id for adapter implementation. Currently available (`local`, `awss3v3`).
- **filesystem** - the name of filesystem defined in `oneup_flysystem` configuration.
- **segments** - optional, to use only in `master` storage. Define maximum number of segments for media files
(see [SuluMediaBundle](https://github.com/sulu/sulu/tree/develop/src/Sulu/Bundle/MediaBundle)).