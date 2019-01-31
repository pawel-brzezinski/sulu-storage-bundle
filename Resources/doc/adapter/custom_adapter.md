# SuluStorageBundle

Currently SuluStorageBundle does not support too many adapters. I hope that it will change in future but for now you can easily create SuluStorageBundle implementation of one of the Flysystem adapter (for available adapters please visit [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/index.md) documentation).

### Custom adapter implementation

The only thing you need to do is write a plugin for required Flysystem adapter and define this plugin as service. This plugin should provide method to get path to file content (for example in Local adapter it is absolute path to file but in AWS S3 adapter it is public url to file).

#### Write plugin
Content Path plugin must extend abstract `PB\Bundle\SuluStorageBundle\Flysystem\Plugin\AbstractContentPathPlugin`.

**Hint:** Check existing plugins for [Local](../../../Flysystem/Plugin/ContentPath/LocalContentPathPlugin.php) adapter 
and for [AWS S3](../../../Flysystem/Plugin/ContentPath/AwsS3v3ContentPathPlugin.php) adapter.

#### Define service with plugin

```
services:
    acme.custom.content_path.plugin:
        class: Acme\SiteBundle\Flysystem\Plugin\CustomContentPathPlugin
```

### More info
To get more information about Flysystem plugin check:

[OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/filesystem_plugin.md) plugin configuration
[Flysystem](https://flysystem.thephpleague.com/docs/advanced/plugins/)  official documentation
