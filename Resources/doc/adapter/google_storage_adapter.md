# SuluStorageBundle

## Google Cloud Storage adapter

#### Filesystem configuration
Define service with Google Cloud Storage client instance and your Bucket instance. Example:
```
services:
    acme.google_storage_client:
        class: Google\Cloud\Storage\StorageClient
        arguments:
            - projectId: "your-project-id"
              keyFilePath: '/path/to/service-account.json' # optional

    acme.google_storage_bucket:
        class: Google\Cloud\Storage\Bucket
        factory: 'acme.google_storage_client:bucket'
        arguments:
            - 'your-bucket-name'
```

Example `OneupFlysystemBundle` configuration for local adapter:
```
oneup_flysystem:
    adapters:
        storage_google_storage:
            googlecloudstorage:
                client: acme.google_storage_client
                bucket: acme.google_storage_bucket

    filesystems:
        storage_google_storage:
            adapter: storage_google_storage
            alias: pb_storage_google_storage
            plugins: ['pb_sulu_storage.flysystem.google_storage.content_path.plugin']
```
**Attention!** Do not forget to define content path plugin for adapter. Content path plugin service id for Google Cloud Storage adapter is `pb_sulu_storage.flysystem.google_storage.content_path.plugin`.

For more information visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/adapter_googlecloudstorage.md) documentation.

#### Storage configuration

SuluStorageBundle config example (in your `config.yml`):
```
pb_sulu_storage:
    provider: flysystem
    flysystem:
        filesystem:
            storage: pb_storage_google_storage
            format_cache: pb_storage_google_storage
```
