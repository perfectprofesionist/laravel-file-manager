# Laravel File Manager

Laravel File Manager is a robust and user-friendly package designed to simplify file management within Laravel applications. It provides an intuitive interface and a comprehensive set of features to help you upload, organize, and control access to your files and folders seamlessly.

## Key Features

- **File Operations:** Effortlessly upload, rename, move, and delete files.
- **Folder Management:** Create, rename, and remove folders to keep your files organized.
- **Previews & Thumbnails:** Instantly preview images and generate thumbnails for quick identification.
- **Access Control:** Fine-grained permissions and integration with Laravel’s authentication system to restrict file access.
- **Customizable Storage:** Configure storage disks and paths to suit your project’s needs.
- **Responsive UI:** Clean and responsive interface for both desktop and mobile devices.

## Getting Started

Register the file manager routes in your `routes/web.php` file:

```php
FileManager::routes();
```

Once registered, you can access the file manager interface at `/file-manager` in your browser.

## Configuration

Customize the package by editing the `config/filemanager.php` file. Here you can set storage disks, define access permissions, and adjust other options to fit your workflow.

