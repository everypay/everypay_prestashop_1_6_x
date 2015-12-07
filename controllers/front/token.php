<?php
//vaR_dump($_REQUEST);
class EverypaypaymentsTokenModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
                
                $this->module->submitToken();
	}
}