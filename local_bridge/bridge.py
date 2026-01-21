import sys
import os
import time
import requests
import json
import schedule
from zk import ZK, const
from datetime import datetime

# ================= CONFIGURATION =================
DEVICE_IP = '192.168.0.201'  # IP of your eSSL K30 Device
DEVICE_PORT = 4370           # Default Port
API_URL = 'http://127.0.0.1:8000/api/essl/attendance' # Localhost URL
PROCESS_URL = 'http://127.0.0.1:8000/api/attendance/process' # Trigger Calculation
SYNC_INTERVAL = 1            # Sync every 1 minute for testing
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

        # ALWAYS Trigger Calculation (Unconditional)
        print("Triggering Attendance Calculation...")
        try:
            proc_res = requests.get(PROCESS_URL, timeout=10)
            print(f"Server Calculation: {proc_res.status_code}")
        except:
            print("Note: Calculation trigger timed out (Job running in background)")

    except Exception as e:
        print(f"Device Connection Error: {e}")
    finally:
        if conn:
            conn.disconnect() 
            print("Device Disconnected.")

def main():
    print("--- Unitecture Biometric Bridge Started ---")
    print(f"Target API: {API_URL}")
    print(f"Sync Interval: {SYNC_INTERVAL} minutes")
    
    # Run once immediately
    sync_data()
    
    # Schedule
    schedule.every(SYNC_INTERVAL).minutes.do(sync_data)
    
    while True:
        schedule.run_pending()
        time.sleep(1)

if __name__ == "__main__":
    main()
