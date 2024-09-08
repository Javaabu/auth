<?php

namespace Javaabu\Auth\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait DetectsGmailAliases
{
    public function scopeHasGmailAlias(Builder $query, string $gmail, string $field = 'email'): void
    {
        // get the domain
        $domain = Str::of($gmail)
                    ->afterLast('@')
                    ->lower()
                    ->toString();

        if ($domain != 'gmail.com') {
            $query->where($field, $gmail);
            return;
        }

        // get the username
        $username = Str::of($gmail)
                        ->beforeLast('@')
                        ->lower() // normalize to lowercase
                        ->replace('.', '') // remove dots
                        ->beforeLast('+') // remove +
                        ->toString();


        $query->whereRaw("
            LOWER(
                REPLACE(
                    SUBSTR($field, 1, CASE
                        WHEN INSTR($field, '+') > 0
                            THEN INSTR($field, '+') - 1
                        ELSE INSTR($field, '@') - 1
                    END),
                     '.', ''
                )
            ) = ?", [$username])
                ->where($field, 'LIKE', '%@gmail.com');
    }
}
