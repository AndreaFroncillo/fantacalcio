@props([
'name',
'label',
'type' => 'text',
])

<div>
    <x-ui.label :for="$name">
        {{ $label }}
    </x-ui.label>

    <div class="mt-2">
        <x-ui.input
            :id="$name"
            :name="$name"
            :type="$type"
            {{ $attributes }} />
    </div>

    <x-ui.form-error
        :messages="$errors->get($name)" />
</div>