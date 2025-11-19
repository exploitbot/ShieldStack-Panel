#!/usr/bin/env python3
"""
Download ALL images from ManageEngine ServiceDesk Solutions
Saves with descriptive filenames for manual organization later
"""
import csv
import re
import os
import sys
import time
import requests
from requests.auth import HTTPBasicAuth
import urllib3
import platform

# Disable SSL warnings
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Set CSV field size limit (Windows-compatible)
try:
    if platform.system() == 'Windows':
        csv.field_size_limit(500000)
    else:
        csv.field_size_limit(sys.maxsize)
except:
    csv.field_size_limit(500000)

# ManageEngine URL
HELPDESK_URL = "https://helpdesk.appsforte.com"

print("=" * 80)
print("ManageEngine ServiceDesk - Download ALL Images")
print("=" * 80)
print()
print("This script will download all 420+ images and save them with descriptive")
print("filenames so you can organize them manually later.")
print()
username = input("Enter ManageEngine username: ").strip()
password = input("Enter ManageEngine password: ").strip()
print()

def sanitize_filename(text, max_length=50):
    """Convert text to safe filename"""
    # Remove/replace problematic characters
    text = re.sub(r'[<>:"/\\|?*]', '_', text)
    text = re.sub(r'\s+', '_', text)
    text = text.strip('._')
    # Limit length
    if len(text) > max_length:
        text = text[:max_length]
    return text

