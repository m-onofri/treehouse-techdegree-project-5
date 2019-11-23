# treehouse-techdegree-project-5

The goal of this project is to create a blog using the Slim 3 framework, SQLite database and Twig as the template engine.

# How to install 

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

In your browser, go to http://localhost:4000/

 ## Main features

* The main page lists blog entry titles with a title and date/time created. 
* Each blog entry title links to a detail page that displays the blog entry title, date, body, and comments.
* Each blog entry has a comment form that allows anonymous users to post comments.
* It's possible to add a new post or update an existing one.

## Additional features

* Add ability to categorize with tags: a blog entry can exist with no tags, or have multiple tags.
* Add a tags page that allows to edit and delete tags, and lists all the posts with a specific tag.
* Add the ability to delete a blog entry.
* Route blog entries to search engine friendly post slugs (instead of ID).
* Display alert messages when specific form fields are empty.
* Add pagination.

## Code organization


## Notes


## Cross-browser consistency

The project was checked on MacOS in Chrome, Firefox, Opera and Safari, and on these browsers it works properly.

