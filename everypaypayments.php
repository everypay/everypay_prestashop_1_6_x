<?php
/*
 * 2007-2012 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2012 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
    exit;

define('EVERYPAY_BASE_FOLDER', 'everypaypayments');

/*
 * Everypay library
 */
require_once (_PS_MODULE_DIR_ . EVERYPAY_BASE_FOLDER .  '/everypay-php-master/init.php');
/*
use Everypay\Everypay;
use Everypay\Payment as EverypayPayment;
use Everypay\Customer as EverypayCustomer;
 */

class EverypayPayments extends PaymentModule {

    const OPEN = 1;
    const SUCCESS = 2;
    const ERRORNEOUS = 3;
    const CLOSED = 0;
    const ORDER_STATUS_CAPTURE_PENDING = 11; //Awaiting paypal payment

    public  $configuration;

    private $online;
    private $adminMessages;
    private $defaults;
    private $redirectOnCheck;
    private $pk;
    private $sk;

    public function __construct() {
        $this->name = 'everypaypayments';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.14';
        $this->author = 'Everypay';
        $this->adminMessages = array(
            'warnings' => array(),
            'errors' => array(),
            'success' => array()
        );
        $this->online = true;
        $this->displayName = $this->l('Everypay Card payments');
        $this->description = $this->l('Accept credit/debit card payments through Everypay service');

        $this->redirectOnCheck = true;

        $this->live_mode = 0;
        $this->mode = 'test';
        $this->pk = $this->sk = '';

        $this->defaults = array(
            'EVERYPAY_PUBLIC_KEY'      => '',
            'EVERYPAY_SECRET_KEY'      => '',
            'EVERYPAY_CUSTOMER_MODE'   => false,
            'EVERYPAY_BUTTON_MODE'     => true,
            'EVERYPAY_LIVE_MODE'       => false,
            'EVERYPAY_TEST_PUBLIC_KEY' => '',
            'EVERYPAY_TEST_SECRET_KEY' => '',
        );

        $this->_loadConfiguration();

        parent::__construct();
    }

