<?php
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/sfDynamics.class.php');


class ManagerMock extends sfDynamicsManager
{
  public function generateCssHtml()
  {
    return '<link rel="stylesheet" type="text/css" media="screen" href="/dynamics/foo.css" />';
  }
  public function generateJsHtml()
  {
    return '<script type="text/javascript" src="/dynamics/foo.js"></script>';
  }
}

$t = new lime_test(3, new lime_output_color());


if (!sfContext::hasInstance())
{
  require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
  sfCoreAutoload::register();
  require_once dirname(__FILE__).'/../../fixtures/project/config/ProjectConfiguration.class.php';
  sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration('frontend', 'test', isset($debug) ? $debug : true));
  if (!sfContext::hasInstance())
  {
    $t->error('A context instance is required');
    die();
  }
  $t->info('A frontend context has been initialized for tests');
}


$t->comment('->addSfDynamicsTags()');
sfConfig::set('app_sfDynamicsPlugin_manager', 'ManagerMock');
$manager = sfDynamics::getManager();
$t->info('New placeholder system');
$content = <<<END
<html>
  <head>
    <!-- Include sfDynamics css tags -->
    <link rel="stylesheet" type="text/css" media="screen" href="/bar.css" />
  </head>
  <body>
    <p>Lorem ipsum</p>

    <!-- Include sfDynamics js tags -->
    <script type="text/javascript" src="/bar.js"></script>
  </body>
</html>
END;
$waited = <<<END
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="screen" href="/dynamics/foo.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/bar.css" />
  </head>
  <body>
    <p>Lorem ipsum</p>

    <script type="text/javascript" src="/dynamics/foo.js"></script>
    <script type="text/javascript" src="/bar.js"></script>
  </body>
</html>
END;
$t->is(
  $manager->filterContent(new sfEvent('lorem', 'event.name'), $content),
  $waited,
  '->addSfDynamicsTags() replaces placeholder by sfDynamics tags'
);

$t->info('Backward compatibility tests: content has no placeholder');
if ('append' !== sfDynamicsConfig::getAssetsPositionInHead())
{
  $t->error('We can’t test append inclusion');
  $t->skip('skip 1 tests', 1);
}
else
{
  $content = <<<END
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="screen" href="/bar.css" />
    <script type="text/javascript" src="/bar.js"></script>
  </head>
  <body>
    <p>Lorem ipsum</p>
  </body>
</html>
END;
  $waited = <<<END
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="screen" href="/bar.css" />
    <script type="text/javascript" src="/bar.js"></script>
  <link rel="stylesheet" type="text/css" media="screen" href="/dynamics/foo.css" />
<script type="text/javascript" src="/dynamics/foo.js"></script>
</head>
  <body>
    <p>Lorem ipsum</p>
  </body>
</html>
END;
  $t->is(
    $manager->filterContent(new sfEvent('lorem', 'event.name'), $content),
    $waited,
    '->addSfDynamicsTags() inserts tags at the bottom of <head> if position "append" and placeholder not found "(default)"'
  );
}

sfConfig::set('app_sfDynamicsPlugin_assets_position_in_head', 'prepend');
if ('prepend' !== sfDynamicsConfig::getAssetsPositionInHead())
{
  $t->error('We can’t test prepend inclusion');
  $t->skip('skip 1 tests', 1);
}
else
{
  $content = <<<END
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="screen" href="/bar.css" />
    <script type="text/javascript" src="/bar.js"></script>
  </head>
  <body>
    <p>Lorem ipsum</p>
  </body>
</html>
END;
  $waited = <<<END
<html>
  <head>
<script type="text/javascript" src="/dynamics/foo.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/dynamics/foo.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/bar.css" />
    <script type="text/javascript" src="/bar.js"></script>
  </head>
  <body>
    <p>Lorem ipsum</p>
  </body>
</html>
END;
  $t->is(
    $manager->filterContent(new sfEvent('lorem', 'event.name'), $content),
    $waited,
    '->addSfDynamicsTags() inserts tags at the beginning of <head> if position "prepend" and placeholder not found'
  );
}
