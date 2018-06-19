# minplate
A mini template engine for PHP

### Introduction
minplate is a mini template engine for PHP which has a very simple API and easy to learn. mintemplate use pure PHP so it's super fast and you don't have to learn new syntax. minplate support the below feature:
- assign variables for the template.
- creating block in the layout which can be override.
- include/inherite other template file.

That's all!

### API
```php
function __construct($template_path = './');
function assign(string $variable_name, $value);
function include(string $template_name);
function block(string $block_name);
function end_block(string $block_name);
function render(string $template_name, array $data = []);
```

### Examples
index.php
```php
<?php
use MinPlate\Template;

$template = new Template('../templates');
$template->assign('name', 'An');
return $template->render('page.tpl');
```

layout.tpl
```html+php
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $this->block('title'); ?><?php $this->end_block('title'); ?></title>
  </head>
  <body>
  <?php $this->block('content'); ?>
  <?php $this->end_block('content'); ?>
  </body>
</html>
```
page.tpl
```html+php
<?php $this->include('layout.tpl'); ?>

<?php $this->block('title'); ?>
Hello world!
<?php $this->end_block('title'); ?>

<?php $this->block('content'); ?>
  <section class="section">
    <div class="container">
      <h1 class="title">
        <?php $this->block('content_title'); ?>
        Hello <?= $name ?>!
        <?php $this->end_block('content_title'); ?>
      </h1>
      <p class="subtitle">
        My first website!
      </p>
    </div>
  </section>
<?php $this->end_block('content'); ?>
```
