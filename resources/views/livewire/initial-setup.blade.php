<div>
    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-64 mx-auto">

    <div class="prose mt-6">
        @include('livewire.initial-setup.' . Str::kebab($stage))
    </div>
</div>
