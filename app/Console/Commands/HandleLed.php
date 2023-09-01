<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class HandleLed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:led {color} {interval?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Illuminate!';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $colors = [
            'red' => [17],
            'green' => [18],
            'blue' => [27],
        ];

        $pins = $colors[$this->argument('color')];

        $this->deluminate(Arr::flatten($colors));
        $this->illuminate($pins);

        if($interval = $this->argument('interval')) {
            do {
                usleep($interval * 1000);
                $this->deluminate($pins);
                usleep($interval * 1000);
                $this->illuminate($pins);
            } while(true);

        }

        return 0;
    }

    private function illuminate($pins)
    {
        $this->setPins($pins, 1);
    }

    private function deluminate($pins)
    {
        $this->setPins($pins, 0);
    }

    private function setPins($pins, $value)
    {
        foreach($pins as $pin) {
            exec("gpioset gpiochip0 {$pin}={$value}");
        }
    }
}
