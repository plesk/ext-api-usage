<?php

class IndexController extends pm_Controller_Action
{

    protected $_accessLevel = 'admin';

    public function indexAction()
    {
        $this->view->pageTitle = $this->lmsg('formTitle');

        $form = new Modules_ApiUsage_Form_CreateClientAndWebspace();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $form->process();
            $this->_status->addMessage('info', $this->lmsg('objectsCreated'));
            $this->_helper->json(array('redirect' => pm_Context::getModulesListUrl()));
        }

        $this->view->form = $form;
    }
}
