# IsekaiDev - Packager
This is an backend system for a GitHub integration to automatically pack WoltLab Suite™ packages.

## How does it work ?
Through the webhook system this system gets notified every time a commit is pushed.  
Does the `head_commit` contains \[wsp\] or \[wsp=custom release message\] in it, this systems is going to clone the repository and automatically pack it into a valid WoltLab Suite™ package.
After this the system creates a release in the repository and uploads the package as a asset to it.

# Installation
Installation instructions coming soon...

# Used 3rd Party Libraries
[Smarty](http://www.smarty.net/)  
[Cz\Git](https://github.com/czproject/git-php)  
[JWToken](https://github.com/bllohar/php-jwt-class-with-RSA-support)

# Project inspiration
This project is inspired of the npm package [`wspackager`](https://github.com/padarom/wspackager).  

### Why not using the `wspackager` package?
As I don't own a server at the moment I'm limited to the webhoster where i am.  
Because the host doesn't support npm, i needed something similar to it, so i decided to write a similar project in PHP.