# CakePHP Integrated
Better integration testing with [CakePHP](https://cakephp.org). An intuitive API for integration testing your CakePHP applications.

## Step 1: Installation & Setup
Install this package using Composer:

```bash
composer require viraj/cakephp-integrated --dev
```

You'll also need to set a baseUrl for your application. By default, it is set to "http://localhost", however, you'll likely need to change this. Do so by either adding a $baseUrl to your test class:

```php
protected $baseUrl = 'http://your-dev-url';
```


>This package comes installed with the [TestDummy](https://github.com/viraj-khatavkar/cakephp-testdummy#step-2-create-a-factories-file) package. It is recommended to use factories and the `DatabaseMigrations` trait instead of `fixtures` for optimal productivity with this package. You can learn more about that in the official documentation of the [TestDummy](https://github.com/viraj-khatavkar/cakephp-testdummy#step-2-create-a-factories-file) package. 

## Step 2: Extend the base class:

After CakePHP 3.4.1:

```php
class DemoTest extends CakeTestCase
{
}
```

Before CakePHP 3.4.1

```php
class DemoTest extends LegacyTestCase
{
}
```

## Step 3: Write tests ;)

The API for both the classes is going to be the same. We will use the `CakeTestCase` as an example. Here is an example test to help you understand how this works:

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
             ->check('published')
             ->press('Submit')
             ->canSeePageIs('/posts')
             ->seeText('My first post');
    }
}
``` 

## API
Here is the API of this package which can be used to write your tests:

**`$this->fillInField($elementName, $text)`**

Fill the text in the input field identified with name of element

```php
$this->fillInField('name', 'Viraj Khatavkar');
```

**`$this->check($elementName)`**

Check the checkbox identified with name element

```php
$this->check('agree_to_terms');
```

**`$this->uncheck($elementName)`**

Uncheck the checkbox identified with name of element

```php
$this->uncheck('agree_to_terms');
```

**`$this->select($elementName, $option)`**

Select a radio button or an option from the dropdown field identified with name of element

```php
//Dropdown
$this->select('state', 'Pennsylvania');

//Radio
$this->select('gender', 'M');
```

**`$this->press($buttonText)`**

Press a button with the provided name or text.

```php
//Text of the button
$this->press('Submit');


//Name of the button
$this->press('submit');
```

**`$this->canSeePageIs($url)`**

Assert that the page URI matches the given url.

```php
$this->canSeePageIs('/posts');
```

**`$this->canSeePageUrlContains($url)`**

Assert that the page URI contains the given url.

```php
$this->canSeePageUrlContains('/po');
```

**`$this->actingAs($user)`**

Set the currently logged in user for the application.

```php

$user = factory('Users')->create();

$this->actingAs($user)->openPage('/posts/add');
```

### Looking for a comprehensive guide on implementing TDD practices in a real world CakePHP application?

I'm writing a book on implementing TDD for real-world CakePHP applications! [Check it out](https://tddforcakephp.com/) if you are having a hard time on writing tests in a real world application.
