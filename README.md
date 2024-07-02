Drupal core upgrade instructions via composer
-----------------------------------------------------------------
https://www.drupal.org/docs/updating-drupal/updating-drupal-core-via-composer

##Take DB back before starting the process of upgrading core or modules

###THESE INSTRUCTIONS NEED TO BE APPLIED ON LOCAL THEN PUSHED TO HIGHER ENVs
To update only Drupal core without any modules or themes, use:
* composer update drupal/core-recommended --with-dependencies

To update Drupal core and all dependencies, use:
* composer update drupal/core 'drupal/core-*' --with-all-dependencies

###ON HIGHER ENVs.
* composer install

##cache clear
Run vendor/drush/drush/drush cr

##update db after core upgrade
Run vendor/drush/drush/drush updatedb

##CSS changes are applied to the files under
themes/custom/sabc/scss/custom/_variables.scss and
themes/custom/sabc/scss/overrides/_variables.scss

##Iggy CSS file location
themes/custom/sabc/assets/css/app.css

##Iggy CSS file override location
themes/custom/sabc/scss/overrides/iggy.scss

##How to install SASS globally
     SASS is used to compile scss to css

    - If you use Node.js, you can also install Sass using npm by running
        npm install -g sass

    - If you use the Chocolatey package manager for Windows, you can install Dart Sass by running
        choco install sass

    - Compile scss command
        sass  themes/custom/sabc/scss/style.scss themes/custom/sabc/css/custom.css
