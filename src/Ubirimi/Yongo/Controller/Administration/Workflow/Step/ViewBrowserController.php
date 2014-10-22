<?php

namespace Ubirimi\Yongo\Controller\Administration\Workflow\Step;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class ViewBrowserController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $clientId = $session->get('client/id');
        $stepId = $request->get('id');

        $step = $this->getRepository('yongo.workflow.workflow')->getStepById($stepId);
        $workflowId = $step['workflow_id'];
        $workflow = $this->getRepository('yongo.workflow.workflow')->getMetaDataById($workflowId);

        if ($workflow['client_id'] != $clientId) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }

        $menuSelectedCategory = 'issue';

        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Workflow Step Browser';

        require_once __DIR__ . '/../../../../Resources/views/administration/workflow/step/ViewBrowser.php';
    }
}