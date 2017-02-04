Change Log
==========

2017-02-03
----------

Changed:
 
 * Updated dependencies for Laravel 5.4 / Laravel-Doctrine 

2017-01-11
----------

Changed:

 * Updated dependencies for ramsey/uuid 

2016-10-11
----------

Changed:

 * Repository config now supports named entity managers in repository definitions

2016-08-01
----------

Added:

 * JsonCollectionType: JSON arrays are hydrated to an ArrayCollection instance
 
Changed:

 * Repository config now supports named entity managers in repository definitions
 * Updated readme to reflect changes in 0.4.
 
2016-07-31
----------

Changed:

 * Fixed config loading for behaviours; was not merging default config.

2016-07-30
----------

Changed:

 * Reverted Blamable subscriber changes; causing DI resolution issue as Guard
   depends on UserProvider service that LaravelDoctrine creates onBoot.

2016-07-22
----------

Added:

 * IdentifiableWithTimestamps
 * MakeEntity command for building entity stubs + repository
 
BC Breaks:

 * Renamed ServiceProvider class to BehavioursServiceProvider, now loads commands
 * Renamed repositories config files to doctrine_repositories

2016-04-14
----------

Changed:

 * Added compiles() to provider to allow main classes to be compiled in production

2016-02-13
----------

Changed:

 * Fixed SluggableEventSubscriber missing interface

2015-11-29 - 2
--------------

Added:

 * config/repositories.php

Changed:

 * RepositoryServiceProvider is no longer abstract, has a config file

2015-11-29
----------

Added:

 * Sluggable

2015-11-28
----------

Added:

 * CanRenumberCollection
 * NumericallySortable
 * Publishable
 * Versionable

2015-11-25
----------

Initial commit.
