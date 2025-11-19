Download ALL Images from ManageEngine ServiceDesk
=================================================

SIMPLIFIED SCRIPT - Just Downloads Images with Descriptive Names
-----------------------------------------------------------------

This script (download_all_images.py) downloads ALL images from your
ManageEngine ServiceDesk solutions and saves them with descriptive
filenames so you can organize them manually later.

NO Excel creation - just pure image downloading!


WHAT IT DOES:
-------------
✓ Downloads all 420+ embedded images from ManageEngine
✓ Saves with descriptive filenames including:
  - Solution ID
  - Solution Title (sanitized for filename safety)
  - Image number (if solution has multiple images)
  - Image ID for reference

✓ Creates an index file listing all images and their details
✓ Detects image format (PNG, JPG, GIF) automatically


FILENAME FORMAT:
----------------
Single image per solution:
  Sol660_How_to_Reset_Password_ID69773.png

Multiple images per solution:
  Sol123_Img1of3_Network_Configuration_ID45678.png
  Sol123_Img2of3_Network_Configuration_ID45679.png
  Sol123_Img3of3_Network_Configuration_ID45680.png


HOW TO RUN:
-----------
1. Copy the entire 'grabimages' folder to your Windows PC

2. Make sure these files are in the folder:
   - download_all_images.py (the script)
   - solutions.csv (solution data)
   - solution_attachments.csv (optional)

3. Open Command Prompt or PowerShell

4. Navigate to the grabimages folder:
   cd C:\path\to\grabimages

5. Run the script:
   python download_all_images.py

6. Enter your ManageEngine credentials when prompted:
   Username: apps.forte
   Password: Help20!8


OUTPUT:
-------
Creates a folder: ManageEngine_Images/

Contains:
  - All downloaded images with descriptive names
  - _IMAGE_INDEX.txt (detailed list of all images)


REQUIREMENTS:
-------------
✓ Python 3
✓ Required libraries: requests, urllib3
  Install with: pip install requests urllib3

✓ Network access to: https://helpdesk.appsforte.com
✓ Valid ManageEngine login credentials


ESTIMATED TIME:
---------------
- 420 images × 0.3 seconds = ~2-3 minutes minimum
- Plus download time (depends on image sizes and connection)
- Total: 5-10 minutes expected


INDEX FILE:
-----------
The _IMAGE_INDEX.txt file contains:
  ✓ Full list of all downloaded images
  ✓ Which solution each image belongs to
  ✓ Image numbers (1 of 3, 2 of 3, etc.)
  ✓ File sizes
  ✓ Download summary and statistics

Use this index to reference which images belong to which solutions!


ORGANIZING MANUALLY:
--------------------
After download, you can:
1. Browse images by solution ID in the filename
2. Use the index file to find images for specific solutions
3. Sort/filter by filename to group related images
4. Manually embed them into documents as needed


TROUBLESHOOTING:
----------------
Error: "Cannot find solutions.csv"
→ Make sure solutions.csv is in the same folder as the script

Error: "Authentication failed"
→ Check your username and password
→ Verify you can access https://helpdesk.appsforte.com

Error: "Connection error"
→ Check your network connection
→ Make sure you can reach the ManageEngine server

Some images failed to download:
→ Normal - some image references may be outdated
→ Check the index file to see which ones failed
→ Most images should download successfully


DIFFERENCES FROM OTHER SCRIPTS:
--------------------------------
download_all_images.py (THIS ONE):
  → Just downloads images with descriptive names
  → No Excel creation
  → Simplest option for manual organization
  → Creates detailed index file

download_images_windows.py:
  → Downloads images AND creates Excel file
  → Embeds first image of each solution
  → More complex, takes longer

download_images_auto.py:
  → Linux version with hardcoded credentials
  → Creates Excel automatically


NEXT STEPS:
-----------
After running this script, you'll have:
  ✓ Folder full of images with clear, descriptive names
  ✓ Index file showing which images belong to which solutions
  ✓ All images ready to organize however you want!

You can then manually:
  - Create your own documents
  - Organize images by category
  - Select which images to include where
  - Full control over the organization!


SUPPORT:
--------
Script location: /var/www/html/knowledgebase/grabimages/
For questions, check the other documentation files in this folder.
