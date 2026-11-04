<x-forms::card :title="__('API tokens')" data-api-tokens>
    <div class="flex flex-col gap-4">
        @if(session('plain_text_token'))
            <x-forms::alert type="success">
                {{ __('Copy your new API token now. It will not be shown again.') }}
                <code class="block break-all font-mono" dir="ltr" data-plain-text-token>{{ session('plain_text_token') }}</code>
            </x-forms::alert>
        @endif

        <x-forms::form :action="$urls['api-tokens.store']" class="flex flex-wrap items-end gap-2">
            <x-forms::text name="token_name" :label="__('Token name')" required maxlength="255" dir="auto" />
            <x-forms::submit>{{ __('Create token') }}</x-forms::submit>
        </x-forms::form>

        @if($api_tokens)
            <ul class="flex flex-col divide-y divide-gray-200">
                @foreach($api_tokens as $token)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2" data-api-token="{{ $token['id'] }}">
                        <div class="flex flex-col">
                            <span class="font-medium" dir="auto">{{ $token['name'] }}</span>
                            <span class="text-sm text-gray-600">
                                {{ $token['last_used_at'] ? __('Last used :time', ['time' => $token['last_used_at']]) : __('Never used') }}
                            </span>
                        </div>

                        <x-forms::form method="DELETE" :action="route($user_type->routeNameFor('api-tokens.destroy'), $token['id'])"
                                       data-confirm="{{ __('Delete this token?') }}">
                            <x-forms::submit color="danger" size="sm">{{ __('Delete') }}</x-forms::submit>
                        </x-forms::form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-forms::card>
