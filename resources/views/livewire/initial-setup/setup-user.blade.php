<h2 class="text-center">
    @lang('gui.initial-setup.setup-user.title')
</h2>

<div class="space-y-2">
    @lang('gui.initial-setup.setup-user.intro')
</div>

<div class="mt-4">
    <form wire:submit="setupUser">
        {{ $this->form }}

        <button class="px-3 py-1 bg-sky-600 text-white rounded hover:bg-sky-700 focus:outline-none cursor-pointer mt-4" type="submit">
            @lang('gui.initial-setup.setup-user.submit')
        </button>
    </form>

    <x-filament-actions::modals />
</div>
