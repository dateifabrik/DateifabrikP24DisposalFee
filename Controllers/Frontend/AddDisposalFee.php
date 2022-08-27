<?php

class Shopware_Controllers_Frontend_AddDisposalFee extends Enlight_Controller_Action
{

    // Controller wird nur bei change der Werte im select-Feld ausgeführt
    public function indexAction()
    {
        // hole den Wert der Option aus dem Formular-Request
        $applyLicenseFeeOption = $this->Request()->getPost('applyLicenseFee');        

        // schreibe den Wert einfach nur in die session,
        // behandelt wird er dann im BasketData-Subscriber
        $this->container->get('session')->offsetSet('applyLicenseFeeOption', $applyLicenseFeeOption);
        $this->redirect(array('controller' => 'checkout', 'action' => 'confirm'));        
   
    }

}