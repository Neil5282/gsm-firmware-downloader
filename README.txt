GSM Firmware Downloader
Academic project: GSM Firmware Management & Download System

DEMO:
Open demo.html directly in a browser. It uses the public OTA catalog API for live firmware metadata and localStorage to simulate the application's database so you can preview the UI without XAMPP.

DEMO ACCOUNTS:
User:  user@gsm.local / user123

Test On XAMPP:
1. Copy this folder to C:\xampp\htdocs\gsm-firmware-downloader
2. Create a MySQL database and import database.sql in phpMyAdmin.
3. Edit config.php with your MySQL credentials.
4. Open http://localhost/gsm-firmware-downloader/
5. The PHP app is structured for MySQL and the external OTA catalog API.

IMPORTANT:
The demo stores only activity/metadata, not firmware files. The external OTA catalog is public/read-only and exposes device, region, model, version and source metadata.
