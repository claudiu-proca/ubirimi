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

namespace Ubirimi\LoginTimeService;

use Ubirimi\Container\UbirimiContainer;
use Ubirimi\Repository\General\UbirimiClient;
use Ubirimi\Repository\User\UbirimiUser;
use Ubirimi\Util;

class LoginTimeService
{
    public function clientSaveLoginTime($clientId)
    {
        $datetime = Util::getServerCurrentDateTime();

        UbirimiContainer::get()['repository']->get(UbirimiClient::class)->updateLoginTime($clientId, $datetime);
    }

    public function userSaveLoginTime($userId)
    {
        $datetime = Util::getServerCurrentDateTime();

        UbirimiContainer::get()['repository']->get(UbirimiUser::class)->updateLoginTime($userId, $datetime);
    }
}