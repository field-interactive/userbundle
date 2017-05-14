FieldUserBundle
=============

Das FieldUserBundle ergänzt das Symfony Framework um ein datenbankgestütztes Benutzersystem.
Es bietet ein flexibles Gerüst bestehend aus den Grundfunktionen eines üblichen Benutzersystems und einer Benutzerverwaltung mit Aufgaben wie dem Zurücksetzen des Passworts oder dem Deaktivieren eines Benutzers.

Folgende Funktionen umfasst das FieldUserBundle:

- Registrierung neuer Benutzer
- Anmeldung bestehender Benutzer
- Möglichkeit zum Zurücksetzen des Passworts
- Bearbeitbare Benutzerprofile
- Rollenhierarchie
- Zugangsbeschränkte Benutzerverwaltung
- Datenhaltung über Doctrine ORM in einer MySQL-Datenbank

Installation
------------

Die Installation besteht aus einem schnellen 7-stufigen Prozess:

1.    Das FieldUserBundle mit Composer herunterladen
2.    Aktivierung des Bundles
3.    Erstellen einer eigenen Benutzerklasse
4.    Konfiguration der security.yml
5.    Konfiguration des FieldUserBundle
6.    FieldUserBundle-Routing importieren
7.    Aktualisierung des Datenbankschemas

**Schritt 1: Das FieldUserBundle mit Composer herunterladen**

Konsolenbefehl zur Installation des Bundles mit Composer:

```
$ composer require field/user-bundle "~1.0"
```

Composer wird das Bundle selbstständig im Projekt unter dem Verzeichnis vendor/field/user-bundle installieren.

**Schritt 2: Aktivierung des Bundles**

Für die Aktivierung des Bundles im AppKernel ist folgender Eintrag nötig:

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

**Schritt 3: Erstellen einer eigenen Benutzerklasse**

Damit das Bundle die Benutzerdaten in der Datenbank speichern kann, ist es notwendig eine eigene User-Klasse, basierend auf der abstrakten Klasse Field\UserBundle\Entity\User, in Ihrem Projekt zu implementieren.

In Ihrer eigenen User-Klasse können Sie den Benutzer um weitere Attribute und Methoden ganz nach Ihren Belieben erweitern.

Die Basis-Implementation könnte wie folgt aussehen:
 
```
<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Field\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="field_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __construct()
    {
        parent::__construct();
        // Ihre eigene Logik
    }
}
```

**Schritt 4: Konfiguration der security.yml**

Um Ihre Symfony Anwendung um die Sicherheitsstandards des FieldUserBundles zu ergänzen, müssen Sie Ihre security.yml in der Projektkonfiguration anpassen.

Ein Minimal-Beispiel können Sie folgenden Codebeispiel entnehmen:

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

**Schritt 5: Konfiguration des FieldUserBundle**

Nachdem Sie jetzt Ihre eigene Benutzerklasse angelegt haben und die security.yml angepasst haben, ist es nun an der Zeit Ihrer Projektkonfiguration mitzuteilen, die Benutzerklasse auch zu verwenden.

Dafür genügt folgender Parameter in Ihrer config.yml hinzuzufügen:

```
# app/config/config.yml
parameters:
    user_class: 'AppBundle\Entity\User'
```

**Schritt 6: FieldUserBundle-Routing importieren**

Um die neu gewonnenen Funktionen in Ihrer Anwendung auch nutzen zu können sollten Sie noch die Routing-Annotations des FieldUserBundles in Ihrer routing.yml importieren.

```
# app/config/routing.yml
field_user:
    resource: "@FieldUserBundle/Controller/"
    type:     annotation
```

**Schritt 7: Aktualisierung des Datenbankschemas**
 
Zuletzt gilt es noch Ihre Datenbank auf den aktuellsten Stand zu bringen und um die neue Benutzer Klasse zu erweitern.

Dank Doctrine können Sie dies über folgenden Befehl erledigen:

```
$ php bin/console doctrine:schema:update --force
```

Mit diesem letzten Schritt sollten Sie erfolgreich das FieldUserBundle in Ihrer Anwendung installiert haben.

Lizenz
-------

Dieses Bundle steht unter der MIT-Lizenz. Die vollständige Lizenz können Sie [hier einsehen](LICENSE).

Über
-----

Das FieldUserBundle ist eine Entwicklung der Field Interactive GmbH.

Die Field Interactive GmbH eint die Digitalagentur Field Digital GmbH und die Kommunikationsagentur Field Communications GmbH.

In interdisziplinären Teams arbeiten über 20 Mitarbeiter und Mitarbeiterinnen an drei Standorten in Dortmund, Brilon und Lünen.
Zusammen bilden sie eine Agentur für digitale Markenführung.

Fehlerberichte oder Verbesserungswünsche
-----------------------------------------

Fehler und Verbesserungen werden direkt im [GitLab Repository](https://gitlab.com/myboom/userbundle/issues) des Bundles gesammelt und bearbeitet.