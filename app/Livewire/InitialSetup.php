<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\Recorder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

class InitialSetup extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public $stage = 'setupUser';

    public function mount()
    {
        if(!Recorder::make()->needsInitialSetup()) {
            return redirect('');
        }

        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.initial-setup', [
            'recoveryPassword' => Recorder::make()->getRecoveryPassword(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label(__('gui.initial-setup.setup-user.fields.name')),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->label(__('gui.initial-setup.setup-user.fields.email')),
                TextInput::make('password')
                    ->required()
                    ->password()
                    ->revealable()
                    ->label(__('gui.initial-setup.setup-user.fields.password')),
            ])
            ->statePath('data');
    }

    public function setupUser()
    {
        $user = User::create([
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => bcrypt($this->data['password']),
        ]);

        auth()->guard('web')->login($user);

        return redirect('/');
    }
}
