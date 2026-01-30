#include <WiFi.h>
#include <WebServer.h>
#include <WiFiClientSecure.h>
#include <UniversalTelegramBot.h>
#include <HTTPClient.h> 
#include <time.h>
#include <HardwareSerial.h>
#include "DFRobotDFPlayerMini.h" 
#include <Preferences.h>
// #include <WiFiManager.h> // <-- HAPUS INI

// ================= CONFIG WIFI (HARDCODE) =================
// GANTI DENGAN NAMA HOTSPOT DAN PASSWORD HP ANDA
const char* ssid     = "tot";     // <--- GANTI INI
const char* password = "12345678";      // <--- GANTI INI

// ================= CONFIG BOT =================
#define BOTtoken "8364317794:AAECu4wvnvSu-zxKuChqjVvPTsvE4iFBJeE"
#define CHAT_ID "6929287612"

// ================= CONFIG HOSTING =================
String linkSimpan = "https://ghazi.ftiunwaha.my.id/sawah/sawah.php"; 
String linkDebug  = "https://ghazi.ftiunwaha.my.id/sawah/debug.php"; 

// ================= OBJECTS =================
WiFiClientSecure clientBot;
UniversalTelegramBot bot(BOTtoken, clientBot);
WebServer server(80);
HardwareSerial mySerial(2); 
DFRobotDFPlayerMini myDFPlayer;
Preferences preferences;

// ================= VARIABLES =================
bool statusAlat = false;
int volumeLevel = 20; 
int soundIndex = 1; 
int totalLagu = 5; // Default awal

struct Jadwal {
  int onH = -1; int onM = -1;
  int offH = -1; int offM = -1;
  bool aktif = false; 
};
Jadwal daftarJadwal[5]; 

// WAKTU
const char* ntpServer = "pool.ntp.org";
const long gmtOffset_sec = 7 * 3600; 
const int daylightOffset_sec = 0;
time_t waktuNyala, waktuMati;

// ================= FUNGSI BANTUAN =================
String getTimeNow() {
  struct tm t; if (!getLocalTime(&t)) return "--:--:--";
  char b[30]; strftime(b, sizeof(b), "%H:%M:%S", &t); return String(b);
}
int getCurHour() { struct tm t; if(!getLocalTime(&t)) return -1; return t.tm_hour; }
int getCurMin() { struct tm t; if(!getLocalTime(&t)) return -1; return t.tm_min; }
String padding2(int a) { return (a < 10) ? "0"+String(a) : String(a); }
String fmtDurasi(long d) { return String(d/3600)+"j "+String((d%3600)/60)+"m "+String(d%60)+"d"; }

// ================= DATABASE INTERNAL =================
void simpanJadwalKeMemori() {
  preferences.begin("sawah-db", false);
  preferences.putInt("vol", volumeLevel);
  preferences.putInt("sound", soundIndex);
  for(int i=0; i<5; i++) {
    String k = String(i);
    preferences.putInt(("onH"+k).c_str(), daftarJadwal[i].onH);
    preferences.putInt(("onM"+k).c_str(), daftarJadwal[i].onM);
    preferences.putInt(("offH"+k).c_str(), daftarJadwal[i].offH);
    preferences.putInt(("offM"+k).c_str(), daftarJadwal[i].offM);
    preferences.putBool(("akt"+k).c_str(), daftarJadwal[i].aktif);
  }
  preferences.end();
}

void bacaJadwalDariMemori() {
  preferences.begin("sawah-db", true);
  volumeLevel = preferences.getInt("vol", 20);
  soundIndex  = preferences.getInt("sound", 1);
  for(int i=0; i<5; i++) {
    String k = String(i);
    daftarJadwal[i].onH = preferences.getInt(("onH"+k).c_str(), -1);
    daftarJadwal[i].onM = preferences.getInt(("onM"+k).c_str(), -1);
    daftarJadwal[i].offH = preferences.getInt(("offH"+k).c_str(), -1);
    daftarJadwal[i].offM = preferences.getInt(("offM"+k).c_str(), -1);
    daftarJadwal[i].aktif = preferences.getBool(("akt"+k).c_str(), false);
  }
  preferences.end();
}

