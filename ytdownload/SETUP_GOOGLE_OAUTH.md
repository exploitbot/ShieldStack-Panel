# Google OAuth2 Setup Guide for YT Saver

Your YouTube downloader now has a beautiful mobile-friendly interface with Google OAuth2 authentication support!

## Features

✨ **Mobile-First Design**
- Responsive layout that works on phones, tablets, and desktops
- Touch-friendly buttons (44px minimum height)
- Hamburger menu for mobile navigation
- Optimized for all screen sizes

🔐 **Google OAuth2 Authentication**
- Sign in with your Google account
- Bypass age-restricted content
- Download region-locked videos
- Cache authentication for future use

## Quick Setup (Optional but Recommended)

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Click "Select a Project" → "New Project"
3. Name it "YT Saver" and create it
4. Wait for project creation to complete

### Step 2: Enable Required APIs

1. In the search bar, search for "Google+ API"
2. Click on it and click "Enable"
3. Search for "YouTube Data API v3"
4. Click on it and click "Enable"

### Step 3: Create OAuth2 Credentials

1. Go to "Credentials" in the left sidebar
2. Click "Create Credentials" → "OAuth client ID"
3. You may be asked to create an "OAuth consent screen" first:
   - Click "Create Consent Screen"
   - Choose "External"
   - Fill in:
     - App name: "YT Saver"
     - User support email: Your email
     - Developer contact: Your email
   - Click "Save and Continue"
   - No need to add scopes, click "Save and Continue"
   - Click "Back to Dashboard"

4. Now create the credentials again:
   - Click "Create Credentials" → "OAuth client ID"
   - Application type: **Web application**
   - Name: "YT Saver Web Client"
   - Authorized redirect URIs, add:
     ```
     https://shieldstack.dev/ytdownload/api/auth/callback
     ```
   - Click "Create"

5. You'll see your credentials:
   - Copy the **Client ID**
   - Copy the **Client Secret**

### Step 4: Configure on Server

SSH into your server and run:

```bash
# Set environment variables
export GOOGLE_CLIENT_ID="your-client-id-here"
export GOOGLE_CLIENT_SECRET="your-client-secret-here"
export GOOGLE_REDIRECT_URI="https://shieldstack.dev/ytdownload/api/auth/callback"

# Restart the service
sudo systemctl restart ytdownload.service
```

To make these permanent, add them to `/etc/environment`:

```bash
sudo nano /etc/environment
```

Add these lines:
```
GOOGLE_CLIENT_ID="your-client-id"
GOOGLE_CLIENT_SECRET="your-client-secret"
GOOGLE_REDIRECT_URI="https://shieldstack.dev/ytdownload/api/auth/callback"
```

Save (Ctrl+X, Y, Enter) and restart:
```bash
sudo systemctl restart ytdownload.service
```

### Step 5: Test It!

1. Visit https://shieldstack.dev/ytdownload/
2. Click "Sign in with Google" button
3. Follow Google's authentication flow
4. You'll be logged in and can now download age-restricted videos!

## Features After Setup

✅ **Download Age-Restricted Videos**
- Sign in with your Google account
- Automatically verify age
- Download restricted content

✅ **Mobile Experience**
- Works perfectly on mobile phones
- Touch-optimized interface
- Fast loading and smooth animations

✅ **Privacy**
- No data stored on our servers
- Only uses authentication for YouTube
- Credentials are secure and local

## Troubleshooting

### "Google OAuth2 not configured" error

This means the environment variables aren't set. Follow Step 4 above to set them up.

### Login redirects but doesn't work

Make sure:
- Client ID and Secret are correct
- Redirect URI matches exactly: `https://shieldstack.dev/ytdownload/api/auth/callback`
- HTTPS is being used (not HTTP)

### Still having issues?

Check the logs:
```bash
sudo journalctl -u ytdownload.service -f
```

## Manual Testing

Without OAuth2 configured, the app still works perfectly for:
- Regular YouTube videos
- Public videos
- Most downloadable content

Just set up OAuth2 when you need to download age-restricted content.

## API Endpoints (For Developers)

```
POST /api/auth/google - Get Google OAuth URL
GET /api/auth/callback - Handle OAuth callback
GET /api/auth/logout - Logout
GET /api/user - Get logged-in user info
```

---

Enjoy your new mobile-friendly YouTube downloader! 🎉
