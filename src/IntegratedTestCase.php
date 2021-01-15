<?php
namespace Integrated;


use Cake\ORM\Entity;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use TestDummy\BaseTestCase;

abstract class IntegratedTestCase extends BaseTestCase
{
    /**
     * The Symfony DomCrawler instance.
     *
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * All of the stored inputs for the current page.
     *
     * @var array
     */
    protected $inputs = [];

    /**
     * All of the stored uploads for the current page.
     *
     * @var array
     */
    protected $uploads = [];

    /**
     * The current page URL.
     *
     * @var string
     */
    protected $currentUrl;

    /**
     * Make a GET request to the given uri.
     *
     * @param $url
     *
     * @return $this
     */
    public function openPage($url)
    {
        $this->makeRequest($url);

        return $this;
    }

    /**
     * Prepare the relative URL, given by the user.
     *
     * @param $url
     * @param bool $full
     *
     * @return string
     */
    protected function prepareUrlForRequest($url, $full = true)
    {
        if ($this->startsWith($url, '/')) {
            $url = substr($url, 1);
        }

        if (!$this->startsWith($url, 'http')) {
            $url = $this->baseUrl . '/' . $url;
        }

        return trim($url, '/');
    }

    /**
     * Call a URI in the application.
     *
     * @param $url
     * @param string $method
     * @param array $data
     *
     * @return $this
     */
    protected function makeRequest($url, $method = 'GET', $data = [])
    {
        $this->_sendRequest($url, $method, $data);

        $this->currentUrl = $url;

        $this->clearInputs()->followRedirects();

        $this->assertResponseOk();

        $this->crawler = new Crawler($this->_getBodyAsString(), $this->baseUrl . $this->currentUrl);

        return $this;
    }

    /**
     * Clear the inputs for the current page.
     *
     * @return $this
     */
    protected function clearInputs()
    {
        $this->inputs = [];

        $this->uploads = [];

        return $this;
    }

    public function seeText($content, $ignoreCase = true, $message = '')
    {
        if (!$this->_response) {
            $this->fail('No response, cannot see content. ' . $message);
        }

        $this->assertStringContainsStringIgnoringCase($content, $this->_getBodyAsString(), $message);

        return $this;
    }

    public function dontSeeText($content, $ignoreCase = true, $message = '')
    {
        if (!$this->_response) {
            $this->fail('No response, cannot see content. ' . $message);
        }

        $this->assertStringNotContainsStringIgnoringCase($content, $this->_getBodyAsString(), $message);

        return $this;
    }

    /**
     * Click a link with the given body, name, or ID attribute.
     *
     * @param  string $name
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    protected function click($name)
    {
        $link = $this->crawler->selectLink($name);

        if (!count($link)) {
            $link = $this->filterByNameOrId($name, 'a');

            if (!count($link)) {
                throw new InvalidArgumentException(
                    "Could not find a link with a body, name, or ID attribute of [{$name}]."
                );
            }
        }

        $path = $this->buildUrl($link->link()->getUri());

        $this->openPage($path);

        return $this;
    }

    /**
     * Build url path with query string if present
     *
     * @param string $uri
     *
     * @return mixed|string
     */
    protected function buildUrl(string $uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);

