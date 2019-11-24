# treehouse-techdegree-project-5

The goal of this project is to create a blog using the Slim 3 framework, SQLite database and Twig as the template engine.

## How to install 

Clone the git repository in the folder of your choice:
```
git clone https://github.com/m-onofri/treehouse-techdegree-project-5.git
```

Install the packages:
```
cd treehouse-techdegree-project-5
composer install
```

Run the server:
```
cd public
php -S localhost:4000
```

In your browser, go to http://localhost:4000/ and see the blog.

 ## Main features

* The main page lists blog entry titles with a title and date/time created. 
* Each blog entry title links to a detail page that displays the blog entry title, date, body, and comments.
* Each blog entry has a comment form that allows anonymous users to post comments.
* It's possible to add a new post or update an existing one.

## Additional features

* Add ability to categorize with tags: a blog entry can exist with no tags, or have multiple tags.
* Add a tags page that allows to edit and delete tags, and lists all the posts with a specific tag.
* Add the ability to delete a blog entry and the associated comments.
* Route blog entries to search engine friendly post slugs (instead of ID).
* Display alert messages when specific form fields are empty.
* Add pagination.

## Code organization

* In the **'public'** folder you can find:
    - the index.php file;
    - **'css'** folder with all the styles of the project.
* The **templates** folder contains all the twig files for the view.
* in the **'src'** folder you can find:
    - the subfolder models containing the **Post**, **Comment** and **Tag** classes, responsible for managing the data of the application;
    - the subfolder controllers containing the **PostController**, **CommentController** and **TagController** classes, responsible for controlling the flow of the app execution;
    - the routes.php file with all the routes of the project.

## Notes

* **Pagination**: for the pagination I adapted to my project the code from [**PHP Slim Pagination**](https://github.com/romanzipp/PHP-Slim-Pagination)
* **Slug**: to generate slugs I installed the package [**cocur/slugify**](https://github.com/cocur/slugify)

## Cross-browser consistency

The project was checked on MacOS in Chrome, Firefox, Opera and Safari, and on these browsers it works properly.

