
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=agence-adeliom_easy-redirect-bundle)](https://sonarcloud.io/dashboard?id=agence-adeliom_easy-redirect-bundle)

# Easy Redirect Bundle

This bundle adds entities for redirects and 404 errors.

For redirects, 404 errors are intercepted and the requested path is looked up. If a match is found it redirects to the found redirect's destination. The count and last accessed date are updated as well. A redirect form type and validation is available as well.

404 errors can be logged as well. Each 404 error is it's own record in the database. The path, full URL, timestamp, and referer are stored. Storing each error as a separate record allows viewing statistics over time and seeing all the referer URLs. When a redirect is created or updated, 404 records that match it's path are deleted.

## Installation with Symfony Flex

Add our recipes endpoint

```json
{
  "extra": {
    "symfony": {
      "endpoint": [
        "https://api.github.com/repos/agence-adeliom/symfony-recipes/contents/index.json?ref=flex/main",
        ...
        "flex://defaults"
      ],
      "allow-contrib": true
    }
  }
}
```

Install with composer

```bash
composer require agence-adeliom/easy-redirect-bundle
```

### Setup database

#### Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

#### Without

```bash
php bin/console doctrine:schema:update --force
```

## Documentation

### Manage redirect in your Easyadmin dashboard

Go to your dashboard controller, example : `src/Controller/Admin/DashboardController.php`

```php
<?php

namespace App\Controller\Admin;

...
use Adeliom\EasyRedirectBundle\Admin\EasyRedirectTrait;

class DashboardController extends AbstractDashboardController
{
    ...
    use EasyRedirectTrait;

    ...
    public function configureMenuItems(): iterable
    {
        ...
        yield from $this->configRedirectEntry();

        ...
```

### Configuration

```yaml
# config/packages/easy_redirect.yaml
easy_redirect:
    redirect_class:     ~ # Required and must be an instance of "Adeliom\EasyRedirectBundle\Entity\Redirect"
    not_found_class:    ~ # Required and must be an instance of "Adeliom\EasyRedirectBundle\Entity\NotFound"
    model_manager_name: ~ # If a custom model manager is used by default its 'default'

    # When enabled, when a redirect is updated or created, the NotFound entites with a matching path are removed.
    remove_not_founds: true
```


## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@arnaud-ritti](https://github.com/arnaud-ritti)


## Thanks to

[kbond/ZenstruckRedirectBundle](kbond/ZenstruckRedirectBundle)


