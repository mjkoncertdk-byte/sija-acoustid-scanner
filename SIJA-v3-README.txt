SIJA Music v3 package

Files:
1. sija-artist-portal-v3.php
   - WordPress plugin for one.com / SIJA-music.com
   - Artist portal, login, release requests, upload progress, admin status, streams, earnings, documents
   - Scanner settings page under SIJA Portal > Scanner Settings

2. sija-acoustid-scanner.php
   - Upload this to a VPS/server where fpcalc is installed
   - Replace:
     $SHARED_SECRET = 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET';
     $ACOUSTID_API_KEY = 'PASTE_YOUR_ACOUSTID_API_KEY_HERE';

Ubuntu scanner install:
sudo apt update
sudo apt install libchromaprint-tools ffmpeg

In WordPress:
1. Upload sija-artist-portal-v3.php as a plugin.
2. Activate it.
3. Use shortcode:
   [sija_artist_portal]
4. Go to SIJA Portal > Scanner Settings.
5. Insert scanner endpoint URL, e.g.
   https://scanner.yourdomain.com/sija-acoustid-scanner.php
6. Insert the same shared secret token you placed in scanner.php.

Important:
Do not put your AcoustID API key in the WordPress frontend.
Keep it only in the external scanner file.