def load_solution_titles():
    """Load solution titles from CSV for descriptive filenames"""
    print("[1/3] Loading solution titles...")

    script_dir = os.path.dirname(os.path.abspath(__file__))
    solutions_csv = os.path.join(script_dir, 'solutions.csv')

    if not os.path.exists(solutions_csv):
        solutions_csv = 'solutions.csv'

    if not os.path.exists(solutions_csv):
        print(f"      ERROR: Cannot find solutions.csv")
        print(f"      Looked in: {script_dir}")
        return {}

    solutions = {}
    with open(solutions_csv, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            solutions[row['solutionid']] = row['title']

    print(f"      Loaded {len(solutions)} solution titles")
    print()
    return solutions

def extract_image_references(solutions):
    """Extract all image references with solution context"""
    print("[2/3] Extracting image references from solutions...")

    script_dir = os.path.dirname(os.path.abspath(__file__))
    solutions_csv = os.path.join(script_dir, 'solutions.csv')

    if not os.path.exists(solutions_csv):
        solutions_csv = 'solutions.csv'

    # Structure: [(solution_id, solution_title, image_path, image_number, url_type), ...]
    image_list = []

    with open(solutions_csv, 'r', encoding='utf-8') as f:
        content = f.read()

    lines = content.split('\n')
    for line in lines:
        if not line.strip():
            continue

        # Extract solution ID
        match = re.match(r'^(\d+),', line)
        if not match:
            continue

        sol_id = match.group(1)
        sol_title = solutions.get(sol_id, f"Solution_{sol_id}")

        all_images = []

        # Pattern 1: API v3 URLs - /api/v3/solutions/660/images/69773
        api_matches = re.findall(r'/api/v3/solutions/\d+/images/(\d+)', line)
        for img_id in api_matches:
            all_images.append(('api', img_id))

        # Pattern 2: Inline images - /inlineimages/Solution/267/0.png
        inline_matches = re.findall(r'/inlineimages/Solution/\d+/([^"<>\s]+)', line)
        for img_path in inline_matches:
            all_images.append(('inline', img_path))

        if all_images:
            # Remove duplicates while preserving order
            unique_imgs = []
            seen = set()
            for img_type, img_ref in all_images:
                key = f"{img_type}:{img_ref}"
                if key not in seen:
                    unique_imgs.append((img_type, img_ref))
                    seen.add(key)

            for idx, (img_type, img_ref) in enumerate(unique_imgs, 1):
                image_list.append((sol_id, sol_title, img_ref, idx, len(unique_imgs), img_type))

    print(f"      Found {len(image_list)} images across {len(set(x[0] for x in image_list))} solutions")
    print()
    return image_list

def download_all_images(image_list, session):
    """Download all images with descriptive filenames"""

    script_dir = os.path.dirname(os.path.abspath(__file__))
    image_dir = os.path.join(script_dir, "ManageEngine_Images")

    if not os.path.exists(image_dir):
        os.makedirs(image_dir)

    print(f"[3/3] Downloading {len(image_list)} images...")
    print(f"      Saving to: {image_dir}")
    print()

    # Also create an index file
    index_file = os.path.join(image_dir, "_IMAGE_INDEX.txt")

    successful = 0
    failed = 0

    with open(index_file, 'w', encoding='utf-8') as index:
        index.write("ManageEngine ServiceDesk - Downloaded Images Index\n")
        index.write("=" * 80 + "\n\n")
        index.write(f"Total images to download: {len(image_list)}\n")
        index.write(f"Download date: {time.strftime('%Y-%m-%d %H:%M:%S')}\n\n")
        index.write("=" * 80 + "\n\n")

        for current, (sol_id, sol_title, img_ref, img_num, total_imgs, img_type) in enumerate(image_list, 1):

            # Show progress
            if current % 10 == 0 or current == 1:
                print(f"      [{current}/{len(image_list)}] Downloading... ({successful} OK, {failed} failed)")

            # Create descriptive filename based on image type
            title_part = sanitize_filename(sol_title, max_length=40)

            # Get file extension from img_ref if it's an inline image
            if img_type == 'inline':
                # img_ref is like "0.png" or "1355601197193.jpg"
                file_ext = os.path.splitext(img_ref)[1] or '.png'
                img_id_part = os.path.splitext(img_ref)[0]
            else:
                # img_ref is just the ID number
                file_ext = '.png'
                img_id_part = img_ref

            if total_imgs > 1:
                filename = f"Sol{sol_id}_Img{img_num}of{total_imgs}_{title_part}_{img_id_part}{file_ext}"
            else:
                filename = f"Sol{sol_id}_{title_part}_{img_id_part}{file_ext}"

            filepath = os.path.join(image_dir, filename)

            # Build URL list based on image type
            if img_type == 'api':
                urls_to_try = [
                    f"{HELPDESK_URL}/api/v3/solutions/{sol_id}/images/{img_ref}",
                    f"{HELPDESK_URL}/solutions/{sol_id}/images/{img_ref}",
                ]
            else:  # inline
                urls_to_try = [
                    f"{HELPDESK_URL}/inlineimages/Solution/{sol_id}/{img_ref}",
                ]

            downloaded = False
            for url in urls_to_try:
                try:
                    response = session.get(url, verify=False, timeout=30)

                    if response.status_code == 200 and len(response.content) > 100:
                        # Save image
                        with open(filepath, 'wb') as f:
                            f.write(response.content)

                        downloaded = True
                        successful += 1

                        # Log to index
                        index.write(f"✓ {filename}\n")
                        index.write(f"  Solution ID: {sol_id}\n")
                        index.write(f"  Solution Title: {sol_title}\n")
                        index.write(f"  Image Reference: {img_ref}\n")
                        index.write(f"  Image Type: {img_type}\n")
                        index.write(f"  Image {img_num} of {total_imgs} in this solution\n")
                        index.write(f"  Size: {len(response.content)} bytes\n")
                        index.write(f"  URL: {url}\n")
                        index.write("\n")

                        break

                except Exception as e:
                    continue

            if not downloaded:
                failed += 1
                index.write(f"✗ FAILED: Solution {sol_id} - Image {img_ref} ({img_type})\n")
                index.write(f"  Title: {sol_title}\n")
                index.write(f"  URLs tried: {', '.join(urls_to_try)}\n\n")

            # Be nice to the server
            time.sleep(0.3)

        # Write summary
        index.write("\n" + "=" * 80 + "\n")
        index.write("DOWNLOAD SUMMARY\n")
        index.write("=" * 80 + "\n")
        index.write(f"Total images: {len(image_list)}\n")
        index.write(f"Successfully downloaded: {successful}\n")
        index.write(f"Failed: {failed}\n")
        index.write(f"Success rate: {successful/len(image_list)*100:.1f}%\n")

    print()
    print(f"      [{len(image_list)}/{len(image_list)}] Complete!")
    print()
    print(f"      ✓ Successfully downloaded: {successful}/{len(image_list)} images")
    print(f"      ✗ Failed: {failed}/{len(image_list)}")
    print()

    return successful, failed

def main():
    # Create session
    session = requests.Session()
    session.auth = HTTPBasicAuth(username, password)
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    })

    # Test authentication
    print("[0/3] Testing authentication...")
    try:
        response = session.get(HELPDESK_URL, verify=False, timeout=10)
        if response.status_code == 401:
            print("      ✗ Authentication failed! Check username/password.")
            input("\nPress Enter to exit...")
            return
        print("      ✓ Authentication successful")
        print()
    except Exception as e:
        print(f"      ✗ Connection error: {e}")
        input("\nPress Enter to exit...")
        return

    # Load solution titles
    solutions = load_solution_titles()
    if not solutions:
        print("✗ Cannot proceed without solutions.csv")
        input("\nPress Enter to exit...")
        return

    # Extract image references
    image_list = extract_image_references(solutions)
    if not image_list:
        print("✗ No images found to download")
        input("\nPress Enter to exit...")
        return

    # Download all images
    successful, failed = download_all_images(image_list, session)

    # Final summary
    print("=" * 80)
    print("DOWNLOAD COMPLETE!")
    print("=" * 80)
    print()
    print(f"Images saved to: {os.path.join(os.path.dirname(os.path.abspath(__file__)), 'ManageEngine_Images')}")
    print()
    print("Filename format:")
    print("  Single image: Sol123_SolutionTitle_ID45678.png")
    print("  Multiple:     Sol123_Img2of3_SolutionTitle_ID45678.png")
    print()
    print("An index file '_IMAGE_INDEX.txt' has been created with details")
    print("about each image and which solution it belongs to.")
    print()
    print("=" * 80)
    input("\nPress Enter to exit...")

if __name__ == '__main__':
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nCancelled by user.")
        input("Press Enter to exit...")
    except Exception as e:
        print(f"\n✗ Error: {e}")
        import traceback
        traceback.print_exc()
        input("\nPress Enter to exit...")