// ================= KOMUNIKASI HOSTING =================
void kirimLog(String urlFull) {
  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;
    urlFull.replace(" ", "%20"); 
    http.begin(urlFull);
    int code = http.GET();
    http.end();
  }
}

void kirimKeHosting(String status, String sumber) {
  String url = linkSimpan + "?status=" + status + "&sumber=" + sumber;
  kirimLog(url);
}

void laporError(String tipe, String pesan) {
  // 1. Kirim ke Serial Monitor (Laptop)
  Serial.println("DEBUG: " + pesan);
  
  // 2. Kirim ke Hosting (Database Web)
  String url = linkDebug + "?tipe=" + tipe + "&pesan=" + pesan;
  kirimLog(url);

  // 3. KIRIM KE TELEGRAM (INI YANG KURANG KEMARIN)
  String msgBot = "⚠️ *" + tipe + "*\n" + pesan;
  bot.sendMessage(CHAT_ID, msgBot, "Markdown");
}

// ================= KONTROL ALAT =================
void nyalakanAlat(String sumber) {
  if (statusAlat) return; 
  
  myDFPlayer.volume(volumeLevel);
  delay(100);
  myDFPlayer.loop(soundIndex);           
  
  statusAlat = true;
  time(&waktuNyala);
  
  String msg = "🔔 *ALAT ON (" + sumber + ")*\n🎵 Suara: Track " + String(soundIndex) + "\n🕒 " + getTimeNow();
  bot.sendMessage(CHAT_ID, msg, "Markdown");
  kirimKeHosting("ON", sumber);
  Serial.println("Perintah PLAY dikirim.");
}

void matikanAlat(String sumber) {
  if (!statusAlat) return; 
  myDFPlayer.stop(); 
  statusAlat = false;
  time(&waktuMati);
  
  long durasi = difftime(waktuMati, waktuNyala);
  bot.sendMessage(CHAT_ID, "⛔ *ALAT OFF (" + sumber + ")*\n⏱ Durasi: " + fmtDurasi(durasi), "Markdown");
  kirimKeHosting("OFF", sumber);
  Serial.println("Perintah STOP dikirim.");
}

