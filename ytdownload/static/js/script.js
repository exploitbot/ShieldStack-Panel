let selectedFormat = null;
let isAuthenticated = false;

const urlInput = document.getElementById('urlInput');
const fetchBtn = document.getElementById('fetchBtn');
const loadingSpinner = document.getElementById('loadingSpinner');
const videoInfo = document.getElementById('videoInfo');
const errorMessage = document.getElementById('errorMessage');
const downloadBtn = document.getElementById('downloadBtn');
const loginBtn = document.getElementById('loginBtn');
const logoutBtn = document.getElementById('logoutBtn');
const userSection = document.getElementById('userSection');
const featuresSection = document.getElementById('featuresSection');
const authPrompt = document.getElementById('authPrompt');
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const navRight = document.getElementById('navRight');

document.addEventListener('DOMContentLoaded', () => {
    checkAuthStatus();
    setupEventListeners();
    setupMobileMenu();
    checkAuthCallback();
});

function setupEventListeners() {
    fetchBtn.addEventListener('click', fetchVideoInfo);
    downloadBtn.addEventListener('click', downloadVideo);
    loginBtn.addEventListener('click', handleGoogleLogin);
    logoutBtn.addEventListener('click', handleLogout);
    authPrompt.addEventListener('click', handleGoogleLogin);
    
    urlInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            fetchVideoInfo();
        }
    });
}

function setupMobileMenu() {
    mobileMenuToggle.addEventListener('click', () => {
        navRight.classList.toggle('active');
    });
    
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nav-container')) {
            navRight.classList.remove('active');
        }
    });
}

function checkAuthCallback() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('auth') === 'success') {
        checkAuthStatus();
        window.history.replaceState({}, document.title, window.location.pathname);
        showSuccess('Successfully signed in! You can now download age-restricted videos.');
    }
}

async function checkAuthStatus() {
    try {
        const response = await fetch('api/user');
        const data = await response.json();
        
        if (data.authenticated) {
            isAuthenticated = true;
            updateUIAuthenticated(data.user);
        } else {
            isAuthenticated = false;
            updateUIUnauthenticated();
        }
    } catch (error) {
        console.error('Auth check error:', error);
    }
}

function updateUIAuthenticated(user) {
    loginBtn.classList.add('hidden');
    userSection.classList.remove('hidden');
    
    if (user.picture) {
        document.getElementById('userAvatar').src = user.picture;
    }
    if (user.name) {
        const firstName = user.name.split(' ')[0];
        document.getElementById('userName').textContent = firstName;
    }
}

function updateUIUnauthenticated() {
    loginBtn.classList.remove('hidden');
    userSection.classList.add('hidden');
}

async function handleGoogleLogin() {
    try {
        const response = await fetch('api/auth/google');
        const data = await response.json();
        
        if (data.auth_url) {
            window.location.href = data.auth_url;
        } else {
            showError('Authentication not available. Try without signing in.');
        }
    } catch (error) {
        showError('Login failed: ' + error.message);
    }
}

async function handleLogout() {
    try {
        await fetch('api/auth/logout');
        isAuthenticated = false;
        updateUIUnauthenticated();
        hideVideoInfo();
        showSuccess('Logged out successfully');
    } catch (error) {
        showError('Logout failed');
    }
}

async function fetchVideoInfo() {
    const url = urlInput.value.trim();
    
    if (!url) {
        showError('Please paste a YouTube URL');
        return;
    }
    
    if (!url.includes('youtube') && !url.includes('youtu.be')) {
        showError('Please enter a valid YouTube URL');
        return;
    }
    
    hideVideoInfo();
    hideError();
    hideFeaturesIfNeeded();
    showLoading();
    fetchBtn.disabled = true;
    
    try {
        const response = await fetch('api/video-info', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ url: url })
        });
        
        const data = await response.json();
        hideLoading();
        fetchBtn.disabled = false;
        
        if (!data.success) {
            showError(data.error || 'Failed to fetch video', data.requires_auth);
            return;
        }
        
        displayVideoInfo(data);
        showVideoInfo();
        
    } catch (error) {
        hideLoading();
        fetchBtn.disabled = false;
        showError('Network error: ' + error.message);
    }
}

