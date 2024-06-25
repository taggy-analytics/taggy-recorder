#!/bin/bash

grep -q '^HLS_SEGMENT_DURATION=' ./.env || echo 'HLS_SEGMENT_DURATION=6' >> ./.env
