import sys
import os
import time
import requests
import json
import schedule
from zk import ZK, const
from datetime import datetime

# ================= CONFIGURATION =================
# Default config, overwritten by config.json if it exists
config = {
    "device_ip": "192.168.1.201",
    "device_port": 4370,
    "api_url": "http://127.0.0.1:8000/api/essl/attendance",
    "process_url": "http://127.0.0.1:8000/api/attendance/process",
    "sync_interval_minutes": 15
}

CONFIG_FILE = 'config.json'
if os.path.exists(CONFIG_FILE):
    try:
        with open(CONFIG_FILE, 'r') as f:
            file_config = json.load(f)
            config.update(file_config)
            print("Loaded configuration from config.json")
    except Exception as e:
        print(f"Error loading config.json: {e}")

DEVICE_IP = config['device_ip']
DEVICE_PORT = config['device_port']
API_URL = config['api_url']
PROCESS_URL = config['process_url']
SYNC_INTERVAL = config['sync_interval_minutes']

LAST_SYNC_FILE = 'last_sync.txt'

def get_last_sync_time():
    """Read the last synced timestamp from file."""
    if not os.path.exists(LAST_SYNC_FILE):
        return None
    try:
        with open(LAST_SYNC_FILE, 'r') as f:
            return datetime.strptime(f.read().strip(), '%Y-%m-%d %H:%M:%S')
    except:
        return None

def save_last_sync_time(dt):
    """Save the latest synced timestamp."""
    with open(LAST_SYNC_FILE, 'w') as f:
        f.write(dt.strftime('%Y-%m-%d %H:%M:%S'))

def sync_data():
    print(f"[{datetime.now()}] Starting Sync Process...")
    
    conn = None
    zk = ZK(DEVICE_IP, port=DEVICE_PORT, timeout=5, password=0, force_udp=False, ommit_ping=False)
    
    try:
        print(f"Connecting to Device at {DEVICE_IP}...")
        conn = zk.connect()
        print("Connected Successfully!")
        
        # Get Attendance Records
        attendance = conn.get_attendance()
        print(f"Fetched {len(attendance)} records from device.")
        
        last_sync = get_last_sync_time()
        new_records = []
        latest_record_time = last_sync

        for record in attendance:
            # record.timestamp is a datetime object
            if last_sync is None or record.timestamp > last_sync:
                new_records.append({
                    'user_id': record.user_id,
                    'timestamp': record.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                    'status': record.status,
                    'punch': record.punch
                })
                
                # Update local tracker for latest time found
                if latest_record_time is None or record.timestamp > latest_record_time:
                    latest_record_time = record.timestamp

        if not new_records:
            print("No new records to sync.")
        else:
            print(f"Found {len(new_records)} new records. Uploading to Cloud...")

            # Push to Cloud
            try:
                payload = {'logs': new_records}
                response = requests.post(API_URL, json=payload, timeout=10)
                
                if response.status_code == 200:
                    print("Upload Successful!")
                    if latest_record_time:
                        save_last_sync_time(latest_record_time)
                else:
                    print(f"Server Error: {response.status_code} - {response.text}")

            except Exception as api_error:
                print(f"Failed to connect to Cloud API: {api_error}")

    except Exception as e:
        print(f"Device Connection Error: {e}")
    finally:
        if conn:
            conn.disconnect() 
            print("Device Disconnected.")

    # --- Trigger Processing Logic (Running after connection closed) ---
    print("Triggering Attendance Calculation on Server...")
    
    # 1. Process Yesterday (To catch up if PC was off at 7 AM)
    try:
        # Note: Ensure API_URL base is correct for processing
        # API_URL is .../attendance
        # PROCESS_URL is .../attendance/process
        
        requests.get(PROCESS_URL + '/yesterday', timeout=10)
        print(" -> Triggered 'Yesterday' Processing (Catch-up)")
    except Exception as e:
        print(f"Trigger Yesterday Error: {e}")

    # 2. Process Today (For live status)
    try:
        requests.get(PROCESS_URL + '/today', timeout=10)
        print(" -> Triggered 'Today' Processing")
    except Exception as e:
        print(f"Trigger Today Error: {e}")

def main():
    print("--- Unitecture Biometric Bridge Started ---")
    print(f"Target API: {API_URL}")
    
    # 1. Run ONCE immediately on Startup
    # This covers cases where the PC was OFF at 10 AM.
    # As soon as it turns on, this runs and "catches up" all missing data.
    print(">> Startup Sync Initiated (Catching up on any missed data)...")
    sync_data()
    
    # 2. Schedule: Run strictly at 10:00 AM
    print(">> Scheduling Daily Sync at 10:00 AM...")
    schedule.every().day.at("10:00").do(sync_data)
    
    while True:
        schedule.run_pending()
        time.sleep(1)

if __name__ == "__main__":
    main()