function displayVideoInfo(data) {
    document.getElementById('videoTitle').textContent = data.title;
    document.getElementById('thumbnail').src = data.thumbnail;
    document.getElementById('uploader').textContent = 'By ' + data.uploader;
    
    const minutes = Math.floor(data.duration / 60);
    const seconds = data.duration % 60;
    document.getElementById('duration').innerHTML = '<span>' + minutes + 'm ' + seconds + 's</span>';
    
    const typeSpan = document.getElementById('type');
    typeSpan.innerHTML = data.is_short 
        ? '<span>YouTube Short</span>' 
        : '<span>Video</span>';
    
    const qualityOptions = document.getElementById('qualityOptions');
    qualityOptions.innerHTML = '';
    selectedFormat = null;
    
    const formats = data.formats;
    const qualityKeys = Object.keys(formats);
    
    if (qualityKeys.length === 0) {
        showError('No formats available for this video');
        hideVideoInfo();
        return;
    }
    
    selectedFormat = formats[qualityKeys[0]];
    
    qualityKeys.forEach((quality) => {
        const format = formats[quality];
        const btn = document.createElement('button');
        btn.className = 'quality-btn';
        btn.textContent = quality;
        
        if (quality === qualityKeys[0]) {
            btn.classList.add('selected');
        }
        
        btn.addEventListener('click', () => {
            document.querySelectorAll('.quality-btn').forEach(b => {
                b.classList.remove('selected');
            });
            btn.classList.add('selected');
            selectedFormat = format;
        });
        
        qualityOptions.appendChild(btn);
    });
}

async function downloadVideo() {
    if (!selectedFormat || !urlInput.value.trim()) {
        showError('Please select a quality');
        return;
    }
    
    downloadBtn.disabled = true;
    const statusMsg = document.getElementById('downloadStatus');
    statusMsg.classList.remove('hidden', 'error', 'success');
    statusMsg.classList.add('loading');
    statusMsg.textContent = 'Downloading... This may take a minute.';
    
    try {
        const response = await fetch('api/download', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                url: urlInput.value.trim(),
                format_id: selectedFormat.format_id
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            statusMsg.classList.remove('loading');
            statusMsg.classList.add('error');
            statusMsg.textContent = 'Download failed: ' + (data.error || 'Unknown error');
            downloadBtn.disabled = false;
            return;
        }
        
        statusMsg.classList.remove('loading');
        statusMsg.classList.add('success');
        statusMsg.textContent = 'Download ready! Starting...';
        
        const downloadLink = document.createElement('a');
        downloadLink.href = 'api/download-file?path=' + encodeURIComponent(data.filepath);
        downloadLink.download = data.filename;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        downloadBtn.disabled = false;
        
        setTimeout(() => {
            statusMsg.classList.add('hidden');
        }, 4000);
        
    } catch (error) {
        statusMsg.classList.remove('loading');
        statusMsg.classList.add('error');
        statusMsg.textContent = 'Error: ' + error.message;
        downloadBtn.disabled = false;
    }
}

function showError(message, requiresAuth) {
    document.getElementById('errorText').textContent = message;
    if (requiresAuth && !isAuthenticated) {
        authPrompt.classList.remove('hidden');
    } else {
        authPrompt.classList.add('hidden');
    }
    errorMessage.classList.remove('hidden');
    navRight.classList.remove('active');
}

function showSuccess(message) {
    const errorText = document.getElementById('errorText');
    errorText.textContent = message;
    errorMessage.classList.remove('hidden');
    setTimeout(() => {
        errorMessage.classList.add('hidden');
    }, 3000);
}

function showLoading() {
    loadingSpinner.classList.remove('hidden');
}

function hideLoading() {
    loadingSpinner.classList.add('hidden');
}

function showVideoInfo() {
    videoInfo.classList.remove('hidden');
}

function hideVideoInfo() {
    videoInfo.classList.add('hidden');
}

function hideError() {
    errorMessage.classList.add('hidden');
}

function hideFeaturesIfNeeded() {
    if (videoInfo.classList.contains('hidden')) {
        featuresSection.style.display = 'none';
    }
}
