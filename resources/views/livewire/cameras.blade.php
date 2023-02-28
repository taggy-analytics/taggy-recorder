<div class="p-4">
    <table class="border-separate border-spacing-2">
        @foreach($cameras as $id => $camera)
            <tr>
                <td>{{ $camera['name'] }}</td>
                <td>{{ $camera['status'] }}</td>
                <td >
                    <x-select
                        placeholder="Select mode"
                        :options="[
                            ['name' => 'Automatic', 'id' => 'automatic'],
                            ['name' => 'Manual', 'id' => 'manual'],
                        ]"
                        option-label="name"
                        option-value="id"
                        :clearable="false"
                        wire:model="cameras.{{ $id }}.recording_mode"
                        wire:key="camera-{{ $camera['id'] }}"
                    />
                </td>
                <td>
                    @if($camera['isRecording'])
                        <div class="animate-pulse rounded-full bg-green-500 h-6 w-6"></div>
                        <x-button primary label="Stop" wire:click="stopRecording" />
                    @elseif($camera['status'] == \App\Enums\CameraStatus::READY)
                        <x-button negative label="Start" wire:click="startRecording" />
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <div class="mt-4 text-sm">
        {{ now()->toDateTimeString() }}
    </div>

</div>
