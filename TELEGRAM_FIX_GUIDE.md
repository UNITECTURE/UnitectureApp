# Telegram Notifications Setup Guide

## Problem Identified

Some users have **invalid Telegram chat IDs** in the database:
- User 9 (raj): Chat ID = `99` ❌ Invalid
- User 11 (akshay): Chat ID = `3434` ❌ Invalid

Valid Telegram chat IDs are 9-10 digit numbers like `5895863008`.

This is causing all Telegram notifications to fail with "Bad Request: chat not found" errors.

---

## Quick Fix for Users with Invalid Chat IDs

### Option 1: Update Database Directly (Quick Fix)

Users with invalid chat IDs need to update their Telegram chat ID. They can get their correct chat ID by:

1. Open Telegram
2. Search for your bot: `@YourBotUsername`
3. Click **START** or send `/start`
4. The bot will reply with your chat ID
5. Copy the chat ID and update your profile

### Option 2: Use SQL to temporarily NULL invalid IDs

```sql
-- Set invalid chat IDs to NULL so they can be re-linked
UPDATE users SET telegram_chat_id = NULL WHERE id IN (9, 11);
```

---

## How to Get Telegram Bot Username

To find your bot's username:

1. Go to Telegram
2. Search for `@BotFather`
3. Send `/mybots`
4. Select your bot
5. You'll see the bot username (e.g., `@UnitectureBot`)

---

## Proper Telegram Integration Setup

### Step 1: Add Webhook Route to `routes/web.php`

Add this code to handle bot commands:

```php
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;

Route::post('/telegram/webhook', function(Request $request, TelegramService $telegram) {
    $data = $request->all();
    
    if (isset($data['message'])) {
        $chatId = $data['message']['chat']['id'];
        $text = $data['message']['text'] ?? '';
        
        if ($text === '/start' || $text === '/myid') {
            $message = "📱 <b>Your Telegram Chat ID:</b> <code>{$chatId}</code>\n\n";
            $message .= "Copy this ID and add it to your profile in Unitecture App to receive notifications.";
            
            $telegram->sendMessage($chatId, $message);
        }
    }
    
    return response()->json(['ok' => true]);
});
```

### Step 2: Set Webhook (One-time setup)

Run this command in browser or terminal:

```bash
# Replace YOUR_BOT_TOKEN with actual token from .env
# Replace YOUR_DOMAIN with your actual domain (use ngrok for local testing)

curl -X POST "https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook" \
  -d "url=https://YOUR_DOMAIN/telegram/webhook"
```

**For local testing with ngrok:**
```bash
ngrok http 8000
# Copy the https URL, then:
# https://api.telegram.org/bot8204365062:AAFOJHqgdZiKePfd0EX5BlJuoSAZ85f5guA/setWebhook?url=https://YOUR_NGROK_URL/telegram/webhook
```

### Step 3: Users Link Their Telegram

1. Each user opens Telegram
2. Search for the bot (e.g., `@UnitectureTaskBot`)
3. Click START or send `/start`
4. Bot replies with their chat ID
5. Copy the chat ID
6. Go to Unitecture App profile
7. Paste chat ID in Telegram field
8. Save

---

## Alternative: Manual Chat ID Update for Specific Users

If you need to fix raj and akshay urgently:

### Method 1: They send you their chat ID

Ask raj and akshay to:
1. Open Telegram
2. Search for `@userinfobot`
3. Click START
4. The bot shows their ID
5. Send you the ID

Then update database:
```sql
UPDATE users SET telegram_chat_id = 'THEIR_ACTUAL_CHAT_ID' WHERE full_name = 'raj';
UPDATE users SET telegram_chat_id = 'THEIR_ACTUAL_CHAT_ID' WHERE full_name = 'akshay';
```

### Method 2: Use IDBot

Ask them to:
1. Open Telegram
2. Search for `@myidbot`
3. Click START
4. Copy their ID
5. Update their profile in your app

---

## Testing Telegram Notifications

After fixing chat IDs, test with:

```bash
php artisan tinker
```

```php
$user = User::find(9); // raj
$telegram = app(\App\Services\TelegramService::class);
$telegram->sendMessage($user->telegram_chat_id, '<b>Test!</b> Your notifications are working! ✅');
```

---

## Verification Checklist

✅ Check all users have valid chat IDs:
```bash
php check_telegram_ids.php
```

✅ All chat IDs should be 9-10 digits
✅ Test send message to each user
✅ Check logs for no "chat not found" errors

---

## Summary

**Root Cause:** Invalid chat IDs (99, 3434) → "chat not found" errors

**Solution:**
1. NULL the invalid chat IDs in database
2. Users send `/start` to your bot
3. Bot replies with correct chat ID
4. Users update their profile with correct ID
5. Test notifications

**Commands to fix now:**
```sql
UPDATE users SET telegram_chat_id = NULL WHERE telegram_chat_id IN ('99', '3434');
```

Then have raj and akshay properly link their Telegram accounts.
