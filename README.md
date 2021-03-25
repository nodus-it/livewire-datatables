# Livewire Datatables

[![License](https://poser.pugx.org/nodus-it/livewire-datatables/license)](//packagist.org/packages/nodus-it/livewire-datatables)
[![Latest Unstable Version](https://poser.pugx.org/nodus-it/livewire-datatables/v/unstable)](//packagist.org/packages/nodus-it/livewire-datatables)
[![Total Downloads](https://poser.pugx.org/nodus-it/livewire-datatables/downloads)](//packagist.org/packages/nodus-it/livewire-datatables)
[![Build Status](https://travis-ci.org/nodus-it/livewire-datatables.svg?branch=master)](https://travis-ci.org/nodus-it/livewire-datatables)
[![StyleCI](https://github.styleci.io/repos/311639565/shield?branch=master)](https://github.styleci.io/repos/311639565?branch=master)
[![codecov](https://codecov.io/gh/nodus-it/livewire-datatables/branch/master/graph/badge.svg)](https://codecov.io/gh/nodus-it/livewire-datatables)

_An awesome package for easy dynamic datatables with livewire._

## Installation

``composer require nodus-it/livewire-core:dev-master``

## Usage

### Full site Livewire component

If you want to use the datatable component without creating an extra blade file you can use our livewire component helper to output the datatable with
an existing layout file.

1. Include the `SupportsLivewire` Trait in your Controller
2. Use and return the `livewire` function at the end of your Controller function

```php
function index(){
    return $this->livewire(UserListView::class);
}
```

2.1 If you want to change the layout or section name use the fallowing functions

```php
function index(){
    return $this->livewire(UserListView::class)->layout('myfolder.layoutName')->section('myContentSection');
}
```

**Default Layout:** layouts.app

**Default Section:** content

2.2 To add additional parameters to the layout blade, you can use the second parameter of the `livewire` function

### Embedded livewire component

If you want to create your own blade file and use datatables inline, you can just use the default livewire commands

```php
@livewire('livewire-componenent-name')
```

## Roadmap

#### Near

- Support für SearchKeys über mehrere Relations
- Support für SortKeys über Relations
- Style anders lösen -> Nicht direkt über Bootstrap Klassen
- Modals einzeln erzeugen oder ein Modal mit Javascript-Handling manipulieren

#### Later

- Advanced scopes
- Tailwind Theme
