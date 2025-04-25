#!/bin/bash

INTERVAL=10

TODAY=$(date +"%Y-%m-%d")
LOGFILE_DETAIL="mem_${TODAY}.log"
LOGFILE_TOTAL="mem_total_${TODAY}.log"

while true; do
    TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")

    # Rotate daily
    NEW_TODAY=$(date +"%Y-%m-%d")
    if [ "$NEW_TODAY" != "$TODAY" ]; then
        TODAY="$NEW_TODAY"
        LOGFILE_DETAIL="mem_${TODAY}.log"
        LOGFILE_TOTAL="mem_total_${TODAY}.log"
    fi

    # Collect PSS per PID
    smem -c "pid pss" --no-header > /tmp/pss.tmp

    # Log each process
    ps -eo pid,user,args --no-headers | while read -r PID USER CMDLINE; do
        PSS=$(awk -v pid="$PID" '$1 == pid {print $2}' /tmp/pss.tmp)
        if [ -n "$PSS" ]; then
            echo "$TIMESTAMP $PID $USER $CMDLINE $PSS" >> "$LOGFILE_DETAIL"
        fi
    done

    # Log total PSS
    TOTAL_PSS=$(awk '{sum += $2} END {print sum}' /tmp/pss.tmp)
    echo "$TIMESTAMP $TOTAL_PSS" >> "$LOGFILE_TOTAL"

    sleep $INTERVAL
done
