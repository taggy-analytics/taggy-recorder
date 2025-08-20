<h2 class="text-center">
    @lang('gui.initial-setup.recovery-password.title')
</h2>

<div class="space-y-2">
    @lang('gui.initial-setup.recovery-password.intro')
</div>

<div>
    <div
        x-data="{ copied: false, hasCopied: false }"
        class="mt-8"
    >
        <!-- Centered, content-width box -->
        <div class="w-fit mx-auto">
            <div class="inline-flex items-center gap-3 bg-gray-100 rounded-lg px-4 py-3 shadow-sm ring-1 ring-black/5">
                        <span class="font-mono text-2xl tracking-wide text-gray-900">
                            {{ $recoveryPassword }}
                        </span>

                <div class="relative">
                    <button
                        x-clipboard.raw="{{ $recoveryPassword }}"
                        @click="
                                    copied = true;
                                    hasCopied = true;
                                    setTimeout(() => copied = false, 1800)
                                "
                        class="px-3 py-1 text-sm bg-sky-600 text-white rounded hover:bg-sky-700 focus:outline-none cursor-pointer"
                        aria-live="polite"
                    >
                        @lang('gui.initial-setup.recovery-password.copy')
                    </button>

                    <!-- Tooltip -->
                    <div
                        x-show="copied"
                        x-transition
                        class="absolute -top-9 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 whitespace-nowrap pointer-events-none"
                    >
                        @lang('gui.initial-setup.recovery-password.copied')
                        <div class="absolute left-1/2 -bottom-1 w-2 h-2 bg-gray-900 rotate-45 -translate-x-1/2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second button that stays active once copy happened -->
        <div class="w-fit mx-auto mt-4">
            <button
                wire:click="$set('stage', 'setupUser')"
                :disabled="!hasCopied"
                class="px-4 py-2 text-sm rounded font-semibold text-white transition-colors "
                :class="hasCopied ? 'bg-sky-600 hover:bg-sky-700 focus:outline-none cursor-pointer' : 'bg-gray-400 cursor-not-allowed'"
            >
                @lang('gui.initial-setup.recovery-password.continue')
            </button>
        </div>
    </div>
</div>
