<?php

/*
 *  Copyright (C) 2012-2014 SC Ubirimi SRL <info-copyright@ubirimi.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Ubirimi\HelpDesk\Controller\Administration\Organization;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\HelpDesk\Repository\Organization\Organization;
use Ubirimi\Repository\General\UbirimiLog;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class EditController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $emptyName = false;
        $duplicateOrganization = false;

        $organizationId = $request->get('id');
        $organization = $this->getRepository(Organization::class)->getById($organizationId);

        if ($request->request->has('edit_organization')) {
            $name = Util::cleanRegularInputField($request->request->get('name'));
            $description = Util::cleanRegularInputField($request->request->get('description'));

            if (empty($name)) {
                $emptyName = true;
            }

            $organizationDuplicate = $this->getRepository(Organization::class)->getByName(
                $session->get('client/id'), mb_strtolower($name),
                $organizationId
            );

            if ($organizationDuplicate) {
                $duplicateOrganization = true;
            }

            if (!$emptyName && !$organizationDuplicate) {
                $currentDate = Util::getServerCurrentDateTime();
                $this->getRepository(Organization::class)->updateById($organizationId, $name, $description, $currentDate);

                $this->getLogger()->addInfo('UPDATE Organization ' . $name, $this->getLoggerContext());

                return new RedirectResponse('/helpdesk/administration/organizations');
            }
        }

        $menuSelectedCategory = 'helpdesk_organizations';
        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_HELP_DESK_NAME. ' / Create Organization';

        return $this->render(__DIR__ . '/../../../Resources/views/administration/organization/EditOrganization.php', get_defined_vars());
    }
}
