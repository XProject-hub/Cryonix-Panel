# Cryonix Panel

<p align="center">
  <strong>IPTV Management Panel</strong><br>
  Professional streaming management for IPTV providers
</p>

---

## 🎬 What is Cryonix Panel?

Cryonix Panel is the **IPTV management software** that you install on your server. It provides:
- Live TV channel management
- VOD (Movies) management  
- TV Series with seasons/episodes
- User (line) management
- EPG integration
- Multi-server load balancing
- Xtream API compatibility

**Note:** You need a license from [Cryonix Cloud](https://cloud.cryonix.io) to use this panel.

## 🚀 Installation

### One-Line Install

```bash
curl -fsSL https://install.cryonix.io/panel | sudo bash -s -- --license=YOUR_LICENSE_KEY
```

### Manual Install

```bash
git clone https://github.com/XProject-Hub/cryonix-panel.git
cd cryonix-panel
sudo bash install/install.sh --license=YOUR_LICENSE_KEY
```

## 🔑 Default Login

| Field | Value |
|-------|-------|
| URL | http://your-server/admin |
| Username | `admin` |
| Password | `Cryonix` |

## 📋 Requirements

- Ubuntu 20.04/22.04/24.04
- 2+ CPU cores, 4GB+ RAM
- PHP 8.1 or 8.2
- MariaDB/MySQL
- Nginx
- FFmpeg

## 📁 Structure

```
cryonix-panel/
├── api/              # Xtream API endpoints
├── config/           # Configuration
├── core/             # Core classes
├── controllers/      # HTTP Controllers
├── database/         # Schema & seeders
├── install/          # Installer
├── public/           # Web root
├── views/            # Admin panel UI
└── README.md
```

## 🔌 Streaming URLs

| Type | URL Format |
|------|------------|
| Live | `/live/{user}/{pass}/{stream_id}.ts` |
| Movie | `/movie/{user}/{pass}/{movie_id}.mp4` |
| Series | `/series/{user}/{pass}/{episode_id}.mp4` |
| M3U | `/get.php?username={user}&password={pass}` |
| EPG | `/xmltv.php?username={user}&password={pass}` |

## 📺 Xtream API

Compatible with standard Xtream Codes API:
- `GET /player_api.php?username=X&password=X`
- `GET /player_api.php?username=X&password=X&action=get_live_categories`
- `GET /player_api.php?username=X&password=X&action=get_live_streams`
- `GET /player_api.php?username=X&password=X&action=get_vod_categories`
- `GET /player_api.php?username=X&password=X&action=get_vod_streams`
- `GET /player_api.php?username=X&password=X&action=get_series`

---

**Copyright 2026 XProject-Hub**

