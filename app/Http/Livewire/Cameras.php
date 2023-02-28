<?php

namespace App\Http\Livewire;

use App\Models\Camera;
use Livewire\Component;

class Cameras extends Component
{
    public $cameras;

    protected $rules = [
        'cameras.*.name' => 'string',
        'cameras.*.recording_mode' => 'string',
    ];

    public function booted()
    {
        $this->cameras = Camera::all()
            ->mapWithKeys(fn(Camera $camera) => [$camera->id => [
                'id' => $camera->id,
                'name' => $camera->name,
                'status' => $camera->status,
                'recording_mode' => $camera->recording_mode,
                'isRecording' => $camera->isRecording(),
            ]]);
    }

    public function updated($key, $value)
    {
        $key = explode('.', $key);

        Camera::find($key[1])->update([$key[2] => $value]);
    }

    public function startRecording(Camera $camera)
    {
        $camera->startRecording();
    }

    public function stopRecording(Camera $camera)
    {
        $camera->stopRecording();
    }
}
