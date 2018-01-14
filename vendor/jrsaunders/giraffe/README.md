# Giraffe
**Ajax: Server/Client Helper/Router**

[![Latest Stable Version](https://poser.pugx.org/jrsaunders/giraffe/v/stable)](https://packagist.org/packages/jrsaunders/giraffe)
[![Total Downloads](https://poser.pugx.org/jrsaunders/giraffe/downloads)](https://packagist.org/packages/jrsaunders/giraffe)
[![Latest Unstable Version](https://poser.pugx.org/jrsaunders/giraffe/v/unstable)](https://packagist.org/packages/jrsaunders/giraffe)
[![License](https://poser.pugx.org/jrsaunders/giraffe/license)](https://packagist.org/packages/jrsaunders/giraffe)

supports:
* DataTables
* Ajax Uploads
* Redirects
* Notifications
* Modals
* General all purpose Ajax
* Debugging

## Install: Server Side

**index.php / bootstrap.php**


Add this to one or both of these php files:


```
use Giraffe\Giraffe;
Giraffe::setEnvironment(ENVIRONMENT);
Giraffe::setProject('MY SITE NAME OR PRJECT NAME');
Giraffe::setDeveloperEmails(
     'me@mailprovider.co.uk,my_partner@theirmailprovider.co.uk'
);
Giraffe::setJSDIR(__DIR__ . '/public/assets/js/giraffe/');

```
###Put a route in place to get to an ajax controller

Here is an example of how that ajax controller may look:

```
namespace MyApp\Ajax;

use Giraffe\Giraffe;

class Ajax 
{
    public function someMethodYouRouteToForAjax()
    {
        $allowedControllers = [Login::class, Dashboard::class];
        new Giraffe($allowedControllers, 'MyApp\\Controller\\', 'ajax', MYAPP_PATH . '/Views/ajax/');
    }
}
```

## Install: Client/Front End Side
Create a folder named giraffe in your js directory.  Then `chown apache:apache giraffe` or `chmod 777 giraffe`.

Giraffe can update your Javascript File when composer updates the package.

Giraffe is designed to be used in conjunction with JQuery.






