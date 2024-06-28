#!/bin/bash

# Navigate to the desired directory
cd /var/www/taggy/current

sudo apt install python3.11-venv

# Create a virtual environment
python3 -m venv myenv

# Activate the virtual environment
source myenv/bin/activate

# Install psutil package
pip install psutil

# Run the stall_debug_logger.py script in the background, redirecting output to /dev/null
python3 ./stall_debug_logger.py & >/dev/null
