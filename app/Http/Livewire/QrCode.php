<?php

namespace App\Http\Livewire;

use App\Support\Recorder;
use Livewire\Component;

class QrCode extends Component
{
    public function render()
    {
        return view('livewire.qr-code', [
            'qrCodeData' => Recorder::make()->getSystemId(),
        ]);
    }
}
