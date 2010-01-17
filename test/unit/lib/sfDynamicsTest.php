<?php
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/sfDynamics.class.php');



class ManagerMock extends sfDynamicsManager
{
}

$t = new lime_test(1, new lime_output_color());

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

$t->comment('::getManager()');
sfConfig::set('app_sfDynamicsPlugin_manager', 'ManagerMock');
$t->isa_ok(sfDynamics::getManager(), 'ManagerMock', '::getManager() the manager class can be customized in app.yml');