// ================= WEB DASHBOARD =================
String dashboardHTML() {
  String stColor = statusAlat ? "linear-gradient(135deg, #0f9b0f, #054a05)" : "linear-gradient(135deg, #3a3a3a, #1f1f1f)";
  String stText  = statusAlat ? "AKTIF 🔊" : "NON-AKTIF 💤";

  String listJadwal = "";
  int slotKosong = -1;
  int jumlahJadwal = 0;

  for(int i=0; i<5; i++) {
    if(daftarJadwal[i].aktif) {
      jumlahJadwal++;
      listJadwal += "<div class='timer-item'><span>⏰ <b>" + padding2(daftarJadwal[i].onH) + ":" + padding2(daftarJadwal[i].onM) + 
                    "</b> s/d <b>" + padding2(daftarJadwal[i].offH) + ":" + padding2(daftarJadwal[i].offM) + "</b></span>";
      listJadwal += "<a href='/del?id=" + String(i) + "' class='btn-del'>Hapus</a></div>";
    } else if (slotKosong == -1) {
      slotKosong = i; 
    }
  }
  if(jumlahJadwal == 0) listJadwal = "<p style='opacity:0.5; font-size:12px;'>Belum ada jadwal tersimpan.</p>";

  // --- LOOPING DINAMIS SESUAI TOTAL LAGU ---
  String optSound = "";
  for(int j=1; j <= totalLagu; j++){ 
    String sel = (j == soundIndex) ? "selected" : "";
    optSound += "<option value='" + String(j) + "' " + sel + ">Suara " + String(j) + "</option>";
  }

  String html = R"rawliteral(
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Smart Sawah</title>
  <style>
    :root { --bg:#121212; --card:#1e1e1e; --accent:#00bcd4; --text:#fff; }
    body { font-family: sans-serif; background: var(--bg); color: var(--text); margin:0; padding:15px; display:flex; justify-content:center; }
    .cont { width:100%; max-width:400px; display:flex; flex-direction:column; gap:15px; }
    .head { text-align:center; margin-bottom:5px; }
    .st-card { background: )rawliteral"; html += stColor; html += R"rawliteral(; padding:25px; border-radius:15px; text-align:center; box-shadow:0 8px 16px rgba(0,0,0,0.3); }
    .grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .btn { border:none; padding:15px; border-radius:10px; font-weight:bold; color:#fff; cursor:pointer; text-decoration:none; display:block; text-align:center; }
    .on { background:#2ecc71; } .off { background:#e74c3c; } .save { background:var(--accent); color:#000; width:100%; margin-top:10px; }
    .card { background:var(--card); padding:15px; border-radius:15px; }
    .timer-item { background:#333; padding:10px; border-radius:8px; margin-bottom:5px; display:flex; justify-content:space-between; align-items:center; font-size:14px; }
    .btn-del { background:#ff5f5f; color:#fff; padding:5px 10px; border-radius:5px; text-decoration:none; font-size:11px; }
    input, select { background:#333; border:none; color:#fff; padding:8px; border-radius:5px; text-align:center; font-weight:bold; }
    .ft { text-align:center; font-size:11px; color:#555; margin-top:20px; }
  </style>
</head>
<body>
  <div class="cont">
    <div class="head"><h3>🌾 KONTROL SAWAH 🌾</h3><small>)rawliteral"; html += getTimeNow(); html += R"rawliteral(</small></div>
    <div class="st-card"><h2>)rawliteral"; html += stText; html += R"rawliteral(</h2></div>
    <div class="grid">
      <a href="/on" class="btn on">▶ NYALAKAN</a>
      <a href="/off" class="btn off">⏹ MATIKAN</a>
    </div>
    <div class="card">
      <p style="margin:0 0 10px 0;"><b>🎵 Pilih Suara & Volume</b></p>
      <form action="/setsound" method="GET" style="display:flex; gap:10px;">
        <select name="snd" style="flex:1;">)rawliteral"; html += optSound; html += R"rawliteral(</select>
        <button type="submit" style="background:#444; color:#fff; border:none; border-radius:5px;">SET</button>
      </form>
      <form action="/setvolume" method="GET" style="margin-top:10px;">
        <input type="range" name="val" min="0" max="30" value=")rawliteral"; html += String(volumeLevel); html += R"rawliteral(" style="width:100%;" onchange="this.form.submit()">
        <div style="text-align:center; font-size:12px;">Volume: <span style="color:var(--accent)">)rawliteral"; html += String(volumeLevel); html += R"rawliteral(</span></div>
      </form>
    </div>
    <div class="card">
      <p style="margin:0 0 10px 0;"><b>🕒 Daftar Jadwal Otomatis</b></p>
      )rawliteral"; html += listJadwal; html += R"rawliteral(
      )rawliteral"; 
      if(slotKosong != -1) {
        html += R"rawliteral(
        <div style="margin-top:15px; border-top:1px solid #333; padding-top:10px;">
          <small>Tambah Jadwal Baru:</small>
          <form action="/add" method="GET">
            <input type="hidden" name="id" value=")rawliteral"; html += String(slotKosong); html += R"rawliteral(">
            <div style="display:flex; justify-content:space-between; margin-top:5px;">
              <div>ON <input type="number" name="on_h" min="0" max="23" placeholder="06" style="width:35px">:<input type="number" name="on_m" min="0" max="59" placeholder="00" style="width:35px"></div>
              <div>OFF <input type="number" name="off_h" min="0" max="23" placeholder="18" style="width:35px">:<input type="number" name="off_m" min="0" max="59" placeholder="00" style="width:35px"></div>
            </div>
            <button class="btn save">SIMPAN JADWAL</button>
          </form>
        </div>
        )rawliteral";
      } else {
        html += "<p style='color:#f39c12; font-size:12px; text-align:center;'>Slot Jadwal Penuh (Max 5)</p>";
      }
      html += R"rawliteral(
    </div>
    <div class="ft">ESP32 Smart Agriculture v2.5 (Hardcoded)</div>
  </div>
</body>
</html>
)rawliteral";
  return html;
}

// ================= HANDLERS =================
void handleRoot() { server.send(200, "text/html", dashboardHTML()); }
void handleOn() { nyalakanAlat("WEB"); server.sendHeader("Location", "/"); server.send(303); }
void handleOff() { matikanAlat("WEB"); server.sendHeader("Location", "/"); server.send(303); }

void handleSetVolume() { 
  if(server.hasArg("val")) {
    volumeLevel = server.arg("val").toInt();
    myDFPlayer.volume(volumeLevel);
    simpanJadwalKeMemori();
  }
  server.sendHeader("Location", "/"); server.send(303); 
}

void handleSetSound() {
  if(server.hasArg("snd")) {
    soundIndex = server.arg("snd").toInt();
    simpanJadwalKeMemori();
    if(statusAlat) myDFPlayer.loop(soundIndex);
  }
  server.sendHeader("Location", "/"); server.send(303);
}

void handleAdd() {
  if(server.hasArg("id")) {
    int id = server.arg("id").toInt();
    if(id >= 0 && id < 5) {
      daftarJadwal[id].onH = server.arg("on_h").toInt();
      daftarJadwal[id].onM = server.arg("on_m").toInt();
      daftarJadwal[id].offH = server.arg("off_h").toInt();
      daftarJadwal[id].offM = server.arg("off_m").toInt();
      daftarJadwal[id].aktif = true;
      simpanJadwalKeMemori();
      bot.sendMessage(CHAT_ID, "🕒 *JADWAL BARU DITAMBAHKAN*", "Markdown");
    }
  }
  server.sendHeader("Location", "/"); server.send(303);
}

void handleDel() {
  if(server.hasArg("id")) {
    int id = server.arg("id").toInt();
    if(id >= 0 && id < 5) {
      daftarJadwal[id].aktif = false; 
      simpanJadwalKeMemori();
    }
  }
  server.sendHeader("Location", "/"); server.send(303);
}

// ================= SETUP (REVISI ANTI-RESET) =================
void setup() {
  Serial.begin(115200);
  
  // 1. WAJIB KONEK WIFI DULUAN (Supaya bisa lapor error)
  Serial.print("Menghubungkan ke "); Serial.println(ssid);
  WiFi.begin(ssid, password);

  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 20) { // Coba 10 detik
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() != WL_CONNECTED) {
     Serial.println("\nGagal Konek WiFi! Alat masuk Mode Offline.");
  } else {
     Serial.println("\nTerhubung WiFi!");
     Serial.println("IP: " + WiFi.localIP().toString());

     long rssi = WiFi.RSSI();
     Serial.print("Kuat Sinyal Awal: ");
     Serial.print(rssi);
     Serial.println(" dBm");
  }
  
  // 2. SETTING WAKTU & BOT (Butuh WiFi)
  clientBot.setInsecure();
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);

  // 3. BARU CEK HARDWARE (DFPLAYER)
  // Perhatikan pinnya: pastikan sesuai kabel (misal 16, 17)
  mySerial.begin(9600, SERIAL_8N1, 26, 27); // <--- PASTIKAN PIN INI BENAR
  delay(3000); 
  bacaJadwalDariMemori();

  if (!myDFPlayer.begin(mySerial)) {
    Serial.println("WARNING: DFPlayer Error");
    // Sekarang aman panggil laporError karena WiFi sudah dicoba konek
    if (WiFi.status() == WL_CONNECTED) {
        laporError("WARNING", "DFPlayer Unresponsive");
    }
  } else {
    Serial.println("DFPlayer OK.");
    myDFPlayer.setTimeOut(500);
    myDFPlayer.volume(volumeLevel);
    myDFPlayer.EQ(DFPLAYER_EQ_POP);
  }

  // --- HITUNG JUMLAH FILE DARI SD CARD ---
  // Cek dulu apakah DFPlayer berhasil connect sebelumnya
  if (myDFPlayer.available()) { 
      int counts = myDFPlayer.readFileCounts();
      Serial.print("Jumlah File SD Card: "); Serial.println(counts);
      if (counts > 0) totalLagu = counts;
      else totalLagu = 20;
  } else {
      totalLagu = 20; // Default jika error
  }

  // 4. KIRIM NOTIFIKASI BOOTING
  String ipAdd = WiFi.localIP().toString();
  String pesanBoot = "✅ *SYSTEM ONLINE*\n";
  pesanBoot += "📶 Sinyal: " + String(WiFi.RSSI()) + " dBm\n";
  pesanBoot += "📂 Total Lagu: " + String(totalLagu) + "\n"; 
  pesanBoot += "🌍 IP: " + ipAdd + "\n";
  pesanBoot += "🔗 Dashboard: http://" + ipAdd; 

  if(WiFi.status() == WL_CONNECTED) {
    bot.sendMessage(CHAT_ID, pesanBoot, "Markdown");
  }

  // 5. ROUTING WEB
  server.on("/", handleRoot);
  server.on("/on", handleOn);
  server.on("/off", handleOff);
  server.on("/setvolume", handleSetVolume);
  server.on("/setsound", handleSetSound);
  server.on("/add", handleAdd);
  server.on("/del", handleDel);

  server.begin();
}

// ================= LOOP =================
void loop() {
  server.handleClient();
  
  int curH = getCurHour();
  int curM = getCurMin();
  
  if(curH != -1) {
    for(int i=0; i<5; i++) {
      if(daftarJadwal[i].aktif) {
        if(curH == daftarJadwal[i].onH && curM == daftarJadwal[i].onM && !statusAlat) {
          nyalakanAlat("AUTO JADWAL-" + String(i+1));
        }
        else if(curH == daftarJadwal[i].offH && curM == daftarJadwal[i].offM && statusAlat) {
          matikanAlat("AUTO JADWAL-" + String(i+1));
        }
      }
    }
  }

  // --- LOGIKA SELF-DIAGNOSIS ---
  static unsigned long lastCheck = 0;
  if (millis() - lastCheck > 60000) { 
    lastCheck = millis();
    
    // HANYA CEK JIKA ADA DATA MASUK
    if (myDFPlayer.available()) {
       int type = myDFPlayer.readType();
       // Jika error card removed
       if (type == DFPlayerCardRemoved) { 
          laporError("WARNING", "SD Card Dicabut!");
       }
    }

    // 2. CEK SINYAL (Hanya jika konek)
    if (WiFi.status() == WL_CONNECTED) {
      long rssi = WiFi.RSSI();

      Serial.print("Cek Sinyal Rutin: "); 
      Serial.print(rssi);
      Serial.println(" dBm");

      if (rssi < -90) {
         laporError("WARNING", "Low WiFi Signal: " + String(rssi) + "dBm");
      }
    }

    // 3. CEK WAKTU
    struct tm t;
    if (!getLocalTime(&t)) {
       laporError("ERROR", "NTP Time Sync Failed");
    } else {
       if (t.tm_year + 1900 < 2024) { 
          laporError("ERROR", "System Time Invalid (Year " + String(t.tm_year + 1900) + ")");
       }
    }
  }

  delay(200); 
}