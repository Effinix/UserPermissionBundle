## Default config file:

```yaml
effinix_user_permission:
  permission_store:
    provider:
    permissions:
      - login
  do_cache: true

when@dev:
  effinix_user_permission:
    do_cache: false
```
