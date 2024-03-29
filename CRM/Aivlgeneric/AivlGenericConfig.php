<?php
use CRM_Aivlgeneric_ExtensionUtil as E;

/**
 * Class for Amnesty International Vlaanderen Configuration (with container usage)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 25 juni 2020
 * @license AGPL-3.0
 */
class CRM_AivlGeneric_AivlGenericConfig {
  /**
   * @var CRM_AivlGeneric_AivlGenericConfig
   */
  protected static $singleton;

  private $_aivlContactId = NULL;
  private $_aivlEmployees = [];
  private $_assigneeRecordTypeId = NULL;
  private $_callAssignmentActivityTypeId = NULL;
  private $_completedActivityStatusId = NULL;
  private $_scheduledActivityStatusId = NULL;
  private $_defaultLocationTypeId = NULL;
  private $_employeeRelationshipTypeId = NULL;
  private $_expiredMembershipStatusId = NULL;
  private $_femaleGenderId = NULL;
  private $_mailingListGroupTypeId = NULL;
  private $_mailingListGroupTypeName = NULL;
  private $_maleGenderId = NULL;
  private $_mobilePhoneTypeId = NULL;
  private $_petitionCampaignTypeId = NULL;
  private $_phonePhoneTypeId = NULL;
  private $_prefixOptionGroupId = NULL;
  private $_sourceRecordTypeId = NULL;
  private $_targetRecordTypeId = NULL;
  private $_tmCaAgencyReferenceCustomFieldId = NULL;
  private $_tmCaCallDateCustomFieldId = NULL;
  private $_tmCallAssignmentTable = NULL;
  private $_tmCaResultAmountCustomFieldId = NULL;
  private $_tmCaResultAgentCustomFieldId = NULL;
  private $_tmCaResultCodeCustomFieldId = NULL;
  private $_tmCaResultEndDateCustomFieldId = NULL;
  private $_tmCaResultFreqIntervalCustomFieldId = NULL;
  private $_tmCaResultFreqUnitCustomFieldId = NULL;
  private $_tmCaResultIbanCustomFieldId = NULL;
  private $_tmCaResultMandateCodeColumn = NULL;
  private $_tmCaResultMandateCodeNewCustomFieldId = NULL;
  private $_tmCaResultReasonNoCustomFieldId = NULL;
  private $_tmCaResultStartDateCustomFieldId = NULL;
  private $_tmCaResultUploadDateCustomFieldId = NULL;
  private $_tmCaResultCodeSddCustomFieldId = NULL;
  private $_toCheckActivityTypeId = NULL;
  private $_welkomstPakketActivityTypeId = NULL;

  /**
   * CRM_AivlGeneric_AivlGenericConfig constructor.
   */
  public function __construct() {
    if (!self::$singleton) {
      self::$singleton = $this;
    }
  }

  /**
   * @return \CRM_AivlGeneric_AivlGenericConfig
   */
  public static function getInstance() {
    if (!self::$singleton) {
      self::$singleton = new CRM_AivlGeneric_AivlGenericConfig();
    }
    return self::$singleton;
  }

  /**
   * @param int
   */
  public function setAivlContactId($id) {
    $this->_aivlContactId = $id;
  }

  /**
   * @return int
   */
  public function getAivlContactId() {
    return $this->_aivlContactId;
  }

  /**
   * @param int
   */
  public function setCompletedActivityStatusId($id) {
    $this->_completedActivityStatusId = $id;
  }

  /**
   * @return int
   */
  public function getCompletedActivityStatusId() {
    return $this->_completedActivityStatusId;
  }

  /**
   * @param int
   */
  public function setScheduledActivityStatusId($id) {
    $this->_scheduledActivityStatusId = $id;
  }

  /**
   * @return int
   */
  public function getScheduledActivityStatusId() {
    return $this->_scheduledActivityStatusId;
  }

  /**
   * @param int
   */
  public function setExpiredMembershipStatusId($id) {
    $this->_expiredMembershipStatusId = $id;
  }

  /**
   * @return int
   */
  public function getExpiredMembershipStatusId() {
    return $this->_expiredMembershipStatusId;
  }

  /**
   * @param int
   */
  public function setToCheckActivityTypeId($id) {
    $this->_toCheckActivityTypeId = $id;
  }

