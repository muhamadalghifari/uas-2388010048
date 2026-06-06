# 🚀 UAS Administrasi Server — Cloud Computing II

**Nama:** Muhamad Devani Alghifari  
**NIM:** 2388010048  
**Mata Kuliah:** Administrasi Server (Cloud Computing II)  
**Dosen:** Mohamad Firdaus, M.Kom.  

---

## 🌐 Live Demo

| App | URL |
|-----|-----|
| 🖼️ Static Web (Portfolio) | http://52.76.224.122 |
| 🎬 Dynamic Web (CineList) | http://52.76.224.122:8080 |

---

## 📋 Deskripsi Project

Project UAS ini mendeploy **2 aplikasi web** menggunakan Docker Compose di AWS EC2, dengan CI/CD Pipeline otomatis menggunakan GitHub Actions.

### Aplikasi yang Di-deploy:
1. **Static Web** — Portfolio pribadi berbasis HTML/CSS/JS dengan desain modern
2. **Dynamic Web (CineList)** — Aplikasi Movie Watchlist berbasis PHP + MariaDB dengan fitur login, register, dan CRUD film

---

## 🏗️ Arsitektur Sistem

```
Developer (Local)
     │
     │ git push
     ▼
GitHub Repository
     │
     │ Trigger GitHub Actions
     ▼
┌─────────────────────────────────┐
│         GitHub Actions          │
│                                 │
│  ┌──────────────────────────┐  │
│  │  Static Web Pipeline     │  │
│  │  - Build Docker Image    │  │
│  │  - Push → Docker Hub     │  │
│  │  - Deploy → EC2          │  │
│  └──────────────────────────┘  │
│                                 │
│  ┌──────────────────────────┐  │
│  │  Dynamic App Pipeline    │  │
│  │  - Build Docker Image    │  │
│  │  - Push → Docker Hub     │  │
│  │  - Deploy → EC2          │  │
│  └──────────────────────────┘  │
└─────────────────────────────────┘
     │
     │ Pull & Deploy
     ▼
┌─────────────────────────────────┐
│         AWS EC2 (UAS-NIM)       │
│         52.76.224.122           │
│                                 │
│  ┌──────────┐  ┌─────────────┐ │
│  │static-web│  │ dynamic-app │ │
│  │ :80      │  │ :8080       │ │
│  │ nginx    │  │ php:apache  │ │
│  └──────────┘  └──────┬──────┘ │
│                        │        │
│                ┌───────▼──────┐ │
│                │  MariaDB DB  │ │
│                │  cinelist    │ │
│                └──────────────┘ │
│                                 │
│  Network: appnet (bridge)       │
└─────────────────────────────────┘
```

---

## 🐳 Docker Compose

```yaml
services:
  static-web:
    image: muhamadalghifari/static-web:latest
    container_name: static-web
    ports:
      - "80:80"
    networks:
      - appnet

  dynamic-app:
    image: muhamadalghifari/dynamic-app:latest
    container_name: dynamic-app
    environment:
      DB_HOST: db
      DB_USER: root
      DB_PASS: secret
      DB_NAME: cinelist
    ports:
      - "8080:80"
    depends_on:
      - db
    networks:
      - appnet

  db:
    image: mariadb:10.11
    container_name: db
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: cinelist
    volumes:
      - ./dynamic-app/database.sql:/docker-entrypoint-initdb.d/database.sql
      - dbdata:/var/lib/mysql
    networks:
      - appnet

networks:
  appnet:
    driver: bridge

volumes:
  dbdata:
```

---

## ⚙️ CI/CD Pipeline (GitHub Actions)

Pipeline menggunakan **Paths Filter** sehingga setiap aplikasi memiliki pipeline yang **terisolasi dan efisien**:

- Jika hanya `static-web/` yang berubah → hanya **Static Web Pipeline** yang berjalan
- Jika hanya `dynamic-app/` yang berubah → hanya **Dynamic App Pipeline** yang berjalan
- Kedua pipeline berjalan **paralel** jika keduanya berubah

### Alur Pipeline:
```
git push → GitHub Actions Trigger
         ├── Static Web Pipeline
         │     ├── Build Docker Image
         │     ├── Push ke Docker Hub
         │     └── SSH Deploy ke EC2
         └── Dynamic App Pipeline
               ├── Build Docker Image
               ├── Push ke Docker Hub
               └── SSH Deploy ke EC2
```

### GitHub Secrets yang Digunakan:
| Secret | Keterangan |
|--------|------------|
| `DOCKER_USERNAME` | Username Docker Hub |
| `DOCKER_TOKEN` | Access Token Docker Hub |
| `EC2_HOST` | IP Publik AWS EC2 |
| `EC2_KEY` | Private Key SSH (.pem) |

---

## 🗄️ Database Auto-Seeding

Database MariaDB ter-seed otomatis saat container pertama kali dijalankan melalui:
```
/docker-entrypoint-initdb.d/database.sql
```

Tabel yang dibuat:
- `users` — untuk autentikasi login
- `movies` — untuk data watchlist film

---

## 🚀 Zero-Touch Deployment

Demonstrasi **Zero-Touch Deployment**:

1. Edit kode di local (VSCode)
2. `git add . && git commit -m "update" && git push`
3. GitHub Actions otomatis trigger
4. Image di-build dan di-push ke Docker Hub
5. EC2 otomatis pull image terbaru dan restart container
6. Perubahan langsung terlihat di browser **tanpa perlu SSH ke server**

---

## 📦 Docker Hub Images

| Image | Link |
|-------|------|
| Static Web | https://hub.docker.com/r/muhamadalghifari/static-web |
| Dynamic App | https://hub.docker.com/r/muhamadalghifari/dynamic-app |

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Cloud Provider | AWS EC2 (ap-southeast-1) |
| OS | Ubuntu 22.04 LTS |
| Containerization | Docker + Docker Compose |
| CI/CD | GitHub Actions |
| Container Registry | Docker Hub |
| Static Web | HTML, CSS, JavaScript, Nginx |
| Dynamic Web | PHP 8.2, Apache |
| Database | MariaDB 10.11 |
| IP Management | AWS Elastic IP |

---

## 📁 Struktur Project

```
uas-2388010048/
├── .github/
│   └── workflows/
│       └── deploy.yml
├── static-web/
│   ├── Dockerfile
│   └── index.html
├── dynamic-app/
│   ├── Dockerfile
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── add.php
│   ├── edit.php
│   ├── delete.php
│   ├── logout.php
│   ├── auth.php
│   ├── config.php
│   └── database.sql
└── docker-compose.yml
```

---

## 👤 Akun Demo CineList

| Username | Password |
|----------|----------|
| admin | admin123 |
