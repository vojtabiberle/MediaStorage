# MediaStorage
Is generic library for working with every file or image in your PHP application. Right now is here onle Nette/Latte bridge,
but feel free to write another.

Every needed class implements generic interfaces and are documented.

## Principles (in points)
+ files are stored outside of html root directory
+ file information are stored in database
+ files are managed from one place (upload / delete)
+ every file can be attached to anything through namespace
+ namespace is something like TAGs
+ namespaces can be defined in code or based on other database objects (IDs)
+ here is view helpers (Currently Latte macros) witch consumes IFile object or namespace for generating URL
+ first time, when URL is requested is handled by PHP and PHP will check file (can check rights or password) and
  if it is needed, copy the file to public html root (directory based on requested URI)
+ next requests by webserver are made directly to file in public html root

### Images speciality
+ support for image filters: scaling, sharping, cropping
+ support for noimage

### Files speciality
+ support for automated generated icons for file thumbnails

## Programming principles
+ Bridges to another frameworks/libaries should be stupid
+ All work should be deletage to Manager (`IManager` implementation)
+ Manager works with filesystem (`IFilesystemStorage`) and database (`IDatabaseStorage`)
+ Only "smart" thing on integration is generating right URLs based on parameters
+ MediaGalery sould be only stupid grid view and some request handling - all requests should be delegated do Manager
  (examples in `MediaStorage\Bridges\Nette\Presenters\MediaPresenter`)

# TODO:
+ move all sources to `src` directory
+ improve documentation