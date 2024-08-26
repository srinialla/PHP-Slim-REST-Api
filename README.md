<div align="center">
	<h1> PHP Slim REST API </h1>
</div>

<div align="center">
	<a href="#changelog">
		<img src="https://img.shields.io/badge/stability-stable-green.svg" alt="Status">
	</a>
	<a href="#changelog">
		<img src="https://img.shields.io/badge/release-v1.0.0.8-blue.svg" alt="Version">
	</a>
	<a href="#changelog">
		<img src="https://img.shields.io/badge/update-october-yellowgreen.svg" alt="Update">
	</a>
	<a href="#license">
		<img src="https://img.shields.io/badge/license-MIT%20License-green.svg" alt="License">
	</a>
</div>

 
This is a simple REST Web Service which allow:
  
  * Post short text quote of no more than 500 characters
  * Bring a list with the latest published quotes
  * Search for quote by your text
  * Like the quote
  * Delete a specific quote by its id

<a name="started"></a>
## Getting Started
This page will help you get started with this API.

<a name="requirements"></a>
### Requirements

  * PHP
  * MySQL
  * Apache Server
  * Slim Framework v3

<a name="installation"></a>
### Installation

#### Copy this project

  1. Clone or Download this repository
  2. Unzip the archive if needed
  3. Copy the folder in the htdocs dir/ to your own location
  4. Open the project folder in any editor

#### Install the project

  1. Open terminal/Command promt inside project folder or use editor terminor


  2. Install with composer

```bash
$ composer install
```

  Or

```bash
$ composer install --ignore-platform-reqs
```

#### import Database 
	file  `.database.sql`.

#### Configure the project

 
  modify the file `.env`.
  Change the database configuration in the file.

### Running from the Command line
  Run command inside `projectFolder/public` directory
  Change PORT number as you like

```bash
$ php -S localhost:8085
```

### Routes

`RootPath` => `http://localhost/PHP-Slim-REST-Api/public/api/`
or
`RootPath` => `http://localhost:8085/api/`
# API Documentation

## Routes

### Status
- **GET** `/status`
  - Check server and database status.

### User Registration
- **POST** `/register`
  - Register a new user.

### User Login
- **POST** `/login`
  - User login.

### Token Verification
- **POST** `/verify`
  - Verify the token.

### Admin Posts
- **GET** `/admin/posts`
  - Retrieve all posts.

### Create Post
- **POST** `/admin/post`
  - Create a new post.

### Update Post
- **PUT** `/admin/post`
  - Update an existing post.

### Get Liked Users
- **GET** `/admin/likes/{Id}`
  - Get users who liked a specific quote.

### Like a Quote
- **POST** `/admin/like/{Id}`
  - Like a specific quote.

### Search Quotes
- **GET** `/admin/search/{quote}`
  - Search for quotes.


* More Details ([POSTMAN Collection](https://documenter.getpostman.com/view/37901323/2sAXjF9uxD))


<a name="built"></a>
## :wrench: Built With

  * XAMPP for Windows 5.6.32 ([XAMPP](https://www.apachefriends.org/download.html))
  * Visual Studio Code ([VSCode](https://code.visualstudio.com/))
  * COMPOSER ([COMPOSER](https://getcomposer.org/))
  * Advance REST Extension for Chrome ([AdvanceREST]([https://chrome.google.com/webstore/detail/resteasy/nojelkgnnpdmhpankkiikipkmhgafoch](https://chromewebstore.google.com/detail/advanced-rest-client/hgmloofddffdnphfgcellkdfbfbjeloo))


<a name="authors"></a>
## :eyeglasses: Authors

  * **Srinu Alla** - *Owner* - [SrinuAlla](https://github.com/srinialla) 

<h3 align="left">Connect with me:</h3>
<p align="left">
<a href="https://linkedin.com/in/srinualla" target="blank"><img align="center" src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/linked-in-alt.svg" alt="srinualla" height="30" width="40" /></a>
</p>

 
<a name="license"></a>
## :memo: License

This API is licensed under the MIT License - see the
 [MIT License](https://opensource.org/licenses/MIT) for details.
