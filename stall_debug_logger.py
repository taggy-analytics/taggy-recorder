#!/usr/bin/env python3

import os
import time
from datetime import datetime, timedelta
import subprocess

log_dir = '/var/www/taggy/storage/logs'
log_interval = 1  # seconds

def get_log_filename():
    return os.path.join(log_dir, f"stall-debug_{datetime.now().strftime('%Y-%m-%d')}.log")

def log_system_metrics():
    while True:
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

        cpu_usage = subprocess.getoutput("ps aux --sort=-%cpu | head -n 11")
        mem_usage = subprocess.getoutput("ps aux --sort=-%mem | head -n 11")
        free_memory = subprocess.getoutput("free -h")
        htop_output = subprocess.getoutput("htop -b -n 1")

        log_entry = (
            f"{now}\n\n"
            f"CPU Usage:\n{cpu_usage}\n\n"
            f"Memory Usage:\n{mem_usage}\n\n"
            f"Free Memory:\n{free_memory}\n\n"
            f"HTop Output:\n{htop_output}\n\n"
            f"{'='*50}\n"
        )

        with open(get_log_filename(), 'a') as log_file:
            log_file.write(log_entry)

        time.sleep(log_interval)

def delete_old_logs():
    now = datetime.now()
    cutoff = now - timedelta(days=7)
    for filename in os.listdir(log_dir):
        if filename.startswith("stall-debug_"):
            filepath = os.path.join(log_dir, filename)
            filedate = datetime.strptime(filename[len("stall-debug_"):len("stall-debug_")+10], '%Y-%m-%d')
            if filedate < cutoff:
                os.remove(filepath)

if __name__ == "__main__":
    if not os.path.exists(log_dir):
        os.makedirs(log_dir)

    while True:
        log_system_metrics()
        delete_old_logs()
