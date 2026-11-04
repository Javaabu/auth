---
title: Unique Gmail Validation Rule
sidebar_position: 15
---

This package provides a `UniqueGmail` validation rule to detect gmail aliases. The rule uses the `hasGmailAlias()` scope of the `Javaabu\Auth\Concerns\DetectsGmailAliases` trait, which the package's `User` model has. Here is how to use the rule.

## Basic Usage

```php
use Javaabu\Auth\Rules\UniqueGmail;
use App\Models\User;

$rule = new UniqueGmail(User::class);
```

The class takes in the following arguments:

- **`$model_class`**: (Required) The fully qualified class name of a model with the `DetectsGmailAliases` trait.
- **`$email_column`**: The email column to check against. If left blank, will use the attribute name as the column name.


## Ignoring a Specific User ID

To ignore a specific user, you can use the `ignore()` method.

```php
use Javaabu\Auth\Rules\UniqueGmail;
use App\Models\User;

$user = request()->user();

$rule = (new UniqueGmail(User::class))->ignore($user->id);

```

The ignore method takes in the following arguments:

- **`$user_id`**: (Required) The user id to ignore. Can be a string or Model instance.
- **`$id_column`**: Which column to use as the id. If left blank, will use the model's key column.
