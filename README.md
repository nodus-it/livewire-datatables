# Livewire Datatables

[![License](https://poser.pugx.org/nodus-it/livewire-datatables/license)](//packagist.org/packages/nodus-it/livewire-datatables)
[![Latest Unstable Version](https://poser.pugx.org/nodus-it/livewire-datatables/v/unstable)](//packagist.org/packages/nodus-it/livewire-datatables)
[![Total Downloads](https://poser.pugx.org/nodus-it/livewire-datatables/downloads)](//packagist.org/packages/nodus-it/livewire-datatables)
[![Build Status](https://travis-ci.com/nodus-it/livewire-datatables.svg?branch=master)](https://travis-ci.com/nodus-it/livewire-datatables)
[![StyleCI](https://github.styleci.io/repos/311639565/shield?branch=master)](https://github.styleci.io/repos/311639565?branch=master)
[![codecov](https://codecov.io/gh/nodus-it/livewire-datatables/branch/master/graph/badge.svg)](https://codecov.io/gh/nodus-it/livewire-datatables)

_An awesome package for easy dynamic datatables with **Laravel Livewire** and **Bootstrap v4**._

## Installation
You can install the package via composer:
````
composer require nodus-it/livewire-datatables
````
You can publish the config file with:
````
php artisan vendor:publish --provider="Nodus\Packages\LivewireDatatables\LivewireDatatablesServiceProvider" --tag="livewire-datatables:config"
````
You can publish the blade views with:
````
php artisan vendor:publish --provider="Nodus\Packages\LivewireDatatables\LivewireDatatablesServiceProvider" --tag="livewire-datatables:views"
````

Now with the package installed we need to set up some things in order for things to properly work.

First of all, all prerequisites of the livewire library are of course required. For these steps consult: https://laravel-livewire.com/docs/2.x/installation

Second you should check the configs of the core package and this package and make sure all settings work for your project. Especially the blade stack names for styles and scripts could potentially differ from our defaults! 

Additionally, we need to include the styles of the livewire datatable package.
````php
@livewireDatatableStyles
````

## Usage
### General tooling
For information of the general tooling around this package (e.g. full component rendering, CSP support, ...) please consult the core package documentation under: https://github.com/nodus-it/livewire-core

### The ``DataTable`` component
Todo

### The ``ConfirmModal`` component
Add the confirm-modal component to your layout at entrance level of your documents body.
````html
<body>
    <!-- Much content... -->
    
    <livewire:livewire-datatables.confirm-modal/>
</body>
````
Now you're already good to go for the confirmation buttons of your datatables.

Furthermore, it's possible to use the confirm-modal component from outside the datatable aswell. You simply need to emit the ``confirm:show`` event:
````html
wire:click="$emit('confirm:show', 'route.name')"
````
With the third parameter it is possible to customize the modal texts and colors. For further details take a look inside the ``ConfirmModal`` class.

## Roadmap
- Support for search keys through multiple relations
- Support for sort keys through relations
- Advanced scopes
- More themes (Tailwind3, Bootstrap5)
- Fix the query rebuilding problem (currently using the IDs array) by using EloquentBuilder serialization

## Testing
````
composer test
````

## License
The MIT License (MIT). Please see [License File](LICENCE) for more information.
