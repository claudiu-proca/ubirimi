<?php

namespace Ubirimi\Yongo\Controller\Administration\Field\Configuration;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\UbirimiController;
use Ubirimi\SystemProduct;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Field\FieldConfiguration;
use Ubirimi\Yongo\Repository\Field\Field;

class EditConfigController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();
        $fieldConfigurationId = $request->get('field_configuration_id');
        $fieldId = $request->get('id');

        $fieldConfiguration = FieldConfiguration::getMetaDataById($fieldConfigurationId);

        if ($fieldConfiguration['client_id'] != $session->get('client/id')) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }

        $fieldConfigurationData = FieldConfiguration::getDataByConfigurationAndField($fieldConfigurationId, $fieldId);
        $description = $fieldConfigurationData['field_description'];
        $field = Field::getById($fieldId);

        if ($field['client_id'] != $session->get('client/id')) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }

        $menuSelectedCategory = 'issue';

        if ($request->request->has('edit_field_configuration')) {
            $description = $request->request->get('description');
            FieldConfiguration::updateFieldDescription($fieldConfigurationId, $fieldId, $description);

            return new RedirectResponse('/yongo/administration/field-configuration/edit/' . $fieldConfigurationId);
        }

        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Update Field';

        return $this->render(__DIR__ . '/../../../../Resources/views/administration/field/configuration/EditConfig.php', get_defined_vars());
    }
}
