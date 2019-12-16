## Setup instructions

1. Install docker-compose https://docs.docker.com/compose/install/
2. Open console in project's root dir and run:

  > docker-compose up --build --d
  
3. Go to your browser and open http://test.io:8089 (phpMyAdmin)
4. Use `root` `root` as login and password in phpMyAdmin
5. Import `test` database from `test.sql`
6. Go to https://www.reddit.com/prefs/apps and create/open an app  
7. Save your reddit login, reddit password, app id and app secret to `settings.php`
8. Go to your browser and open http://test.io:8088/index.php
