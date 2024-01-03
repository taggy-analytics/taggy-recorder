from gpiozero import RGBLED
from colorzero import Color
from time import sleep
from signal import pause

import sys

def main():
    color = sys.argv[1]
    interval = float(sys.argv[2]) if len(sys.argv) > 2 else 0

    led = RGBLED(red=17, green=18, blue=27)
    led.color = Color(color)

    if interval > 0:
        while True:
            sleep(interval)
            led.color = Color('black')
            sleep(interval)
            led.color = Color(color)

    pause()

if __name__ == "__main__":
    main()
