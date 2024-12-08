User Administration
===================

User Administration Application is based on Nette. It is basic app for managing registred users.

Installation
------------
composer create-project jackie/user_admin

After Installation
------------------
- Change database user and password in config\common.neon if neccessary.

- Open schema sql in root folder and run the commands.

- Find the file \vendor\ublaboo\datagrid\src\Components\DataGridPaginator\templates\data_grid_paginator.latte
change here the code for that is in data_grid_paginator.latte in root folder

- Setup server with command
php -S localhost:8000 -t www
Open page http://localhost:8000