  /**
   * @return int
   */
  public function getToCheckActivityTypeId() {
    return $this->_toCheckActivityTypeId;
  }

  /**
   * @param int
   */
  public function setCallAssignmentActivityTypeId($id) {
    $this->_callAssignmentActivityTypeId = $id;
  }

  /**
   * @return int
   */
  public function getCallAssignmentActivityTypeId() {
    return $this->_callAssignmentActivityTypeId;
  }

  /**
   * @param int
   */
  public function setWelkomstPakketActivityTypeId($id) {
    $this->_welkomstPakketActivityTypeId = $id;
  }

  /**
   * @return int
   */
  public function getWelkomstPakketActivityTypeId() {
    return $this->_welkomstPakketActivityTypeId;
  }

  /**
   * @param int
   */
  public function setPrefixOptionGroupId($id) {
    $this->_prefixOptionGroupId = $id;
  }

  /**
   * @return int
   */
  public function getPrefixOptionGroupId() {
    return $this->_prefixOptionGroupId;
  }

  /**
   * @param int
   */
  public function setFemaleGenderId($id) {
    $this->_femaleGenderId = $id;
  }

  /**
   * @return int
   */
  public function getFemaleGenderId() {
    return $this->_femaleGenderId;
  }

  /**
   * @param int
   */
  public function setMaleGenderId($id) {
    $this->_maleGenderId = $id;
  }

  /**
   * @return int
   */
  public function getMaleGenderId() {
    return $this->_maleGenderId;
  }

  /**
   * @param int
   */
  public function setAssigneeRecordTypeId($id) {
    $this->_assigneeRecordTypeId = $id;
  }

  /**
   * @return int
   */
  public function getAssigneeRecordTypeId() {
    return $this->_assigneeRecordTypeId;
  }

  /**
   * @param int
   */
  public function setSourceRecordTypeId($id) {
    $this->_sourceRecordTypeId = $id;
  }

  /**
   * @return int
   */
  public function getSourceRecordTypeId() {
    return $this->_sourceRecordTypeId;
  }

  /**
   * @param int
   */
  public function setTargetRecordTypeId($id) {
    $this->_targetRecordTypeId = $id;
  }

  /**
   * @return int
   */
  public function getTargetRecordTypeId() {
    return $this->_targetRecordTypeId;
  }

  /**
   * @param string
   */
  public function setTmCaResultMandateCodeColumn($columnName) {
    $this->_tmCaResultMandateCodeColumn = $columnName;
  }

  /**
   * @return string
   */
  public function getTmCaResultMandateCodeColumn() {
    return $this->_tmCaResultMandateCodeColumn;
  }

