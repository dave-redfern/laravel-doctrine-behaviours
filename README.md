## Behaviours for Laravel-Doctrine ORM

Adds some very common traits, contracts and event subscribers that can be used with the Laravel-Doctrine ORM
package, replacing Gedmo Blameable and Timestampable as well as a UUID pre-persist behaviour.

This library is very opinionated on naming and Doctrine config. Each of the behaviours and all
the traits work on the basis that Doctrine is running with configuration files and not annotations.
The names of each field are set within the traits.

### Requirements

 * PHP 5.5+
 * laravel 5.1+
 * laravel-doctrine/orm

### Installation

Install using composer, or checkout / pull the files from github.com.

 * composer install somnambulist/laravel-doctrine-behaviours

### Enabling in Doctrine

To enable the behaviours, add each EventSubscriber to the config/doctrine.php file:

    'managers' => [
        'default' => [
            'events' => [
                'subscribers' => [
                    \Somnambulist\Doctrine\EventSubscribers\BlamableEventSubscriber::class,
                    \Somnambulist\Doctrine\EventSubscribers\SluggableEventSubscriber::class,
                    \Somnambulist\Doctrine\EventSubscribers\TimestampableEventSubscriber::class,
                    \Somnambulist\Doctrine\EventSubscribers\UuidEventSubscriber::class,
                    \Somnambulist\Doctrine\EventSubscribers\VersionableEventSubscriber::class,
                ]
            ],
        ]
    ],

Then to ensure Carbon is used in your entities, and the following to the types:

    'custom_types' => [
        'date'       => \Somnambulist\Doctrine\Types\DateType::class,
        'datetime'   => \Somnambulist\Doctrine\Types\DateTimeType::class,
        'datetimetz' => \Somnambulist\Doctrine\Types\DateTimeTzType::class,
        'time'       => \Somnambulist\Doctrine\Types\TimeType::class,
    ],

 * _Note:_ the type overrides are required when using TimestampableEventSubscriber.
 * _Note:_ these behaviours are intended to be used with meta-data files not annotations!

## Behaviours / Traits

### Blamable

Blamable adds createdBy and updatedBy and then attempts to set the name of the User
who performed the create/update. This is extracted from the auth()->user() object.
The User object is checked in turn for one of the following:

 * UUID (UuidContract / getUuid)
 * Username (getUsername)
 * Email (getEmail)
 * Id (getId)

If the User (somehow) does not implement any of those, the class and identifier are
pulled and attempted to be located from a Doctrine repository and then the same check
is made again on the found entity.

If there is no user a default is applied: 'system'

This can be overridden by setting the environment value: DOCTRINE_BLAMABLE_DEFAULT_USER.

To add Blamable support, ensure your entity implements the Blamable contract. A trait
is provided that adds the appropriate methods:

    use Somnambulist\Doctrine\Contracts\Blamable as BlamableContract;
    use Somnambulist\Doctrine\Traits\Blamable;

    class MyEntity implements BlamableContract
    {
        use Blamable;
    }

Be sure to update your mapping files to include the fields:

    fields:
        createdBy:
            type: string
            length: 36

        updatedBy:
            type: string
            length: 36

These fields should be at least 36 characters long as UUIDs may be used with them.

### Sluggable

Sluggable adds getSlug / setSlug i.e. a URL friendly text representation of the name.
A trait is included implementing the property and methods. In addition, there is an
event subscriber that will set the slug from the name if the entity implements
Nameable. This is checked on prePersist and preUpdate. Slugs are not updated if they
already exist.

Name to Slug is performed via the Laravel helper function: str_slug().

Example usage:

    use Somnambulist\Doctrine\Contracts\Sluggable as SluggableContract;
    use Somnambulist\Doctrine\Traits\Sluggable;

    class MyEntity implements SluggableContract
    {
        use Sluggable;
    }

Mapping file fields:

    fields:
        slug:
            type: string
            length: 255

You should add a unique constraint to the slug since they should be unique. Otherwise,
do not use the event subscriber and manage updating / checking manually before performing
a Doctrine flush().

### Timestampable

Timestampable adds createdAt / updatedAt fields and persistence to your entities. This
extension uses Carbon internally (just like Laravels Eloquent). It is not necessary to
populate these fields as they will be automatically set by the event subscriber. As
Carbon is used internally, the type mappings **must** be added to the config/doctrine.php
file.

To add timestampable, simply implement the contract and optionally include the trait:

    use Somnambulist\Doctrine\Contracts\Timestampable as TimestampableContract;
    use Somnambulist\Doctrine\Traits\Timestampable;

    class MyEntity implements TimestampableContract
    {
        use Timestampable;
    }

Then add the following to your entity mapping files:

    fields:
        createdAt:
            type: datetime

        updatedAt:
            type: datetime

Now your entities will be tagged with the date/time they are created/updated.

Note: the default timezone is used. Be sure to set this before using. It is recommended
to set your PHP default timezone to UTC, and then translate in the UI.

### Universally Identifiable

UniversallyIdentifiable adds a UUID field to your entity, auto-creating one if not already
set when new entities are persisted. This behaviour does not check entities on update: only
prePersist.

UUIDs are generated by the ramsey/uuid library. Only UUIDv4 are used.

Like the other behaviours to use UniversallyIdentifiable, simply implement the contract
and optionally use the trait:

    use Somnambulist\Doctrine\Contracts\UniversallyIdentifiable as UniversallyIdentifiableContract;
    use Somnambulist\Doctrine\Traits\UniversallyIdentifiable;

    class MyEntity implements UniversallyIdentifiableContract
    {
        use UniversallyIdentifiable;
    }

This will ensure that this entity will be uniquely identifiable in any system - very useful
when exposing entities in an API.

Be sure to add a UUID field definition to your entity mapping files:

    uniqueConstraints:
        uniq_table_name_here_uuid:
            columns: [ uuid ]

    fields:
        uuid:
            type: guid

It is a good idea to add a unique constraint to the UUID field just in case.

### Versionable

Versionable adds a simple "version" property that is increment on each update. If it is not
set to (int) 1 on prePersist, the value will be initialised. This can then be used keep track
of the number of times an entity has been changed.

Be sure to add the following to you mapping files:

    fields:
        version:
            type: integer

### Others

In addition to the main behaviours there are several additional contracts / traits:

 * Identifiable - adds id / getId
 * Nameable - adds name / getName / setName
 * NumericallySortable - adds orderinal / getOrdinal / setOrdinal
 * Publishable - adds publishedAt / getPublishedAt / setPublishedAt / isPublished / publishAt / unPublish
 * Trackable - Identifiable, Nameable, Blamable, Timestampable
 * GloballyTrackable - Identifiable, Nameable, Blamable, Timestampable, UniversallyIdentifiable

NumericallySortable adds a simple "ordinal" field to allow records to be sorted by an incrementing
integer value. This needs to be manually tracked in your entities e.g.: on add/remove against a colection
add a "renumber" method call that will iterate and re-number the items in the collection. A simple
CanRenumberCollection interface/trait is included but you may need something more sophisticated.

Trackable / GloballyTrackable simply wrap up the other traits / contracts providing a convenient
way to add everything either as an internal entity (Trackable) or a potentially externally facing
entity (GloballyTrackable).

## Links

 * [Laravel Doctrine](http://laraveldoctrine.org)
 * [Laravel](http://laravel.com)
 * [Doctrine](http://doctrine-project.org)