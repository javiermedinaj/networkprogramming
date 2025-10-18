# Guía Completa: Deploy de Aplicación PHP+MySQL en AWS EC2

## 📋 Tabla de Contenidos
1. [Configuración Inicial de EC2](#1-configuración-inicial-de-ec2)
2. [Conexión SSH](#2-conexión-ssh)
3. [Instalación del Stack LAMP](#3-instalación-del-stack-lamp)
4. [Configuración de Elastic IP](#4-configuración-de-elastic-ip)
5. [Configuración de Dominio](#5-configuración-de-dominio)
6. [Setup de GitHub Actions (CI/CD)](#6-setup-de-github-actions-cicd)
7. [Problemas Encontrados y Soluciones](#7-problemas-encontrados-y-soluciones)
8. [Seguridad y Mejores Prácticas](#8-seguridad-y-mejores-prácticas)
9. [Comandos de Referencia Rápida](#9-comandos-de-referencia-rápida)

---

## 1. Configuración Inicial de EC2

### 1.1 Crear Instancia EC2

**Configuración seleccionada:**
- **AMI:** Ubuntu Server 24.04 LTS (ami-0cfde0ea8edd312d4)
- **Tipo de instancia:** t3.micro (2 vCPU, 1GB RAM)
- **Almacenamiento:** 8 GB gp3 SSD
- **Free Tier:** ✅ Elegible (750 horas/mes gratis por 12 meses)

### 1.2 Configurar Security Group

**Puertos abiertos inicialmente:**

| Puerto | Protocolo | Servicio | Source | Observación |
|--------|-----------|----------|--------|-------------|
| 22 | TCP | SSH | 0.0.0.0/0 | ⚠️ CAMBIAR después |
| 80 | TCP | HTTP | 0.0.0.0/0 | ✅ OK |
| 443 | TCP | HTTPS | 0.0.0.0/0 | ✅ OK |

### 1.3 Key Pair (Par de Claves)

1. Crear nuevo Key Pair o seleccionar existente
2. **Formato:** .pem (para Mac/Linux) o .ppk (para PuTTY en Windows)
3. **Descargar y guardar:** `apacheserver.pem`
4. **⚠️ IMPORTANTE:** Este archivo NO se puede volver a descargar

**Almacenamiento seguro:**
```bash
# Mover a carpeta segura
mkdir -p ~/.ssh/aws-keys
mv ~/Downloads/apacheserver.pem ~/.ssh/aws-keys/
chmod 400 ~/.ssh/aws-keys/apacheserver.pem
```

---

## 2. Conexión SSH

### 2.1 Obtener IP Pública

1. AWS Console → EC2 → Instances
2. Seleccionar instancia "apacheserver"
3. Copiar **Public IPv4 address**

### 2.2 Conectarse

**Mac/Linux:**
```bash
# Dar permisos al archivo .pem
chmod 400 ~/.ssh/aws-keys/apacheserver.pem

# Conectar
ssh -i ~/.ssh/aws-keys/apacheserver.pem ubuntu@TU_IP_PUBLICA
```

**Windows (PowerShell):**
```powershell
ssh -i C:\ruta\a\apacheserver.pem ubuntu@TU_IP_PUBLICA
```

**Windows (PuTTY):**
- Convertir .pem a .ppk con PuTTYgen
- Configurar en Connection → SSH → Auth

### 2.3 Verificación de Conexión

✅ **Conexión exitosa sin password** - Usa criptografía de clave pública/privada (más seguro que passwords tradicionales)

---

## 3. Instalación del Stack LAMP

### 3.1 Actualizar Sistema

```bash
sudo apt update && sudo apt upgrade -y
```

### 3.2 Instalar Apache

```bash
sudo apt install apache2 -y
```

**Verificar instalación:**
```bash
apache2 -v
# Server version: Apache/2.4.58 (Ubuntu)

sudo systemctl status apache2
```

**⚠️ Nota importante:** En Ubuntu el servicio se llama `apache2`, no `apache`

### 3.3 Instalar MySQL

```bash
sudo apt install mysql-server -y
```

**Verificar instalación:**
```bash
mysql --version
# mysql  Ver 8.0.43-0ubuntu0.24.04.2
```

### 3.4 Instalar PHP y Módulos

```bash
sudo apt install php libapache2-mod-php php-mysql php-cli php-curl php-gd php-mbstring php-xml php-zip -y
```

**Verificar instalación:**
```bash
php -v
```

### 3.5 Reiniciar Apache

```bash
sudo systemctl restart apache2
```

### 3.6 Probar Instalación

**Crear archivo de prueba PHP:**
```bash
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php
```

**Acceder desde navegador:**
```
http://TU_IP_PUBLICA/info.php
```

✅ Deberías ver la página de información de PHP

**⚠️ Eliminar después de verificar:**
```bash
sudo rm /var/www/html/info.php
```

---

## 4. Configuración de Elastic IP

### 4.1 ¿Por qué Elastic IP?

**Problema sin Elastic IP:**
- Cada vez que parás/prendés la instancia, la IP cambia
- Si tenés un dominio, tendrías que actualizar DNS cada vez

**Solución con Elastic IP:**
- IP estática que nunca cambia
- Necesaria para configurar dominio

### 4.2 Crear y Asociar Elastic IP

**Pasos:**
1. EC2 → Network & Security → Elastic IPs
2. Click en **"Allocate Elastic IP address"**
3. Click en **"Allocate"**
4. Seleccionar la IP creada (ej: 3.149.97.214)
5. Actions → **"Associate Elastic IP address"**
6. **Resource type:** Instance
7. **Instance:** Seleccionar tu instancia "apacheserver"
8. Click en **"Associate"**

### 4.3 Costos

| Estado | Costo |
|--------|-------|
| Asociada a instancia corriendo | $0/mes ✅ |
| Asociada a instancia parada | ~$3.60/mes ⚠️ |
| Sin asociar | ~$3.60/mes ⚠️ |

**💡 Recomendación:** Si vas a parar la instancia frecuentemente, desasocia la Elastic IP primero para no pagar.

---

## 5. Configuración de Dominio

### 5.1 Dominio Obtenido

- **Dominio:** javiermedina.tech
- **Proveedor:** Tech Domains
- **Costo:** Gratis por 1 año

### 5.2 Configurar DNS Records

**En Tech Domains → Manage DNS:**

| Type | Host | Value | TTL |
|------|------|-------|-----|
| A | @ | 3.149.97.214 | 3600 |
| A | www | 3.149.97.214 | 3600 |

**Propagación:** 5-30 minutos

### 5.3 Verificar DNS

```bash
# Desde cualquier terminal
ping javiermedina.tech
nslookup javiermedina.tech

# Debe resolver a: 3.149.97.214
```

### 5.4 Configurar Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/javiermedina.conf
```

**Contenido:**
```apache
<VirtualHost *:80>
    ServerName javiermedina.tech
    ServerAlias www.javiermedina.tech
    
    DocumentRoot /var/www/html/networkprogramming
    
    <Directory /var/www/html/networkprogramming>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/javiermedina_error.log
    CustomLog ${APACHE_LOG_DIR}/javiermedina_access.log combined
</VirtualHost>
```

**Activar sitio:**
```bash
sudo a2ensite javiermedina.conf
sudo a2dissite 000-default.conf  # Opcional
sudo apache2ctl configtest
sudo systemctl restart apache2
```

---

## 6. Setup de GitHub Actions (CI/CD)

### 6.1 Crear Usuario para Deploy

```bash
# Crear usuario
sudo adduser github-deploy
# Presionar Enter para todos los prompts (dejar en blanco)

# Agregar a grupos necesarios
sudo usermod -aG www-data github-deploy
```

### 6.2 Generar Claves SSH

```bash
# Generar par de claves
sudo -u github-deploy ssh-keygen -t ed25519 -C "github-deploy" -f /home/github-deploy/.ssh/id_ed25519 -N ""

# Ver clave pública
sudo cat /home/github-deploy/.ssh/id_ed25519.pub

# Ver clave privada (para GitHub Secret)
sudo cat /home/github-deploy/.ssh/id_ed25519
```

### 6.3 Configurar Autenticación SSH

```bash
# Agregar clave pública a authorized_keys
sudo mkdir -p /home/github-deploy/.ssh
sudo cat /home/github-deploy/.ssh/id_ed25519.pub | sudo tee /home/github-deploy/.ssh/authorized_keys

# Permisos correctos (MUY IMPORTANTE)
sudo chmod 700 /home/github-deploy/.ssh
sudo chmod 600 /home/github-deploy/.ssh/authorized_keys
sudo chmod 600 /home/github-deploy/.ssh/id_ed25519
sudo chown -R github-deploy:github-deploy /home/github-deploy/.ssh
```

**Verificar permisos:**
```bash
ls -la /home/github-deploy/.ssh/
# Debe mostrar:
# drwx------ github-deploy github-deploy .ssh/
# -rw------- github-deploy github-deploy authorized_keys
# -rw------- github-deploy github-deploy id_ed25519
# -rw-r--r-- github-deploy github-deploy id_ed25519.pub
```

### 6.4 Probar Conexión SSH Local

```bash
sudo -u github-deploy ssh github-deploy@localhost
# Debe conectar SIN pedir password
```

### 6.5 Configurar GitHub Secrets

**Ir a tu repositorio → Settings → Secrets and variables → Actions**

Crear 3 secrets:

| Name | Value |
|------|-------|
| SSH_HOST | 3.149.97.214 |
| SSH_USER | github-deploy |
| SSH_KEY | (contenido completo de id_ed25519 - la clave PRIVADA) |

**⚠️ SSH_KEY debe incluir:**
```
-----BEGIN OPENSSH PRIVATE KEY-----
...todo el contenido...
-----END OPENSSH PRIVATE KEY-----
```

### 6.6 Clonar Repositorio en el Servidor

```bash
cd /var/www/html

# Clonar repo
sudo git clone https://github.com/javiermedinaj/networkprogramming.git

# Permisos correctos
sudo chown -R github-deploy:www-data networkprogramming
sudo chmod -R 755 networkprogramming

# Configurar git para github-deploy
sudo -u github-deploy git config --global user.name "GitHub Deploy"
sudo -u github-deploy git config --global user.email "deploy@javiermedina.tech"
sudo -u github-deploy git config --global pull.rebase false
```

### 6.7 Crear GitHub Actions Workflow

**En tu repositorio local, crear:** `.github/workflows/deploy.yml`

```yaml
name: Deploy to AWS EC2

on:
  push:
    branches: [ main ]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout code
      uses: actions/checkout@v4
      
    - name: Deploy to EC2
      uses: appleboy/ssh-action@v1.0.3
      with:
        host: ${{ secrets.SSH_HOST }}
        username: ${{ secrets.SSH_USER }}
        key: ${{ secrets.SSH_KEY }}
        port: 22
        script: |
          cd /var/www/html/networkprogramming
          git config --global --add safe.directory /var/www/html/networkprogramming
          git fetch origin
          git reset --hard origin/main
          git clean -fd
          sudo chown -R github-deploy:www-data .
          sudo chmod -R 755 .
          echo "✅ Deploy exitoso: $(date)"
          git log -1 --oneline
```

### 6.8 Configurar Permisos Sudo

```bash
sudo visudo
```

**Agregar al final:**
```
github-deploy ALL=(ALL) NOPASSWD: /usr/bin/chown, /usr/bin/chmod
```

### 6.9 Commit y Push

```bash
git add .github/workflows/deploy.yml
git commit -m "Add GitHub Actions CI/CD"
git push origin main
```

**Verificar:** GitHub → Actions (deberías ver el workflow corriendo)

---

## 7. Problemas Encontrados y Soluciones

### 7.1 Error: "apache.service not found"

**Problema:**
```bash
sudo systemctl restart apache
# Failed to restart apache.service: Unit apache.service not found.
```

**Causa:** En Ubuntu el servicio se llama `apache2`, no `apache`

**Solución:**
```bash
sudo systemctl restart apache2
sudo systemctl status apache2
```

---

### 7.2 Error: "Connection timed out" al acceder desde navegador

**Problema:** No se puede acceder a `http://IP_PUBLICA`

**Causa:** Usaste la IP privada (172.31.x.x) en lugar de la pública

**Solución:**
```bash
# Obtener IP pública desde el servidor
curl ifconfig.me

# Usar esa IP en el navegador
http://3.149.97.214
```

---

### 7.3 Error: "ssh: unable to authenticate" en GitHub Actions

**Problema:**
```
ssh: handshake failed: ssh: unable to authenticate
```

**Causa:** La clave pública no está en authorized_keys o permisos incorrectos

**Solución:**
```bash
# Agregar clave a authorized_keys
sudo cat /home/github-deploy/.ssh/id_ed25519.pub | sudo tee /home/github-deploy/.ssh/authorized_keys

# Permisos correctos
sudo chmod 700 /home/github-deploy/.ssh
sudo chmod 600 /home/github-deploy/.ssh/authorized_keys
sudo chown -R github-deploy:github-deploy /home/github-deploy/.ssh

# Probar conexión
sudo -u github-deploy ssh github-deploy@localhost
```

---

### 7.4 Error: "detected dubious ownership in repository"

**Problema:**
```bash
git branch
# fatal: detected dubious ownership in repository
```

**Causa:** El repositorio pertenece a un usuario diferente (root) en lugar de github-deploy

**Solución:**
```bash
# Cambiar ownership
sudo chown -R github-deploy:www-data /var/www/html/networkprogramming

# Agregar a safe directories
sudo -u github-deploy git config --global --add safe.directory /var/www/html/networkprogramming
```

---

### 7.5 Git pull no actualiza el código (archivos modificados)

**Problema:**
```bash
sudo -u github-deploy git status
# Changes not staged for commit:
#   modified:   (70+ archivos)
```

**Causa:** Cambios en permisos o line endings bloquean el pull

**Solución:**
```bash
cd /var/www/html/networkprogramming

# Descartar TODOS los cambios locales
sudo -u github-deploy git reset --hard origin/main

# Traer actualización
sudo -u github-deploy git pull origin main
```

**Explicación de comandos:**
- `git pull`: Intenta fusionar (falla si hay conflictos)
- `git reset --hard`: Descarta TODO lo local y fuerza sincronización

---

### 7.6 Cambios no se reflejan en el navegador

**Problema:** Hiciste push pero el sitio no se actualiza

**Causas posibles:**

1. **Estás viendo otra carpeta:**
   ```bash
   # Verificar qué carpeta estás accediendo
   # Si accedes a http://IP/ → /var/www/html/index.html
   # Si accedes a http://IP/networkprogramming/ → /var/www/html/networkprogramming/
   ```

2. **Caché del navegador:**
   - Ctrl + Shift + R (forzar recarga)
   - Abrir en modo incógnito

3. **GitHub Actions falló:**
   - Verificar en GitHub → Actions
   - Ver logs de error

---

## 8. Seguridad y Mejores Prácticas

### 8.1 ⚠️ CRÍTICO: Restringir SSH a tu IP

**Problema actual:** SSH está abierto a todo el mundo (0.0.0.0/0)

**Solución:**
1. EC2 → Security Groups → Tu security group
2. Editar regla Inbound para SSH (puerto 22)
3. Cambiar Source de `0.0.0.0/0` a `MI_IP/32`

```bash
# Obtener tu IP actual
curl ifconfig.me

# Usar esa IP en el Security Group
```

**Configuración final recomendada:**

| Puerto | Source | Descripción |
|--------|--------|-------------|
| 22 | TU_IP/32 | SSH solo desde tu IP |
| 80 | 0.0.0.0/0 | HTTP público |
| 443 | 0.0.0.0/0 | HTTPS público |

### 8.2 Deshabilitar Root Login por SSH

```bash
sudo nano /etc/ssh/sshd_config
```

**Buscar y cambiar:**
```
PermitRootLogin no
PasswordAuthentication no
```

**Reiniciar SSH:**
```bash
sudo systemctl restart ssh
```

### 8.3 Configurar Firewall (UFW)

```bash
# Habilitar UFW
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Ver estado
sudo ufw status
```

### 8.4 Actualizaciones Automáticas de Seguridad

```bash
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

### 8.5 Backups

**Snapshots de EBS:**
1. EC2 → Elastic Block Store → Volumes
2. Seleccionar volumen de tu instancia
3. Actions → Create snapshot
4. Configurar snapshots automáticos con AWS Backup

---

## 9. Comandos de Referencia Rápida

### 9.1 Gestión de Instancia EC2

```bash
# Conectarse por SSH
ssh -i ~/.ssh/aws-keys/apacheserver.pem ubuntu@3.149.97.214

# Ver información de sistema
uname -a
df -h  # Espacio en disco
free -h  # Memoria RAM
htop  # Monitor de procesos
```

### 9.2 Apache

```bash
# Servicios
sudo systemctl start apache2
sudo systemctl stop apache2
sudo systemctl restart apache2
sudo systemctl status apache2

# Logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/access.log

# Verificar configuración
sudo apache2ctl configtest

# Sitios
sudo a2ensite nombre.conf  # Habilitar
sudo a2dissite nombre.conf  # Deshabilitar
```

### 9.3 MySQL

```bash
# Conectar
sudo mysql

# Crear base de datos
CREATE DATABASE mi_app;
CREATE USER 'usuario'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON mi_app.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;

# Ver bases de datos
SHOW DATABASES;
```

### 9.4 Git en el Servidor

```bash
cd /var/www/html/networkprogramming

# Ver estado
sudo -u github-deploy git status

# Actualizar manualmente
sudo -u github-deploy git fetch origin
sudo -u github-deploy git reset --hard origin/main
sudo -u github-deploy git pull origin main

# Ver logs
sudo -u github-deploy git log -5 --oneline

# Ver diferencias
sudo -u github-deploy git diff
```

### 9.5 Permisos

```bash
# Estructura típica
sudo chown -R github-deploy:www-data /var/www/html/networkprogramming
sudo chmod -R 755 /var/www/html/networkprogramming

# Archivos específicos
sudo chmod 644 archivo.txt  # Lectura/escritura owner, lectura otros
sudo chmod 755 script.sh    # Ejecutable
```

### 9.6 Mantenimiento

```bash
# Actualizar sistema
sudo apt update
sudo apt upgrade -y

# Limpiar paquetes no usados
sudo apt autoremove -y
sudo apt autoclean

# Ver espacio en disco
df -h
du -sh /var/www/html/*

# Reiniciar servidor
sudo reboot
```

---

## 📊 Resumen del Stack Completo

```
Internet
    ↓
Route53 (DNS) - javiermedina.tech → 3.149.97.214
    ↓
Elastic IP (3.149.97.214)
    ↓
Security Group (Firewall)
    ↓
EC2 Instance (Ubuntu 24.04, t3.micro)
    ├── Apache 2.4.58 (:80, :443)
    ├── PHP 8.x + módulos
    ├── MySQL 8.0.43
    └── /var/www/html/networkprogramming/
            ↑
        Git Pull
            ↑
    GitHub Actions (CI/CD)
            ↑
        Git Push
            ↑
    Desarrollo Local
```

---

## 💰 Costos Estimados

| Recurso | Costo Free Tier | Costo Post-Free |
|---------|-----------------|-----------------|
| EC2 t3.micro | $0 (750h/mes) | ~$7.50/mes |
| Elastic IP | $0 (si está corriendo) | $0 |
| EBS 8GB | $0 (30GB gratis) | $0.80/mes |
| Transferencia | $0 (100GB gratis) | Variable |
| Dominio .tech | $0 (primer año) | ~$10/año |
| **TOTAL** | **$0/mes** | **~$8-10/mes** |

---

## 🎯 Próximos Pasos

- [ ] Configurar SSL/HTTPS con Let's Encrypt
- [ ] Configurar subdominios (n8n.javiermedina.tech)
- [ ] Implementar backups automáticos
- [ ] Configurar monitoreo con CloudWatch
- [ ] Optimizar Apache para mejor rendimiento
- [ ] Implementar CDN con CloudFront

---

## 📚 Referencias

- [AWS EC2 Documentation](https://docs.aws.amazon.com/ec2/)
- [Ubuntu Server Guide](https://ubuntu.com/server/docs)
- [Apache Documentation](https://httpd.apache.org/docs/2.4/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Let's Encrypt](https://letsencrypt.org/)

---

**Documentación creada por:** Javier Medina  
**Fecha:** Octubre 2025  
**Proyecto:** Network Programming - Deploy AWS  
**Repositorio:** https://github.com/javiermedinaj/networkprogramming