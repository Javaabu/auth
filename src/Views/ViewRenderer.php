<?php

namespace Javaabu\Auth\Views;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Inertia\Inertia;
use Javaabu\Auth\Support\PageData;
use Javaabu\Auth\UserType;
use RuntimeException;

/**
 * Renders a page with the user type's view stack: Blade (`auth::login`) or Inertia (`auth::Login`)
 */
class ViewRenderer
{
    public const string BLADE = 'blade';

    public const string INERTIA = 'inertia';

    public function __construct(
        protected PageData $page_data,
    ) {}

    /**
     * @param  string  $page  e.g. `login` or `settings.profile`
     * @param  array<string, mixed>  $data
     * @param  string  $layout  `auth` for guest pages, `account` for signed-in pages
     */
    public function render(UserType $type, string $page, array $data = [], string $layout = 'auth'): View|Responsable
    {
        $data = [...$this->page_data->shared($type), ...$data];

        if ($type->viewStack() === self::INERTIA) {
            return $this->renderInertia($page, $data);
        }

        return view("auth::{$page}", [
            ...$data,
            'user_type' => $type,
            'layout' => $type->layoutName($layout),
            'layout_section' => config('javaabu-auth.layout_section', 'content'),
        ]);
    }

    /**
     * The Inertia page name for a page: `settings.profile` → `auth::Settings/Profile`
     */
    public function inertiaPage(string $page): string
    {
        $segments = array_map(fn (string $segment) => str($segment)->studly()->toString(), explode('.', $page));

        return 'auth::' . implode('/', $segments);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function renderInertia(string $page, array $data): Responsable
    {
        if (! class_exists(Inertia::class)) {
            throw new RuntimeException('Install inertiajs/inertia-laravel to use the inertia views, or set javaabu-auth.views to blade.');
        }

        return Inertia::render($this->inertiaPage($page), [
            ...$data,
            'strings' => $this->page_data->strings(),
        ]);
    }
}
