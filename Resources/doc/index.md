# SuluStorageBundle

## Getting started

This bundle was tested for Sulu version **1.5.x** and **1.6.x** based on Symfony version **2.8.x** and **3.x**.

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

It is time to use **OneupFlysystemBundle**. In your `config.yml` file you need to configure filesystem which you want 
to use as the master storage, replica storage (optional) and format cache storage. Here is the example:

```
oneup_flysystem:
    adapters:
        master_adapter:
            awss3v3:
                client: my.s3_client
                bucket: mybucket
                prefix: sulu/uploads/media
        replica_adapter:
            local:
                directory: "%kernel.var_dir%/uploads/media"
        cache_format_adapter:
            awss3v3:
                client: my.s3_client
                bucket: mybucket
                prefix: sulu/uploads/cache
    filesystems:
        master:
            adapter: master_adapter
        replica:
            adapter: replica_adapter
        format_cache:
            adapter: cache_format_adapter
```

**Note:** Filesystem for replica is not necessary if you don't want to use replication.

To get more information please visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle).

#### Step 5: Configure storage

In this step you have to configure storage for master, replica (optional) and format cache. In your `config.yml` file
add:

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

**Note:** Storage for replica is not necessary if you don't want to use replication.

For more information please visit [Config Options](config_options.md) documentation.

#### Step 6: Define routing

SuluStorageBundle override some standard SuluMediaBundle elements. One of these elements is overwritten URL addresses.
To enable this overwriting we have add this definition:

```
pb_sulu_storage:
    resource: "@PBSuluStorageBundle/Resources/config/routing.yml"
```

to `app/config/website/routing.yml` (this file is included to admin routing). 

**Attention:** It is very important to add this **before** `sulu_media` definition. Finally it should looks like this:

```
pb_sulu_storage:
    resource: "@PBSuluStorageBundle/Resources/config/routing.yml"

sulu_media:
    resource: "@SuluMediaBundle/Resources/config/routing_website.yml"
    
...
```

## Cache
Visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/filesystem_cache.md)
documentation for more information.

That's all! You can now use your Flysystem filesystems storage.