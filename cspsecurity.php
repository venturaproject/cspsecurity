<?php
/*
 * 2007-2016 PrestaShop
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
 *  @author    Prestashop <15719484+venturaproject@users.noreply.github.com> 
 *  @copyright  2007-2016 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}



class CspSecurity extends Module
{


    public function __construct()
    {
        $this->name = 'cspsecurity';
        $this->author = 'PrestaShop';
        $this->version = '1.1.0';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->getTranslator()->trans('CSP Security', [], 'Modules.CspSecurity.Admin');

        $this->description = $this->getTranslator()->trans('Add a content security policy to prevent JS attacks.', [], 'Modules.CspSecurity.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);

    }

    public function install()
    {

        $this->createConfig();

        return parent::install()
        && $this->registerHook('displayHeader');

    }

    public function uninstall()
    {

        $this->removeConfig();

        return parent::uninstall();
    }


    public function isUsingNewTranslationSystem()
    {
        return true;
    }


    public function createConfig()
    {

        $response = Configuration::updateValue('CSP_SECURITY_ENABLED', true);

        return $response;
    }

    public function removeConfig()
    {

        $response = Configuration::deleteByName('CSP_SECURITY_ENABLED');

        return $response;
    }

    public function getContent()
    {
        $output = '';
        $errors = [];

        if (Tools::isSubmit('submit' . $this->name)) {
            $errors = $this->validateForm();

            if (empty($errors)) {
                $this->updateConfiguration();
                $output = $this->displayConfirmation($this->getTranslator()->trans('The settings have been updated.', [], 'Admin.Notifications.Success'));
            } else {
                $output = $this->displayError(implode('<br />', $errors));
            }
        }

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    protected function validateForm()
    {
        $errors = [];

        $rand = Tools::getValue('CSP_SECURITY_ENABLED');

        if (!Validate::isBool($rand)) {
            $errors[] = $this->getTranslator()->trans('Invalid selected value', [], 'Modules.CspSecurity.Admin');
        }

        return $errors;
    }

    protected function updateConfiguration()
    {
        Configuration::updateValue('CSP_SECURITY_ENABLED', (bool) Tools::getValue('CSP_SECURITY_ENABLED'));

    }

    public function renderForm()
    {

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTranslator()->trans('Configuration', [], 'Modules.CspSecurity.Admin'),
                    'icon' => 'icon-cogs',
                ),

                'input' => array(array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Enable CSP', [], 'Modules.CspSecurity.Admin'),
                    'name' => 'CSP_SECURITY_ENABLED',
                    'class' => 'fixed-width-xs',
                    'desc' => $this->getTranslator()->trans('Enabled the Content-Security-Policy meta tag will be displayed in the web source code.', [], 'Modules.CspSecurity.Admin'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', [], 'Admin.Global'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', [], 'Admin.Global'),
                        ),
                    ),
                ),


                ),
                'submit' => array(
                    'title' => $this->getTranslator()->trans('Save', [], 'Admin.Actions'),
                ),
            ),
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {

        return array(

            'CSP_SECURITY_ENABLED' => Tools::getValue('CSP_SECURITY_ENABLED', (bool) Configuration::get('CSP_SECURITY_ENABLED')),

        );

    }
    

    public function hookDisplayHeader($params)
    {
        if (!Configuration::get('CSP_SECURITY_ENABLED')) {
         return;
        }
        
        return $this->display(__FILE__, 'views/templates/hook/cspsecurity.tpl');

    }


}
