@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'filament-accounts-input-error text-sm text-danger-600 dark:text-danger-400']) }}>{{ $message }}</p>
@enderror
