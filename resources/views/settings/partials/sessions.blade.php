<x-forms::card :title="__('Browser sessions')" data-sessions>
    <div class="flex flex-col gap-4">
        <p class="text-sm">{{ __('The browsers and devices where you are logged in.') }}</p>

        <ul class="flex flex-col divide-y divide-gray-200">
            @foreach($sessions as $session)
                <li class="flex flex-col py-2" data-session="{{ $session['id'] }}">
                    <span class="font-medium">
                        {{ $session['platform'] ?? __('Unknown') }} · {{ $session['browser'] ?? __('Unknown') }}
                    </span>
                    <span class="text-sm text-gray-600">
                        <span dir="ltr">{{ $session['ip_address'] }}</span> ·
                        @if($session['is_current_device'])
                            {{ __('This device') }}
                        @else
                            {{ __('Last active :time', ['time' => $session['last_active']]) }}
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>

        <x-forms::form method="DELETE" :action="$urls['sessions.destroy']">
            <x-forms::submit color="secondary">{{ __('Log out other browser sessions') }}</x-forms::submit>
        </x-forms::form>
    </div>
</x-forms::card>
