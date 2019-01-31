# SuluStorageBundle

## Getting started
This bundle was tested for Sulu version **1.6.x** based on Symfony version **2.8.x** and **3.x**.

## Installation
#### Step 1: Download the bundle
Download SuluStorageBundle via Composer:
```
composer require pawel-brzezinski/sulu-storage-bundle
```

#### Step 2: Download necessary adapters
Standard Flysystem package include only default adapters. Check below the adapters list which must be installed 
additionally if you want to use them:
  - The AwsS3v3 adapter `"league/flysystem-aws-s3-v3"`
  - The Cached adapter `"league/flysystem-cached-adapter"`
  
#### Step 3: Enable the bundle
Add this bundle to `app/AbstractKernel.php`
```
new PB\Bundle\SuluStorageBundle\PBSuluStorageBundle(),
```

#### Step 4: Configure filesystems
It is time to use **OneupFlysystemBundle**. In your `config.yml` file you need to configure filesystem which you want to use as the master storage, replica storage (optional) and format cache storage. Here is the example:
```
oneup_flysystem:
    cache:
        storage_cache:
            predis:
                client: cache.redis
                
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
        storage_awss3v3:
            awss3v3:
                client: storage.s3_client
                bucket: smartint
                prefix: pb_storage
    
    filesystems:
        media_storage:
            adapter: storage_awss3v3
            alias: custom_alias_to_media_storage
            cache: storage_cache
            plugins: ['pb_sulu_storage.flysystem.awss3v3.content_path.plugin']
            
        format_storage:
            adapter: storage_local
            alias: custom_alias_to_format_storage
            cache: storage_cache
            plugins: ['pb_sulu_storage.flysystem.local.content_path.plugin']
```

**Note:** Each storage MUST HAVE defined content path plugin (see documentation for specific adapter).

To get more information please visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle).

#### Step 5: Configure storage

In this step you have to configure Sulu to use new storage. In your `config.yml` file add:
```
pb_sulu_storage:
    provider: flysystem
    logger: 'logger'
    flysystem:
        filesystem:
            storage: custom_alias_to_media_storage
            format_cache: custom_alias_to_format_storage
```
For more information please visit [Config Options](config_options.md) documentation.

#### Step 6: Define routing
SuluStorageBundle override some standard SuluMediaBundle elements. One of these elements is overwritten URL addresses.
To enable this overwriting we have add this definition:
```
pb_sulu_storage:
    resource: "@PBSuluStorageBundle/Resources/config/routing.yaml"
```
to `app/config/website/routing.yml` (this file is included to admin routing). 
**Attention:** It is very important to add this **before** `sulu_media` definition. Finally it should looks like this:
```
pb_sulu_storage:
    resource: "@PBSuluStorageBundle/Resources/config/routing.yaml"

sulu_media:
    resource: "@SuluMediaBundle/Resources/config/routing_website.yml"
    
...
```

## Cache
To improve files loading time you should consider enabling caching for Flysystem filesystems.
Visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/filesystem_cache.md) documentation for more information.

That's all! You can now use your Flysystem filesystems storage.

## Supported adapters
- [Local adapter](adapter/local_adapter.md)
- [AwsS3v3 adapter](adapter/awss3v3_adapter.md)
- [Replicate adapter](adapter/replicate_adapter.md)
- [Custom adapter implementation](adapter/custom_adapter.md)