        return $query ? $path . '?' . $query : $path;
    }

    /**
     * Fill an input field with the given text.
     *
     * @param  string $element
     * @param  string $text
     *
     * @return $this
     */
    public function fillInField($element, $text)
    {
        return $this->storeInput($element, $text);
    }

    /**
     * Check a checkbox on the page.
     *
     * @param  string $element
     *
     * @return $this
     */
    protected function check($element)
    {
        return $this->storeInput($element, true);
    }

    /**
     * Uncheck a checkbox on the page.
     *
     * @param  string $element
     *
     * @return $this
     */
    protected function uncheck($element)
    {
        return $this->storeInput($element, false);
    }

    /**
     * Select an option from a dropdown.
     *
     * @param  string $element
     * @param  string $option
     *
     * @return static
     */
    public function select($element, $option)
    {
        return $this->storeInput($element, $option);
    }

    /**
     * Attach a file to a form.
     *
     * @param  string $element
     * @param  string $absolutePath
     *
     * @return static
     */
    public function attachFile($element, $absolutePath)
    {
        $name = str_replace('#', '', $element);

        $this->uploads[$name] = $absolutePath;

        return $this->storeInput($element, $absolutePath);
    }

    /**
     * Press the form submit button with the given text.
     *
     * @param  string $buttonText
     *
     * @return static
     */
    public function press($buttonText)
    {
        return $this->submitForm($buttonText, $this->inputs);
    }

    public function canSeePageIs($url)
    {
        $this->assertEquals($url, $this->currentUrl);

        return $this;
    }

    public function canSeePageUrlContains($url)
    {
        $this->assertStringContainsString($url, $this->currentUrl);

        return $this;
    }

    /**
     * Store a form input.
     *
     * @param  string $name
     * @param  string $value
     *
     * @return static
     */
    protected function storeInput($name, $value)
    {
        $this->assertFilterProducedResults($name);

        $name = str_replace(['#', '[]'], '', $name);

        $this->inputs[$name] = $value;

        return $this;
    }

    /**
     * Assert that the filtered Crawler contains nodes.
     *
     * @param  string $filter
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function assertFilterProducedResults($filter)
    {
        $crawler = $this->filterByNameOrId($filter);

        if (!count($crawler)) {
            $message = "Nothing matched the '{$filter}' CSS query provided for {$this->currentUrl}.";

            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Filter elements according to the given name or ID attribute.
     *
     * @param  string $name
     * @param  array|string $elements
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function filterByNameOrId($name, $elements = '*')
    {
        $name = str_replace('#', '', $name);

        $id = str_replace(['[', ']'], ['\\[', '\\]'], $name);

        $elements = is_array($elements) ? $elements : [$elements];

        array_walk($elements, function (&$element) use ($name, $id) {
            $element = "{$element}#{$id}, {$element}[name='{$name}']";
        });

        return $this->crawler->filter(implode(', ', $elements));
    }

    /**
     * Submit a form on the page.
     *
     * @param  string $buttonText
     * @param  array $formData
     *
     * @return static
     */
    public function submitForm($buttonText, $formData = [])
    {
        $this->makeRequestUsingForm(
            $this->fillForm($buttonText, $formData)
        );

        return $this;
    }

    /**
     * Make a request to the application using the given form.
     *
     * @param  \Symfony\Component\DomCrawler\Form $form
     * @param  array $uploads
     *
     * @return $this
     */
    protected function makeRequestUsingForm(Form $form, array $uploads = [])
    {
        $path = $this->buildUrl($form->getUri());

        return $this->makeRequest($path, $form->getMethod(), $form->getPhpValues());
    }

    /**
     * Converts form files to UploadedFile instances.
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     * @param $uploads
     *
     * @return array
     */
//    public function convertUploadsForTesting(Form $form, $uploads)
//    {
//        $files = $form->getFiles();
//
//        $names = array_keys($files);
//
//        $files = array_map(function (array $file, $name) use ($uploads) {
//            return $this->getUploadedFileForTesting($file, $uploads, $name);
//        }, $files, $names);
//
//        $uploads = array_combine($names, $files);
//
//        foreach ($uploads as $key => $file) {
//            if (preg_match('/.*?(?:\[.*?\])+/', $key)) {
//                $this->prepareArrayBasedFileInput($uploads, $key, $file);
//            }
//        }
//
//        return $uploads;
//    }

    /**
     * Store an array based file upload with the proper nested array structure.
     *
     * @param  array $uploads
     * @param  string $key
     * @param  mixed $file
     */
//    protected function prepareArrayBasedFileInput(&$uploads, $key, $file)
//    {
//        preg_match_all('/([^\[\]]+)/', $key, $segments);
//
//        $segments = array_reverse($segments[1]);
//
//        $newKey = array_pop($segments);
//
//        foreach ($segments as $segment) {
//            $file = [$segment => $file];
//        }
//
//        $uploads[$newKey] = $file;
//
//        unset($uploads[$key]);
//    }

    /**
     * Create an UploadedFile instance for testing.
     *
     * @param  array $file
     * @param  array $uploads
     * @param  string $name
     *
     * @return \Zend\Diactoros\UploadedFile
     */
//    protected function getUploadedFileForTesting($file, $uploads, $name)
//    {
//        if ($file['error'] == UPLOAD_ERR_NO_FILE) {
//            return;
//        }
//
//        $originalName = isset($uploads[$name]) ? basename($uploads[$name]) : $file['name'];
//
//        return new UploadedFile(
//            $file['tmp_name'], $file['size'], $file['error'], $originalName, $file['type']
//        );
//    }

    /**
     * Fill out the form, using the given data.
     *
     * @param  string $buttonText
     * @param  array $formData
     *
     * @return \Symfony\Component\DomCrawler\Form
     */
    protected function fillForm($buttonText, $formData = [])
    {
        if (!is_string($buttonText)) {
            $formData = $buttonText;
            $buttonText = null;
        }

        return $this->getForm($buttonText)->setValues($formData);
    }

    /**
     * Get the form from the DOM.
     *
     * @param  string|null $button
     *
     * @throws InvalidArgumentException
     * @return \Symfony\Component\DomCrawler\Form
     */
    protected function getForm($button = null)
    {
        // If the first argument isn't a string, that means
        // the user wants us to auto-find the form.

        try {
            if ($button) {
                return $this->crawler->selectButton($button)->form();
            }

            return $this->crawler->filter('form')->form();
        } catch (InvalidArgumentException $e) {
            // We'll catch the exception, in order to provide a
            // more readable failure message for the user.

            throw new InvalidArgumentException(
                "Couldn't find a form that contains a button with text '{$button}'."
            );
        }
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     *
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    public function actingAs(Entity $user)
    {
        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        return $this;
    }

    public function addToSession(array $data)
    {
        $this->session($data);

        return $this;
    }

}
