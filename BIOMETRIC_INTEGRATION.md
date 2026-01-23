# Biometric Attendance Integration Guide

This guide details how to integrate your eSSL Biometric machine with the Unitecture App. The system is designed to fetch raw logs daily at 11:00 PM, store them, and process them into daily attendance records.

## 1. Prerequisites / Installation

### Library
We typically use `rats/zkteco-modules` to communicate with ZK-based devices (like eSSL).
Run the following command to install the library:

```bash
composer require rats/zkteco-modules
```

### Environment Configuration
Add your biometric machine's IP and Port to your `.env` file:

```env
BIOMETRIC_DEVICE_IP=192.168.1.201
BIOMETRIC_DEVICE_PORT=4370
```

## 2. Architecture

### Database Tables
- **attendance_logs**: Stores raw punch data directly from the machine (biometric_id, punch_time).
- **attendances**: Stores processed daily records (user_id, clock_in, clock_out, duration).
- **users**: Requires `biometric_id` column to map device users to system users.

### Command: `attendance:fetch`
A custom Artisan command has been created at `app/Console/Commands/FetchBiometricAttendance.php`.
**Workflow:**
1. Connects to the device using the IP/Port.
2. Fetches all attendance logs.
3. Saves new logs to `attendance_logs` table (skips duplicates).
4. Processes the new logs:
   - Groups logs by User and Date.
   - Finds the Earliest Punch (Clock In) and Latest Punch (Clock Out).
   - Updates the `attendances` table with these times.

## 3. Automation (Scheduler)

The command is scheduled to run daily at **11:00 PM** in `routes/console.php`:

```php
Schedule::command('attendance:fetch')->dailyAt('23:00');
```

### Enabling the Scheduler
For this to work automatically, you need to ensure the Laravel Scheduler cron entry is running on your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 4. Manual Usage

You can trigger the fetch process manually at any time by running:

```bash
php artisan attendance:fetch
```

## 5. Troubleshooting

- **Connection Failed**: Ensure the server can ping the biometric device IP. VPN or local network access is required.
- **No Logs**: Check if the device is empty or permission denied.
- **User Not Mapped**: Ensure the `biometric_id` in the `users` table matches the ID on the biometric machine.
