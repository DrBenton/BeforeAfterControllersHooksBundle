# BeforeAfterControllersHooksBundle

[![build status](https://secure.travis-ci.org/DrBenton/BeforeAfterControllersHooksBundle.png)](http://travis-ci.org/DrBenton/BeforeAfterControllersHooksBundle)

If you like [Silex routes middlewares](http://silex.sensiolabs.org/doc/middlewares.html#route-middlewares)
or [Ruby on Rails actions filters](http://guides.rubyonrails.org/action_controller_overview.html#filters),
you may appreciate this Bundle, as it mimics this behaviour in Symfony2, thanks to specific Annotations.

This type of "before" and "after" callbacks around Controllers Actions may not be very "design patterns compliant",
but it's pragmatic and allows easy DRY principle application :-)

With the "@BeforeHook" and "@AfterHook" Annotations, you can trigger methods of the Controller itself,
or Symfony Services methods.

If a "@BeforeHook" callback returns a Response object, the Request handling is short-circuited
(the next hooks won't be run, neither the Controller action), and the Response is passed to the "@AfterHook" callback(s)
right away - or returned to the client if no @AfterHook is linked to this Controller Action.

## Synopsis

As always, a code sample may be worth a thousand words:

```php
<?php

namespace AppBundle\Controller;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook as AfterHook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Before any of this Controller Action, its 'checkSomething' method
 * will be triggered:
 *
 * @BeforeHook("checkSomething")
 */
class BooksController extends Controller
{
    /**
     * The Controller 'checkBooksAvailability()' method will be triggered
     * before this Controller action:
     *
     * @BeforeHook("checkBooksAvailability")
     * @Template()
     */
    public function indexAction()
    {
        $books = $this->getDoctrine()
                    ->getRepository('AppBundle:Book')
                    ->findAll();

        return ['books' => $books];
    }

    /**
     * You can also send params to the triggered hook:
     *
     * @BeforeHook("doSomethingBeforeAction", args={"param1", "param2"})
     * @Template()
     */
    public function showAction(Book $book)
    {
        return ['book' => $book];
    }

    /**
     * Want to trigger a Symfony Service method? No problem!
     * Just use the "@[serviceId]::[methodName]" notation:
     *
     * @BeforeHook("@logger::addInfo", args={"showComments() will be called"})
     * @Template()
     */
    public function showCommentsAction(Book $book)
    {
        return ['book' => $book];
    }

    /**
     * You can also trigger a custom callable after the Controller action:
     *
     * @AfterHook("addDebugCodeAfterAction")
     * @Template()
     */
    public function showSomethingAction(Book $book)
    {
        return ['book' => $book];
    }

    /**
     * You can use Services here too, and use params. Any "%response%" param
     * will be replaced with the Controller's returned Symfony Reponse.
     *
     * @AfterHook("@my_service::doSomethingAfterAction", args={"%response%", {"key" => "value"}})
     * @Template()
     */
    public function showSomethingAction(Book $book)
    {
        return ['book' => $book];
    }

    protected function checkSomething()
    {
        // Do something here...
        // It this method returns a Symfony Response, the Controller
        // will be short-circuited and this Response will be sent to the client.
    }

    protected function checkBooksAvailability()
    {
        // idem: return a Response here if oy want to short-circuit the Controller
    }

    protected function doSomethingBeforeAction($arg1, $arg2)
    {
        // Do something here...
    }

    protected function addDebugCodeAfterAction(Response $controllerResponse)
    {
        if ($this->container->getParameter('debug')) {
            $controllerResponse->setContent(
                $controllerResponse->getContent() .
                '<script src="//assets/js/debug.js"></script>'
            );
        }
    }
}
```

## Installation

### Step 1: Composer

Add the following line to the `composer.json` file:

``` json
{
    "require": {
        "dr-benton/before-after-controllers-hooks-bundle": "@dev-master"
    }
}
```

Then run:

``` bash
$ composer update "dr-benton/before-after-controllers-hooks-bundle"
```

### Step 2: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new DrBenton\Bundle\BeforeAfterControllersHooksBundle\BeforeAfterControllersHooksBundle(),
    );
}
```

## License

Copyright (c) 2014 Olivier Philippon

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.