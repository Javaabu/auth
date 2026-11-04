@if($status ?? session('status'))
    <x-forms::alert type="success" data-auth-status>
        {{ $status ?? session('status') }}
    </x-forms::alert>
@endif
