FieldUserBundle
=============

The FieldUserBundle extends the Symfony Framework by a database-supported user system.

It provides a flexible framework consisting of the basic functions of a common user system and user management with tasks such as resetting the password or deactivating a user.

The FieldUserBundle includes the following functions:

- Registration of new users
- Registration of existing users
- Possibility to reset the password
- Editable user profiles
- Role hierarchy
- Restricted user management
- Data storage via Doctrine ORM in a MySQL database

Installation
------------

The installation consists of a 7-step process:

1.  Download the FieldUserBundle with Composer
2.  Activation of the bundle
3.  Create your own user class
4.  Configuration of the security.yml
5.  Configuration of the FieldUserBundle
6   Import FieldUserBundle routing
7   Updating the database schema

**Step 1: Download the Bundle**

Open a command console, enter your project directory and execute:

```
$ composer require field/user-bundle "~1.0"
```

Composer will install the bundle on its own in the project under the directory vendor/field/user-bundle.

**Step 2: Enable the Bundle**

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Field\UserBundle\FieldUserBundle(),
        // ...
    );
}
```

**Step 3: Create your own user class**

In order for the bundle to store the user data in the database, it is necessary to implement its own user class, based on the abstract class `Field\UserBundle\Entity\User`, in your project.

In your own user class, you can add additional attributes and methods to the user as you want.

The basic implementation could look like this:
 
```
<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Field\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="field_user")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __construct()
    {
        parent::__construct();
        // Your own logic
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
```

**Step 4: Configuration of the security.yml**

To add the security standards of the FieldUserBundles to your Symfony application, you have to adapt your `security.yml` in the project configuration.

A minimal example can be found in the following code example:

```
# app/config/security.yml
security:
    encoders:
        Field\UserBundle\Entity\User:
            algorithm: bcrypt
            cost:      13

    providers:
        db_provider:
            entity:
                class: AppBundle:User
                property: email
        in_memory:
            memory: ~

    role_hierarchy:
        ROLE_ADMIN: ROLE_USER

    firewalls:
        main:
            anonymous: ~
            provider: db_provider
            form_login:
                login_path: login
                check_path: login
                default_target_path: /profile
            logout: true
            remember_me:
                secret:   '%secret%'
                lifetime: 604800
                path:     /profile

    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
```

**Step 5: Configuration of the FieldUserBundle**

Now that you have created your own user class and modified the `security.yml`, it is time to tell your project configuration to use the user class as well.

All you need to do is add the following parameter to your `config.yml`:

```
# app/config/config.yml
parameters:
    user_class: 'AppBundle\Entity\User'
```

**Step 6: Import FieldUserBundle routing**

In order to be able to use the newly acquired functions in your application, you should import the routing annotations of the FieldUserBundles into your `routing.yml`.

```
# app/config/routing.yml
field_user:
    resource: "@FieldUserBundle/Controller/"
    type:     annotation
    
logout:
    path:     /logout
```

**Step 7: Updating the database schema**
 
Finally, you need to update your database to the latest version and extend the new user class.

Thanks to Doctrine you can do this with the following command:

```
$ php bin/console doctrine:schema:update --force
```

If you use the Symfony 2.x structure in your project, use `app/console` instead of `bin/console` in the commands.

With this last step you should have successfully installed the FieldUserBundle in your application.

License
-------

This bundle is under the MIT license. The full license can be viewed [here](LICENSE).

About us
-----

We are [Field Interactive](https://www.field-interactive.com/)