@props(['field'])

@php
    $messages = collect($errors->get($field))
        ->merge(collect($errors->get("{$field}.*"))->flatten())
        ->all();
@endphp

<x-input-error :messages="$messages" {{ $attributes }} />
