from zk import ZK

# Setup
DEVICE_IP = '192.168.0.201' # Change this to your device IP
PORT = 4370

print(f"Attempting to connect to {DEVICE_IP}...")

zk = ZK(DEVICE_IP, port=PORT, timeout=10)

try:
    conn = zk.connect()
    print("✅ Connection Successful!")
    
    # 1. Get Device Name
    print(f"Device Name: {conn.get_device_name()}")
    
    # 2. Get Users
    users = conn.get_users()
    print(f"\nFound {len(users)} users on device.")
    for user in users[:5]: # Show first 5 users
        print(f" - UID: {user.uid}, Name: {user.name}, Priv: {user.privilege}")

    # 3. Get Attendance
    logs = conn.get_attendance()
    print(f"\nFound {len(logs)} attendance records.")
    for log in logs[-5:]: # Show last 5 records
        print(f" - Log: {log.timestamp} | User ID: {log.user_id} | Status: {log.status}")

    conn.disconnect()

except Exception as e:
    print(f"❌ Error: {e}")
    print("Please check if the device IP is correct and your PC is on the same network.")
