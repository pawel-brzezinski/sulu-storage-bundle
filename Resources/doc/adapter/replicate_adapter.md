# SuluStorageBundle

## Local adapter

#### Filesystem configuration
Example `OneupFlysystemBundle` configuration for local adapter:
```
oneup_flysystem:
    adapters:
        storage_local:
            local:
                directory: "%kernel.root_dir%/../var/uploads/media"
        storage_awss3v3:
            awss3v3:
                client: storage.s3_client
                bucket: smartint
                prefix: pb_storage
        storage_replicate:
            replicate:
                sourceAdapter: storage_local
                replicaAdapter: storage_awss3v3
                        
    filesystems:
        storage_replica:
            adapter: storage_replicate
            alias: pb_storage_replica
            cache: storage_cache
            plugins: ['pb_sulu_storage.flysystem.local.content_path.plugin']
```
**Attention!** Do not forget to define content path plugin for ***source*** adapter.

For more information visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/adapter_replicate.md) documentation.

#### Storage configuration

SuluStorageBundle config example (in your `config.yml`):
```
pb_sulu_storage:
    provider: flysystem
    flysystem:
        filesystem:
            storage: storage_replica
            format_cache: storage_replica
```
