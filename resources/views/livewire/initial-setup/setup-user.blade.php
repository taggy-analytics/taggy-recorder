<h2 class="text-center">
    @lang('gui.initial-setup.setup-user.title')
</h2>

<div class="space-y-2">
    @lang('gui.initial-setup.setup-user.intro')
</div>

<div class="mt-4 space-y-2">
    <flux:input :label="__('gui.initial-setup.setup-user.fields.name')" wire:model="userData.name"/>
    <flux:input :label="__('gui.initial-setup.setup-user.fields.email')" wire:model="userData.email" type="email"/>
    <flux:input :label="__('gui.initial-setup.setup-user.fields.password')" wire:model="userData.password" type="password"/>

    <button class="px-3 py-1 bg-sky-600 text-white rounded hover:bg-sky-700 focus:outline-none cursor-pointer mt-4" wire:click="setupUser">
        @lang('gui.initial-setup.setup-user.submit')
    </button>
</div>
