# Sistem Pengusir Hama Cerdas Berbasis IoT (ESP32)

Project Capstone - Universitas KH. A. Wahab Hasbullah (2026)
**Tim Pengembang:** Mohammad Ghazi Al Ghifari dkk.

## 📌 Deskripsi Proyek
Sistem ini adalah alat pengusir hama (burung/tikus) otomatis yang dapat dikendalikan jarak jauh melalui Web Dashboard (Local Network) dan terintegrasi dengan Bot Telegram untuk notifikasi real-time. Sistem dirancang untuk membantu petani memantau dan menjaga lahan sawah secara efisien.

## 🚀 Fitur Utama
1.  **Remote Control:** Menyalakan/mematikan suara via Web Dashboard.
2.  **Jadwal Otomatis:** Alat bekerja mandiri sesuai jam yang ditentukan.
3.  **Smart Notification:** Laporan status dan error dikirim ke Telegram.
4.  **Self-Diagnosis:** Deteksi otomatis kerusakan hardware (SD Card/DFPlayer).
5.  **Offline Persistence:** Jadwal tetap tersimpan meski alat dimatikan (EEPROM/Preferences).

## 🛠️ Perangkat Keras (Hardware)
* **Mikrokontroler:** ESP32 DevKit V1
* **Audio:** DFPlayer Mini + Amplifier PAM8403 + Speaker
* **Penyimpanan:** Micro SD Card (Format FAT32)
* **Power:** Adaptor 12V DC + Buck Converter (to 5V)

## 📂 Struktur Folder
```text
/
├── firmware/
│   ├── Alat_Sawah.ino            # Kode utama ESP32
│   └── libraries/          # Library pendukung (DFRobot, UniversalTelegramBot)
├── web-backend/
│   ├── sawah.php           # API penerima data
│   └── admin_control.php   # Dashboard Web Interface
│   
├── database/
│   └── ghazi_riwayat_iot.sql        # Skema Database MySQL
├── docs/
│   ├── User_Manual.pdf     # Panduan Pengguna (Petani)
│   └── Skematik_Wiring.png # Gambar rangkaian kabel
└── README.md               # File dokumentasi ini
