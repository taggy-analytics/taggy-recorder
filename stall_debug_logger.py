import psutil
import time
from datetime import datetime, timedelta
from collections import deque

def log_top_processes(log_file):
    process_info = {}

    # Deque to store CPU times for the last 10 seconds
    history = deque(maxlen=10)

    while True:
        # Get current time
        current_time = datetime.now()

        # Get current CPU times
        current_cpu_times = {}
        for p in psutil.process_iter(['pid', 'cpu_times', 'cmdline']):
            try:
                current_cpu_times[p.pid] = (p.cpu_times(), p.cmdline())
            except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
                pass

        # Store the current CPU times with the timestamp
        history.append((current_time, current_cpu_times))

        # If we have less than 10 entries, we can't calculate a 10-second average yet
        if len(history) < 10:
            time.sleep(1)
            continue

        # Calculate CPU usage over the last 10 seconds for each process
        total_cpu_usage = {}
        for i in range(1, len(history)):
            prev_time, prev_cpu_times = history[i - 1]
            curr_time, curr_cpu_times = history[i]
            interval = (curr_time - prev_time).total_seconds()

            for pid, (curr_times, cmdline) in curr_cpu_times.items():
                if pid in prev_cpu_times:
                    prev_times, _ = prev_cpu_times[pid]
                    user_time = curr_times.user - prev_times.user
                    system_time = curr_times.system - prev_times.system
                    cpu_percent = (user_time + system_time) / interval * 100

                    if pid not in total_cpu_usage:
                        total_cpu_usage[pid] = []
                    total_cpu_usage[pid].append((cpu_percent, cmdline))

        # Calculate the average CPU usage over the 10 seconds
        avg_cpu_usage = {pid: (sum(usage for usage, _ in usages) / len(usages), usages[0][1]) for pid, usages in total_cpu_usage.items()}

        # Sort by average CPU usage
        sorted_processes = sorted(avg_cpu_usage.items(), key=lambda x: x[1][0], reverse=True)

        # Get top 10 processes
        top_processes = sorted_processes[:10]

        # Log to file
        with open(log_file, 'a') as f:
            f.write(f"{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
            for pid, (avg_cpu, cmdline) in top_processes:
                try:
                    cmdline_str = ' '.join(cmdline)
                    f.write(f"{avg_cpu:.2f}% {cmdline_str} (PID: {pid})\n")
                except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
                    pass
            f.write("\n")

        # Sleep for 1 second
        time.sleep(1)

if __name__ == "__main__":
    log_file = "process_log.txt"
    log_top_processes(log_file)