    /**
     * Install the plugin
     *
     */
    public function install() {
        if (
                !parent::install()
                || !$this->registerHook('payment')
                || !$this->registerHook('header')
                || !$this->registerHook('backOfficeHeader')
                || !$this->registerHook('paymentReturn')
                || !$this->registerHook('displayMyAccountBlock')
                || !$this->registerHook('displayCustomerAccount')
                || !$this->_installdb()
                || !$this->_initConfiguration()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall the plugin
     *
     */
    public function uninstall() {
        if (!parent::uninstall()
                || !$this->_deleteConfiguration()
                || !$this->_uninstallDB()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Database installation
     *
     */
    private function _installdb() {

        $table1 = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everypay_customers` (
  `id_customer_token` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_last_four` varchar(10) NOT NULL,
  `card_has_expired` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
  `card_type` varchar(50) NOT NULL,
  `cus_token` varchar(50) NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
  `exp_month` tinyint(1) unsigned DEFAULT NULL,
  `exp_year` smallint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_customer_token`),
  KEY `idcust2` (`id_customer`),
  CONSTRAINT `idcust2` FOREIGN KEY (`id_customer`) REFERENCES `' . _DB_PREFIX_ . 'customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';


$table2 = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everypay_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `crd_token` varchar(50) DEFAULT NULL,
  `id_cart` int(10) unsigned NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `status` int(10) NOT NULL,
  `id_currency` int(10) unsigned NOT NULL,
  `pmt_token` varchar(50) DEFAULT NULL,
  `id_order` int(10) unsigned DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `amount` bigint(10) unsigned NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_customer_token` int(10) unsigned DEFAULT NULL,
  `save_customer` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart2` (`id_cart`) USING BTREE,
  KEY `order2` (`id_order`) USING BTREE,
  KEY `currency_is_foreign` (`id_currency`) USING BTREE,
  KEY `customer_is_foreign` (`id_customer`),
  KEY `id_customer_is_foreign` (`id_customer_token`),
  CONSTRAINT `cart_is_foreign` FOREIGN KEY (`id_cart`) REFERENCES `' . _DB_PREFIX_ . 'cart` (`id_cart`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `currency_is_foreign` FOREIGN KEY (`id_currency`) REFERENCES `' . _DB_PREFIX_ . 'currency` (`id_currency`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_is_foreign` FOREIGN KEY (`id_customer`) REFERENCES `' . _DB_PREFIX_ . 'customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `id_customer_is_foreign` FOREIGN KEY (`id_customer_token`) REFERENCES `' . _DB_PREFIX_ . 'everypay_customers` (`id_customer_token`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_is_foreign` FOREIGN KEY (`id_order`) REFERENCES `' . _DB_PREFIX_ . 'orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        if (!Db::getInstance()->Execute($table1) || !Db::getInstance()->Execute($table2)){
            return false;
        }

        return true;
    }

    /**
     * Database drop tables
     *
     * @return boolean
     */
    private function _uninstallDb() {

        $drop1 = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everypay_tokens`');
        $drop2 = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everypay_customers`');

        if ($drop1 && $drop2) {
            return true;
        }

        return false;
    }

    /**
     * Function to make an html list of the errors/warnings/success or messages
     *
     * @param type $mode
     * @return string
     */
    private function _displayAdminMessages($mode = 'errors') {
        $available_modes = array('warnings', 'success', 'errors');
        if (!in_array($mode, $available_modes)) {
            return '';
        }

        switch ($mode) {
            case 'warnings':
                $array = $this->adminMessages['warnings'];
                $class = 'warn';
                break;

            case 'success':
                $array = $this->adminMessages['success'];
                $class = 'success conf';
                break;

            case 'errors':
                $array = $this->adminMessages['errors'];
                $class = 'error';
                break;

            default:
                return null;
                break;
        }
        $html = '';

        if (!empty($array)) {
            $unique_errors = array();

            foreach ($array as $key => $index) {
                $unique_errors[md5($index)] = $index;
            }
            $html = implode('</li></ul></div><div class="' . $class . '"><ul><li>', $unique_errors);
            $html = (empty($html)) ? '' : '<div class="' . $class . '"><ul><li>' . $html . '</li></ul></div>';
        }
        return $html;
    }

    /**
     * Init the configuration array.
     *
     * Note: If you want a configuration value that don't need to be saved
     * in the database (like available currencies), setup it in here.
     */
    private function _loadConfiguration() {
        $config = array();

        foreach ($this->defaults as $key => $value) {
            $config[$key] = Configuration::get($key);
        }

        $config = $this->_validateConfiguration($config);

        //DO NOT TOUCH THIS LINE HERE
        $config['EVERYPAY_ACCEPTED_CURRENCIES'] = array('EUR');
        $config['EVERYPAY_EXPIRATION_SECONDS'] = 10 * 60; //5 minutes

        if ($this->live_mode) {
            $this->pk = $config['EVERYPAY_PUBLIC_KEY'];
            $this->sk = $config['EVERYPAY_SECRET_KEY'];
        } else {
            Everypay\Everypay::$isTest = true;
            $this->pk = $config['EVERYPAY_TEST_PUBLIC_KEY'];
            $this->sk = $config['EVERYPAY_TEST_SECRET_KEY'];
        }

        //General smarty variables
        $generalSmarty = array(
            'EVERYPAY_CUSTOMER_MODE' => $config['EVERYPAY_CUSTOMER_MODE'],
            'EVERYPAY_BUTTON_MODE'   => $config['EVERYPAY_BUTTON_MODE'],
            'EVERYPAY_MODULE_NAME'   => $this->name,
            'EVERYPAY_LIVE_MODE'     => $this->live_mode,
            'EVERYPAY_PUBLIC_KEY'    => $this->pk,
            'EVERYPAY_TEMPLATE_DIR'  => (isset($module_template_dir) && !empty($module_template_dir)) ? ($module_template_dir) : _MODULE_DIR_ . $this->name . '/'
        );

        $config['EVERYPAY_LIVE_MODE'] = $this->live_mode;
        Context::getContext()->smarty->assign($generalSmarty);

        $this->configuration = $config;

        return $config;
    }

    /**
     * Validate the post configuration parameters
     * and remove if any invalid found
     *
     *
     * @param array $params
     * @param boolean $remove
     * @return array
     */
    private function _validateConfiguration($params, $remove = false) {
        if ((int) $params['EVERYPAY_LIVE_MODE'] >= 1) {
            $pk = $params['EVERYPAY_PUBLIC_KEY'];
            $sk = $params['EVERYPAY_SECRET_KEY'];
            $this->live_mode = $params['EVERYPAY_LIVE_MODE'] = 1;
            $this->mode = 'live';
        } else {
            $pk = $params['EVERYPAY_TEST_PUBLIC_KEY'];
            $sk = $params['EVERYPAY_TEST_SECRET_KEY'];
            $this->live_mode = $params['EVERYPAY_LIVE_MODE'] = 0;
            $this->mode = 'test';
        }

        //now set the keys according to mode
        $this->pk = $params['pk'] = $pk;
        $this->sk = $params['sk'] = $sk;

        if (empty($pk)) {
            $tmpError = str_replace('%MODE', $this->mode, $this->l('Missing PUBLIC KEY (%MODE mode)'));
            $this->adminMessages['errors'][] = $tmpError;
            $this->warning = $tmpError;
            $this->online = false;
        } else {
            try {
                Everypay\Everypay::setApiKey($sk);
            } catch (Exception $e) {
                $this->online = false;
                $this->adminMessages['errors'][] = $e->getMessage();
            }
        }

        if (empty($sk)) {
            $tmpError = str_replace('%MODE', $this->mode, $this->l('Missing SECRET KEY (%MODE mode)'));
            $this->adminMessages['errors'][] = $tmpError;
            $this->warning = $tmpError;
            $this->online = false;
        }

        /**
         * Check to determine if PHP enviroment
         * does have the needed extensions available, such as json and curl.
         */
        try {
            Everypay\Everypay::checkRequirements();
        } catch (RuntimeException $e) {
            $tmpError = $this->l('Please ensure you have json and curl extensions enabled in php');
            $this->adminMessages['errors'][] = $tmpError;
            $this->warning = $tmpError;
            $this->online = false;
        }

        //strict values
        if (!empty($params['EVERYPAY_CUSTOMER_MODE']) && (int) $params['EVERYPAY_CUSTOMER_MODE'] == 1) {
            $params['EVERYPAY_CUSTOMER_MODE'] = 1;
        } else {
            $params['EVERYPAY_CUSTOMER_MODE'] = 0;
        }

        if (!empty($params['EVERYPAY_BUTTON_MODE']) && (int) $params['EVERYPAY_BUTTON_MODE'] == 1) {
            $params['EVERYPAY_BUTTON_MODE'] = 1;
        } else {
            $params['EVERYPAY_BUTTON_MODE'] = 0;
        }
        //overwrite
        $params['EVERYPAY_BUTTON_MODE'] = 1;

        if ((_PS_VERSION_ < '1.5')) {
            $this->adminMessages['errors'][] = $this->l('This plugin supports prestashop versions > 1.5!');
            $this->warning = $this->l('The EveryPay plugin supports prestashop versions from 1.5 an later! Install the 1.4 version instead.');
            $this->online = false;
        }

        return $params;
    }

    /**
     * Init the configuration parameters in the PS database. The default
     * values are from the array $this->defaults
     *
     * @return boolean
     */
    private function _initConfiguration() {
        try {
            foreach ($this->defaults as $key => $value) {
                Configuration::updateValue($key, $value);
            }
        } catch (Exception $e) {
            return false;
        }

        Everypay\Everypay::throwExceptions(true);
        return true;
    }

    /**
     * Update the configuration of module
     *
     * @param array $params
     * @return null
     */
    private function _updateConfiguration($params) {
        foreach ($params as $key => $value) {
            Configuration::updateValue($key, $value);
        }

        $this->_loadConfiguration();

        if (Tools::isSubmit('submitConfiguration')) {
            if (!(empty($this->adminMessages['warnings']) && empty($this->adminMessages['warnings']))) {
                $this->adminMessages['success'][] = $this->l('Updated with some warnings');
            } else {
                $this->adminMessages['success'][] = $this->l('Successfully updated');
            }
        }
        return null;
    }

    /**
     * Delete configuration
     */
    private function _deleteConfiguration() {
        Configuration::deleteByName('EVERYPAY_PUBLIC_KEY');
        Configuration::deleteByName('EVERYPAY_SECRET_KEY');
        Configuration::deleteByName('EVERYPAY_CUSTOMER_MODE');
        Configuration::deleteByName('EVERYPAY_BUTTON_MODE');
        Configuration::deleteByName('EVERYPAY_LIVE_MODE');
        Configuration::deleteByName('EVERYPAY_TEST_PUBLIC_KEY');
        Configuration::deleteByName('EVERYPAY_TEST_SECRET_KEY');

        return true;
    }

    /**
     * Validate the submitted values from the forms
     *
     * @return array
     */
    private function _postValidation() {
        $params = array(
            'EVERYPAY_PUBLIC_KEY'    => Tools::getValue('input_public_key'),
            'EVERYPAY_SECRET_KEY'    => Tools::getValue('input_secret_key'),
            'EVERYPAY_CUSTOMER_MODE' => Tools::getValue('input_customer_mode'),
            'EVERYPAY_BUTTON_MODE'   => Tools::getValue('input_button_mode'),
            'EVERYPAY_LIVE_MODE'     => Tools::getValue('mode'),
            'EVERYPAY_TEST_PUBLIC_KEY' => Tools::getValue('input_test_public_key'),
            'EVERYPAY_TEST_SECRET_KEY' => Tools::getValue('input_test_secret_key')
        );

        $this->adminMessages = array(
            'errors'   => array(),
            'warnings' => array(),
            'success'  => array(),
        );

        $validation = $this->_validateConfiguration($params, true);

        return $validation;
    }

    /**
     * Post proccess the submitted values of admin settings
     *
     */
    private function _postProcess() {
        if (Tools::isSubmit('submitConfiguration')) {
            $params = $this->_postValidation();

            $this->_updateConfiguration($params);
        }
    }

    /**
     * Fetches a template
     *
     * @param string $name
     * @return string
     */
    private function _fetchTemplate($name) {
        if (_PS_VERSION_ < '1.4')
            $this->context->smarty->currentTemplate = $name;
        elseif (_PS_VERSION_ < '1.5') {
            $views = 'templates/';
            if (@filemtime(dirname(__FILE__) . '/' . $name))
                return $this->display(__FILE__, $name);
            elseif (@filemtime(dirname(__FILE__) . '/' . $views . 'hook/' . $name))
                return $this->display(__FILE__, $views . 'hook/' . $name);
            elseif (@filemtime(dirname(__FILE__) . '/' . $views . 'front/' . $name))
                return $this->display(__FILE__, $views . 'front/' . $name);
            elseif (@filemtime(dirname(__FILE__) . '/' . $views . 'back/' . $name))
                return $this->display(__FILE__, $views . 'back/' . $name);
        }

        return $this->display(__FILE__, $name);
    }

    /**
     * Display the form in the admin backpanel
     *
     * @return string
     */
    public function getContent() {
        $this->_postProcess();

        $this->context->smarty->assign(array(
            'adminMessages' => array(
                'warnings' => $this->_displayAdminMessages('warnings'),
                'errors' => $this->_displayAdminMessages('errors'),
                'success' => $this->_displayAdminMessages('success')
            ),
            'EVERYPAY_CONFIGURATION' => $this->configuration,
            'trans' => array(
                'What\'s this?' => $this->l('What\'s this?'),
                'Choose Mode'   => $this->l('Choose Mode'),
                'Save Settings' => $this->l('Save Settings'),
                'Register'      => $this->l('Register'),
                'Are you NEW to EveryPay? You should' => $this->l('Are you NEW to EveryPay? You should'),
                'Sign Up'       => $this->l('Sign Up'),
                'EveryPay account settings' => $this->l('EveryPay account settings'),
                'Public Key'    => $this->l('Public Key'),
                'Secret Key'    => $this->l('Secret Key'),
                'Where can I find these?' => $this->l('Where can I find these?'),
                'Other Settings'          => $this->l('Other Settings'),
                'Enable customers to save cards' => $this->l('Enable customers to save cards'),
                'Enable quick button'            => $this->l('Enable quick button'),
                'Accept payments with VISA/Mastercard safely, through EveryPay' => $this->l('Accept payments with VISA/Mastercard safely, through EveryPay')
            )
        ));

        $output = $this->_fetchTemplate('views/templates/back/admin.tpl');

        return $output;
    }

    /**
     * Find a customer by his id_customer_id
     *
     * @param type $cus_id
     * @return array or null
     */
    private function _findCustomerTokenWithId($cus_id) {
        $cart = Context::getContext()->cart;

        $sql = 'SELECT * FROM '
                . _DB_PREFIX_ . 'everypay_customers
            WHERE id_customer_token=' . (int) ($cus_id) . ' AND
                id_customer = ' . (int) $cart->id_customer
                . ' AND active=1';
        //echo $sql;
        $results = Db::getInstance()->getRow($sql);

        if (empty($results))
            return null;
        return $results;
    }

    /**
     * Find an open token with the submitted id. If found it means
     *  it can be used.
     *
     * @param int $token
     * @return a token row or false if not found
     */
    private function _canUseToken($token) {
        $cart = Context::getContext()->cart;

        $params = array(
            'id' => $token,
            'status' => self::OPEN,
            'id_cart' => (int) $cart->id,
            'id_order' => NULL,
            _DB_PREFIX_ . 'everypay_tokens.id_customer' => (int) $cart->id_customer
        );

        $result = $this->_getCardToken($params);

        if (!$result) {
            return false;
        }

        if ($result[0]['secsDiff'] > $this->configuration['EVERYPAY_EXPIRATION_SECONDS']) {
            $closeParams = array_merge($$result[0],array(
                'status' => self::ERRORNEOUS,
                'message' => 'Expired'
            ));
            $this->_closeToken($closeParams);
            return false;
        }

        return $result[0];
    }

    /**
     * Check if the details in the the Token record in the database are the
     * same with the ones in the current cart
     *
     * @param array $tokenRow
     * @return boolean
     */
    private function _tokenDetailsHaveChanged($tokenRow) {
        $cart = Context::getContext()->cart;

        $total = (int)Tools::ps_round($cart->getOrderTotal()*100);
        $totalOnRow = (int) $tokenRow['amount'];
        $id_currency = (int) $cart->id_currency;
        $id_currencyOnRow = (int) $tokenRow['id_currency'];

        if ($total != $totalOnRow || $id_currency != $id_currencyOnRow) {
            return true;
        }

        return false;
    }

    /**
     * Update the Token record in the database
     * according to the current values in cart
     *
     * @param int $token
     * @return boolean
     */
    private function _updateDetailsOfToken($token) {
        $cart = Context::getContext()->cart;

        $where = 'id=' . (int) $token;

        $data = array(
            'amount' => (int)Tools::ps_round($cart->getOrderTotal()*100),
            'id_currency' => (int) $cart->id_currency,
        );

        $update = Db::getInstance()->update('everypay_tokens', $data, $where);
        if ($update) {
            return true;
        }

        return false;
    }

    /**
     * Redirect to the standalone payment form. Applies mostly
     * when an error has been found.
     *
     * @param int $messageCode
     */
    private function _redirectToCardForm($messageCode = 505) {

        $redirectMsg = !is_null($messageCode) ? array('msg' => $messageCode) : NULL;

        Tools::redirect(Context::getContext()->link->getModuleLink($this->name, 'error', $redirectMsg, true));
        exit;
    }

    /**
     * Return the error to show in the card form
     *
     * @param int $index
     * @return string
     */
    private function _frontMessages($index = 505) {
        $messages = array(
            405 => $this->l('An error has occured with your payment proccess. Please try again.'),
            410 => $this->l('The Amount or Currency in your Cart have changed. Please review and confirm your order.'),
            415 => $this->l('There seems to be a problem while paying with your card. Please make sure that it has not expired, the number and CVV fields are correct, and that the card is eligible to make payments. Alternatively you can try another card'),
            505 => $this->l('Your token has expired or is invalid. Please try again.'),
        );

        if (isset($messages[(int) $index])) {
            return $messages[(int) $index];
        }
        return $this->l('Unknown error');
    }

    /**
     * Closes a token record.
     *
     * @param array $params
     * @return boolean
     */
    private function _closeToken($params) {

        $where = 'id=' . (int) $params['tokenRow']['id'];

        $data = array(
            'amount'      => (int) $params['amountInteger'],
            'id_currency' => (int) $params['cart']->id_currency,
            'id_cart'     => (int) $params['cart']->id,
            'id_customer' => (int) $params['cart']->id_customer,
            'status'      => (int) $params['status']
        );

        if (isset($params['id_customer_token']) && !empty($params['id_customer_token'])) {
            $data['id_customer_token'] = (int) $params['id_customer_token'];
        }

        if (isset($params['id_order']) && !empty($params['id_order'])) {
            $data['id_order'] = (int) $params['id_order'];
        }

        if (isset($params['pmt_token']) && !empty($params['pmt_token'])) {
            $data['pmt_token'] = $params['pmt_token'];
        }

        if (isset($params['message']) && !empty($params['message'])) {
            $data['message'] = $params['message'];
        }

        $update = Db::getInstance()->update('everypay_tokens', $data, $where);

        if ($update) {
            return true;
        }

        return false;
    }

    /**
     * This function will redirect (if $this->redirectOnCheck is true)
     * to the order page if any shop-module
     * related issue is found ex. the module is disabled for the
     * current order, the customer or cart is empty etc. If the private
     *
     * @param none
     * @return boolean (if not redirected)
     */
    private function _checkBeforeSend() {
        $errorfound = false;

        if (Tools::getValue('msg')) {
            $this->context->smarty->assign(array(
                'EVERYPAY_MSG' => $this->_frontMessages(Tools::getValue('msg'))
                )
            );
        }

        $redirectLocation = Context::getContext()->link->getPageLink('order', true, NULL, "step=3");

        $cart = $this->context->cart;

        if (!$this->online
                || !$this->active
                || !$cart->getNbProducts($cart->id)
                || $cart->id_customer == 0
                || $cart->id_address_delivery == 0
                || $cart->id_address_invoice == 0) {
            $errorfound = true;
        }


        // Check that this payment option is still available in case the
        // customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == $this->name) {
                $authorized = true;
                break;
            }

        if (!$authorized) {
            $errorfound = true;
        }

        //customer
        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            $errorfound = true;
        }

        //currency
        $currency_order = new Currency((int) ($cart->id_currency));
        $currencies_module = $this->getCurrency((int) $cart->id_currency);

        $allowed_currency = false;

        if (is_array($currencies_module)){
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']
                        && in_array(
                                strtoupper($currency_module['iso_code']), $this->configuration['EVERYPAY_ACCEPTED_CURRENCIES']
                        )
                ) {
                    $allowed_currency = true;
                    break;
                }
            }
        }

        if (Configuration::get('PS_CATALOG_MODE')){
            $errorfound = true;
        }

        if (!$allowed_currency) {
            $errorfound = true;
        }

        if ($errorfound && $this->redirectOnCheck) {
            Tools::redirect($redirectLocation);
        } elseif ($errorfound) {
            return false;
        }

        return true;
    }

    /*
     * Close all OPEN tokens created in the past for this customer and cart
     *
     * @param none
     * @return boolean (if closed)
     */

    private function _closePreviousOpenTokens() {
        $cart = Context::getContext()->cart;

        $where = 'status=' . self::OPEN . '
            AND id_customer=' . (int) $cart->id_customer . '
            AND id_order IS NULL
            AND id_cart=' . (int) $cart->id;

        $data = array(
            'status' => self::CLOSED,
        );

        return $update = Db::getInstance()->update('everypay_tokens', $data, $where);
    }

    /**
     * Insert a row of a created token
     *
     * @param string $crd_token
     * @param array $extra_params
     * @return int or false (if not inserted)
     */
    private function _insertCardToken($crd_token, $extra_params = array()) {
        $this->_closePreviousOpenTokens();

        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);

        $data = array(
            'id_cart' => (int) $cart->id,
            'id_customer' => (int) $cart->id_customer,
            'status' => self::OPEN,
            'id_currency' => (int) $cart->id_currency,
            'amount' => (int)Tools::ps_round($cart->getOrderTotal()*100)
        );

        if (!is_null($crd_token)) {
            $data['crd_token'] = "$crd_token";
        }

        $mergedData = array_merge($data, $extra_params);

        $insert = Db::getInstance()->insert('everypay_tokens', $mergedData);

        if ($insert) {
            return (int) Db::getInstance()->Insert_ID();
        } else {
            return false;
        }
    }

    /**
     * Insert customer details (returned from the Everypay Customer object)     *
     *
     * @param stdClass $evCustomerObj
     * @return int or false
     */
    private function _insertCardCustomer($evCustomerObj) {

        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        $isguest = (Validate::isLoadedObject($customer) && !$cart->isGuestCartByCartId($cart->id)) ? false : true;

        if ($isGuest || !$this->configuration['EVERYPAY_CUSTOMER_MODE']) {
            return false;
        }

        $insert = Db::getInstance()->insert('everypay_customers', array(
            'id_customer' => (int) $cart->id_customer,
            'card_last_four' => $evCustomerObj->card->last_four,
            'card_has_expired' => 0, //:todo
            'card_type' => $evCustomerObj->card->type,
            'cus_token' => $evCustomerObj->token,
            'exp_month' => (int) $evCustomerObj->card->expiration_month,
            'exp_year' => (int) $evCustomerObj->card->expiration_year,
                ));

        if (!$insert) {
            return false;
        }

        $insert_id = Db::getInstance()->Insert_ID();

        return (int) $insert_id;
    }

    /**
     * Get records of the Token array - filtered by the params given
     *
     * @param array $params
     * @return null
     */
    private function _getCardToken($params) {
        $sql = 'SELECT *,
' . _DB_PREFIX_ . 'everypay_customers.cus_token,
' . _DB_PREFIX_ . 'everypay_customers.id_customer,
' . _DB_PREFIX_ . 'everypay_tokens.id_customer_token,
TIME_TO_SEC(TIMEDIFF(NOW(),' . _DB_PREFIX_ . 'everypay_tokens.date)) AS secsDiff
FROM
' . _DB_PREFIX_ . 'everypay_tokens
LEFT JOIN ' . _DB_PREFIX_ . 'everypay_customers ON '
                . _DB_PREFIX_ . 'everypay_customers.id_customer_token = '
                . _DB_PREFIX_ . 'everypay_tokens.id_customer_token';

        $where = array();
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                $where[] = "$key IS NULL";
            } else {
                $where[] = "$key=" . $value;
            }
        }

        if (!empty($where)) {
            $where = ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= $where;
        //echo $sql;
        $results = Db::getInstance()->ExecuteS($sql);

        if (empty($results))
            return null;

        return $results;
    }

    /**
     * Return all the customer's saved cards
     *
     * @return array or null
     */
    private function _getCustomerCards() {
        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        $isguest = (Validate::isLoadedObject($customer) && !$cart->isGuestCartByCartId($cart->id)) ? false : true;

        if ($isguest || !$this->configuration['EVERYPAY_CUSTOMER_MODE']) {
            return null;
        }

        $sql = 'SELECT * FROM '
                . _DB_PREFIX_ . 'everypay_customers
            WHERE id_customer = ' . (int) $cart->id_customer . '
                AND card_has_expired IN (0,1)
                AND active = 1';

        $results = Db::getInstance()->ExecuteS($sql);

        if (empty($results))
            return array('valid' => array(), 'expired' => array());

        $filtered_results =
        $these_have_expired =
        $delete_ids = array();

        foreach ($results as $card) {
            $card['exp_month'] = str_pad($card['exp_month'],2, '0', STR_PAD_LEFT);
            $expiry_date = strtotime($card['exp_year'] . '-' . $card['exp_month'] . '-01');
            $now = strtotime(date('Y') . '-' . (int) date('m') . '-01');

            if ($expiry_date < $now) {
                $delete_ids[] = $card['id_customer_token'];
                $these_have_expired[] = $card;
            } else {
                $filtered_results[] = $card;
            }
        }

        if (!empty($delete_ids)) {
            $where = 'id_customer_token IN (' . implode(',', $delete_ids) . ')';

            $data = array(
                'card_has_expired' => 1
            );

            $update = Db::getInstance()->update('everypay_customers', $data, $where);
        }

        return array('valid' => $filtered_results, 'expired' => $these_have_expired);
    }

    private function _findCustomerCardById($id_customer_token) {
        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        $isguest = (Validate::isLoadedObject($customer) && !$cart->isGuestCartByCartId($cart->id)) ? false : true;

        if ($isguest || !$this->configuration['EVERYPAY_CUSTOMER_MODE']) {
            return null;
        }

        $sql = 'SELECT * FROM '
                . _DB_PREFIX_ . 'everypay_customers
            WHERE id_customer = ' . (int) $cart->id_customer . '
                AND card_has_expired IN (0, 1)
                AND active = 1
                AND id_customer_token=' . (int) $id_customer_token;

        $results = Db::getInstance()->ExecuteS($sql);

        if (empty($results))
            return null;
        return $results;
    }

    /**
     * Delete (in fact disactivate a users card)
     *
     * @param int $id_customer_token
     * @return boolean
     */
    private function _deleteCustomerCard($id_customer_token) {

        $where = 'id_customer_token=' . (int) $id_customer_token;

        $data = array(
            'active' => 0
        );

        $update = Db::getInstance()->update('everypay_customers', $data, $where);
        if ($update) {
            return true;
        }

        return false;
    }

    /**
     * The proccess to delete a card (if the form is submitted)
     *
     * @return null
     */
    private function _processDeleteCard() {
        if (Tools::isSubmit('deleteCard') && Tools::getValue('card')) {

            if (!$this->_findCustomerCardById(Tools::getValue('card'))) {
                $msg = $this->l('Could not find this card');
            } else {
                $deletion = $this->_deleteCustomerCard(Tools::getValue('card'));

                if ($deletion) {
                    $msg = $this->l('Your card has been removed');
                } else {
                    $msg = $this->l('Could not find this card');
                }
            }

            Context::getContext()->smarty->assign('msg', $msg);
        }

        return null;
    }

    /**
     * Setup the smarty variables for the card payment form -
     * either if it's a standalone form or not
     *
     * @return boolean
     */
    public function configurePaymentForm() {
        global $cookie;

        if (!$this->_checkBeforeSend()) {
            return false;
        }

        $cart     = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        $isguest  = (Validate::isLoadedObject($customer) && !$cart->isGuestCartByCartId($cart->id)) ? false : true;

        $this->context->smarty->assign(array(
            'EVERYPAY_CONFIGURATION' => $this->configuration,
            'isGuest'                => $isguest,
            'EVERYPAY_CARDS'         => $this->_getCustomerCards(),
            'EVERYPAY_FORM_ACTION'   => Context::getContext()->link->getModuleLink($this->name, 'token', array(), true),
        ));

        if ($this->configuration['EVERYPAY_BUTTON_MODE']) {
            $iso_code = Language::getIsoById((int) $cookie->id_lang);
            $products = $cart->getProducts();

            if (count($products) > 1) {
                $description = $this->l('Buy from') . ' ' . Configuration::get('PS_SHOP_NAME');
            } else {
                $description = (($products[0]['cart_quantity'] > 1)
                        ? ($products[0]['cart_quantity'] . ' × ') : '')
                        . $products[0]['name'];
                $description = Configuration::get('PS_SHOP_NAME') . ' | ' . $description;
            }

            $this->context->smarty->assign(array(
                'amountInteger' => (int) Tools::ps_round($cart->getOrderTotal() * 100),
                'description'   => $description,
                'locale'        => $iso_code
            ));
        }

        $this->context->smarty->assign(array(
            'EVERYPAY_CARD_SELECTION' => $this->_fetchTemplate('views/templates/front/card_selection.tpl')
        ));

        return true;
    }

    /*
     * *************************************************
     *              ACTION METHODS
     * ************************************************** */

    public function customerCardsPage() {
        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)
                || !$this->configuration['EVERYPAY_CUSTOMER_MODE']
                || $cart->isGuestCartByCartId($cart->id)
        ) {
            $redirectLocation = Context::getContext()->link->getPageLink('my-account', true);
            Tools::redirect($redirectLocation);
        }

