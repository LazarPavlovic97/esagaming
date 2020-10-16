# ESAGAMING

## Installation instruction
* Add to  'C:\Windows\System32\drivers\etc\hosts' following:  
`127.0.0.1      esagaming.local`

* Add to server vhosts config file \(in my case C:\Apache24\conf\extra\httpd-vhosts.conf\) following:
```
<VirtualHost *:80>
    DocumentRoot "C:/Projects/esagaming"
    ServerName esagaming.local

    <Directory C:/Projects/esagaming>
        Options All
        AllowOverride All
        Require all granted
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php?_url=/$1 [QSA,L]
    </Directory>
ErrorLog C:/Projects/esagaming/log/error.log
</VirtualHost>
```
Change path for *DocumentRoot*, *Directory* and *ErrorLog*, regarding of the path where you put the project

* Open **Configuration.php** file of the project and add password for your local database under *DATABASE_NAME* constant \(instead of existing one\)

## Running the application

### Main page \(esagaming.local\)
* List of all games
* Creating new game, only number of starting points for game is required; game is created on *Create game* button

### Game page \(by clicking on game link on main page\)
* List of all armies for that game \(with current number of units and strategy\)
* Creating new army, only name is required; number of starting units will be added depending on game default
* *Run attack* button, next_attack table shows who is attacking next \(written above Run attack button\)
* *Autorun* button, on click starts auto running attacks \(refresh of the page in current number of units multiple 0.01 seconds\); when game is finished, buttons disappear and note is shown that game is finished

## Navigating the code
Three main folders:
* **Models**
* **Controllers**
* **Views**

## Additional
Database added to project root \(esagaming.sql\)