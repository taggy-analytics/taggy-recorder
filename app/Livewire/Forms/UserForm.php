<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class UserForm extends Form
{
    public $name = '';

    public $email = '';

    public $password = '';

    protected function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ];
    }
}
