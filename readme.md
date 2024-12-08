# User Administration


User Administration Application is based on Nette. It is basic app for managing registred users.

## Installation

```
composer create-project jackie/user_administration
```

## After Installation

- Change database user and password in _config\common.neon_ if neccessary.

- Open schema sql in root folder and run the commands.

- Find the file _vendor\ublaboo\datagrid\src\Components\DataGridPaginator\templates\data_grid_paginator.latte_ 
and copy over the content of the `data_grid_paginator.latte` file in the root folder

- Setup server
```
cd user-administration
php -S localhost:8000 -t www 
```

Open page http://localhost:8000
