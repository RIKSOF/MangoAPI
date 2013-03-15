INSTALLING on *nix
==================

1. Clone MangoAPI
$> git clone https://github.com/RIKSOF/MangoAPI.git mangoapi

2. Goto mangoapi root directory
$> cd mangoapi

3. Rewrite Rules (.htaccess files)
   Check that you have a .htaccess file in 

    i. ./ with the following content

    `<IfModule mod_rewrite.c>
        RewriteEngine on
        RewriteBase /mangoapi/
        RewriteRule    ^$ app/webroot/    [L]
        RewriteRule    (.*) app/webroot/$1 [L]
    </IfModule>`

    ii. ./app with the following content
    
    `<IfModule mod_rewrite.c>
        RewriteEngine on
        RewriteBase /mangoapi/app/
            RewriteRule api/(.*) webroot/api/index/$1 [L]
        RewriteRule ^$   webroot/   [L]
        RewriteRule (.*) webroot/$1 [L]
    </IfModule>`
    
    iii. ./app/webroot with the following content
    
    `<IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /mangoapi/app/webroot/
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !favicon.ico$
        RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
    </IfModule>`
    
4. Install mongodb driver for php
$> pecl install mongo
If the above command does not work you might have to install one or more of the following:
$> yum install gcc perl pear
AND then try `pecl install mongo` again.

TEST Installation
=================

Base url for your mangoapi e.g.,
mymangoapi.com OR if mywebapplication.com/mangoapi

1. <baseUrl>/api 
Expected Result
---------------
`[]`

2. <baseUrl>/viewer
Expected Result
---------------
You should see a UI that allows you to add, fetch and edit json documents. 

