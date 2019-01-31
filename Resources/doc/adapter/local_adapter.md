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
                permissions:
                    file:
                        public: 0744
                        private: 0700
                    dir:
                        public: 0755
                        private: 0700
                        
    filesystems:
        storage_local:
            adapter: storage_local
            alias: pb_storage_local
            plugins: ['pb_sulu_storage.flysystem.local.content_path.plugin']
```
**Attention!** Do not forget to define content path plugin for adapter. Contant path plugin service id for Local adapter is `pb_sulu_storage.flysystem.local.content_path.plugin`.

For more information visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/adapter_local.md) documentation.

#### Storage configuration

SuluStorageBundle config example (in your `config.yml`):
```
pb_sulu_storage:
    provider: flysystem
    flysystem:
        filesystem:
            storage: pb_storage_local
            format_cache: pb_storage_local
```
