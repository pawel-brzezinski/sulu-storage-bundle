# SuluStorageBundle

## Custom adapter implementation

Currently SuluStorageBundle does not support too many adapters. I hope that it will change in future but for now
you can easily create SuluStorageBundle implementation of one of the Flysystem adapter (for available adapters 
please visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/index.md) documentation).

#### Step 1: Unique identifier for adapter

Think about some unique identifier for your adapter implementation. In this documentation it will be represented by
`my_adapter_id` string. 

Currently, these ids are in use:
- local
- awss3v3

#### Step 2: Path resolver

###### Create path resolver class

You need to create path resolver for adapter which will be an implementation of `PathResolverInterface`.
For more clarity check out currently existing path resolvers, for ex:
- [LocalPathResolver](../../Resolver/LocalPathResolver.php)
- [AwsS3v3PathResolver](../../Resolver/AwsS3v3PathResolver.php)

###### Define path resolver service

Define your path resolver class as service and tag it by `pb_sulu_storage.path_resolver` tag
with `my_adapter_id` alias. Example:

```
app.local.path_resolver:
    class: AppBundle\Resolver\MyPathResolver
    tags:
        - { name: pb_sulu_storage.path_resolver, alias: my_adapter_id }
```

**Hint:** Check [AbstractPathResolver.php](../../Resolver/AbstractPathResolver.php).
Maybe you just need to extend this abstract.

#### Step 2: Url resolver

This resolver is very useful for remote adapters where you can access
to your resources by url (like in AWS S3 ex: `https://somebucket.s3.eu-central-1.amazonaws.com/path/to/file.jpg`,
or in Local ex: `/uploads/file.jpg`).

In this case, StreamMediaController will make redirect if it will be an external url instead of render file.

###### Create url resolver class

Create url resolver class for adapter which will be an implementation of `UrlResolverInterface`.
For more clarity check out currently existing url resolver:
- [AwsS3v3UrlResolver](../../Resolver/AwsS3v3UrlResolver.php)
- [LocalUrlResolver](../../Resolver/LocalUrlResolver.php)

###### Define url resolver service

Define your url resolver class as service and tag it by `pb_sulu_storage.url_resolver` tag 
with `my_adapter_id` alias. Example:

```
app.local.url_resolver:
    class: AppBundle\Resolver\MyUrlResolver
    tags:
        - { name: pb_sulu_storage.url_resolver, alias: my_adapter_id }
```

#### Step 3: Use your adapter implementation

Finally you can use your adapter implementation as storage. Example:

```
master:
    type: my_adapter_id
    filesystem: fsname
    segments: 10
```
