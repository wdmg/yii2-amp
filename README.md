[![Progress](https://img.shields.io/badge/required-Yii2_v2.0.13-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Github all releases](https://img.shields.io/github/downloads/wdmg/yii2-amp/total.svg)](https://GitHub.com/wdmg/yii2-amp/releases/)
[![GitHub version](https://badge.fury.io/gh/wdmg/yii2-amp.svg)](https://github.com/wdmg/yii2-amp)
![Progress](https://img.shields.io/badge/progress-in_development-red.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-amp.svg)](https://github.com/wdmg/yii2-amp/blob/master/LICENSE)

# Yii2 Google AMP
AMP pages generator

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.20 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)
* [Yii2 Options](https://github.com/wdmg/yii2-options) module (optionality)
* [Yii2 Pages](https://github.com/wdmg/yii2-pages) module (support)
* [Yii2 News](https://github.com/wdmg/yii2-news) module (support)

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-amp"`

After configure db connection, run the following command in the console:

`$ php yii amp/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations
  3) Flush AMP cache

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-amp/migrations`

# Configure
To add a module to the project, add the following data in your configuration file:

    'modules' => [
        ...
        'amp' => [
            'class' => 'wdmg\amp\Module',
            'supportModels'  => [ // list of supported models for displaying a AMP pages
                'pages' => 'wdmg\pages\models\Pages',
                'news' => 'wdmg\news\models\News',
            ],
            'cacheExpire' => 3600, // cache lifetime, `0` - for not use cache
            'ampRoute' => '/' // default route to render AMP pages (use "/" - for root)
        ],
        ...
    ],

# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('amp')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [in progress development]
* v.1.0.1 - Added default layout and view for AMP pages
* v.1.0.0 - Added console, migrations and controller, support for Pages and News models