  /**
   * @param int
   */
  public function setTmCaResultMandateCodeNewCustomFieldId($id) {
    $this->_tmCaResultMandateCodeNewCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultMandateCodeNewCustomFieldId() {
    return $this->_tmCaResultMandateCodeNewCustomFieldId;
  }

  /**
   * @param string
   */
  public function setTmCallAssignmentTable($tableName) {
    $this->_tmCallAssignmentTable = $tableName;
  }

  /**
   * @return string
   */
  public function getTmCallAssignmentTable() {
    return $this->_tmCallAssignmentTable;
  }

  /**
   * @param int
   */
  public function setTmCaResultCodeCustomFieldId($id) {
    $this->_tmCaResultCodeCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultCodeCustomFieldId() {
    return $this->_tmCaResultCodeCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultAgentCustomFieldId($id) {
    $this->_tmCaResultAgentCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultAgentCustomFieldId() {
    return $this->_tmCaResultAgentCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultReasonNoCustomFieldId($id) {
    $this->_tmCaResultReasonNoCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultReasonNoCustomFieldId() {
    return $this->_tmCaResultReasonNoCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaCallDateCustomFieldId($id) {
    $this->_tmCaCallDateCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaCallDateCustomFieldId() {
    return $this->_tmCaCallDateCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultUploadDateCustomFieldId($id) {
    $this->_tmCaResultUploadDateCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultUploadDateCustomFieldId() {
    return $this->_tmCaResultUploadDateCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaAgencyReferenceCustomFieldId($id) {
    $this->_tmCaAgencyReferenceCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaAgencyReferenceCustomFieldId() {
    return $this->_tmCaAgencyReferenceCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultAmountCustomFieldId($id) {
    $this->_tmCaResultAmountCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultAmountCustomFieldId() {
    return $this->_tmCaResultAmountCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultFreqIntervalCustomFieldId($id) {
    $this->_tmCaResultFreqIntervalCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultFreqIntervalCustomFieldId() {
    return $this->_tmCaResultFreqIntervalCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultFreqUnitCustomFieldId($id) {
    $this->_tmCaResultFreqUnitCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultFreqUnitCustomFieldId() {
    return $this->_tmCaResultFreqUnitCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultIbanCustomFieldId($id) {
    $this->_tmCaResultIbanCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultIbanCustomFieldId() {
    return $this->_tmCaResultIbanCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultEndDateCustomFieldId($id) {
    $this->_tmCaResultEndDateCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultEndDateCustomFieldId() {
    return $this->_tmCaResultEndDateCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultStartDateCustomFieldId($id) {
    $this->_tmCaResultStartDateCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultStartDateCustomFieldId() {
    return $this->_tmCaResultStartDateCustomFieldId;
  }

  /**
   * @param int
   */
  public function setTmCaResultCodeSddCustomFieldId(int $id) {
    $this->_tmCaResultCodeSddCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getTmCaResultCodeSddCustomFieldId() {
    return $this->_tmCaResultCodeSddCustomFieldId;
  }

  /**
   * @param int
   */
  public function setPetitionCampaignTypeId($id) {
    $this->_petitionCampaignTypeId = $id;
  }

  /**
   * @return int
   */
  public function getPetitionCampaignTypeId() {
    return $this->_petitionCampaignTypeId;
  }

  /**
   * @param int
   */
  public function setMailingListGroupTypeId($id) {
    $this->_mailingListGroupTypeId = $id;
  }

  /**
   * @return int
   */
  public function getMailingListGroupTypeId() {
    return $this->_mailingListGroupTypeId;
  }

  /**
   * @param int
   */
  public function setWelkomstPakketTypeCustomFieldId($id) {
    $this->_welkomstPakketTypeCustomFieldId = $id;
  }

  /**
   * @return int
   */
  public function getWelkomstPakketTypeCustomFieldId() {
    return $this->_welkomstPakketTypeCustomFieldIdmailingListGroupTypeId;
  }

  /**
   * @param string
   */
  public function setMailingListGroupTypeName($name) {
    $this->_mailingListGroupTypeName = $name;
  }

  /**
   * @return string
   */
  public function getMailingListGroupTypeName() {
    return $this->_mailingListGroupTypeName;
  }

  /**
   * @param array
   */
  public function setAivlEmployees($contacts) {
    $this->_aivlEmployees = $contacts;
  }

  /**
   * @return array
   */
  public function getAivlEmployees() {
    return $this->_aivlEmployees;
  }

  /**
   * @param int|null $id
   * @return void
   */
  public function setDefaultLocationTypeId(?int $id) {
    $this->_defaultLocationTypeId = $id;
  }

  /**
   * @return int|null
   */
  public function getDefaultLocationTypeId(): ?int {
    return $this->_defaultLocationTypeId;
  }

  /**
   * @param int|null $id
   * @return void
   */
  public function setMobilePhoneTypeId(?int $id) {
    $this->_mobilePhoneTypeId = $id;
  }

  /**
   * @return int|null
   */
  public function getMobilePhoneTypeId(): ?int {
    return $this->_mobilePhoneTypeId;
  }

  /**
   * @param int|null $id
   * @return void
   */
  public function setPhonePhoneTypeId(?int $id) {
    $this->_phonePhoneTypeId = $id;
  }

  /**
   * @return int|null
   */
  public function getPhonePhoneTypeId(): ?int {
    return $this->_phonePhoneTypeId;
  }

  /**
   * @param int|null $id
   * @return void
   */
  public function setEmployeeRelationshipTypeId(?int $id) {
    $this->_employeeRelationshipTypeId = $id;
  }

  /**
   * @return int|null
   */
  public function getEmployeeRelationshipTypeId(): ?int {
    return $this->_employeeRelationshipTypeId;
  }

  /**
   * Method to create a to check activity
   *
   * @param $activityData
   * @throws CiviCRM_API3_Exception
   */
  public function createToCheckActivity($activityData) {
    // default source contact id
    if (!isset($activityData['source_contact_id'])) {
      $activityData['source_contact_id'] = 'user_contact_id';
    }
    // default subject
    if (!isset($activityData['subject'])) {
      $activityData['subject'] = "To Check";
    }
    // default status
    if (!isset($activityData['status_id'])) {
      $activityData['status_id'] = "Scheduled";
    }
    $activityData['activity_type_id'] = $this->getToCheckActivityTypeId();
    try {
      civicrm_api3('Activity', 'create', $activityData);
    }
    catch (CiviCRM_API3_Exception $ex) {
      throw new API_Exception(E::ts("Could not create a To Check activity in ") . __METHOD__
        . E::ts(", error from API Activity create: ") . $ex->getMessage());
    }
  }

  /**
   * uses SMARTY to render a template
   *
   * @return string
   */
  public function renderTemplate($templatePath, $vars) {
    $smarty = CRM_Core_Smarty::singleton();
    // first backup original variables, since smarty instance is a singleton
    $oldVars = $smarty->get_template_vars();
    $backupFrame = array();
    foreach ($vars as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $backupFrame[$key] = isset($oldVars[$key]) ? $oldVars[$key] : NULL;
    }
    // then assign new variables
    foreach ($vars as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $smarty->assign($key, $value);
    }
    // create result
    $result =  $smarty->fetch($templatePath);
   // reset smarty variables
    foreach ($backupFrame as $key => $value) {
      $key = str_replace(' ', '_', $key);
      $smarty->assign($key, $value);
    }
    return $result;
  }

  /**
   * Method to get a contacts display name
   *
   * @param int|null $contactId
   * @return string|null
   */
  public function getContactDisplayName(?int $contactId): ?string {
    $displayName = NULL;
    if ($contactId) {
      try {
        $contact = \Civi\Api4\Contact::get()
          ->addSelect('display_name')
          ->addWhere('id', '=', $contactId)
          ->setLimit(1)
          ->setCheckPermissions(FALSE)->execute()->first();
        if (isset($contact['display_name'])) {
          $displayName = $contact['display_name'];
        }
      }
      catch (API_Exception $ex) {
      }
    }
    return $displayName;
  }

  /**
   * Method to create contact URL
   *
   * @param int|null $contactId
   * @param $absolute
   * @return string|null
   */
  public function setContactUrl(?int $contactId, $absolute = FALSE): ?string {
    $contactUrl = NULL;
    if ($contactId) {
      $contactUrl = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$contactId, $absolute);
    }
    return $contactUrl;
  }

  /**
   * Method to get option value label
   *
   * @param string $optionGroupName
   * @param int $value
   * @return string|null
   */
  public function getOptionValueLabel(string $optionGroupName, int $value): ?string {
    $foundLabel = NULL;
    try {
      $optionValue = \Civi\Api4\OptionValue::get()
        ->addSelect('label')
        ->addWhere('option_group_id:name', '=', $optionGroupName)
        ->addWhere('value', '=', $value)
        ->setLimit(1)
        ->setCheckPermissions(FALSE)->execute()->first();
      if ($optionValue['label']) {
        $foundLabel = $optionValue['label'];
      }
    }
    catch (API_Exception $ex) {
    }
    return $foundLabel;
  }

  /**
   * Method to get option value list for option group
   *
   * @param string $optionGroupName
   * @return array
   */
  public function getOptionGroupList(string $optionGroupName): array {
    $optionGroupList = [];
    try {
      $optionValues = \Civi\Api4\OptionValue::get()
        ->addSelect('label', 'value')
        ->addWhere('option_group_id:name', '=', $optionGroupName)
        ->addWhere('is_active', '=', TRUE)
        ->setCheckPermissions(FALSE)->execute();
      foreach ($optionValues as $optionValue) {
        if ($optionValue['value'] && $optionValue['label']) {
          $optionGroupList[$optionValue['value']] = $optionValue['label'];
        }
      }
    }
    catch (API_Exception $ex) {
    }
    return $optionGroupList;
  }

}
