<?php

namespace Javaabu\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Javaabu\Auth\User;

class UniqueGmail implements ValidationRule
{
    protected string $ignore_user_id;

    protected string $id_column = '';

    /**
     * Constructor
     *
     * @param class-string<User> $model_class
     */
    public function __construct(
        protected string $model_class,
        protected string $email_column = '',
    ) {
    }

    public function ignore(string|Model $user_id, string $id_column = ''): static
    {
        $this->ignore_user_id = $user_id instanceof Model ? $user_id->id : $user_id;
        $this->id_column = $id_column;

        return $this;
    }

    public function getIgnoreUserId(): ?string
    {
        return $this->ignore_user_id ?? null;
    }

    public function getEmailColumn(string $attribute): string
    {
        return $this->email_column ?: $attribute;
    }

    protected function getIdColumn(): string
    {
        if ($this->id_column) {
            return $this->id_column;
        }

        $model_class = $this->model_class;

        /** @var Model $model */
        $model = new $model_class();

        return $model->getKeyName();
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value) || (! is_string($value))) { // delegate required validation to Laravel
            return;
        }

        // get the domain
        $domain = Str::of($value)
            ->afterLast('@')
            ->lower()
            ->toString();

        // not a gmail, so not relevant
        if ($domain != 'gmail.com') {
            return;
        }

        $model_class = $this->model_class;

        $has_alias = $model_class::query()
                        ->hasGmailAlias($value, $this->getEmailColumn($attribute))
                         ->when($this->getIgnoreUserId(), function ($query) {
                             $query->where($this->getIdColumn(), '!=', $this->ignore_user_id);
                         })
                         ->exists();

        if ($has_alias) {
            $fail(trans('auth::validation.unique-gmail', ['attribute' => $attribute]));
        }
    }
}
