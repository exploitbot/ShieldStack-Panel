from flask import Flask, render_template, request, jsonify, send_file, session, redirect, url_for
from flask_session import Session
import yt_dlp
import os
import secrets
import json
from urllib.parse import quote, parse_qs, urlparse
import requests
from google_auth_oauthlib.flow import Flow
from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials
import pickle

app = Flask(__name__)
app.secret_key = secrets.token_hex(32)

# Session configuration
app.config['SESSION_TYPE'] = 'filesystem'
app.config['SESSION_PERMANENT'] = True
app.config['PERMANENT_SESSION_LIFETIME'] = 86400 * 30  # 30 days
app.config['SESSION_FILE_DIR'] = '/tmp/ytdownload_sessions'
Session(app)

# Create session directory
os.makedirs(app.config['SESSION_FILE_DIR'], exist_ok=True)

# Create directories for downloads and cookies
DOWNLOAD_DIR = "/tmp/ytdownload"
COOKIES_DIR = "/tmp/ytdownload_cookies"
os.makedirs(DOWNLOAD_DIR, exist_ok=True)
os.makedirs(COOKIES_DIR, exist_ok=True)

# Google OAuth2 configuration
GOOGLE_CLIENT_ID = os.getenv('GOOGLE_CLIENT_ID', '')
GOOGLE_CLIENT_SECRET = os.getenv('GOOGLE_CLIENT_SECRET', '')
GOOGLE_REDIRECT_URI = os.getenv('GOOGLE_REDIRECT_URI', 'https://shieldstack.dev/ytdownload/api/auth/callback')

SCOPES = ['https://www.googleapis.com/auth/userinfo.profile', 
          'https://www.googleapis.com/auth/userinfo.email']

def get_google_flow():
    """Create Google OAuth2 flow"""
    if not GOOGLE_CLIENT_ID or not GOOGLE_CLIENT_SECRET:
        return None
    
    return Flow.from_client_secrets_file(
        '/tmp/google_credentials.json',
        scopes=SCOPES,
        redirect_uri=GOOGLE_REDIRECT_URI
    ) if os.path.exists('/tmp/google_credentials.json') else None

def get_user_cookies_file():
    """Get the cookies file path for the current user"""
    if 'user_id' in session:
        return os.path.join(COOKIES_DIR, f"{session['user_id']}_cookies.txt")
    return None

def get_video_info(url, use_auth=False):
    """Get video information and available formats"""
    try:
        ydl_opts = {
            'quiet': True,
            'no_warnings': True,
            'extract_flat': False,
            'skip_download': True,
            'socket_timeout': 30,
            'http_headers': {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            },
        }
        
        # Add authentication cookies if available
        if use_auth and 'user_id' in session:
            cookies_file = get_user_cookies_file()
            if os.path.exists(cookies_file):
                ydl_opts['cookiefile'] = cookies_file
        
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info = ydl.extract_info(url, download=False)
            
            # Get formats with proper filtering
            formats = {}
            
            if 'formats' in info and info['formats']:
                video_formats = [f for f in info['formats'] 
                               if f.get('vcodec') != 'none' and f.get('height') and f.get('height') >= 144]
                
                for fmt in video_formats:
                    height = fmt.get('height')
                    quality_label = f"{height}p"
                    if quality_label not in formats:
                        formats[quality_label] = {
                            'format_id': fmt['format_id'],
                            'resolution': height,
                            'fps': fmt.get('fps', 30),
                            'ext': fmt.get('ext', 'mp4'),
                            'has_audio': fmt.get('acodec') != 'none'
                        }
            
            sorted_formats = dict(sorted(formats.items(), 
                                        key=lambda x: int(x[1]['resolution']),
                                        reverse=True))
            
            return {
                'success': True,
                'title': info.get('title', 'Unknown'),
                'duration': info.get('duration', 0),
                'thumbnail': info.get('thumbnail', ''),
                'is_short': info.get('duration', 0) < 60,
                'formats': sorted_formats,
                'uploader': info.get('uploader', 'Unknown')
            }
    except Exception as e:
        error_str = str(e)
        if 'age' in error_str.lower() or 'sign in' in error_str.lower():
            return {
                'success': False,
                'error': 'This video is age-restricted. Sign in with Google to access it.',
                'requires_auth': True
            }
        return {
            'success': False,
            'error': str(e)
        }

@app.route('/')
def index():
    """Serve the main page"""
    return render_template('index.html')

@app.route('/api/user')
def get_user():
    """Get current user info"""
    if 'user_info' in session:
        return jsonify({
            'authenticated': True,
            'user': session['user_info']
        })
    return jsonify({'authenticated': False})

