# SuluStorageBundle

## Local adapter

#### Filesystem configuration
Define service with AWS S3 client instance. Example:
```
services:
    storage.s3_client:
        class: Aws\S3\S3Client
        arguments:
            -
                version: 'latest'
                region: '<s3-region>'
                credentials:
                    key: '<key>'
                    secret: '<secret>'
```

Example `OneupFlysystemBundle` configuration for local adapter:
```
oneup_flysystem:
    adapters:
        storage_awss3v3:
            awss3v3:
                client: storage.s3_client
                bucket: my_bucket
                        
    filesystems:
        storage_awss3v3:
            adapter: storage_awss3v3
            alias: pb_storage_awss3v3
            plugins: ['pb_sulu_storage.flysystem.awss3v3.content_path.plugin']
```
**Attention!** Do not forget to define content path plugin for adapter. Contant path plugin service id for Local adapter is `pb_sulu_storage.flysystem.awss3v3.content_path.plugin`.

For more information visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/adapter_awss3.md) documentation.

#### Storage configuration

SuluStorageBundle config example (in your `config.yml`):
```
pb_sulu_storage:
    provider: flysystem
    flysystem:
        filesystem:
            storage: pb_storage_awss3v3
            format_cache: pb_storage_awss3v3
```
