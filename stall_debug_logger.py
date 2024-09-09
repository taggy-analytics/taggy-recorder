import psutil
import time
from datetime import datetime
from collections import deque

def log_top_processes(log_file):
    process_info = {}

    # Deque to store CPU times for the last 10 seconds
    history = deque(maxlen=10)

    while True:
        # Get current time
        current_time = datetime.now()

        # Get current CPU and memory info
        current_cpu_times = {}
        current_memory_info = {}
        for p in psutil.process_iter(['pid', 'cpu_times', 'cmdline', 'memory_info']):
            try:
                current_cpu_times[p.pid] = (p.cpu_times(), p.cmdline())
                current_memory_info[p.pid] = (p.memory_info().rss, p.cmdline())  # rss is the Resident Set Size (RAM in bytes)
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
        sorted_cpu_processes = sorted(avg_cpu_usage.items(), key=lambda x: x[1][0], reverse=True)

        # Get top 10 CPU-consuming processes
        top_cpu_processes = sorted_cpu_processes[:10]

        # Sort by memory usage
        sorted_memory_processes = sorted(current_memory_info.items(), key=lambda x: x[1][0], reverse=True)

        # Get top 10 memory-consuming processes
        top_memory_processes = sorted_memory_processes[:10]

        # Log to file
        with open(log_file, 'a') as f:
            f.write(f"{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")

            # Log top 10 CPU-consuming processes
            f.write("Top 10 CPU-consuming processes:\n")
            for pid, (avg_cpu, cmdline) in top_cpu_processes:
                try:
                    cmdline_str = ' '.join(cmdline)
                    f.write(f"{avg_cpu:.2f}% CPU {cmdline_str} (PID: {pid})\n")
                except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
                    pass

            # Log top 10 memory-consuming processes
            f.write("\nTop 10 RAM-consuming processes:\n")
            for pid, (mem_usage, cmdline) in top_memory_processes:
                try:
                    mem_usage_mb = mem_usage / (1024 * 1024)  # Convert bytes to MB
                    cmdline_str = ' '.join(cmdline)
                    f.write(f"{mem_usage_mb:.2f} MB RAM {cmdline_str} (PID: {pid})\n")
                except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
                    pass

            f.write("\n")

        # Sleep for 1 second
        time.sleep(1)

if __name__ == "__main__":
    log_file = "process_log.txt"
    log_top_processes(log_file)
