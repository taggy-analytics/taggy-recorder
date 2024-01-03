from gpiozero import RGBLED
from colorzero import Color
from time import sleep
from signal import pause

import sys

def main():
    colorInput = sys.argv[1]
    interval = float(sys.argv[2]) if len(sys.argv) > 2 else 1  # default interval set to 1 second

    # Split the colorInput into individual colors
    colors = colorInput.split('/')

    led = RGBLED(red=17, green=18, blue=27)

    if interval > 0:
        while True:
            for color in colors:
                led.color = Color(color)
                sleep(interval)

                # Turn off the LED only if there is one color and the interval is greater than zero
                if len(colors) == 1:
                    led.color = Color('black')
                    sleep(interval)
    else:
        # If interval is not greater than zero, just set to the first color without blinking
        led.color = Color(colors[0])

    pause()

if __name__ == "__main__":
    main()
