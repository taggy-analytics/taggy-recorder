<?php

namespace App\Livewire;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use App\Support\Recorder;
use Livewire\Component;

class InitialSetup extends Component
{
    public UserForm $userData;

    public $stage = 'recoveryPassword';

    public function mount()
    {
        if(!Recorder::make()->needsInitialSetup()) {
            return redirect('');
        }
    }

    public function render()
    {
        return view('livewire.initial-setup', [
            'recoveryPassword' => Recorder::make()->getRecoveryPassword(),
        ]);
    }

    public function setupUser()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->userData->name,
            'email' => $this->userData->email,
            'password' => bcrypt($this->userData->password),
        ]);

        auth()->guard('web')->login($user);

        return redirect('/');
    }
}
