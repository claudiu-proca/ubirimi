<?php

namespace Ubirimi\Yongo\Controller\Administration\Field\Configuration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\UbirimiController;
use Ubirimi\Repository\Log;
use Ubirimi\SystemProduct;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Field\FieldConfiguration;

class DeleteController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $Id = $request->request->get('id');
        $fieldConfiguration = FieldConfiguration::getMetaDataById($Id);

        FieldConfiguration::deleteDataByFieldConfigurationId($Id);
        FieldConfiguration::deleteById($Id);

        $currentDate = Util::getServerCurrentDateTime();
        Log::add(
            $session->get('client/id'),
            SystemProduct::SYS_PRODUCT_YONGO,
            $session->get('user/id'),
            'DELETE Yongo Field Configuration ' . $fieldConfiguration['name'],
            $currentDate
        );

        return new Response('');
    }
}
