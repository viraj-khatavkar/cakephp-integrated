# CakePHP Integrated
Better integration testing with [CakePHP](https://cakephp.org). An intuitive API for integration testing your CakePHP applications.

## Step 1: Installation
Install this package using Composer:

```bash
composer require viraj/cakephp-integrated
```

## Step 2: Write tests ;)
You need to extend the `CakeTestCase` class to be able to write tests using the API.
Here is an example test to help you understand how this works:

```php
class DemoTest extends CakeTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function unauthenticated_user_cannot_see_the_add_posts_page()
    {
        $this->openPage('/posts/add')
             ->canSeePageUrlContains('/users/login');
    }

    /** @test */
    public function authenticated_user_can_add_a_new_post()
    {
        $user = factory('Users')->create();

        $this->actingAs($user)
             ->openPage('/posts/add')
             ->fillInField('title', 'My first post')
             ->fillInField('author', 'Viraj Khatavkar')
             ->fillInField('body', 'My Post body')
             ->check('#published')
             ->press('Submit')
             ->canSeePageIs('/posts')
             ->seeText('My first post');
    }
}
``` 

## API
Here is the API of this package which can be used to write your tests:

### `$this->fillInField($element, $text)`

Fill the text in the input field identified with name or id of `$element`

```php
$this->fillInField('name', 'Viraj Khatavkar');

OR use a CSS identifier

$this->fillInField('#name', 'Viraj Khatavkar');
```

### `$this->check($element)`

Check the checkbox identified with name or id of `$element`

```php
$this->check('#agree-to-terms');
```

### `$this->uncheck($element)`

Uncheck the checkbox identified with name or id of `$element`

```php
$this->uncheck('#agree-to-terms');
```

### `$this->select($element, $option)`

Select a radio button or an option from the dropdown field identified with name or id of `$element`

```php
//Dropdown
$this->select('state', 'Pennsylvania');

//Radio
$this->select('#gender', 'M');
```

### `$this->press($buttonText)`

Press a button with the provided name or text.

```php
//Text of the button
$this->press('Submit');


//Name of the button
$this->press('submit');
```

### `$this->canSeePageIs($url)`

Assert that the page URI matches the given url.

```php
$this->canSeePageIs('/posts');
```

### `$this->canSeePageUrlContains($url)`

Assert that the page URI contains the given url.

```php
$this->canSeePageUrlContains('/po');
```

### `$this->actingAs($user)`

Set the currently logged in user for the application.

```php

$user = factory('Users')->create();

$this->actingAs($user)->openPage('/posts/add');
```
