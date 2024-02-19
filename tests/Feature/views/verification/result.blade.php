@if($errors->isEmpty())
    {{ $message }}
@endif

@php
    $error = $errors->all();
@endphp

@unless(empty($error))
    @if(count($error) > 1)
        @foreach($error as $error_text)
            {{ $error_text }}
        @endforeach
    @else
        {{ $error[0] }}
    @endif
@endunless
