<?php

namespace Ubirimi\Yongo\Controller\Issue;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Container\UbirimiContainer;
use Ubirimi\Event\LogEvent;
use Ubirimi\Event\UbirimiEvents;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Event\IssueEvent;
use Ubirimi\Yongo\Event\YongoEvents;
use Ubirimi\Yongo\Repository\Field\Field;
use Ubirimi\Yongo\Repository\Issue\Issue;
use Ubirimi\Yongo\Repository\Issue\IssueComment;
use Ubirimi\Yongo\Repository\Issue\IssueComponent;
use Ubirimi\Yongo\Repository\Issue\IssueCustomField;
use Ubirimi\Yongo\Repository\Issue\IssueVersion;

class EditController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $issueId = $request->get('issue_id');
        $fieldTypes = $request->get('field_types');
        $fieldValues = $request->get('field_values');

        $loggedInUserId = $session->get('user/id');

        $fieldTypesCustom = $request->get('field_types_custom');
        $fieldValuesCustom = $request->get('field_values_custom');

        $attachIds = $request->get('attach_ids');
        $attachIdsToBeKept = $request->get('attach_ids');

        if (!is_array($attachIdsToBeKept))
            $attachIdsToBeKept = array();

        $newIssueData = array();
        $newIssueCustomFieldsData = array();

        $oldIssueData = Issue::getByParameters(array('issue_id' => $issueId), $loggedInUserId);
        $projectId = $oldIssueData['issue_project_id'];

        $oldIssueData['component'] = IssueComponent::getByIssueIdAndProjectId($issueId, $projectId, 'array', 'name');
        if ($oldIssueData['component'] == null) {
            $oldIssueData['component'] = array();
        }
        $oldIssueData['affects_version'] = IssueVersion::getByIssueIdAndProjectId($issueId, $projectId, Issue::ISSUE_AFFECTED_VERSION_FLAG, 'array', 'name');
        if ($oldIssueData['affects_version'] == null) {
            $oldIssueData['affects_version'] = array();
        }
        $oldIssueData['fix_version'] = IssueVersion::getByIssueIdAndProjectId($issueId, $projectId, Issue::ISSUE_FIX_VERSION_FLAG, 'array', 'name');
        if ($oldIssueData['fix_version'] == null) {
            $oldIssueData['fix_version'] = array();
        }

        for ($i = 0; $i < count($fieldTypes); $i++) {
            if ($fieldValues[$i] != 'null' && $fieldValues[$i] != '') {
                if ($fieldTypes[$i] == Field::FIELD_DUE_DATE_CODE) {
                    $fieldValues[$i] = \DateTime::createFromFormat('Y-m-d', $fieldValues[$i])->format('Y-m-d');
                }
                $newIssueData[$fieldTypes[$i]] = $fieldValues[$i];
            } else
                $newIssueData[$fieldTypes[$i]] = null;
        }

        if ($fieldTypesCustom) {
            for ($i = 0; $i < count($fieldTypesCustom); $i++) {
                if ($fieldValuesCustom[$i] != 'null' && $fieldValuesCustom[$i] != '') {
                    $newIssueCustomFieldsData[$fieldTypesCustom[$i]] = $fieldValuesCustom[$i];
                } else {
                    $newIssueCustomFieldsData[$fieldTypesCustom[$i]] = null;
                }
            }
        }

        if (array_key_exists(Field::FIELD_ASSIGNEE_CODE, $newIssueData)) {
            // assignee field is placed on screen
            if ($newIssueData[Field::FIELD_ASSIGNEE_CODE] == -1) {
                $newIssueData[Field::FIELD_ASSIGNEE_CODE] = null;
            }
        }

        if (!isset($newIssueData[Field::FIELD_STATUS_CODE])) {
            $newIssueData[Field::FIELD_STATUS_CODE] = $oldIssueData[Field::FIELD_STATUS_CODE];
        }

        $currentDate = Util::getServerCurrentDateTime();
        Issue::updateById($issueId, $newIssueData, $currentDate);

        $oldIssueCustomFieldsData = array();
        foreach ($newIssueCustomFieldsData as $key => $value) {
            $keyData = explode("_", $key);

            $oldIssueCustomFieldsData[$keyData[0]] = IssueCustomField::getCustomFieldsDataByFieldId($issueId, $key);
            unset($newIssueCustomFieldsData[$key]);
            $newIssueCustomFieldsData[$keyData[0]] = $value;
        }

        $fieldChanges = Issue::computeDifference($oldIssueData, $newIssueData, $oldIssueCustomFieldsData, $newIssueCustomFieldsData);

        Issue::updateHistory($issueId, $loggedInUserId, $fieldChanges, $currentDate);

        // check if on the modal there is a comment field
        if (array_key_exists(Field::FIELD_COMMENT_CODE, $newIssueData) && !empty($newIssueData[Field::FIELD_COMMENT_CODE])) {
            IssueComment::add($issueId, $loggedInUserId, $newIssueData[Field::FIELD_COMMENT_CODE], $currentDate);
        }

        // update the custom fields value
        if ($fieldTypesCustom) {
            IssueCustomField::updateCustomFieldsData($issueId, $newIssueCustomFieldsData, $currentDate);
        }

        Util::manageModalAttachments($issueId, $loggedInUserId, $attachIdsToBeKept);

        $issueEvent = new IssueEvent(null, null, IssueEvent::STATUS_UPDATE, array('oldIssueData' => $oldIssueData, 'fieldChanges' => $fieldChanges));
        $issueLogEvent = new LogEvent(SystemProduct::SYS_PRODUCT_YONGO, 'UPDATE Yongo issue ' . $oldIssueData['project_code'] . '-' . $oldIssueData['nr']);

        UbirimiContainer::get()['dispatcher']->dispatch(YongoEvents::YONGO_ISSUE_EMAIL, $issueEvent);
        UbirimiContainer::get()['dispatcher']->dispatch(UbirimiEvents::LOG, $issueLogEvent);

        return new Response('');
    }
}