# BeforeAfterControllersHooksBundle

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