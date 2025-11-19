# YT Saver - YouTube Video Downloader

A modern, mobile-friendly YouTube video downloader with Google OAuth2 authentication support for downloading age-restricted content.

**Live Demo:** https://shieldstack.dev/ytdownload/

> Update (Nov 2025): ShieldStack AI Editor now supports multi-website selection and multi-session chat (website-scoped sessions with clear/reset). See `/var/www/html/ai-editor/` docs if coordinating cross-app changes.

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Google OAuth2 Setup](#google-oauth2-setup)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)
- [Troubleshooting](#troubleshooting)

## ✨ Features

### Core Functionality
- ✅ Download YouTube videos in multiple quality options (144p - 4K)
- ✅ Support for YouTube Shorts
- ✅ Support for playlists
- ✅ Direct MP4 output
- ✅ Fast downloads with real-time progress

### Mobile Experience
- 📱 Fully responsive design (mobile-first)
- 🎯 Touch-optimized buttons (44px minimum)
- ☰ Hamburger menu for mobile navigation
- ⚡ Optimized for all screen sizes
- 🚀 Fast loading on mobile networks

### Security & Authentication
- 🔐 Google OAuth2 sign-in support
- 🔒 Bypass age-restricted content when authenticated
- 🌍 Download region-locked videos
- 🛡️ Secure credential handling
- 📂 Session-based authentication

### User Interface
- 🎨 Modern dark theme with gradients
- ✨ Smooth animations and transitions
- 🎭 Real-time video information display
- 📊 Quality selection interface
- 📝 Clear error messages with solutions

## 🛠️ Tech Stack

### Backend
- **Framework:** Flask 2.3.3
- **Video Download:** yt-dlp 2023.11.16
- **Authentication:** Google OAuth2 (google-auth-oauthlib)
- **Session Management:** Flask-Session
- **HTTP:** Requests library

### Frontend
- **HTML5:** Semantic markup
- **CSS3:** Responsive design with CSS Grid/Flexbox
- **JavaScript:** Vanilla JS (no dependencies)
- **Icons:** Font Awesome 6.4.0

### Infrastructure
- **Web Server:** nginx (reverse proxy)
- **Process Manager:** systemd
- **Python:** 3.9+
- **OS:** Linux (CentOS/RHEL compatible)

## 📦 Installation

### Prerequisites
```bash
- Python 3.9+
- pip (Python package manager)
- nginx
- systemd
- ~2GB disk space for downloads
```

### Directory Structure
```
/var/www/html/ytdownload/
├── app.py                 # Flask application
├── requirements.txt       # Python dependencies
├── templates/
│   └── index.html        # Main UI template
├── static/
│   ├── css/
│   │   └── style.css     # Responsive styling
│   └── js/
│       └── script.js     # Frontend logic
└── README.md             # Documentation
```

### Installation Steps

1. **Install Python Dependencies**
   ```bash
   sudo pip3 install -r /var/www/html/ytdownload/requirements.txt
   ```

2. **Create systemd Service**
   - Service file: `/etc/systemd/system/ytdownload.service`
   - Starts Flask app automatically on boot
   - Auto-restarts on failure

3. **Configure nginx**
   - Configuration: `/etc/nginx/conf.d/shieldstack.dev.conf`
   - Reverse proxy to Flask on port 5000
   - Handles HTTPS and routing

4. **Start Service**
   ```bash
   sudo systemctl enable ytdownload.service
   sudo systemctl start ytdownload.service
   sudo systemctl status ytdownload.service
   ```

## 🔐 Google OAuth2 Setup

### Optional but Recommended for Age-Restricted Content

#### Step 1: Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create new project named "YT Saver"
3. Enable APIs:
   - Google+ API
   - YouTube Data API v3

#### Step 2: Create OAuth2 Credentials
1. Go to Credentials → Create Credentials → OAuth client ID
2. Choose "Web application"
3. Add redirect URI:
   ```
   https://shieldstack.dev/ytdownload/api/auth/callback
   ```
4. Copy Client ID and Client Secret

#### Step 3: Configure Server
```bash
# Set environment variables
export GOOGLE_CLIENT_ID="your-client-id"
export GOOGLE_CLIENT_SECRET="your-client-secret"
export GOOGLE_REDIRECT_URI="https://shieldstack.dev/ytdownload/api/auth/callback"

# Make permanent by adding to /etc/environment
sudo nano /etc/environment
```

Add:
```
GOOGLE_CLIENT_ID="your-client-id"
GOOGLE_CLIENT_SECRET="your-client-secret"
GOOGLE_REDIRECT_URI="https://shieldstack.dev/ytdownload/api/auth/callback"
```

#### Step 4: Restart Service
```bash
sudo systemctl restart ytdownload.service
```

## 📖 Usage

### Web Interface
1. Visit https://shieldstack.dev/ytdownload/
2. Paste a YouTube URL
3. Click "Get Info"
4. Select quality
5. Click "Download"

### With Google Authentication
1. Click "Sign in with Google" in navbar
2. Authenticate with Google account
3. Automatically redirected back
4. Now can download age-restricted videos
5. Click "Logout" to sign out

### Command Line (Optional)
```bash
# Check service status
sudo systemctl status ytdownload.service

# View logs
sudo journalctl -u ytdownload.service -f

# Restart service
sudo systemctl restart ytdownload.service

# Check for errors
sudo journalctl -u ytdownload.service -n 50
```

## 📡 API Documentation

### Get Video Information
```
POST /api/video-info
Content-Type: application/json

{
  "url": "https://www.youtube.com/watch?v=..."
}

Response:
{
  "success": true,
  "title": "Video Title",
  "duration": 213,
  "thumbnail": "https://...",
  "is_short": false,
  "uploader": "Channel Name",
  "formats": {
    "1080p": {
      "format_id": "96",
      "resolution": 1080,
      "fps": 25,
      "ext": "mp4",
      "has_audio": true
    },
    ...
  }
}
```

### Download Video
```
POST /api/download
Content-Type: application/json

{
  "url": "https://www.youtube.com/watch?v=...",
  "format_id": "96"
}

Response:
{
  "success": true,
  "filename": "Video Title.mp4",
  "filepath": "/tmp/ytdownload/Video Title.mp4"
}
```

### Get User Info
```
GET /api/user

Response:
{
  "authenticated": true,
  "user": {
    "id": "...",
    "email": "...",
    "name": "...",
    "picture": "..."
  }
}
```

### Authentication
```
GET /api/auth/google
- Returns OAuth authorization URL

GET /api/auth/callback
- Handles OAuth redirect (automatic)

GET /api/auth/logout
- Logs out current user
```

## 🗂️ Project Structure Details

### Backend (app.py)
- **Flask Routes:**
  - `/` - Main HTML page
  - `/api/user` - User authentication status
  - `/api/auth/google` - OAuth2 flow initiation
  - `/api/auth/callback` - OAuth2 callback handler
  - `/api/auth/logout` - User logout
  - `/api/video-info` - Get video metadata
  - `/api/download` - Download video
  - `/api/download-file` - Stream downloaded file

- **Key Functions:**
  - `get_video_info()` - Extract video info using yt-dlp
  - `handleGoogleLogin()` - OAuth2 flow management
  - `get_user_cookies_file()` - Credential storage

### Frontend (HTML/CSS/JS)
- **index.html:**
  - Navigation bar with auth buttons
  - Header section
  - URL input form
  - Video preview display
  - Quality selector
  - Download button
  - Features showcase
  - Footer

- **style.css:**
  - CSS variables for theming
  - Mobile-first responsive design
  - Dark theme (bg: #0f172a)
  - Gradient accents (primary: #6366f1)
  - Animations and transitions
  - Touch-optimized layout
  - Hamburger menu styling

- **script.js:**
  - Event listeners and handlers
  - API communication
  - DOM manipulation
  - Authentication flow
  - Mobile menu toggle
  - Error handling

## 🔧 Configuration Files

### systemd Service
```ini
[Unit]
Description=YouTube to MP4 Downloader Flask App
After=network.target

[Service]
Type=simple
User=nginx
WorkingDirectory=/var/www/html/ytdownload
Environment="PATH=/usr/local/bin:/usr/bin:/bin"
ExecStart=/usr/bin/python3 /var/www/html/ytdownload/app.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

### nginx Configuration
```nginx
location /ytdownload/ {
    proxy_pass http://127.0.0.1:5000/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_http_version 1.1;
    proxy_set_header Connection "";
    
    client_max_body_size 500M;
    proxy_connect_timeout 600s;
    proxy_send_timeout 600s;
    proxy_read_timeout 600s;
}

location = /ytdownload {
    return 301 /ytdownload/;
}
```

## 📊 Performance Metrics

- **Page Load:** < 2s on 4G
- **Video Info Fetch:** 2-5s (depends on YouTube)
- **Download Start:** Immediate after video selection
- **Mobile Responsive:** All screen sizes from 320px+
- **Browser Support:** All modern browsers (Chrome, Firefox, Safari, Edge)

## 🐛 Troubleshooting

### Service Won't Start
```bash
# Check logs
sudo journalctl -u ytdownload.service -n 50

# Check syntax
python3 -m py_compile /var/www/html/ytdownload/app.py

# Verify permissions
sudo chown -R nginx:nginx /var/www/html/ytdownload
sudo chmod -R 755 /var/www/html/ytdownload
```

### OAuth2 Not Working
```bash
# Check environment variables
env | grep GOOGLE

# Verify they're set
echo $GOOGLE_CLIENT_ID

# Ensure HTTPS is enabled
curl -I https://shieldstack.dev/ytdownload/
```

### Downloads Fail
```bash
# Check yt-dlp
yt-dlp --version

# Check disk space
df -h /tmp/ytdownload

# Test with direct URL
python3 -c "
import yt_dlp
ydl = yt_dlp.YoutubeDL({'quiet': False})
ydl.extract_info('https://www.youtube.com/watch?v=dQw4w9WgXcQ', download=False)
"
```

### Age-Restricted Content Still Blocked
- Make sure you're signed in with Google
- Check that GOOGLE_CLIENT_ID/SECRET are set
- Verify redirect URI matches exactly
- Clear browser cookies and try again

## 📝 Requirements.txt
```
Flask==2.3.3
yt-dlp==2023.11.16
python-dotenv==1.0.0
requests==2.31.0
google-auth-oauthlib==1.1.0
google-auth-httplib2==0.2.0
google-auth==2.26.1
Flask-Session==0.5.0
```

## 🚀 Deployment Notes

- **Download Directory:** `/tmp/ytdownload/` (temporary, auto-cleaned)
- **Session Directory:** `/tmp/ytdownload_sessions/` (Flask sessions)
- **Cookies Directory:** `/tmp/ytdownload_cookies/` (User auth credentials)
- **Port:** 5000 (Flask internal, proxied via nginx on port 443)
- **User:** nginx (service runs as nginx user)

## 📄 License

This project is provided as-is for personal use.

## 🎯 Future Enhancements

- [ ] Download history tracking
- [ ] Playlist batch download
- [ ] Audio-only extraction
- [ ] Video conversion (MP3, WebM, etc.)
- [ ] Queue management
- [ ] Download speed optimization
- [ ] User accounts and preferences
- [ ] API rate limiting
- [ ] Admin dashboard

## 📞 Support

For issues, check:
1. Service logs: `sudo journalctl -u ytdownload.service -f`
2. nginx logs: `sudo tail -f /var/log/nginx/error.log`
3. Troubleshooting section above
4. Google OAuth2 setup documentation

---

**Created:** November 2025  
**Last Updated:** November 2025  
**Version:** 2.0 (Mobile + OAuth2)
