<?php

require_once 'aivlgeneric.civix.php';
use CRM_Aivlgeneric_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Definition;

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function aivlgeneric_civicrm_container(ContainerBuilder $container) {
  $container->addCompilerPass(new Civi\Aivlgeneric\AivlGenericContainer());
  $container->addCompilerPass(new Civi\Aivlgeneric\DataProcessor\CompilerPass(), PassConfig::TYPE_OPTIMIZE);
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function aivlgeneric_civicrm_config(&$config) {
  _aivlgeneric_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function aivlgeneric_civicrm_xmlMenu(&$files) {
  _aivlgeneric_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function aivlgeneric_civicrm_install() {
  _aivlgeneric_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function aivlgeneric_civicrm_postInstall() {
  _aivlgeneric_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function aivlgeneric_civicrm_uninstall() {
  _aivlgeneric_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function aivlgeneric_civicrm_enable() {
  _aivlgeneric_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function aivlgeneric_civicrm_disable() {
  _aivlgeneric_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function aivlgeneric_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _aivlgeneric_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function aivlgeneric_civicrm_managed(&$entities) {
  _aivlgeneric_civix_civicrm_managed($entities);
}

/**
 * @param string $formName
 * @param CRM_Core_Form $form
 * @return void
 */
function aivlgeneric_civicrm_buildForm($formName, &$form) {
    if ($formName == 'CRM_Campaign_Form_Campaign') {
      $templatePath = E::path('templates/CRM/Aivlgeneric/Activity/campaign_parent_field.tpl');
      if (!file_exists($templatePath)) throw new Exception("Path not found!");
      //$form->add('text', 'parent_id', ts('Parent Campaign'));
      $form->addEntityRef('parent_id', ts('Parent Campaign'), [
        'entity' => 'Campaign',
        'create' => TRUE,
        'select' => ['minimumInputLength' => 0],
      ]);
      // dynamically insert a template block in the page
      CRM_Core_Region::instance('page-body')->add(['template' => $templatePath]);
    }

    return;
//    $form->addAutoSelector();
//    /**
//     * adjust campaign form to make it more efficient for AILV
//     *
//     * @see https://issues.civicoop.org/issues/10936
//     */
//    // JS adjustments
    //Civi::resources()->addScriptFile(E::SHORT_NAME, 'js/campaign_modifications.js');

    // add parent campaign picker
//    $form->addEntityRef(
//      'parent_id',
//      ts('Campaign'),
//      ['entity' => 'Campaign', 'create' => FALSE, 'select' => ['minimumInputLength' => 0]]
//    );
//
//    $campaign_id = (int) CRM_Utils_Request::retrieve('id', 'Integer');
//
//    //$campaigns = CRM_Campaign_BAO_Campaign::getCampaigns(CRM_Utils_Array::value('parent_id', []), $campaign_id);
//    $form->add('select', 'parent_id', ts('Parent ID'),
//                      ['' => ts('- select Parent -')] + $campaigns,
//                      ['class' => 'crm-select2']
//    );

    // add campaign picker html (via template)
    //$templatePath = E::path('templates/CRM/Aivlgeneric/Activity/campaign_parent_field.tpl');
    //CRM_Core_Region::instance('page-body')->add(['template' => $templatePath]);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function aivlgeneric_civicrm_caseTypes(&$caseTypes) {
  _aivlgeneric_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function aivlgeneric_civicrm_angularModules(&$angularModules) {
  _aivlgeneric_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function aivlgeneric_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _aivlgeneric_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function aivlgeneric_civicrm_entityTypes(&$entityTypes) {
  _aivlgeneric_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function aivlgeneric_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function aivlgeneric_civicrm_navigationMenu(&$menu) {
  _aivlgeneric_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _aivlgeneric_civix_navigationMenu($menu);
} // */
