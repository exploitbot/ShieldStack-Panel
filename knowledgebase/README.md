# ManageEngine ServiceDesk Knowledge Base Export

Complete export of all solutions from ManageEngine ServiceDesk database.

## 📊 Main File

**ManageEngine_Solutions_KnowledgeBase_with_Images.xlsx** (490 KB)
- All 647 solutions with descriptions
- HTML converted to plain text
- 420 embedded images documented with markers
- 242 attachments listed
- Professional formatting with frozen headers

## 📁 Directory Structure

```
/var/www/html/knowledgebase/
├── ManageEngine_Solutions_KnowledgeBase_with_Images.xlsx  ⭐ Main file
├── README_KnowledgeBase.txt                               Documentation
├── solution_images_list.csv                               Image attachment list
└── grabimages/                                            Image download scripts
    ├── download_images_auto.py                            Automated downloader
    ├── download_and_embed_images.py                       Interactive downloader
    ├── solutions.csv                                      Raw solution data
    ├── solution_attachments.csv                          Raw attachment data
    ├── HOW_TO_DOWNLOAD_IMAGES.txt                        Usage instructions
    └── IMAGE_EXTRACTION_NOTES.txt                        Technical notes
```

## 📈 Statistics

- **Solutions**: 647
- **Embedded Images**: 420
- **Attachments**: 242
- **Image Attachments**: 39
- **Source**: PostgreSQL database on 10.100.90.222

## 🖼️ About Images

The Excel file contains markers showing where images appear:
- Format: `[IMAGE #1 - ID: 69773]`
- "Embedded Images" column shows count per solution
- Actual images stored in ManageEngine system

## 🔧 To Download Images

See `grabimages/` directory for automated scripts that:
1. Connect to ManageEngine web interface
2. Download all embedded images
3. Create Excel file with images embedded

**Requirements:**
- Network access to helpdesk.appshosting.com or 10.100.90.222
- ManageEngine credentials (apps.forte / Help20!8)
- Python 3 with requests, openpyxl, Pillow

**Run:**
```bash
cd /var/www/html/knowledgebase/grabimages
python3 download_images_auto.py
```

## 📝 Notes

- All HTML content converted to plain text
- Image locations preserved with ID references
- Attachment files were deleted from server (only metadata remains)
- Solutions can be accessed at: https://helpdesk.appshosting.com/

## 🗓️ Export Date

October 29, 2025

---

For questions or updates, see documentation files in this directory.
