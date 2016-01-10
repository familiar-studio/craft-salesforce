<?php
namespace Craft;

class SalesforceController extends BaseController
{
  private $scopes = array();
  private $params = array();

  protected $allowAnonymous = true;

  // sets up an enpoint for saleforce triggers to hit to clear the cache
  public function actionClearCache()
  {
    //map all the fields to the data object
    $data = craft()->request->getPost('data');

    SalesforcePlugin::log('this is saving');
    craft()->salesforce->save('Contact', $data, $id);

  }

  public function actionTest() {


    echo 'this';
    $test = array('this','that');

    $this->returnJson($test);

  }
}
