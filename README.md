![alt text](https://cdn.marshmallow-office.com/media/images/logo/marshmallow.transparent.red.png "marshmallow.")

# Marshmallow commands
Een package met handige commands die door alle projecten heen gebruikt kunnen worden.

### Installing
```
composer require marshmallow/commands
```

### Commands
```bash
php artisan env:set {key} {value}
php artisan marshmallow:resource {resource_name?} {package_name?}

# Run all the cache clearing commands from Laravel
php artisan marshmallow:clear
```
