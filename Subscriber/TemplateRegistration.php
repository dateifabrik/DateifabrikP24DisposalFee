<?php

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;

class TemplateRegistration implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @var \Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @param $pluginDirectory
     * @param \Enlight_Template_Manager $templateManager
     */
    public function __construct($pluginDirectory, \Enlight_Template_Manager $templateManager)
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->templateManager = $templateManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onPreDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchFrontendCheckoutConfirm',
        ];
    }

    public function onPreDispatch()
    {
        $this->templateManager->addTemplateDir($this->pluginDirectory . '/Resources/views');
    }

    

    public function onPostDispatchFrontendCheckoutConfirm(\Enlight_Event_EventArgs $args)
    {

        $actionName = $args->getSubject()->request()->getQuery('action');
        if($actionName == 'confirm'){
            echo "<p>\$args->getSubject()->getBasket()</p>";
            dump($args->getSubject()->getBasket());
            $basketContent = $args->getSubject()->getBasket()['content'];
            $basketSessionID = $args->getSubject()->getBasket()['content'][0]['sessionID'];
            $basketUserID = $args->getSubject()->getBasket()['content'][0]['userID'];

            $i = 0;
            foreach($basketContent as $basketItem){
                echo "<p>\$args->getSubject()->getBasket()['content'][$i]</p>";
                dump($basketItem);
                $i++;
            }

            $sessionIdFromRepo = Shopware()->Models()->getRepository('Shopware\Models\Order\Basket');
            // hole die Daten des Warenkorbs
            echo "<p>\$sessionIdFromRepo->findBy(['sessionId' => \$basketSessionID])</p>";
            dump($sessionIdFromRepo->findBy(['sessionId' => $basketSessionID]));

            $userNameFromRepo = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
            // finde die userId mit Hilfe des Repos
            echo "<p>\$name = \$userNameFromRepo->find(\$basketUserID)->getFirstName(). ' '. \$userNameFromRepo->find(\$basketUserID)->getLastName()</p>";
            dump($name = $userNameFromRepo->find($basketUserID)->getFirstName(). ' '. $userNameFromRepo->find($basketUserID)->getLastName());

            // Shopware => Core => sBasket.php
            // $module = Shopware()->Modules()->Basket();
            // dump($module);

            die();            

        }

    }



}