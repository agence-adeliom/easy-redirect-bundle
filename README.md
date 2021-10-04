
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=agence-adeliom_easy-redirect-bundle)](https://sonarcloud.io/dashboard?id=agence-adeliom_easy-redirect-bundle)

# Easy Redirect Bundle

This bundle adds entities for redirects and 404 errors.

For redirects, 404 errors are intercepted and the requested path is looked up. If a match is found it redirects to the found redirect's destination. The count and last accessed date are updated as well. A redirect form type and validation is available as well.

404 errors can be logged as well. Each 404 error is it's own record in the database. The path, full URL, timestamp, and referer are stored. Storing each error as a separate record allows viewing statistics over time and seeing all the referer URLs. When a redirect is created or updated, 404 records that match it's path are deleted.

## Installation

Install with composer

```bash
composer require agence-adeliom/easy-redirect-bundle
```

## Documentation

[Check it here](doc/index.md)

## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@arnaud-ritti](https://github.com/arnaud-ritti)


## Thanks to

[kbond/ZenstruckRedirectBundle](kbond/ZenstruckRedirectBundle)


