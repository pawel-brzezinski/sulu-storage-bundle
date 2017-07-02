# SuluStorageBundle

## Local adapter

#### Filesystem configuration

For more information visit 
[OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/adapter_local.md) documentation.

#### Storage configuration

Master storage config example (in your *config.yml*):

```
pb_sulu_storage:
    master:
        type: local
        filesystem: your_filesystem_name
        segments: 10
```