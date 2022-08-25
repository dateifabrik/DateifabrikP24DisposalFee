<?php

class Shopware_Controllers_Frontend_AddDisposalFee extends Enlight_Controller_Action
{

    public function indexAction()
    {
        //die('index-Action');
        $this->redirect(array('controller' => 'checkout', 'action' => 'confirm'));
    }

}