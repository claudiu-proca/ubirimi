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

namespace Ubirimi\Yongo\Controller\Issue\Attachment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Issue\IssueComment;

class SaveController extends UbirimiController
{

    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $issueId = $request->request->get('issue_id');
        $attachIdsToBeKept = $request->request->get('attach_ids');
        $comment = Util::cleanRegularInputField($request->request->get('comment'));

        if (!is_array($attachIdsToBeKept)) {
            $attachIdsToBeKept = array();
        }

        Util::manageModalAttachments($issueId, $session->get('user/id'), $attachIdsToBeKept);

        if (!empty($comment)) {
            $currentDate = Util::getServerCurrentDateTime();
            $this->getRepository(IssueComment::class)->add($issueId, $session->get('user/id'), $comment, $currentDate);
        }

        return new Response('');
    }
}