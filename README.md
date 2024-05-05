# ABR & SD Collective Web Project

This project is built using Drupal 10+, a powerful open-source content management system. It allows for the creation and management of various types of content such as blogs, news, and user content.

## Structure

A typical Drupal 10 project consists of several directories:

- `core`: This directory contains the main Drupal installation.

- `modules`: This directory contains all the modules used in the project. Modules extend the functionality of Drupal.

- `profiles`: This directory contains installation profiles, which define what modules and themes to enable, as well as default configuration values.

- `sites`: This directory contains the settings and files for your site. It usually includes a `default` subdirectory, containing `settings.php` (the main settings file) and `files` (for uploaded files).

- `themes`: This directory contains all the themes used in the project. Themes control the appearance of the site.

- `vendor`: This directory contains all the third-party libraries that the project depends on.

## Installation

To install a Drupal 10 project, you typically need to clone the project repository, navigate into the project directory, and install the dependencies. This can be done using Composer, a dependency management tool for PHP.

```bash
# Clone the repository
git clone https://github.com/username/project.git

# Navigate into the directory
cd project

# Install dependencies
composer install

## Installation

# Clone the repository
git clone https://github.com/Mark-M3511/abrsd.git

# Navigate into the directory
cd project

# Install dependencies
npm install

drush serve
```

### About the ABRSD Custom Theme

The `abrsd` custom theme is a Drupal theme based on the Bootstrap Barrio base theme. It leverages the power of Bootstrap to provide a flexible, responsive, and user-friendly design for the project.

The theme includes custom templates, styles, and scripts to provide a unique look and feel for the project. It follows Drupal's best practices for theming and adheres to the standards set by the Bootstrap framework.

The theme's structure typically includes the following:

- `abrsd.info.yml`: This is the main theme configuration file. It defines the theme name, description, type, package, version, core compatibility, libraries, regions, and other settings.

- `abrsd.libraries.yml`: This file is used to define all the CSS and JS assets that your theme uses.

- `abrsd.theme`: This file is where you can set up theme setting form, preprocess functions, and theme hook implementations.

- `css` directory: This directory contains all the CSS files that are used in your theme.

- `js` directory: This directory contains all the JavaScript files that are used in your theme.

- `templates` directory: This directory contains all the Twig template files that Drupal uses to render HTML.

The `abrsd` theme is designed to be easily customizable and extendable, allowing for future enhancements and modifications as the project evolves.

### ABRSD Custom Theme Dependencies

The `abrsd` custom theme has two main contrib dependencies: `bootstrap_barrio` and `bootstrap_sass`.

#### Bootstrap Barrio

`bootstrap_barrio` is a flexible Drupal theme that integrates with the Bootstrap framework. It provides a solid foundation for creating responsive, mobile-first Drupal themes. The `abrsd` theme uses `bootstrap_barrio` as a base theme, extending and customizing its styles and components to suit the project's needs.

To install `bootstrap_barrio`, you can use Composer:

```bash
composer require 'drupal/bootstrap_barrio:^5.1'
```
### Requirements

#### Node.js

Node.js is a JavaScript runtime built on Chrome's V8 JavaScript engine. It's used in this project for managing JavaScript dependencies and running tasks.

To check if you have Node.js installed, run this command in your terminal:

```bash
node -v
```
If Node.js is not installed, you can download it from the official Node.js website.

Gulp SASS
Gulp is a toolkit for automating painful or time-consuming tasks in your development workflow. In this project, we use Gulp with the SASS plugin to compile our SASS files into CSS.

To check if you have Gulp installed, run this command in your terminal:

```bash
gulp -v
```
If Gulp is not installed, you can install it globally with this command:

```bash
npm install --global gulp-cli
```
To install the Gulp SASS plugin, navigate to your project directory and run:

```bash
npm install gulp-sass --save-dev
```
Remember to run npm install in your project directory to install all Node.js dependencies before starting development.



Custom Module