        $this->_processDeleteCard();

        $data = array(
            'EVERYPAY_CARDS' => $this->_getCustomerCards(),
            'form_action' => $this->context->link->getModuleLink($this->name, 'account', array(), true)
        );

        Context::getContext()->smarty->assign($data);

        return null;
    }

    /**
     * Process the Token submitted from the card payment form. This method
     * will redirect to the according page
     *
     * @return null
     */
    public function submitToken() {

        $this->_checkBeforeSend();

        if (Tools::getValue('everypayToken')) {
            $crd_token = Tools::getValue('everypayToken');

            $params = array('crd_token' => "'" . $crd_token . "'");

            /*
             * If the token exists in the database close and redirect
             * to get a new one
             *
             */
            if ($findToken = $this->_getCardToken($params)) {
                $this->_closePreviousOpenTokens();
                $this->_redirectToCardForm();
            } else {
                $cart     = Context::getContext()->cart;
                $customer = new Customer($cart->id_customer);

                $extra_params = array();

                if ($this->configuration['EVERYPAY_CUSTOMER_MODE']
                        && Tools::getValue('remember_card') && !$customer->is_guest) {
                    $extra_params = (array(
                        'save_customer' => 1
                    ));
                }

                $insertToken = $this->_insertCardToken($crd_token, $extra_params);

                if ($insertToken) {
                    return $this->paymentConfirmation($insertToken);
                    //Tools::redirect(Context::getContext()->link->getModuleLink($this->name, 'confirm', ['token' => $insertToken], true));
                } else {
                    $this->_redirectToCardForm();
                }
            }
        } elseif (Tools::getValue('cus_id') && $this->configuration['EVERYPAY_CUSTOMER_MODE'] && Tools::isSubmit('submit_saved_card')) {
            //a customer id is given so... let's say it's 21
            //first find the customer with this id and check that it belongs to this user
            $customerRow = $this->_findCustomerTokenWithId(Tools::getValue('cus_id'));

            if (!$customerRow) {
                $this->_redirectToCardForm();
            }

            $params = array(
                'id_customer_token' => (int) Tools::getValue('cus_id')
            );

            $insertToken = $this->_insertCardToken(NULL, $params);

            if ($insertToken) {
                return $this->paymentConfirmation($insertToken);
            } else {
                $this->_redirectToCardForm();
            }
        } else {
            Tools::redirect(Context::getContext()->link->getPageLink('order', true, NULL, "step=3"));
        }

        return null;
    }

    /**
     * Setup the smarty variables for the confirmation form. Also if the form
     * is submitted do the desired actions (close token, order etc.)
     *
     * @return array (if not redirected)
     */
    public function paymentConfirmation($token) {
        $this->_checkBeforeSend();

        $tokenRow = $this->_canUseToken((int) $token);

        if (empty($token) || !$tokenRow) {
            $this->_redirectToCardForm();
        }

        // check to see if some details on cart have changed in the meanwhile
        if ($amountOfCartHasChanged = $this->_tokenDetailsHaveChanged($tokenRow)) {
            $updateAmountOnCart = $this->_updateDetailsOfToken($token);
            if ($updateAmountOnCart) {
                Tools::redirect(Context::getContext()->link->getModuleLink($this->name, 'confirm', array('token' => $token, 'msg' => 410), true));
            } else {
                $this->_redirectToCardForm();
            }
        }

        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);

        $params = array(
            'amountInteger' => (int)Tools::ps_round($cart->getOrderTotal()*100),
            'amount'        => (float) ($cart->getOrderTotal()),
            'form_action'   => Context::getContext()->link->getModuleLink($this->name, 'confirm', array('token' => $token), true),
            'cart'          => $cart,
            'customer'      => $customer,
            'tokenRow'      => $tokenRow
        );

        if (1 || Tools::isSubmit('submitConfirm')) {
            //Set the API key
            try {
                Everypay\Everypay::setApiKey($this->sk);
            } catch (Exception $e) {
                $params['message'] = $e->getMessage();
                $params['status'] = self::ERRORNEOUS;
                $this->_closeToken($params);
                Tools::redirect(Context::getContext()->link->getPageLink('order', true, NULL, "step=3"));
            }

            $evPayParams = array(
                'token'    => $tokenRow['crd_token'],
                'currency' => strtoupper($currency->iso_code)
            );

            if (!empty($tokenRow['save_customer']) //the customer wants to save his card
                    && $this->configuration['EVERYPAY_CUSTOMER_MODE']
                    && !empty($tokenRow['crd_token'])) {

                $evPayParams = array_merge($evPayParams, array(
                    'create_customer' => 1
                ));

                $evCusUpdateParams = array(
                    'full_name'   => $customer->firstname . ' ' . $customer->lastname,
                    'description' => Context::getContext()->shop->name . ' - ' . $this->l('Customer') . '#' . $customer->id,
                    'email'       => $customer->email
                );
            } elseif (!empty($tokenRow['save_customer']) //the customer wants to save his card but we disabled the customermode in the meanwhile
                    && !$this->configuration['EVERYPAY_CUSTOMER_MODE']
                    && !empty($tokenRow['crd_token'])) {
                $params['message'] = $this->l('The save card option got disabled during a payment proccess');
                $params['status'] = self::ERRORNEOUS;
                $this->_closeToken($params);
                $this->_redirectToCardForm();
            } elseif (!is_null($tokenRow['id_customer_token']) //the order is from an old customer
                    && !empty($tokenRow['cus_token'])
                    && $this->configuration['EVERYPAY_CUSTOMER_MODE']) {
                $evPayParams['token'] = $tokenRow['cus_token'];
            } elseif (!is_null($tokenRow['id_customer_token']) //the order is from an old customer but we disabled the customermode in the meanwhile
                    && !empty($tokenRow['cus_token'])
                    && !$this->configuration['EVERYPAY_CUSTOMER_MODE']) {
                $params['message'] = $this->l('The save card option got disabled during a payment proccess');
                $params['status'] = self::ERRORNEOUS;
                $this->_closeToken($params);
                $this->_redirectToCardForm();
            }

            try {
                $evPayParams = array_merge($evPayParams, array(
                    'payee_email' => $customer->email,
                    'amount'      => $params['amountInteger'],
                    'description' => Context::getContext()->shop->name . ' - ' . $this->l('Cart') . ' #' . $cart->id . ' - ' . Tools::displayPrice($cart->getOrderTotal()),
                    'capture'     => 0
                ));

                $evPayment = Everypay\Payment::create($evPayParams);

                //now update the customer if necessary
                if (isset($evCusUpdateParams) && isset($evPayment->customer->token)){
                    try {
                        $evCustomer = Everypay\Customer::update(
                                $evPayment->customer->token,
                                $evCusUpdateParams);
                        $evPayParams['token'] = $evCustomer->token;
                    } catch (Exception $e) {
                        //we didnt manage to update the customer details
                        // no big deal
                    }
                }

                //error with the payment
                if (isset($evPayment->error)) {
                    $params['message'] = $evPayment->error->message;
                    $params['status'] = self::ERRORNEOUS;
                    $this->_closeToken($params);
                    $this->_redirectToCardForm(415);
                    exit;
                }
            } catch (Exception $e) {
                $params['message'] = $e->getMessage();
                $params['status'] = self::ERRORNEOUS;
                $this->_closeToken($params);
                $this->_redirectToCardForm(415);
                exit;
            }

            $mailVars = array();


            $validateOrder = $this->validateOrder(
                    $cart->id,
                    self::ORDER_STATUS_CAPTURE_PENDING,
                    $params['amount'],
                    $this->displayName,
                    NULL,
                    $mailVars,
                    $params['cart']->id_currency,
                    false,
                    $params['customer']->secure_key);

            if ($validateOrder) {
                $params['id_order']  = $this->currentOrder;
                $params['status']    = self::SUCCESS;
                $params['pmt_token'] = $evPayment->token;
                $params['message']   = 'Success on ' . date('d/m/Y H:i:s');

                if (!is_null($tokenRow['save_customer'])
                    && $this->configuration['EVERYPAY_CUSTOMER_MODE']) {
                    $id_customer_token = $this->_insertCardCustomer($evPayment->customer);

                    if ($id_customer_token) {
                        $params['id_customer_token'] = $id_customer_token;
                    }
                }

                $_closeToken = $this->_closeToken($params);

                $order = new Order((int)($this->currentOrder));

                //capture or die
                try {
                    $evCapture = Everypay\Payment::capture($evPayment->token);
                    $order->setCurrentState(Configuration::get('PS_OS_PAYMENT'));

                    $redirect = array(
                        'controller=order-confirmation',
                        'id_cart=' . (int)($params['cart']->id),
                        'id_module=' . (int)($this->id),
                        'id_order=' . (int)($this->currentOrder),
                        'key=' . $order->secure_key
                    );

                    Tools::redirect('index.php?' . implode('&', $redirect));
                } catch (Exception $e) {
                    $this->_redirectToCardForm();
                }
            } else {
                try {
                    $evRefund = Everypay\Payment::refund($evPayment->token);
                } catch (Exception $e) {
                    //do nothing
                }
                $this->_redirectToCardForm();
            }
        }

        $this->context->smarty->assign($params);

        return $params;
    }

    /*
     * The hooks section starts here
     *
     */

    public function hookPayment() {
        $this->redirectOnCheck = false;

        if ($this->configurePaymentForm()) {
            return $this->_fetchTemplate('views/templates/hook/payment.tpl');
        }

        return null;
    }

    public function hookHeader() {
        if (Configuration::get('PS_CATALOG_MODE')){
            return null;
        }

        $allowedPages = array(
            '/order-opc.php',
            //'/modules/everypay_prestashop_1_4_x/controllers/front/form.php'
        );

        if (1 || in_array($_SERVER['PHP_SELF'], $allowedPages)) {
            $this->context->controller->addCSS($this->_path . 'assets/css/everypay.css');
            $this->context->controller->addJS($this->_path . 'assets/js/front.js');
        }

        return null;
    }

    public function hookPaymentReturn($params) {

        $order_id = Tools::getValue('id_order');

        $order = new Order($order_id);
        $state = $order->getCurrentStateFull($this->context->language->id);
        $carrier = new Carrier($order->id_carrier, $this->context->language->id);

        $this->smarty->assign(array(
            'order'         => $order,
            'total_paid'    => Tools::displayPrice($order->total_paid_tax_incl),
            'state'         => $state,
            'carrier'       => $carrier,
            'order_id_formatted' => sprintf('#%06d', $order_id)
        ));

        return $this->_fetchTemplate('views/templates/hook/payment_return.tpl');
    }

    public function hookBackOfficeHeader() {
        /*if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJQueryPlugin('fancybox');
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/assets/css/admin.css');
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/assets/js/admin.js');

            return true;
        }*/
        return null;
    }

    public function hookDisplayMyAccountBlock($in_footer = true) {
        if (!$this->configuration['EVERYPAY_CUSTOMER_MODE']) {
            return null;
        }

        $assign = array(
            'in_footer' => $in_footer
        );
        Context::getContext()->smarty->assign($assign);

        return $this->_fetchTemplate('views/templates/hook/my-account.tpl');
    }

    public function hookDisplayCustomerAccount($params) {
        return $this->hookDisplayMyAccountBlock(false);
    }

}

