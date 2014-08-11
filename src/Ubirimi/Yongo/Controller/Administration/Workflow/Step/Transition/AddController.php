<?php

namespace Ubirimi\Yongo\Controller\Administration\Workflow\Step\Transition;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Workflow\Workflow;
use Ubirimi\Repository\Log;
use Ubirimi\Yongo\Repository\Issue\IssueEvent;
use Ubirimi\Yongo\Repository\Issue\IssueSettings;
use Ubirimi\Yongo\Repository\Screen\Screen;
use Ubirimi\Yongo\Repository\Workflow\WorkflowFunction;

class AddController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $workflowStepId = $request->get('id');

        $workflowStep = Workflow::getStepById($workflowStepId);
        $workflowId = $workflowStep['workflow_id'];
        $steps = Workflow::getSteps($workflowId);

        $workflowMetadata = Workflow::getMetaDataById($workflowId);
        if ($workflowMetadata['client_id'] != $session->get('client/id')) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }

        $workflowSteps = Workflow::getSteps($workflowId);
        $statuses = IssueSettings::getAllIssueSettings('status', $session->get('client/id'));
        $screens = Screen::getAll($session->get('client/id'));

        $emptyName = false;

        if ($request->request->has('add_transition')) {
            $name = Util::cleanRegularInputField($request->request->get('name'));
            $description = Util::cleanRegularInputField($request->request->get('description'));
            $step = $request->request->get('step');
            $screen = $request->request->get('screen');

            if (empty($name))
                $emptyName = true;

            if (!$emptyName) {
                $currentDate = Util::getServerCurrentDateTime();

                $transitionId = Workflow::addTransition(
                    $workflowId,
                    $screen,
                    $workflowStepId,
                    $step,
                    $name,
                    $description
                );

                Workflow::addPostFunctionToTransition(
                    $transitionId,
                    WorkflowFunction::FUNCTION_SET_ISSUE_STATUS_AS_IN_WORKFLOW_STEP,
                    'set_issue_status'
                );

                Workflow::addPostFunctionToTransition(
                    $transitionId,
                    WorkflowFunction::FUNCTION_UPDATE_ISSUE_CHANGE_HISTORY,
                    'update_issue_history'
                );

                $eventId = IssueEvent::getByClientIdAndCode(
                    $session->get('client/id'),
                    IssueEvent::EVENT_GENERIC_CODE,
                    'id'
                );

                Workflow::addPostFunctionToTransition(
                    $transitionId,
                    WorkflowFunction::FUNCTION_FIRE_EVENT,
                    'event=' . $eventId
                );

                Log::add(
                    $session->get('client/id'),
                    SystemProduct::SYS_PRODUCT_YONGO,
                    $session->get('user/id'),
                    'ADD Yongo Workflow Transition' ,
                    $currentDate
                );

                return new RedirectResponse('/yongo/administration/workflow/view-as-text/' . $workflowId);
            }
        }

        $menuSelectedCategory = 'issue';
        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Create Workflow Transition';

        return $this->render(__DIR__ . '/../../../../../Resources/views/administration/workflow/step/transition/Add.php', get_defined_vars());
    }
}