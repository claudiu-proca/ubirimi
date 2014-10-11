<?php

namespace Ubirimi\Yongo\Repository\Field;

use Ubirimi\Container\UbirimiContainer;

class ConfigurationScheme {

    public $name;
    public $description;
    public $clientId;

    function __construct($clientId, $name, $description) {
        $this->clientId = $clientId;
        $this->name = $name;
        $this->description = $description;

        return $this;
    }

    public static function getByClient($clientId) {
        $query = "SELECT * " .
            "FROM issue_type_field_configuration " .
            "where client_id = ?";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows)
            return $result;
        else
            return null;
    }

    public function save($currentDate) {
        $query = "INSERT INTO issue_type_field_configuration(client_id, name, description, date_created) VALUES (?, ?, ?, ?)";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);

        $stmt->bind_param("isss", $this->clientId, $this->name, $this->description, $currentDate);
        $stmt->execute();

        return UbirimiContainer::get()['db.connection']->insert_id;
    }

    public static function addData($issueTypeFieldConfigurationId, $fieldConfigurationId, $issueTypeId, $currentDate) {
        $query = "INSERT INTO issue_type_field_configuration_data(issue_type_field_configuration_id, field_configuration_id, issue_type_id, date_created) VALUES (?, ?, ?, ?)";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("iiis", $issueTypeFieldConfigurationId, $fieldConfigurationId, $issueTypeId, $currentDate);
        $stmt->execute();
    }

    public static function deleteDataById($Id) {
        $query = "delete from issue_type_field_configuration_data where id = ? limit 1";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $Id);
        $stmt->execute();
    }

    public static function deleteDataByFieldConfigurationSchemeId($Id) {
        $query = "delete from issue_type_field_configuration_data where issue_type_field_configuration_id = ?";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $Id);
        $stmt->execute();
    }

    public static function updateDataById($fieldConfigurationId, $issueTypeFieldConfigurationId, $issueTypeId) {
        $query = "update issue_type_field_configuration_data
                    set field_configuration_id = ?
                    where issue_type_field_configuration_id = ?
                      and issue_type_id = ? limit 1";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("iii", $fieldConfigurationId, $issueTypeFieldConfigurationId, $issueTypeId);
        $stmt->execute();
    }

    public static function updateMetaDataById($Id, $name, $description, $date) {
        $query = "update issue_type_field_configuration
                    set name = ?, description = ?, date_updated = ?
                    where id = ? limit 1";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("sssi", $name, $description, $date, $Id);
        $stmt->execute();
    }

    public static function getMetaDataById($Id) {
        $query = "select * " .
            "from issue_type_field_configuration " .
            "where id = ? " .
            "limit 1";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $Id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows)
            return $result->fetch_array(MYSQLI_ASSOC);
        else
            return null;
    }

    public static function getDataByFieldConfigurationSchemeId($Id) {
        $query = "select issue_type_field_configuration_data.id, issue_type_field_configuration_data.issue_type_id, " .
            "field_configuration.name as field_configuration_name, issue_type.name as issue_type_name, issue_type_field_configuration_data.field_configuration_id " .
            "from issue_type_field_configuration_data " .
            "left join issue_type on issue_type.id = issue_type_field_configuration_data.issue_type_id " .
            "left join field_configuration on field_configuration.id = issue_type_field_configuration_data.field_configuration_id " .
            "where issue_type_field_configuration_data.issue_type_field_configuration_id = ? ";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $Id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows)
            return $result;
        else
            return null;
    }

    public static function getDataById($Id) {
        $query = "select issue_type_field_configuration_data.id, issue_type_field_configuration_data.field_configuration_id, " .
                    "issue_type_field_configuration_data.issue_type_id, " .
                    "issue_type.name as issue_type_name, field_configuration.name as field_configuration_name, issue_type_field_configuration_data.issue_type_field_configuration_id " .
                 "from issue_type_field_configuration_data " .
                 "left join issue_type on issue_type.id = issue_type_field_configuration_data.issue_type_id " .
                 "left join field_configuration on field_configuration.id = issue_type_field_configuration_data.field_configuration_id " .
                 "where issue_type_field_configuration_data.id = ? ";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $Id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows)
            return $result->fetch_array(MYSQLI_ASSOC);
        else
            return null;
    }

    public static function getFieldConfigurations($issueTypeFieldConfigurationId) {
        $query = "select field_configuration.id, field_configuration.name " .
            "from issue_type_field_configuration_data " .
            "left join field_configuration on field_configuration.id = issue_type_field_configuration_data.field_configuration_id " .
            "where issue_type_field_configuration_data.issue_type_field_configuration_id = ? " .
            "group by field_configuration.id";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $issueTypeFieldConfigurationId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows)
            return $result;
        else
            return null;
    }

    public static function getFieldConfigurationsSchemesByFieldConfigurationId($clientId, $fieldConfigurationId) {
        $query = "select issue_type_field_configuration.id, issue_type_field_configuration.name " .
            "from issue_type_field_configuration_data " .
            "left join issue_type_field_configuration on issue_type_field_configuration.id = issue_type_field_configuration_data.issue_type_field_configuration_id " .
            "where issue_type_field_configuration_data.field_configuration_id = ? and " .
            "issue_type_field_configuration.client_id = ? " .
            "group by issue_type_field_configuration_data.field_configuration_id";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("ii", $fieldConfigurationId, $clientId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows)
            return $result;
        else
            return null;
    }

    public static function getIssueTypesForFieldConfiguration($issueTypeFieldConfigurationId, $fieldConfigurationId) {
        $query = "select issue_type.id, issue_type.name " .
            "from issue_type_field_configuration_data " .
            "left join issue_type on issue_type.id = issue_type_field_configuration_data.issue_type_id " .
            "where issue_type_field_configuration_data.issue_type_field_configuration_id = ? and field_configuration_id = ?";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("ii", $issueTypeFieldConfigurationId, $fieldConfigurationId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows)
            return $result;
        else
            return null;
    }

    public static function deleteById($Id) {
        $query = "delete from issue_type_field_configuration where id = ? limit 1";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("i", $Id);
        $stmt->execute();
    }

    public static function deleteByClientId($clientId) {
        $fieldConfigurationSchemes = ConfigurationScheme::getByClient($clientId);

        while ($fieldConfigurationSchemes && $fieldConfigurationScheme = $fieldConfigurationSchemes->fetch_array(MYSQLI_ASSOC)) {
            ConfigurationScheme::deleteDataByFieldConfigurationSchemeId($fieldConfigurationScheme['id']);
            ConfigurationScheme::deleteById($fieldConfigurationScheme['id']);
        }
    }

    public static function getMetaDataByNameAndClientId($clientId, $name) {
        $query = "select * from issue_type_field_configuration where client_id = ? and LOWER(name) = ?";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("is", $clientId, $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows)
            return $result;
        else
            return null;
    }

    public static function getByIssueTypeFieldConfigurationIdAndIssueTypeId($issueTypeFieldConfigurationId, $issueTypeId) {
        $query = "select issue_type_field_configuration.id, issue_type_field_configuration.name " .
            "from issue_type_field_configuration_data " .
            "where issue_type_field_configuration_data.issue_type_field_configuration_id = ? and " .
            "issue_type_field_configuration_data.issue_type_id = ?";

        $stmt = UbirimiContainer::get()['db.connection']->prepare($query);
        $stmt->bind_param("ii", $issueTypeFieldConfigurationId, $issueTypeId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows)
            return $result->fetch_array(MYSQLI_ASSOC);
        else
            return null;
    }
}