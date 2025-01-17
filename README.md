KitpagesDataGridBundle
========================

[![Latest Stable Version](https://img.shields.io/packagist/v/vysokeskoly/data-grid-bundle.svg)](https://packagist.org/packages/vysokeskoly/data-grid-bundle)
[![License](https://img.shields.io/packagist/l/vysokeskoly/data-grid-bundle.svg)](https://packagist.org/packages/vysokeskoly/data-grid-bundle)
[![PHP - Checks](https://github.com/vysokeskoly/KitpagesDataGridBundle/actions/workflows/php-checks.yaml/badge.svg)](https://github.com/vysokeskoly/KitpagesDataGridBundle/actions/workflows/php-checks.yaml)
[![Coverage Status](https://coveralls.io/repos/github/vysokeskoly/KitpagesDataGridBundle/badge.svg?branch=master)](https://coveralls.io/github/vysokeskoly/KitpagesDataGridBundle?branch=master)

This Symfony Bundle is a simple datagrid bundle. It aims to be easy to use and extensible.

## Warning version 3

Version 3 is for symfony ~3.3 and ~4.0 and PHP 7, and only twig ~1.8 

Version 3 is here. You can switch to branch 2.x (or tags 2.x) if you want to stay on legacy version.

There is no BC break in the usage between version 2 and version 3, but the version
3 is not compatible with symfony < 3.3.

## Warning version 2

Version 2 is here. You can switch to branch 1.x (or tags 1.x) if you want to stay on legacy version.
There are BC Breaks between version 1 and version 2.

Actual state
============

see CHANGELOG.md

Features
========

* Display a Data Grid from a Doctrine 2 Query Builder
* Automatic filter
* Sorting on columns
* Easy to configure
* Easy to extend
* Documented (in this readme for basics and in Resources/doc for advanced topics)
* Paginator can be used as a standalone component
* Change of DataGrid behaviour with events
* Change of DataGrid presentation with twig embeds

System Requirement
==================
* jQuery has to be present on your pages
* version 1.8+ of twig is mandatory (use of twig embeds)

Documentation
=============

The documentation is in this README and in [Resources/doc](https://github.com/kitpages/KitpagesDataGridBundle/tree/master/Resources/doc)

* [Installation and simple user's guide : In this README](#installation)
* [Grid Extended Use](https://github.com/kitpages/KitpagesDataGridBundle/tree/master/Resources/doc/10-GridExtendedUse.md)
* [Standalone Paginator](https://github.com/kitpages/KitpagesDataGridBundle/tree/master/Resources/doc/20-StandalonePaginator.md)
* [Events for extended use case](https://github.com/kitpages/KitpagesDataGridBundle/tree/master/Resources/doc/30-Events.md)


Installation
============
You need to add the following lines in your deps :

Add KitpagesChainBundle in your composer.json

```json
{
    "require": {
        "vysokeskoly/data-grid-bundle": "^5.0"
    }
}
```

Now tell composer to download the bundle by running the step:

```bash
$ php composer.phar update vysokeskoly/data-grid-bundle
```

AppKernel.php

```php
$bundles = [
    ...
    new Kitpages\DataGridBundle\KitpagesDataGridBundle(),
];
```

Configuration in config.yml
===========================

These values are default values. You can skip the configuration if it is ok for you.

```yaml
kitpages_data_grid:
    grid:
        default_twig: @KitpagesDataGrid/Grid/grid.html.twig
    paginator:
        default_twig: @KitpagesDataGrid/Paginator/paginator.html.twig
        item_count_in_page: 50
        visible_page_count_in_paginator: 5
```

Note you can use the followin configuration in order to user Bootstrap 3 :

```yaml
kitpages_data_grid:
    grid:
        default_twig: @KitpagesDataGrid/Grid/bootstrap3-grid.html.twig
    paginator:
        default_twig: @KitpagesDataGrid/Paginator/bootstrap3-paginator.html.twig
```


Simple Usage example
====================

## In the controller

```php
use Kitpages\DataGridBundle\Grid\GridConfig;
use Kitpages\DataGridBundle\Grid\Field;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController
{
    public function productListAction(Request $request): Response
    {
        // create query builder
        $repository = $this->getDoctrine()->getRepository('AcmeStoreBundle:Product');
        $queryBuilder = $repository->createQueryBuilder('item')
            ->where('item.price > :price')
            ->setParameter('price', '19.90')
        ;

        $gridConfig = new GridConfig($queryBuilder, 'item.id');
        $gridConfig
            ->addField('item.id')
            ->addField('item.slug', ['filterable' => true])
            ->addField('item.updatedAt', [
                'sortable' => true,
                'formatValueCallback' => fn ($value) => $value->format('Y/m/d')
            ])
        ;

        $gridManager = $this->get('kitpages_data_grid.grid_manager');
        $grid = $gridManager->getGrid($gridConfig, $request);

        return $this->render('AppSiteBundle:Default:productList.html.twig', [
            'grid' => $grid
        ]);
    }
}
```

## Twig associated

In your twig you just have to put this code to display the grid you configured.

    {% embed kitpages_data_grid.grid.default_twig with {'grid': grid} %}
    {% endembed %}

More advanced usage
===================

## In the controller

same controller than before

## Twig associated

If you want to add a column on the right of the table, you can put this code in your twig.

    {% embed kitpages_data_grid.grid.default_twig with {'grid': grid} %}

        {% block kit_grid_thead_column %}
            <th>Action</th>
        {% endblock %}

        {% block kit_grid_tbody_column %}
            <td><a href="{{ path ("my_route", {"id": item['item.id']}) }}">Edit</a></td>
        {% endblock %}

    {% endembed %}

More advanced usage
===================

## In the controller

```php
use Kitpages\DataGridBundle\Grid\GridConfig;
use Kitpages\DataGridBundle\Grid\Field;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function listAction(Request $request, $state): Response
    {
        // create query builder
        $em = $this->get('doctrine')->getEntityManager();
        $queryBuilder = $em->createQueryBuilder()
            ->select('m, e, c')
            ->from('KitappMissionBundle:Mission', 'm')
            ->leftJoin('m.employee', 'e')
            ->leftJoin('m.client', 'c')
            ->where('m.state = :state')
            ->add('orderBy', 'm.updatedAt DESC')
            ->setParameter('state', $state)
        ;

        $gridConfig = new GridConfig($queryBuilder, 'm.id');
        $gridConfig
            ->addField('m.title', ['label' => 'title', 'filterable' => true])
            ->addField('m.country', ['filterable' => true])
            ->addField('c.corporation', ['filterable' => true])
            ->addField('e.lastname', ['filterable' => true])
            ->addField('e.email', ['filterable' => true])
        ;

        $gridManager = $this->get('kitpages_data_grid.grid_manager');
        $grid = $gridManager->getGrid($gridConfig, $request);

        return $this->render('KitappMissionBundle:Admin:list.html.twig', [
            'grid' => $grid
        ]);
    }
}
```

## Twig associated

same Twig than before

Field "as"
==========

For request like

```php
$queryBuilder->select("item, item.id * 3 as foo");
```

You can display the foo field with

```php
$gridConfig->addField("item.id");
$gridConfig->addField("foo");
```


Events
======

You can modify the way this bundle works by listening events and modify some
objects injected in the $event.

see the event documentation in Resources/doc/30-Events.md

Tags
====

Tag system is used to get some fields by tags. When you create a field, you can
define some tags associated to this field. After that, in the grid config, you can
find the fields that match this tag.

```php
// add tag as the third parameter of the field 
$gridConfig->addField("item.id", [], ['foo', 'bar']);
$gridConfig->addField("foo", [], ['myTag', 'foo']);

// get fieldList matching 'bar' tag. There is only one result.
$fieldList = $gridConfig->getFieldListByTag('bar');
$fieldList[0] // -> this is the first Field (which name is 'item.id')
```

Custom class for the $grid object
=================================

By default, the following line
```php
$grid = $gridManager->getGrid($gridConfig, $request);
```
returns an object of the type Grid

You can use your own subclass of Grid. By default the GridManager creates the instance $grid of Grid but you can create the instance by yourself.

```php
class CustomGrid extends Grid
{
	public $myParameter;
}
$myCustomGrid = new CustomGrid();
$grid = $gridManager->getGrid($gridConfig, $request,$myCustomGrid);

// now the $grid object is an instance of CustomGrid (it 
// is exactly the same object than $myCustomGrid, not cloned)
```


Reference guide
===============

## Add a field in the gridConfig

when you add a field, you can set these parameters :

```php
$gridConfig->addField('slug', [
    'label' => 'Mon slug',
    'sortable' => false,
    'visible' => true,
    'filterable' => true,
    'translatable' => true,
    'formatValueCallback' => fn ($value) => strtoupper($value),
    'autoEscape' => true,
    'category' => null, // only used by you for checking this value in your events if you want to...
    'nullIfNotExists' => false, // for leftJoin, if value is not defined, this can return null instead of an exception
]);
```

TIP: Use `FieldOption` enum
```php
$gridConfig->addField('slug', [
    FieldOption::Label->value => 'Mon slug',
    FieldOption::Sortable->value => false,
    FieldOption::Visible->value => true,
    FieldOption::Filterable->value => true,
    FieldOption::Translatable->value => true,
    FieldOption::FormatValueCallback->value => fn ($value) => strtoupper($value),
    FieldOption::AutoEscape->value => true,
    FieldOption::Category->value => null, // only used by you for checking this value in your events if you want to...
    FieldOption::NullIfNotExists->value => false, // for leftJoin, if value is not defined, this can return null instead of an exception
]);
```

## What can you personalize in your twig template

With the embed system of twig 1.8 and more, you can override some parts of the default
rendering (see example in the "More advanced usage" paragraph).

You can consult the base twig template here to see what you can personalize.
https://github.com/kitpages/KitpagesDataGridBundle/blob/master/Resources/views/Grid/grid.html.twig
