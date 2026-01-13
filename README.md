# Api Platform

## installation du site 

```symfony new api-2026 --webapp```

### si oublier de faire --webapp

```composer require webapp```

## gestion doctrine

### voir l'état des migrations

```php bin/console doctrine:migrations:status```

### voir la liste des migrations

```php bin/console doctrine:migrations:list```

### revenir à la migration précédente

```php bin/console doctrine:migrations:migrate prev```

### revenir à une migration précédente avec son nom
```php bin/console doctrine:migrations:migrate DoctrineMigrations\Version20260112152556```

### Astuce pour supprimer la base de données
```php bin/console doctrine:database:drop --force```

## installer fixtures
```composer require orm-fixtures --dev```

```composer require fakerphp/faker```
