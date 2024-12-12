# User Administration


User Administration Application is based on Nette. It is basic app for managing registred users.

## Installation

```
composer create-project jackie/user_administration
```

## After Installation

- Change database user and password in _config\common.neon_ if neccessary.

- Open schema sql in root folder and run the commands.

- Setup server
```
cd user-administration
php -S localhost:8000 -t www 
```

Open page http://localhost:8000
