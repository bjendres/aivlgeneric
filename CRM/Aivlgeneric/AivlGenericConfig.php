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
  private $_toCheckActivityTypeId = NULL;
  private $_expiredMembershipStatusId = NULL;
  private $_prefixOptionGroupId = NULL;
  private $_maleGenderId = NULL;
  private $_femaleGenderId = NULL;

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

}
