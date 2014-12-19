# Revenant Blue

##What is Revenant Blue?

Revenant Blue is a web framework and content management system written in PHP that was built specifically for PHP developers. Revenant Blue was created to be flexible and extensible so developers can decide how much or how little of the CMS they want to use when creating their web applications. Web applications of any size can be readily built with Revenant Blue with only limited programming knowledge required. Revenant Blue keeps as many options open for PHP developers as possible instead of forcing them into a particular coding paradigm.

##MVC structure inside and out

Great care has been taken to ensure a clean separation of concerns between Models, Views and Controllers throughout the source code. Revenant Blue does not implement a heavily abstracted form of MVC like some of the popular MVC frameworks. It instead opts to keep the framework part as light as possible so PHP developers that know the language can develop their applications without having to learn a whole new syntax. All HTML, javascript and CSS are located in the view directory, all business logic and request/response handling are located in the controller directory and all database queries are located in the model directory. The intuitive structure allows for quick learning and production while limiting time wasted hunting down classes and files scattered throughout the application structure.

##Scalable data storage and caching

Revenant Blue uses MySQL with the PDO abstraction layer as its main database. PostgreSQL support, while not in the current version, will be included with future releases. Unlike some content management systems Revenant Blue comes packaged with closure tables for hierarchical structure of comments, categories, forums and other. Redis is used for data caching, key/value storage for real-time applications and optionally as a session handler. Using Redis as a session handler allows for easy session sharing and integration with Node.js applications and easier scalability.

##Open Beta

Revenant Blue is currently in an early open beta state but development will move quickly. For a more comprehensive understanding of Revenant Blue please refer to the guides at http://revenantblue.com/guide

##Installation

Please refer to the installation instructions here: https://revenantblue.com/guide/quick-installation

## Demo

To see a live demo of Revenant Blue you can visit the demo site:

http://rb-demo.com
http://rb-demo.com/rbadmin

username: admin
password: Demo123

The demo resets every 20 minutes.
