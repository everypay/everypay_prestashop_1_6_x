<?php
class EverypaypaymentsErrorModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

                $this->module->configurePaymentForm();

                $this->setTemplate('../hook/error.tpl');
	}
}