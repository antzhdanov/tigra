Tigra - Tool for Magento DB migrations
======================================

Tigra is a tool that simplifies your daily work with Magento as a developer.
As you probably know, there's a [Setup Resource](http://www.magentocommerce.com/knowledge-base/entry/magento-for-dev-part-6-magento-setup-resources) thing in Magento, that has shortcut methods for operations like working with atributes and entity types. The problem with it is that it is connected to a particular module, which is mostly not useful when you need to make changes to different parts of application. Moreover, setup resources are hard to manage so you can't apply your database changes using Cron job or automate things with deployment tool like Phing.
Tigra allows you to create database migrations and manage your database version via command line and/or Magento backend.

Setup
-----
1. Copy all repo files to Magento root directory
2. Login to the backend
3. Create a migrations directory somewhere in Magento directory
4. Follow System->Configuration, Developer->Database migrations and fill in the "Migrations directory" with appropriate directory path.
5. Save config.

Writing your first DB migration
-------------------------------
Before you start, please make sure you are familiar with the following:
1. [MySQL Transactional and Locking Statements](http://dev.mysql.com/doc/refman/5.7/en/sql-syntax-transactions.html)
2. [Statements That Cause an Implicit Commit](http://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html)

Also, make sure that your tables support transactions.

To create a migration, do the following:
1. Run the following in your magento directory:
``` bash
$ cd shell/
$ ./tigra --generate MIGRATION_NAME
```
This will create a skeleton migration for you in your migrations/ directory (e.g., 005_change_site_name.php). Edit it to reflect your needs.

2. Migrate:
``` bash
$ ./tigra up
```

Usage
-----
For your convenience, Tigra is coming shipped with both CLI and WEB interfaces.

## CLI interface
All commands below are executed in the shell/ directory.

### Get help
To get help, run `./tigra` in your magento/shell directory.

### Upgrade database
To upgrade your database, run
``` bash
$ ./tigra up
```

By default, it applies all migrations that it can found in migrations/ directory.

### Downgrade database
To downgrade, run
``` bash
$ ./tigra down
```

It will downgrade the database to the previous version.
If you want to upgrade/downgrade to a particular version, you can pass the `--to` parameter:
``` bash
$ ./tigra up --to 003
```

### Current DB version
The following command prints the current version of the database:
``` bash
$ ./tigra version
```

## Web interface
Web interface is available at the Magento backend, System->Tools->DB Migrations.

### Upgrade database
To upgrade the database using Web interface, follow the "Update to" link near the migration you want to upgrade to.

### Downgrade database
To upgrade the database using Web interface, follow the "Rollback (including)" link near the migration you want to downgrade to.

Migration naming
----------------
In case if you want to create the migration manually, please follow the rules below when picking the migration name.
Each migration should be named as `<migration_num>_<description>.php` and should not contain any spaces.
The code inside should follow the next rules:

1. Class name should be descriptive enough as this text is being stored in the database changelog table as a migration description.
2. Each migration should have both up() and down() methods to make migration mechanism working in two directions.

Here's the small example on how the migration file should look like:
``` php
    <?php

    class Alter_Position extends Tigra_Migration {
        public function up() {
            Mage::log('Upgrading the database...');

            // any magento code you like
        }

        public function down() {
            // the code to cancel changes in up() method
        }
    }
```

Shortcuts
---------
Inside migrations, you can execute any Magento code you like. For example, you can use features of Mage_Eav_Model_Setup model, that is available for you
by calling

``` php
$setup = $this->_getSetup();
```

in your migration file.

