## PHP (>=5.2) SDK for the Pop Up Archive API

This is a the first SDK for the Pop Up Archive API. In an effort to make it as compatible and usable as possible, we limited the functional aspects to those that are available in PHP 5.2. We are working on a more robust SDK that makes use of Composer and other >=5.3 features (e.g. proper classes)

## Usage

1. Get a Pop Up Archive application key 
2. Add the application key to the code
3. Perform OAuth2 dance
4. Request data from the available endpoints

### Authentication

Authentication has been implemented but not fully tested. I have it working in our Pop Up Archive WordPress plugin but full test is still needed.

### Making a request

```php
TODO: Update with the OAuth process
TODO: Update with basic workflow to retrieve metadata


require_once('path/to/src/Popuparchive/popuparchive.php');
client = new New Popuparchive_Services();

// simple call using known endpoint
print_r ( $client->get('https://www.popuparchive.org/api/ENDPOINT' );
```

### See more

*Coming Soon*
