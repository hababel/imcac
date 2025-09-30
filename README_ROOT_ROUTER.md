# IMCAC Root Router
## Cómo correr
1) Copia `.env.example` a `.env` y configura DB.
2) **Apache**: usa este directorio como DocumentRoot y habilita `.htaccess` (AllowOverride All).
3) **PHP embebido**: `php -S localhost:8080 index.php`
4) **Nginx** (ejemplo):
server {
  listen 80; server_name imcac.local; root /var/www/imcac-root;
  index index.php;
  location ~* ^/(core|app|storage)/ { deny all; }
  location / { try_files $uri /index.php?$query_string; }
  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php-fpm.sock;
  }
}
