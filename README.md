# Api Platform

## Installation du site 

```symfony new api-2026 --webapp```

### si oublier de faire --webapp

```composer require webapp```

## Gestion doctrine

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

## Installer fixtures
```composer require orm-fixtures --dev```

```composer require fakerphp/faker```

## Installer API Platform 

```composer require api```

# API Sécurité sur les opérations POST

exemple dans src/Entity/User.php

Empecher directement l'inscription si on n'a pas un rôle admin

```
    #[ApiResource(
        operations: [
            new Post(
                security: 'is_granted("ROLE_ADMIN")
            )
        ]
    )
```

Sécuriser l'opération sur la propriété
```    
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private array $roles = [];
```

### limiter l'écriture uniquement à un groupe (denormalizationContext)

```
#[ApiResource(
    normalizationContext: ['groups' => ['users_read']],
    denormalizationContext: ['groups' => ['users_write']]
)]
#[UniqueEntity(fields:['email'], message: 'Un utilisateur ayant cette adresse E-mail existe déjà')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
```

on autorise l'écriture avec le groupe users_write

```
#[ORM\Column(length: 255)]
    #[Groups(['users_read','users_write'])]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private ?string $firstName = null;
```

