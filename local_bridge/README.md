# Biometric Bridge (Office PC)

This folder contains the Python script required to connect your local eSSL K30 Biometric Device to your Cloud Application.

## Prerequisites
1.  **Python** installed on your Office PC.
2.  **Network Access**: The PC must be on the same network as the Biometric Device.

## Setup Instructions

### 1. Install Python Dependencies
Open Command Prompt in this folder and run:
```bash
pip install -r requirements.txt
```

### 2. Configure the Script
Open `bridge.py` in a text editor and update the following lines at the top:

```python
DEVICE_IP = '192.168.1.201'  # The IP address of your eSSL Machine
API_URL = 'http://your-godaddy-domain.com/api/essl/attendance' # Your actual website URL
```
*Make sure to change `http://your-godaddy-domain.com` to your real website address.*

### 3. Run the Bridge
Double-click `bridge.py` or run it via command prompt:
```bash
python bridge.py
```


### 4. Keep it Running
This window must stay open for the sync to happen. 
To run it in the background silently, you can rename `bridge.py` to `bridge.pyw`.

## ⚠️ Network Configuration Guide

**Crucial Step:** Your PC and the Biometric Device must be in the same "IP Family".

1.  **Check Office Router IP**: 
    -   Connect your PC to the office network.
    -   Open Command Prompt and type `ipconfig`.
    -   Look at **Default Gateway** (e.g., `192.168.1.1`).
2.  **Configure Biometric Device**:
    -   Go to **Menu > Network > Ethernet** on the device.
    -   Set **IP Address** to match the first 3 parts of your Router.
        -   If Router is `192.168.1.1`, set Device to `192.168.1.201`.
        -   If Router is `192.168.0.1`, set Device to `192.168.0.201`.
    -   Set **Gateway** to your Router's IP.
3.  **Update Script**:
    -   Open `bridge.py` and update `DEVICE_IP` to match what you just set on the device.
