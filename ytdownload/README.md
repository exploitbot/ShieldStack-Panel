# YouTube to MP4 Downloader

A web-based YouTube video downloader that allows users to download YouTube videos and Shorts in their preferred quality.

## Features

- Download YouTube videos and Shorts
- Support for multiple quality options (144p, 240p, 360p, 480p, 720p, 1080p, and higher)
- Clean, user-friendly web interface
- Real-time video information display with thumbnail
- Quality selection before download
- Direct MP4 output format

## Installation & Setup

### Prerequisites

- Python 3.9+
- pip (Python package manager)
- nginx (web server)
- systemd (init system)

### Installation Steps

1. All files are located in `/var/www/html/ytdownload/`

2. Install Python dependencies:
   ```bash
   sudo pip3 install -r /var/www/html/ytdownload/requirements.txt
   ```

3. The systemd service is configured at `/etc/systemd/system/ytdownload.service`

4. nginx is configured in `/etc/nginx/conf.d/shieldstack.dev.conf`

### Starting the Service

```bash
sudo systemctl start ytdownload.service
sudo systemctl status ytdownload.service
```

### Accessing the Application

Visit: `http://shieldstack.dev/ytdownload`

## Usage

1. Paste a YouTube video or Shorts URL into the input field
2. Click "Get Info" to fetch video information
3. Select your preferred quality from the options
4. Click "Download Video" to start the download
5. The video will be downloaded and automatically offered for download

## API Endpoints

### GET /ytdownload/
Returns the main web interface

### POST /ytdownload/api/video-info
Get information about a YouTube video
```json
{
  "url": "https://www.youtube.com/watch?v=..."
}
```

### POST /ytdownload/api/download
Download a video with specified quality
```json
{
  "url": "https://www.youtube.com/watch?v=...",
  "format_id": "95"
}
```

### GET /ytdownload/api/download-file
Stream the downloaded file to the client

## Technical Stack

- **Backend**: Python Flask
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Video Processing**: yt-dlp
- **Web Server**: nginx
- **Process Manager**: systemd

## File Structure

```
/var/www/html/ytdownload/
├── app.py                 # Flask application
├── requirements.txt       # Python dependencies
├── templates/
│   └── index.html        # Web interface
└── static/
    ├── css/
    │   └── style.css     # Application styling
    └── js/
        └── script.js     # Frontend logic
```

## Troubleshooting

### Flask app not starting
```bash
sudo systemctl status ytdownload.service
sudo journalctl -u ytdownload.service -n 50
```

### nginx connection issues
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Permission issues
```bash
sudo chown -R nginx:nginx /var/www/html/ytdownload
sudo chmod -R 755 /var/www/html/ytdownload
```

### ffmpeg warning
For best results, install ffmpeg:
```bash
sudo yum install ffmpeg
```

## Notes

- Downloads are stored in `/tmp/ytdownload/`
- Large videos may take time to download depending on quality
- Some videos may have regional restrictions
- YouTube may require authentication for certain videos

## Support

For issues or feature requests, check the service logs:
```bash
sudo journalctl -u ytdownload.service -f
```
