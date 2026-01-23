# Biometric Bridge Deployment Guide

This guide describes how to set up the Biometric Bridge script on a **new Windows 10 PC**. 
This PC's only job is to fetch data from the Biometric Device and send it to your live GoDaddy server.

## 1. Essentials Needed
Since the new machine handles **only** the device communication, you do **NOT** need XAMPP, MySQL, or PHP. You only need:
1.  **Internet Connection** (To talk to GoDaddy).
2.  **Local Network Connection** (To talk to the Biometric Device).
3.  **Python** (To run the script).

---

## 2. Step-by-Step Installation

### Step A: Install Python
1.  Go to the official [Python Downloads](https://www.python.org/downloads/) page.
2.  Download the latest version for Windows (e.g., Python 3.12 or newer).
3.  Run the installer.
4.  **IMPORTANT:** On the first screen, check the box **"Add Python to PATH"**.
5.  Click **Install Now**.

### Step B: Install Required Libraries
Once Python is installed, open **Command Prompt** (cmd) and run the following command to install the necessary libraries:

```cmd
pip install pyzk requests schedule
```

*   `pyzk`: To talk to the biometric machine.
*   `requests`: To send data to your website.
*   `schedule`: To run the process every minute.

---

## 3. Setting Up the Script

1.  Create a folder on your Desktop or C: Drive, e.g., `C:\BiometricBridge`.
2.  Copy your `bridge.py` file into this folder.
3.  **Edit `bridge.py`** with Notepad:

    **Change the URLs** to point to your live website (GoDaddy), NOT `127.0.0.1`:

    ```python
    # Configuration
    DEVICE_IP = '192.168.0.201'  # Keep this if the device IP is the same on the network
    DEVICE_PORT = 4370
    
    # UPDATE THESE TO YOUR LIVE DOMAIN
    # Example: https://your-website.com/api/essl/attendance
    API_URL = 'http://YOUR_GODADDY_DOMAIN.com/api/essl/attendance'
    
    # Example: https://your-website.com/api/attendance/process
    PROCESS_URL = 'http://YOUR_GODADDY_DOMAIN.com/api/attendance/process'
    ```

4.  Save the file.

---

## 4. Testing the Connection

1.  Open Command Prompt inside your folder:
    *   Open folder `C:\BiometricBridge`.
    *   Type `cmd` in the top address bar and hit Enter.
2.  Run the script manually first to check for errors:
    
    ```cmd
    python bridge.py
    ```

3.  **What to look for:**
    *   `Connected Successfully!`
    *   `Fetched X records...`
    *   `Attendance Sync Success: 200`
    
    If you see these success messages, it is working perfectly!

---

## 5. Automate It (Run in Background)

You don't want to keep a black command window open forever. Let's make it run invisibly on startup.

### Option A: The "Startup Folder" Method (Simplest)
1.  Create a new text file in `C:\BiometricBridge` and name it `start_bridge.bat`.
2.  Right-click `start_bridge.bat` > Edit.
3.  Paste this code inside:
    ```bat
    @echo off
    cd /d "C:\BiometricBridge"
    start "" pythonw bridge.py
    ```
    *(Note: `pythonw` runs python without a visible window)*.
4.  Save the file.
5.  Press `Win + R`, type `shell:startup`, and hit Enter.
6.  Create a **Shortcut** to your `start_bridge.bat` file and place it in this Startup folder.
7.  **Done!** Next time the PC turns on, the bridge starts automatically.

### Checking if it's running
Since it's invisible, if you ever need to stop it:
1.  Open **Task Manager** (`Ctrl + Shift + Esc`).
2.  Go to **Details** tab.
3.  Look for `pythonw.exe`.
4.  End Task to stop it.

---

## Summary Checklist for New PC
- [ ] Connected to same router as Biometric Device?
- [ ] Python Installed & Added to PATH?
- [ ] Libraries installed (`pip install...`)?
- [ ] `bridge.py` copied & URLs updated to GoDaddy domain?
- [ ] `start_bridge.bat` added to Startup folder?