@app.route('/api/auth/google')
def auth_google():
    """Initiate Google OAuth2 flow"""
    if not GOOGLE_CLIENT_ID or not GOOGLE_CLIENT_SECRET:
        return jsonify({'success': False, 'error': 'Google OAuth2 not configured. Please contact admin.'}), 500
    
    try:
        flow = Flow.from_client_config(
            {
                'installed': {
                    'client_id': GOOGLE_CLIENT_ID,
                    'client_secret': GOOGLE_CLIENT_SECRET,
                    'auth_uri': 'https://accounts.google.com/o/oauth2/auth',
                    'token_uri': 'https://oauth2.googleapis.com/token',
                    'redirect_uris': [GOOGLE_REDIRECT_URI]
                }
            },
            scopes=SCOPES,
            redirect_uri=GOOGLE_REDIRECT_URI
        )
        
        authorization_url, state = flow.authorization_url(access_type='offline', prompt='consent')
        session['google_oauth_state'] = state
        
        return jsonify({'auth_url': authorization_url})
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/auth/callback')
def auth_callback():
    """Handle Google OAuth2 callback"""
    try:
        state = session.get('google_oauth_state')
        if not state:
            return redirect('/ytdownload/?error=Invalid state')
        
        flow = Flow.from_client_config(
            {
                'installed': {
                    'client_id': GOOGLE_CLIENT_ID,
                    'client_secret': GOOGLE_CLIENT_SECRET,
                    'auth_uri': 'https://accounts.google.com/o/oauth2/auth',
                    'token_uri': 'https://oauth2.googleapis.com/token',
                    'redirect_uris': [GOOGLE_REDIRECT_URI]
                }
            },
            scopes=SCOPES,
            redirect_uri=GOOGLE_REDIRECT_URI,
            state=state
        )
        
        # Get credentials
        flow.fetch_token(authorization_response=request.url)
        credentials = flow.credentials
        
        # Get user info
        response = requests.get(
            'https://www.googleapis.com/oauth2/v1/userinfo',
            headers={'Authorization': f'Bearer {credentials.token}'}
        )
        user_info = response.json()
        
        # Store user info and credentials
        session['user_id'] = user_info['id']
        session['user_info'] = {
            'id': user_info['id'],
            'email': user_info['email'],
            'name': user_info['name'],
            'picture': user_info['picture']
        }
        
        # Store credentials for later use
        credentials_file = os.path.join(COOKIES_DIR, f"{user_info['id']}_credentials.json")
        with open(credentials_file, 'w') as f:
            json.dump({
                'token': credentials.token,
                'refresh_token': credentials.refresh_token,
                'token_expiry': credentials.expiry.isoformat() if credentials.expiry else None,
                'access_token': credentials.token
            }, f)
        
        # Create YouTube cookies file for yt-dlp
        cookies_file = os.path.join(COOKIES_DIR, f"{user_info['id']}_cookies.txt")
        
        # Initialize YouTube cookies using yt-dlp with the access token
        ydl_opts = {
            'quiet': True,
            'cookiesfrombrowser': None,
            'http_headers': {
                'Authorization': f'Bearer {credentials.token}'
            }
        }
        
        session.permanent = True
        session.modified = True
        
        return redirect('/ytdownload/?auth=success')
        
    except Exception as e:
        print(f"Auth callback error: {e}")
        return redirect(f'/ytdownload/?error={str(e)}')

@app.route('/api/auth/logout')
def logout():
    """Logout user"""
    session.clear()
    return jsonify({'success': True})

@app.route('/api/video-info', methods=['POST'])
def video_info():
    """Get video information"""
    data = request.json
    url = data.get('url', '')
    use_auth = 'user_id' in session
    
    if not url:
        return jsonify({'success': False, 'error': 'No URL provided'})
    
    # First try without auth
    info = get_video_info(url, use_auth=False)
    
    # If age-restricted and user is logged in, try with auth
    if not info['success'] and info.get('requires_auth') and use_auth:
        info = get_video_info(url, use_auth=True)
    
    return jsonify(info)

@app.route('/api/download', methods=['POST'])
def download():
    """Download video with specified format"""
    data = request.json
    url = data.get('url', '')
    format_id = data.get('format_id', '')
    
    if not url or not format_id:
        return jsonify({'success': False, 'error': 'Missing URL or format'})
    
    try:
        output_pattern = "%(title)s.%(ext)s"
        output_path = os.path.join(DOWNLOAD_DIR, output_pattern)
        
        ydl_opts = {
            'format': f'{format_id}+bestaudio/best',
            'outtmpl': output_path,
            'quiet': True,
            'no_warnings': True,
            'socket_timeout': 30,
            'http_headers': {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            },
        }
        
        # Add cookies if user is authenticated
        if 'user_id' in session:
            cookies_file = get_user_cookies_file()
            if os.path.exists(cookies_file):
                ydl_opts['cookiefile'] = cookies_file
        
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info = ydl.extract_info(url, download=True)
            filename = ydl.prepare_filename(info)
            
            return jsonify({
                'success': True,
                'filename': os.path.basename(filename),
                'filepath': filename
            })
    
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/download-file', methods=['GET'])
def download_file():
    """Stream the downloaded file"""
    filepath = request.args.get('path', '')
    
    if not filepath or not os.path.exists(filepath):
        return jsonify({'success': False, 'error': 'File not found'}), 404
    
    try:
        return send_file(filepath, as_attachment=True)
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=False, host='0.0.0.0', port=5000)
