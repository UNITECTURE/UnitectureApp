# How to Enable Telegram Notifications for ALL Features

## ⚠️ IMPORTANT: You Need BOTH Bots!

Your app uses **TWO separate Telegram bots**:

1. **@unitecturebot** (Main Bot)
   - Leave requests & approvals
   - Attendance notifications

2. **@unitecturetaskbot** (Task Bot)
   - Task assignments
   - Task status updates
   - Task comments
   - Task due date changes

---

## 🚀 Setup Instructions

### Step 1: Start BOTH Bots in Telegram

Each user must:

#### 1. Start the Main Bot (@unitecturebot)
1. Open Telegram app
2. Search for: **@unitecturebot**
3. Click **START**
4. Copy the chat ID the bot sends you

#### 2. Start the Task Bot (@unitecturetaskbot)
1. In Telegram, search for: **@unitecturetaskbot**
2. Click **START**
3. ✅ You can use the SAME chat ID from main bot

---

### Step 2: Update Your Profile

The chat ID is the SAME for both bots (it's your personal Telegram ID), so:

1. Go to Unitecture App
2. Click your profile
3. Enter your Telegram chat ID
4. Save

---

## ✅ How to Verify It's Working

### Test Script
Run this to test both bots:
```bash
php test_both_bots.php
```

You should receive **TWO messages** in Telegram:
- One from **@unitecturebot** (Leave test)
- One from **@unitecturetaskbot** (Task test)

### If you only receive ONE message:
❌ You haven't started the other bot yet!
→ Go to Telegram and START the missing bot

---

## 🔧 Current Database Status

| User ID | Name | Chat ID | Status |
|---------|------|---------|--------|
| 1 | Admin User | 5895863008 | ✅ Valid |
| 2 | Sarah Supervisor | 1872119262 | ✅ Valid |
| 3 | John Employee | NULL | ❌ Not linked |
| 4 | Jane Employee | NULL | ❌ Not linked |
| 5 | Atharva Kanthak | 5880526986 | ✅ Valid |
| 6 | Abhishek Deshpande | 2064024304 | ✅ Valid |
| 9 | raj | 99 | ❌ INVALID (too short) |
| 11 | akshay | 3434 | ❌ INVALID (too short) |

---

## 🛠️ Fix Invalid Chat IDs

**For raj and akshay:**

Your current chat IDs (99, 3434) are invalid. You need to:

1. Open Telegram
2. Search for `@userinfobot` 
3. Click START
4. Copy your real chat ID (should be 9-10 digits like: 5880526986)
5. Update your profile in the app with the correct chat ID
6. Then START both bots (@unitecturebot AND @unitecturetaskbot)

---

## 📋 Summary

**For Telegram notifications to work, EACH user must:**

✅ Have a valid 9-10 digit chat ID in their profile  
✅ Have started @unitecturebot (for leave/attendance)  
✅ Have started @unitecturetaskbot (for task notifications)  

**The chat ID is the same for both bots - it's your personal Telegram user ID!**
